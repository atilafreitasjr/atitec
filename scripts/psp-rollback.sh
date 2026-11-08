#!/usr/bin/env bash
set -Eeuo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd -- "$SCRIPT_DIR/.." && pwd)"
cd "$REPO_ROOT"

SSH_HOST="${PSP_SSH_HOST:-u447523265@186.244.144.174}"
SSH_PORT="${PSP_SSH_PORT:-65002}"
REMOTE_APP="${PSP_REMOTE_APP:-~/domains/atitec.com.br}"
REMOTE_PHP="${PSP_REMOTE_PHP:-/opt/alt/php84/usr/bin/php}"

BACKUP=""
ASSUME_YES=0

die() {
    printf '\n[PSP-ROLLBACK] ERRO: %s\n' "$*" >&2
    exit 1
}

log() {
    printf '\n[PSP-ROLLBACK] %s\n' "$*"
}

usage() {
    cat <<'USAGE'
Uso: scripts/psp-rollback.sh --backup /caminho/remoto/backup.sql.gz [--yes]

Restaura um backup gerado pelo PSP no banco de produção.
O código NÃO é alterado por este script.

Opções:
  -b, --backup ARQUIVO   Caminho remoto do backup .sql.gz (obrigatório)
  -y, --yes              Confirma a restauração sem perguntar
  -h, --help             Mostra esta ajuda

Variáveis: PSP_SSH_HOST, PSP_SSH_PORT, PSP_REMOTE_APP, PSP_REMOTE_PHP
USAGE
}

while [[ $# -gt 0 ]]; do
    case "$1" in
        -b|--backup)
            BACKUP="${2:-}"
            shift 2
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

[[ -n "$BACKUP" ]] || die 'Informe o backup com --backup.'
[[ -f "$BACKUP" || "$BACKUP" == /* ]] || die 'Informe o caminho remoto absoluto do backup.'

for command in ssh; do
    command -v "$command" >/dev/null 2>&1 || die "Comando não encontrado: $command"
done

BACKUP_Q=$(printf '%q' "$BACKUP")

ssh -p "$SSH_PORT" -o BatchMode=yes "$SSH_HOST" "test -f $BACKUP_Q" \
    || die "Backup não encontrado no servidor: $BACKUP"

ssh -p "$SSH_PORT" "$SSH_HOST" "gzip -t $BACKUP_Q" \
    || die 'O arquivo de backup não é um gzip válido.'

log "Backup: $BACKUP"
log 'ATENÇÃO: a restauração sobrescreve o banco de produção atual.'

if [[ "$ASSUME_YES" -eq 0 ]]; then
    read -r -p 'Confirmar restauração do banco? [s/N] ' answer
    [[ "$answer" =~ ^[sS]$ ]] || die 'Cancelado pelo usuário.'
fi

log 'Colocando a aplicação em manutenção'
ssh -p "$SSH_PORT" "$SSH_HOST" "cd $REMOTE_APP && $REMOTE_PHP artisan down" || true

log 'Restaurando o banco'
ssh -p "$SSH_PORT" "$SSH_HOST" "cd $REMOTE_APP && PSP_BACKUP=$BACKUP_Q $REMOTE_PHP" <<'PHP'
<?php

require getcwd().'/vendor/autoload.php';
\Dotenv\Dotenv::createImmutable(getcwd())->safeLoad();

$backup = getenv('PSP_BACKUP');

if (! is_string($backup) || ! is_file($backup)) {
    fwrite(STDERR, "Backup não encontrado: {$backup}\n");
    exit(1);
}

$env = static fn (string $key): string => (string) ($_ENV[$key] ?? $_SERVER[$key] ?? '');
$defaults = tempnam(sys_get_temp_dir(), 'psp-db-');
chmod($defaults, 0600);

$password = str_replace(['\\', '"'], ['\\\\', '\\"'], $env('DB_PASSWORD'));
file_put_contents(
    $defaults,
    "[client]\nhost=".$env('DB_HOST')."\nuser=".$env('DB_USERNAME')."\npassword=\"{$password}\"\n"
);

$command = 'gunzip -c '.escapeshellarg($backup)
    .' | mysql --defaults-extra-file='.escapeshellarg($defaults).' '
    .escapeshellarg($env('DB_DATABASE'));

passthru($command, $status);
@unlink($defaults);

if ($status !== 0) {
    fwrite(STDERR, "Restauração falhou (status {$status}). A aplicação permanece em manutenção.\n");
    exit($status ?: 1);
}

echo "Banco restaurado.\n";
PHP

log 'Limpando caches'
ssh -p "$SSH_PORT" "$SSH_HOST" "cd $REMOTE_APP && $REMOTE_PHP artisan optimize:clear"

log 'Retirando a aplicação da manutenção'
ssh -p "$SSH_PORT" "$SSH_HOST" "cd $REMOTE_APP && $REMOTE_PHP artisan up"

log 'Concluído. O banco foi restaurado; o código NÃO foi alterado.'
log 'Se necessário reverter o código, use o commit anterior informado pelo PSP no clone do servidor e refaça o build/sync.'
