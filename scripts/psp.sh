#!/usr/bin/env bash
set -Eeuo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd -- "$SCRIPT_DIR/.." && pwd)"
cd "$REPO_ROOT"

SSH_HOST="${PSP_SSH_HOST:-u447523265@186.244.144.174}"
SSH_PORT="${PSP_SSH_PORT:-65002}"
REMOTE_REPO="${PSP_REMOTE_REPO:-~/repos/atitec}"
REMOTE_APP="${PSP_REMOTE_APP:-~/domains/atitec.com.br}"
REMOTE_PUBLIC="${PSP_REMOTE_PUBLIC:-~/domains/atitec.com.br/public_html}"
REMOTE_PHP="${PSP_REMOTE_PHP:-/opt/alt/php84/usr/bin/php}"
REMOTE_COMPOSER="${PSP_REMOTE_COMPOSER:-/usr/local/bin/composer}"
BRANCH="${PSP_BRANCH:-main}"
PROJECT="${PSP_PROJECT:-atitec}"
APP_URL="${PSP_APP_URL:-https://atitec.com.br}"
LOCAL_CONTAINER="${PSP_CONTAINER:-atitec-laravel.test-1}"

MESSAGE=""
TASKS_FILE=""
SKIP_CHECKS=0
DRY_RUN=0
ASSUME_YES=0
NO_TASKS=0
BACKUP_PATH=""
COMMIT_SHA=""
PREVIOUS_REMOTE_SHA=""
LOG_BEFORE=0

die() {
    printf '\n[PSP] ERRO: %s\n' "$*" >&2
    if [[ -n "$BACKUP_PATH" ]]; then
        printf '[PSP] Backup disponível: %s\n' "$BACKUP_PATH" >&2
    fi
    exit 1
}

log() {
    printf '\n[PSP] %s\n' "$*"
}

step() {
    printf '\n[PSP] === %s ===\n' "$*"
}

trap 'printf "\n[PSP] Execução interrompida. Backup: %s\n" "${BACKUP_PATH:-não criado}" >&2' ERR

usage() {
    cat <<'USAGE'
Uso: scripts/psp.sh --message "mensagem do commit" --tasks .psp/tasks.json [opções]

Procedimento de Subida Padrão (PSP):
  1. checks locais (Pint, testes e build)
  2. backup do banco de produção
  3. commit dos arquivos já stageados
  4. push para origin/main
  5. pull no clone do servidor
  6. sincronização do app, assets públicos, Composer, migration e caches
  7. registro das tarefas no projeto e validação em produção

Opções:
  -m, --message TEXTO   Mensagem do commit (obrigatória)
  -t, --tasks ARQUIVO   Manifest JSON de tarefas (obrigatório sem --no-tasks)
      --project SLUG    Slug do projeto padrão (default: atitec)
      --branch NOME     Branch de deploy (default: main)
      --skip-checks     Pula Pint e testes; o build continua sendo executado
      --no-tasks        Não registra tarefas
      --dry-run         Valida insumos e mostra as etapas sem alterar produção
  -y, --yes             Não pede confirmação
  -h, --help            Mostra esta ajuda

Variáveis de ambiente:
  PSP_SSH_HOST, PSP_SSH_PORT, PSP_REMOTE_REPO, PSP_REMOTE_APP,
  PSP_REMOTE_PUBLIC, PSP_REMOTE_PHP, PSP_REMOTE_COMPOSER,
  PSP_BRANCH, PSP_PROJECT, PSP_APP_URL, PSP_CONTAINER

O PSP NÃO faz git add. Stage apenas os arquivos da entrega antes de executar.
USAGE
}

