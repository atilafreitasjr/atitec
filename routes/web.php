<?php

use App\Http\Controllers\Admin\ChannelController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ConversationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\ProjectBillingController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ProjectKanbanController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Cliente\PortalController;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectDocumentController;
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

// ---- Guia do Cliente (documentação do sistema) ----
Route::get('docs/{page?}', [DocsController::class, 'show'])->name('docs.show');

// ---- Pós-login: redireciona por perfil ----
Route::get('/dashboard', function () {
    $user = auth()->user();

    if (in_array($user->role, ['admin', 'gerente', 'financeiro'], true)) {
        return redirect()->route('admin.dashboard');
    }

    // Cliente sem cadastro completo (ex.: entrou pelo Google) informa os dados.
    if (! $user->client_id) {
        return redirect()->route('cadastro.completar');
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
    Route::post('invoices/{invoice}/payments', [InvoiceController::class, 'paymentStore'])
        ->name('invoices.payments.store')->middleware('permission:invoice.edit');
    Route::put('invoices/{invoice}/payments/{payment}', [InvoiceController::class, 'paymentUpdate'])
        ->name('invoices.payments.update')->middleware('permission:invoice.edit');
    Route::delete('invoices/{invoice}/payments/{payment}', [InvoiceController::class, 'paymentDestroy'])
        ->name('invoices.payments.destroy')->middleware('permission:invoice.edit');
    Route::post('invoices/{invoice}/discount', [InvoiceController::class, 'discountStore'])
        ->name('invoices.discount')->middleware('permission:invoice.edit');
    $resource('conversations', ConversationController::class, 'conversation', ['index', 'create', 'store', 'show', 'update', 'destroy']);
    Route::post('conversations/{conversation}/reopen', [ConversationController::class, 'reopen'])
        ->name('conversations.reopen')->middleware('permission:conversation.edit');
    $resource('leads', LeadController::class, 'lead', ['index', 'show', 'update', 'destroy']);
    Route::post('leads/{lead}/proposal', [LeadController::class, 'sendProposal'])
        ->name('leads.proposal')->middleware('permission:lead.edit');
    Route::post('leads/{lead}/convert', [LeadController::class, 'convertToProject'])
        ->name('leads.convert')->middleware(['permission:lead.edit', 'permission:project.create']);
    $resource('channels', ChannelController::class, 'channel', ['index', 'store', 'update', 'destroy']);

    // ---- Usuários e permissões (Spatie) ----
    $resource('users', UserController::class, 'user', ['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::get('roles', [RoleController::class, 'index'])->name('roles.index')->middleware('permission:role.view');
    Route::post('roles', [RoleController::class, 'store'])->name('roles.store')->middleware('permission:role.create');
    Route::put('roles', [RoleController::class, 'bulkUpdate'])->name('roles.bulk')->middleware('permission:role.edit');
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy')->middleware('permission:role.delete');

    // ---- Gestão por projeto: Kanban de entregas/tarefas (isolado por project_id) ----
    Route::prefix('projects/{project}')->name('projects.')->scopeBindings()->group(function () {
        Route::post('documents', [ProjectDocumentController::class, 'store'])
            ->name('documents.store')->middleware('permission:project.edit');
        Route::delete('documents/{document}', [ProjectDocumentController::class, 'destroy'])
            ->name('documents.destroy')->middleware('permission:project.edit');
        Route::post('invoices/from-tasks', [ProjectBillingController::class, 'store'])
            ->name('invoices.from-tasks')->middleware(['permission:invoice.create', 'permission:task.view']);
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

// Downloads privados: o controller verifica acesso staff ou propriedade do projeto/fatura.
Route::middleware('auth')->group(function () {
    Route::get('/documentos/projetos/{project}/{document}', [ProjectDocumentController::class, 'download'])
        ->name('project-documents.download');
    Route::get('/documentos/faturas/{invoice}/boleto', [InvoiceController::class, 'downloadPdf'])
        ->name('invoices.pdf');
});

// ---- Portal do cliente ----
Route::prefix('cliente')->name('cliente.')->middleware(['auth', 'verified', 'role:cliente'])->group(function () {
    Route::get('/', [PortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/projetos', [PortalController::class, 'projetos'])->name('projetos');
    Route::get('/projetos/{id}', [PortalController::class, 'projeto'])->name('projeto');
    Route::get('/projetos/{id}/kanban', [PortalController::class, 'kanban'])->name('kanban');
    Route::get('/financeiro', [PortalController::class, 'financeiro'])->name('financeiro');
    Route::get('/financeiro/{invoice}', [PortalController::class, 'fatura'])->name('fatura');
    Route::get('/orcamentos', [PortalController::class, 'orcamentos'])->name('orcamentos');
    Route::post('/orcamentos/{lead}/resposta', [PortalController::class, 'responderOrcamento'])->name('orcamentos.responder');
    Route::get('/mensagens', [PortalController::class, 'mensagens'])->name('mensagens');
    Route::post('/mensagens', [PortalController::class, 'mensagemStore'])->name('mensagens.store');
    Route::get('/ajuda/{page?}', [DocsController::class, 'ajuda'])->name('ajuda');
});

require __DIR__.'/auth.php';
