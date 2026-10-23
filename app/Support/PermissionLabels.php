<?php

namespace App\Support;

class PermissionLabels
{
    /** Nome de cada módulo de permissão (mesmos módulos do RolePermissionSeeder). */
    public const MODULES = [
        'deliverable' => 'Entregas',
        'task' => 'Tarefas',
        'client' => 'Clientes',
        'project' => 'Projetos',
        'invoice' => 'Faturas e cobranças',
        'lead' => 'Leads / Orçamentos',
        'conversation' => 'Mensagens',
        'ai_support' => 'Atendimento IA',
        'channel' => 'Canais de contato',
        'user' => 'Usuários',
        'role' => 'Papéis e permissões',
    ];

    /** Nome de cada ação de permissão. */
    public const ACTIONS = [
        'view' => 'Ver',
        'create' => 'Criar',
        'edit' => 'Editar',
        'reply' => 'Responder',
        'delete' => 'Excluir',
    ];

    public static function module(string $module): string
    {
        return self::MODULES[$module] ?? ucfirst($module);
    }

    public static function action(string $action): string
    {
        return self::ACTIONS[$action] ?? ucfirst($action);
    }
}