while [[ $# -gt 0 ]]; do
    case "$1" in
        -m|--message)
            MESSAGE="${2:-}"
            shift 2
            ;;
        -t|--tasks)
            TASKS_FILE="${2:-}"
            shift 2
            ;;
        --project)
            PROJECT="${2:-}"
            shift 2
            ;;
        --branch)
            BRANCH="${2:-}"
            shift 2
            ;;
        --skip-checks)
            SKIP_CHECKS=1
            shift
            ;;
        --no-tasks)
            NO_TASKS=1
            shift
            ;;
        --dry-run)
            DRY_RUN=1
            shift
            ;;
        -y|--yes)
            ASSUME_YES=1
            shift
            ;;
        -h|--help)
            usage
            exit 0
            ;;
        *)
            die "Argumento desconhecido: $1"
            ;;
    esac
done

[[ -n "$MESSAGE" ]] || die 'Informe a mensagem do commit com --message.'
[[ -n "$PROJECT" ]] || die 'Informe o projeto com --project.'

if [[ "$NO_TASKS" -eq 0 ]]; then
    [[ -n "$TASKS_FILE" ]] || die 'Informe o manifest de tarefas com --tasks ou use --no-tasks.'
    [[ -f "$TASKS_FILE" ]] || die "Manifest não encontrado: $TASKS_FILE"
    php -r '$data = json_decode(file_get_contents($argv[1]), true); if (! is_array($data) || ! isset($data["tasks"]) || ! is_array($data["tasks"]) || $data["tasks"] === []) { fwrite(STDERR, "Manifest inválido ou sem tarefas.\n"); exit(1); }' "$TASKS_FILE" \
        || die 'Manifest JSON inválido.'
fi

for command in git ssh rsync curl php npm docker; do
    command -v "$command" >/dev/null 2>&1 || die "Comando não encontrado: $command"
done

[[ "$(git branch --show-current)" == "$BRANCH" ]] || die "O PSP exige a branch $BRANCH (atual: $(git branch --show-current))."
git diff --cached --quiet && die 'Nenhum arquivo stageado. Faça o stage somente dos arquivos da entrega.'
if git diff --cached --name-only | grep -Eq '(^|/)\.env($|\.)'; then
    die 'Há arquivo .env stageado. Remova do stage antes de continuar.'
fi

step 'Arquivos que serão commitados'
git diff --cached --stat

if [[ "$ASSUME_YES" -eq 0 ]]; then
    read -r -p 'Continuar com o PSP? [s/N] ' answer
    [[ "$answer" =~ ^[sS]$ ]] || die 'Cancelado pelo usuário.'
fi

step 'Verificando SSH e caminhos do servidor'
ssh -p "$SSH_PORT" -o BatchMode=yes "$SSH_HOST" "test -d $REMOTE_REPO && test -d $REMOTE_APP" \
    || die 'Falha ao conectar no servidor ou caminhos remotos inválidos.'

if [[ "$SKIP_CHECKS" -eq 0 || "$NO_TASKS" -eq 0 ]]; then
    docker inspect "$LOCAL_CONTAINER" >/dev/null 2>&1 \
        || die "Container local não encontrado: $LOCAL_CONTAINER"
fi

if [[ "$SKIP_CHECKS" -eq 0 ]]; then
    step 'Pint'
    vendor/bin/pint --test

    step 'Testes'
    docker exec --user sail "$LOCAL_CONTAINER" php artisan test
fi

step 'Build local'
npm run build

if [[ "$DRY_RUN" -eq 1 ]]; then
    cat <<EOF

[PSP] DRY-RUN concluído sem alterar banco, git ou produção.

Backup que seria criado em: $REMOTE_APP
Commit: $MESSAGE
Push: origin/$BRANCH
Pull: $SSH_HOST:$REMOTE_REPO
Deploy: $REMOTE_APP e $REMOTE_PUBLIC
Tarefas: $([[ "$NO_TASKS" -eq 1 ]] && echo 'ignoradas' || echo "$TASKS_FILE") (projeto $PROJECT)
Validação: $APP_URL
EOF
    exit 0
fi

