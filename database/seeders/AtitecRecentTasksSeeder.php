<?php

namespace Database\Seeders;

use App\Models\Deliverable;
use App\Models\DeliverableTask;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Registra melhorias recentes no Kanban do projeto interno da ATITEC.
 *
 * As tarefas são concluídas e usam a hora técnica de R$ 50,00, mas ficam sem
 * horas realizadas para não gerar custo até que as horas sejam informadas.
 * O seeder é incremental para poder ser executado no deploy sem duplicar.
 */
class AtitecRecentTasksSeeder extends Seeder
{
    private const HOURLY_RATE = 50;

    private const TASKS = [
        [
            'title' => 'Paginação compartilhada no admin e portal',
            'deliverable' => 'Administração e CRM',
            'description' => 'Padronização das paginações Laravel e Livewire com layout ATITEC responsivo, acessível e legível.',
        ],
        [
            'title' => 'Múltiplos clientes por projeto e acesso compartilhado',
            'deliverable' => 'Administração e CRM',
            'description' => 'Relacionamento N:N entre clientes e projetos, seleção no admin e isolamento de faturas e documentos no portal.',
        ],
        [
            'title' => 'Troca persistente de tema claro/escuro no painel',
            'deliverable' => 'Administração e CRM',
            'description' => 'Switch no AdminLTE, preferência em users.theme, migration da coluna tema e paleta clara do painel. Commit 4121d44.',
        ],
        [
            'title' => 'PHP 8.4 no docroot da Hostinger e requisito documentado',
            'deliverable' => 'Fundação do projeto e infraestrutura',
            'description' => 'Handler PHP 8.4 no .htaccess, correção do erro 500 em produção e atualização do requisito no AGENTS.md. Commits 9bb0fb5 e 8ca3f97.',
        ],
        [
            'title' => 'Tema claro fiel na home pública com switch persistente',
            'deliverable' => 'Site institucional',
            'description' => 'Tema claro do ZIP aplicado à home, switch no header, persistência para visitantes e usuários e cores da rede ambiente por tema.',
        ],
        [
            'title' => 'Tema claro nas páginas internas, cartão digital e widget de IA',
            'deliverable' => 'Site institucional',
            'description' => 'Tema claro aplicado em sobre, contato, portfólio, serviços, cases, orçamento e cartão; switch no header de todas as páginas públicas e widget de IA adaptado ao tema.',
        ],
        [
            'title' => 'Contraste dos rótulos de destaque no tema claro do painel',
            'deliverable' => 'Administração e CRM',
            'description' => 'Ajuste do .text-warning para dourado-oliva escuro (#7C6F00) no tema claro do admin e portal, mantendo o amarelo no tema escuro.',
        ],
    ];

    public function run(): void
    {
        $project = Project::where('slug', 'atitec')->firstOrFail();
        $deliverables = Deliverable::where('project_id', $project->id)->get()->keyBy('name');
        $userId = User::where('email', 'admin@atitec.com.br')->value('id');
        $nextOrders = [];
        $created = 0;

        foreach (self::TASKS as $taskData) {
            $deliverable = $deliverables->get($taskData['deliverable']);

            if (! $deliverable) {
                $this->command?->warn("Entrega não encontrada: {$taskData['deliverable']} — tarefa ignorada.");

                continue;
            }

            $nextOrders[$deliverable->id] ??= ((int) $deliverable->tasks()->max('order')) + 1;

            $task = DeliverableTask::firstOrNew([
                'project_id' => $project->id,
                'deliverable_id' => $deliverable->id,
                'title' => $taskData['title'],
            ]);

            if (! $task->exists) {
                $task->fill([
                    'user_id' => $userId,
                    'description' => $taskData['description'],
                    'status' => 'concluido',
                    'priority' => 'media',
                    'phase' => '6',
                    'hours_estimated' => null,
                    'hours_actual' => null,
                    'hourly_rate' => self::HOURLY_RATE,
                    'order' => $nextOrders[$deliverable->id]++,
                ]);
                $created++;
            } else {
                // Uniformiza a hora técnica sem sobrescrever horas/status já registrados.
                $task->description = $taskData['description'];
                $task->hourly_rate = self::HOURLY_RATE;
            }

            $task->save();
        }

        $this->command?->info("ATITEC: {$created} tarefa(s) recente(s) adicionada(s); R$ ".number_format(self::HOURLY_RATE, 2, ',', '.').'/h, sem horas e sem custo.');
    }
}
