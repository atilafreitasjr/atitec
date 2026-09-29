<?php

use App\Http\Controllers\Admin\ChannelController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ConversationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ProjectKanbanController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Cliente\PortalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiteController;
use App\Models\DeliverableTask;
use Illuminate\Support\Facades\Route;

// Binding explícito: {task} refere-se a DeliverableTask (isolado por projeto via scopeBindings).
Route::bind('task', fn ($value) => DeliverableTask::findOrFail($value));

// ---- Site institucional ----
Route::get('/', [SiteController::class, 'home'])->name('site.home');
Route::get('/sobre', [SiteController::class, 'sobre'])->name('site.sobre');
Route::get('/servicos/{servico}', [SiteController::class, 'servico'])->name('site.servico');
Route::get('/portfolio', [SiteController::class, 'portfolio'])->name('site.portfolio');
Route::get('/portfolio/{slug}', [SiteController::class, 'case'])->name('site.case');
Route::get('/contato', [SiteController::class, 'contato'])->name('site.contato');
Route::post('/contato', [SiteController::class, 'contatoStore'])->name('site.contato.store');
Route::get('/orcamento', [SiteController::class, 'orcamento'])->name('site.orcamento');
Route::post('/orcamento', [SiteController::class, 'orcamentoStore'])->name('site.orcamento.store');

// ---- Cartão digital (apontar cartao.atitec.com.br para cá) ----
Route::get('/cartao', [SiteController::class, 'cartao'])->name('site.cartao');
Route::post('/cartao/contato', [SiteController::class, 'cartaoContatoStore'])->name('site.cartao.contato');
Route::get('/cartao/vcard', [SiteController::class, 'cartaoVcard'])->name('site.cartao.vcard');

// Subdomínio dedicado abre direto o cartão (mesmo docroot do site principal).
Route::domain('cartao.atitec.com.br')->group(function () {
    Route::get('/', [SiteController::class, 'cartao']);
});

// ---- Pós-login: redireciona por perfil ----
Route::get('/dashboard', function () {
    $role = auth()->user()->role;
    if (in_array($role, ['admin', 'gerente', 'financeiro'], true)) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('cliente.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ---- Área administrativa (AdminLTE) ----
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'role:admin,gerente,financeiro'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Cada ação do CRUD é protegida pela permissão Spatie correspondente.
    $resource = function (string $uri, string $controller, string $module, array $only = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']) {
        $map = [
            'index' => 'view',
            'create' => 'create', 'store' => 'create',
            'show' => 'view',
            'edit' => 'edit', 'update' => 'edit',
            'destroy' => 'delete',
        ];
        foreach (array_intersect(array_keys($map), $only) as $action) {
            Route::resource($uri, $controller)
                ->only($action)
                ->middleware("permission:{$module}.{$map[$action]}");
        }
    };

    $resource('projects', ProjectController::class, 'project');
    $resource('clients', ClientController::class, 'client');
    $resource('invoices', InvoiceController::class, 'invoice');
    $resource('conversations', ConversationController::class, 'conversation', ['index', 'show', 'store', 'update', 'destroy']);
    Route::get('conversations/create', [ConversationController::class, 'create'])
        ->name('conversations.create')->middleware('permission:conversation.create');
    $resource('leads', LeadController::class, 'lead', ['index', 'show', 'update', 'destroy']);
    $resource('channels', ChannelController::class, 'channel', ['index', 'store', 'update', 'destroy']);

    // ---- Usuários e permissões (Spatie) ----
    $resource('users', UserController::class, 'user', ['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::get('roles', [RoleController::class, 'index'])->name('roles.index')->middleware('permission:role.view');
    Route::post('roles', [RoleController::class, 'store'])->name('roles.store')->middleware('permission:role.create');
    Route::put('roles', [RoleController::class, 'bulkUpdate'])->name('roles.bulk')->middleware('permission:role.edit');
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy')->middleware('permission:role.delete');

    // ---- Gestão por projeto: Kanban de entregas/tarefas (isolado por project_id) ----
    Route::prefix('projects/{project}')->name('projects.')->scopeBindings()->group(function () {
        Route::get('kanban', [ProjectKanbanController::class, 'kanban'])
            ->name('kanban')->middleware('permission:deliverable.view|task.view');
        Route::get('deliverables', [ProjectKanbanController::class, 'deliverablesIndex'])
            ->name('deliverables.index')->middleware('permission:deliverable.view');
        Route::get('deliverables/create', [ProjectKanbanController::class, 'deliverableCreate'])
            ->name('deliverables.create')->middleware('permission:deliverable.create');
        Route::get('deliverables/{deliverable}', [ProjectKanbanController::class, 'deliverableShow'])
            ->name('deliverables.show')->middleware('permission:deliverable.view');
        Route::get('deliverables/{deliverable}/edit', [ProjectKanbanController::class, 'deliverableEdit'])
            ->name('deliverables.edit')->middleware('permission:deliverable.edit');
        Route::get('tasks', [ProjectKanbanController::class, 'tasksIndex'])
            ->name('tasks.index')->middleware('permission:task.view');
        Route::get('tasks/create', [ProjectKanbanController::class, 'taskCreate'])
            ->name('tasks.create')->middleware('permission:task.create');
        Route::get('tasks/{task}/edit', [ProjectKanbanController::class, 'taskEdit'])
            ->name('tasks.edit')->middleware('permission:task.edit');
    });
});

// ---- Portal do cliente ----
Route::prefix('cliente')->name('cliente.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [PortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/projetos', [PortalController::class, 'projetos'])->name('projetos');
    Route::get('/projetos/{id}', [PortalController::class, 'projeto'])->name('projeto');
    Route::get('/projetos/{id}/kanban', [PortalController::class, 'kanban'])->name('kanban');
    Route::get('/financeiro', [PortalController::class, 'financeiro'])->name('financeiro');
    Route::get('/mensagens', [PortalController::class, 'mensagens'])->name('mensagens');
    Route::post('/mensagens', [PortalController::class, 'mensagemStore'])->name('mensagens.store');
});

require __DIR__.'/auth.php';