step 'Backup do banco de produção'
BACKUP_PATH=$(ssh -p "$SSH_PORT" "$SSH_HOST" "cd $REMOTE_APP && $REMOTE_PHP" <<'PHP'
<?php

require getcwd().'/vendor/autoload.php';
\Dotenv\Dotenv::createImmutable(getcwd())->safeLoad();

$env = static fn (string $key): string => (string) ($_ENV[$key] ?? $_SERVER[$key] ?? '');
$dir = getenv('HOME').'/migration-backups';

if (! is_dir($dir) && ! mkdir($dir, 0700, true)) {
    fwrite(STDERR, "Não foi possível criar {$dir}\n");
    exit(1);
}

$backup = $dir.'/atitec-before-psp-'.date('YmdHis').'.sql.gz';
$defaults = tempnam(sys_get_temp_dir(), 'psp-db-');
chmod($defaults, 0600);

$password = str_replace(['\\', '"'], ['\\\\', '\\"'], $env('DB_PASSWORD'));
file_put_contents(
    $defaults,
    "[client]\nhost=".$env('DB_HOST')."\nuser=".$env('DB_USERNAME')."\npassword=\"{$password}\"\n"
);

$command = 'mysqldump --defaults-extra-file='.escapeshellarg($defaults)
    .' --single-transaction --quick --routines --triggers --events --no-tablespaces'
    .' --default-character-set=utf8mb4 --max-allowed-packet=1G '
    .escapeshellarg($env('DB_DATABASE')).' | gzip -1 > '.escapeshellarg($backup);

passthru($command, $status);
@unlink($defaults);

if ($status !== 0) {
    fwrite(STDERR, "mysqldump falhou (status {$status})\n");
    exit($status ?: 1);
}

clearstatcache(true, $backup);

if (! is_file($backup) || filesize($backup) === 0) {
    fwrite(STDERR, "Backup vazio\n");
    exit(1);
}

$gz = gzopen($backup, 'rb');

if (! $gz) {
    fwrite(STDERR, "Backup gzip inválido\n");
    exit(1);
}

while (! gzeof($gz)) {
    if (gzread($gz, 1048576) === false) {
        gzclose($gz);
        fwrite(STDERR, "Falha ao validar o gzip do backup\n");
        exit(1);
    }
}

gzclose($gz);
echo $backup;
PHP
) || die 'Falha ao gerar/validar o backup do banco.'

[[ -n "$BACKUP_PATH" ]] || die 'O backup não retornou um caminho.'
log "Backup validado: $BACKUP_PATH"

step 'Commit'
git commit -m "$MESSAGE"
COMMIT_SHA=$(git rev-parse --short HEAD)
log "Commit criado: $COMMIT_SHA"

step 'Push'
git push origin "$BRANCH"

step 'Pull no servidor'
PREVIOUS_REMOTE_SHA=$(ssh -p "$SSH_PORT" "$SSH_HOST" "cd $REMOTE_REPO && git rev-parse --short HEAD" 2>/dev/null || true)
ssh -p "$SSH_PORT" "$SSH_HOST" "cd $REMOTE_REPO && git pull --ff-only origin $BRANCH"
log "Commit anterior no servidor: ${PREVIOUS_REMOTE_SHA:-desconhecido}"

LOG_BEFORE=$(ssh -p "$SSH_PORT" "$SSH_HOST" "stat -c%s $REMOTE_APP/storage/logs/laravel.log 2>/dev/null || echo 0")

step 'Sincronizando app e assets públicos'
ssh -p "$SSH_PORT" "$SSH_HOST" "rsync -a --exclude='.git/' --exclude='.env' --exclude='/storage/' --exclude='/vendor/' --exclude='/public/' --exclude='/public_html/' $REMOTE_REPO/ $REMOTE_APP/"
ssh -p "$SSH_PORT" "$SSH_HOST" "rsync -a --exclude='adocao/' --exclude='adocao_sis/' --exclude='agriculturafamiliar/' --exclude='default.php' --exclude='default.php.old.php' --exclude='build/' $REMOTE_REPO/public/ $REMOTE_PUBLIC/"

