# ATITEC — Site institucional + Portal do Cliente — AGENTS.md

## Deploy (produção Hostinger, SSH `ssh -p 65002 u852815632@141.136.43.35`)
- Repo: `git@github.com:atilafreitasjr/atitec.git` (branch `main`). Chave de deploy: `~/.ssh/atitec_github.pub` (cadastrada no GitHub).
- Layout no servidor: app em `~/domains/atitec.com.br/atitec` (clone https), `public_html` = symlink → `atitec/public`. Legado preservado em `~/domains/atitec.com.br/backups/legacy_*`.
- `.env` produção (NUNCA commitar): `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://atitec.com.br`, `DB_CONNECTION=mysql`, `DB_HOST=localhost`, `DB_DATABASE=u852815632_atitec`, `DB_USERNAME=u852815632_atitec` (senha no cofre do dono). Sem Redis no compartilhado: `CACHE_STORE=database`, `SESSION_DRIVER=database`, `QUEUE_CONNECTION=database`.
- Checklist de deploy (nesta ordem): `git pull` → `composer install --no-dev --optimize-autoloader` → backup do banco → `php artisan migrate --force` → `php artisan optimize:clear` → `php artisan config:cache` (SEM `route:cache` — há rota closure em `/dashboard`).
- Frontend: build do Vite QUEBRA no servidor (panic do rolldown) — sempre `npm run build` LOCAL + `rsync public/build/` para `atitec/public/build/`.
- Subdomínio `cartao.atitec.com.br`: criar no hPanel e apontar/symlink para o mesmo `public` (a rota `/` do subdomínio abre o cartão via `Route::domain`).
- PHP web >= 8.3 no hPanel (Laravel 13). Forçar HTTPS no hPanel.
- Laravel 13 + Laravel Sail (Docker). PHP 8.3+, MariaDB 11, Redis, phpMyAdmin.
- Subir: `docker compose up -d` (na raiz do projeto).
- Comandos Artisan **dentro do container como usuário `sail`** (evita arquivos owned by root):
  `docker exec --user sail atitec-laravel.test-1 php artisan ...`
- Se algum arquivo ficar owned by root: `docker exec --user root atitec-laravel.test-1 chown -R 1000:1000 /var/www/html/<caminho>`
- Portas (iguais ao agricultura_familiar — usar um projeto por vez, nunca os dois juntos):
  app `80`, Vite `5173`, MariaDB host `3306`, Redis host `6379`, phpMyAdmin `8080`.
- Banco dev: `atitec` / usuário `atitec` / senha `atitec123` (ver `.env`). Banco de testes: `testing` (criado pelo init do Sail).
- Credencial demo: `admin@atitec.com.br` / `password` (role `admin`).
- URLs: site `http://localhost`, admin `http://localhost/admin`, portal `http://localhost/cliente`, phpMyAdmin `http://localhost:8080`.
- Frontend: `npm install` + `npm run build` (ou `npm run dev`). Vite com Tailwind v4 (`@import "tailwindcss"` em `resources/css/app.css`) + entradas AdminLTE (`resources/css/adminlte.css`, `resources/js/adminlte.js`). **Não** usar `@tailwind base/components/utilities` (sintaxe v3) nem `tailwindcss: {}` no `postcss.config.js` (usar `@tailwindcss/postcss`).
- Logo: `public/images/atitec_logo.{png,svg}` (origem em `imagens/`).

## Estrutura
- **Site público** (`App\Http\Controllers\SiteController`, views `resources/views/site/*`, layout `layouts/site.blade.php` — tema preto + amarelo `#FFFF5D`): `/`, `/sobre`, `/servicos/{desenvolvimento-sob-medida|consultoria-comercial|automatizacao-atendimento}`, `/portfolio`, `/portfolio/{slug}`, `/contato`, `/orcamento` (wizard que cria `Lead`), `/cartao` (cartão digital de Átila de Freitas Jr — foto em `public/images/atila_foto.png`, dados em `config/card.php`; ações WhatsApp/ligar/e-mail/SMS, vCard `/cartao/vcard`, portfólio, form que cria `Lead` source `site/cartao`).
- **Admin** (`/admin`, middleware `role:admin,gerente,financeiro`, layout `adminlte::page`, menu em `config/adminlte.php`): Dashboard com KPIs, CRUD Projetos/Clientes/Faturas, Conversas (1:1 + broadcast), Leads (funil), Canais (alimentam o site).
- **Portal do cliente** (`/cliente`, auth): dashboard, projetos com kanban de tarefas, financeiro, mensagens.
- Models: `Client`, `Project` (+`ProjectTask`), `Invoice` (+`Payment`), `Conversation` (+`ClientMessage`), `Lead`, `Channel`, `User` (coluna `role`: admin/gerente/cliente/financeiro + `client_id`).
- **Gestão por projeto (Kanban, portado do agricultura_familiar)**: models `Deliverable` (tabela `deliverables`) + `DeliverableTask` (tabela `deliverable_tasks`, soft deletes), ambos com `project_id` (tenant = `projects.id`). Components Livewire `Kanban\Board`, `Deliverable\{Index,Create,Edit,Show}`, `Task\{Index,Create,Edit}` — sempre `mount(int $projectId)` via trait `Livewire\Concerns\ScopedToProject` (cliente só acessa projetos do próprio `client_id`; mutações exigem permissão Spatie e revalidam `project_id`, retornando 404 cross-projeto). Rotas `/admin/projects/{project}/kanban|deliverables|tasks` (controller `Admin\ProjectKanbanController`, wrappers `admin/projects/*.blade.php` com `@livewireStyles/Scripts`, `scopeBindings`, middleware `permission:`) + portal read-only `/cliente/projetos/{id}/kanban` (Board `readonly`).
- Seeders: `ProjectSeeder` (7 cases reais), `ChannelSeeder` (5 canais), `AdminUserSeeder`, `KanbanRolePermissionSeeder` (permissões `deliverable.*/task.*` + papéis espelhando a coluna `role`; `User::saved` ressincroniza o papel Spatie), `KanbanImportSeeder` (one-shot dos dados reais de `aecaf_producao`; dumps em `/tmp/opencode/deliverables_tasks_*.sql`).
- Dados reais importados: projeto **Agricultura Familiar** com 7 entregas + 31 tarefas da produção (responsáveis zerados — sem correspondência de e-mails entre as bases).

## Convenções
- Middleware de perfil: alias `role` (`App\Http\Middleware\EnsureRole`), registrado em `bootstrap/app.php`.
- Tailwind v4 no site; AdminLTE 4 (pacote `colorlibhq/adminlte-laravel`) no admin — `php artisan adminlte:status` deve reportar "fully installed".
- Estilo PHP: `vendor/bin/pint` (PASS exigido). Testes: `php artisan test` (inclui `SiteSmokeTest`).
- Locale `pt_BR`. Textos do site em pt-BR.
