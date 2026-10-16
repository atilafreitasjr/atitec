<?php

namespace App\Providers;

use App\Support\PasswordPolicy;
use ColorlibHQ\AdminLte\AdminLte;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Password::defaults(fn () => PasswordPolicy::rule());

        // No workspace do cliente o AdminLTE usa o menu do portal; nas páginas
        // admin o menu padrão (config/adminlte.php) é mantido, filtrado pelas
        // permissões Spatie. O menu original é capturado no boot para poder ser
        // restaurado com segurança quando a mesma instância atende outra sessão.
        $adminMenu = config('adminlte.menu');
        $adminDocsUrl = config('adminlte.sidebar_docs_url');

        $clientMenu = [
            ['text' => 'Meu painel', 'route' => 'cliente.dashboard', 'icon' => 'bi bi-speedometer'],
            ['text' => 'Meus projetos', 'route' => 'cliente.projetos', 'icon' => 'bi bi-kanban'],
            ['text' => 'Orçamentos', 'route' => 'cliente.orcamentos', 'icon' => 'bi bi-file-earmark-text'],
            ['text' => 'Financeiro', 'route' => 'cliente.financeiro', 'icon' => 'bi bi-receipt'],
            ['text' => 'Mensagens', 'route' => 'cliente.mensagens', 'icon' => 'bi bi-chat-dots'],
            ['text' => 'Ajuda', 'route' => 'cliente.ajuda', 'icon' => 'bi bi-question-circle'],

            ['header' => 'SITE'],
            ['text' => 'Ver site', 'url' => '/', 'icon' => 'bi bi-globe', 'target' => '_blank'],
        ];

        View::composer('adminlte::page', function () use ($adminMenu, $adminDocsUrl, $clientMenu) {
            $isClient = auth()->user()?->role === 'cliente';

            config([
                'adminlte.menu' => $isClient ? $clientMenu : $adminMenu,
                'adminlte.sidebar_docs_url' => $isClient ? '/cliente/ajuda' : $adminDocsUrl,
            ]);

            // O construtor do menu é singleton; descarta a instância resolvida
            // para que cada render use o menu do papel atual.
            app()->forgetInstance(AdminLte::class);
        });
    }
}