step 'Composer'
ssh -p "$SSH_PORT" "$SSH_HOST" "cd $REMOTE_APP && $REMOTE_PHP $REMOTE_COMPOSER install --no-dev --optimize-autoloader --no-interaction"

step 'Migration'
ssh -p "$SSH_PORT" "$SSH_HOST" "cd $REMOTE_APP && $REMOTE_PHP artisan migrate --force"

if [[ "$NO_TASKS" -eq 0 ]]; then
    step 'Registrando tarefas em produção'
    ssh -p "$SSH_PORT" "$SSH_HOST" "cd $REMOTE_APP && $REMOTE_PHP artisan project:tasks:register --stdin --project \"$PROJECT\"" < "$TASKS_FILE"

    step 'Registrando tarefas no banco local'
    docker exec -i --user sail "$LOCAL_CONTAINER" php artisan project:tasks:register --stdin --project "$PROJECT" < "$TASKS_FILE"
fi

step 'Caches'
ssh -p "$SSH_PORT" "$SSH_HOST" "cd $REMOTE_APP && $REMOTE_PHP artisan optimize:clear && $REMOTE_PHP artisan config:cache && $REMOTE_PHP artisan view:cache"

step 'Enviando build'
rsync -a -e "ssh -p $SSH_PORT" public/build/ "$SSH_HOST:$REMOTE_PUBLIC/build/"

step 'Validando produção'
check_status() {
    local url="$1"
    local expected="$2"
    local code

    code=$(curl -sS -o /dev/null -w '%{http_code}' --max-time 30 "$url")
    [[ "$code" == "$expected" ]] || die "Validação falhou: $url retornou $code (esperado $expected)."
    log "OK $url -> $code"
}

check_status "$APP_URL/" 200
check_status "$APP_URL/sobre" 200
check_status "$APP_URL/contato" 200
check_status "$APP_URL/portfolio" 200
check_status "$APP_URL/servicos/desenvolvimento-sob-medida" 200
check_status "$APP_URL/orcamento" 200
check_status "$APP_URL/cartao" 200
check_status "$APP_URL/login" 200
check_status "$APP_URL/admin" 302
check_status "$APP_URL/up" 200
check_status "$APP_URL/build/manifest.json" 200

LOG_AFTER=$(ssh -p "$SSH_PORT" "$SSH_HOST" "stat -c%s $REMOTE_APP/storage/logs/laravel.log 2>/dev/null || echo 0")

if [[ "$LOG_AFTER" -gt "$LOG_BEFORE" ]]; then
    NEW_ERRORS=$(ssh -p "$SSH_PORT" "$SSH_HOST" "tail -c +$((LOG_BEFORE + 1)) $REMOTE_APP/storage/logs/laravel.log 2>/dev/null | grep -c 'production.ERROR' || true")

    if [[ "$NEW_ERRORS" -gt 0 ]]; then
        ssh -p "$SSH_PORT" "$SSH_HOST" "tail -c +$((LOG_BEFORE + 1)) $REMOTE_APP/storage/logs/laravel.log 2>/dev/null | grep 'production.ERROR' | tail -n 5" >&2
        die "O deploy terminou, mas há $NEW_ERRORS novo(s) erro(s) no log de produção."
    fi
fi

step 'PSP concluído'
cat <<EOF
Commit: $COMMIT_SHA
Backup do banco: $BACKUP_PATH
Commit anterior no servidor: ${PREVIOUS_REMOTE_SHA:-desconhecido}
Projeto das tarefas: $PROJECT
Manifest: $([[ "$NO_TASKS" -eq 1 ]] && echo 'não usado' || echo "$TASKS_FILE")
Validação HTTP: OK
Rollback do banco: scripts/psp-rollback.sh --backup "$BACKUP_PATH" --yes
Rollback de código: usar o commit anterior acima no git e refazer o build/sync.
EOF
