<?php

namespace Database\Seeders;

use App\Models\Deliverable;
use App\Models\DeliverableTask;
use App\Models\Project;
use Illuminate\Database\Seeder;

/**
 * Importação one-shot das tarefas dos 15 commits mais recentes do projeto
 * Porcos Crioulos (07/10/2026 a 06/11/2026).
 *
 * Estimativas de horas de um desenvolvedor sênior a R$ 50,00/h, cobrindo
 * análise, implementação, testes e commit. Tarefas concluídas e em produção.
 * Idempotente: se a primeira entrega desta importação já existir, nada é feito.
 */
class PorcosCrioulosRecentTasksSeeder extends Seeder
{
    private const HOURLY_RATE = 50;

    /** Fase do Kanban por entrega (padrão: 6 = Em Produção). */
    private const PHASE_BY_DELIVERABLE = [
        'Painel gerencial e índices quantitativos (nov/2026)' => '5',
    ];

    /** entregas => [período, descrição, [ [título, horas, descrição], ... ]] */
    private const DELIVERABLES = [
        'Painel gerencial e índices quantitativos (nov/2026)' => [
            ['2026-11-05', '2026-11-06', 'Commit 7c32603. Reformulação da home com indicadores e botões de valores quantitativos em Abate, Cobertura e Tratamento.'],
            [
                ['Painel gerencial: classe de indicadores e nova home', 22, 'Classe Painel com rebanho por categoria/sexo, nascimentos, reprodução, sanidade, produção e movimentação mensal; home reformulada com cards e CSS responsivo (~1.300 linhas).'],
                ['Índices quantitativos do módulo Abate', 5, 'Totais, médias de peso vivo/abate, rendimento médio, melhores/piores rendimentos, série mensal e gráfico (commit 7c32603).'],
                ['Índices quantitativos do módulo Cobertura', 7, 'Taxa de concepção corrigida, coberturas e prenhezes por tipo/mês, ranking de machos, totais e gráfico (commit 7c32603).'],
                ['Índices quantitativos do módulo Tratamento', 6, 'Totais, médias, rankings, série mensal e gráfico; eliminação de consultas duplicadas na listagem (commit 7c32603).'],
            ],
        ],
        'Medicamentos, tratamentos e portal público (out/2026)' => [
            ['2026-10-13', '2026-10-16', 'Commit 1b2938d. Finalização do módulo de Medicamentos, novo módulo de Tratamentos, portal público e causas de óbito.'],
            [
                ['Novo módulo de Tratamentos', 18, 'CRUD de aplicações de medicamentos com dose, via, responsável, retorno e observação; integração com a lista de animais, menu e bloqueio de exclusão de medicamentos vinculados (commit 1b2938d).'],
                ['Finalização do módulo de Medicamentos', 12, 'Visibilidade por propriedade, bula sanitizada, tratamento de registros inexistentes, wizard em 5 etapas e cadastro inline de laboratório/princípio ativo via AJAX (commit 1b2938d).'],
                ['Portal público de medicamentos', 6, 'Página pública com busca por nome, espécie e princípio ativo, laboratórios e bula (commit 1b2938d).'],
                ['Causas de óbito e migrações SQL', 4, 'Quatro causas prováveis com descrições, exibição no formulário/histórico e migrações de tratamento e causa de óbito (commit 1b2938d).'],
            ],
        ],
        'Árvore genealógica 2.0 (out/2026)' => [
            ['2026-10-07', '2026-10-07', 'Commit 83f49ef. Reescrita da árvore genealógica com renderização SVG, painel de dados, zoom, impressão e layout responsivo.'],
            [
                ['Reescrita da árvore genealógica em SVG', 14, 'Nova renderização do organograma, painel de dados do animal, controles de zoom/ajuste, legenda de gerações, impressão e responsividade (~700 linhas alteradas; commit 83f49ef).'],
            ],
        ],
        'Propriedades, mídia e QR Code (nov/2026)' => [
            ['2026-11-04', '2026-11-05', 'Commits efc050b, 8681a97 e 2c4d51d. Campos institucionais da propriedade, controle de permissões, mídia e QR Code.'],
            [
                ['Propriedade: campos institucionais e pré-requisitos de rastreamento', 12, 'Inclusão de "Sobre a cabanha" e "Sistema de criação", permissões de editar/excluir na listagem, verificação de banner/galeria e ajustes de layout base (commit efc050b).'],
                ['QR Code: retorno e galeria de mídia nos módulos', 9, 'Links de retorno ao QR Code nos formulários de manejo, galeria de imagens da propriedade e ajustes de exibição em abate, animal, ECC, Famacha, óbito, peso e tratamento (commit 8681a97).'],
                ['Gestão de banner e galeria da propriedade', 9, 'Upload e exclusão de imagens, sanitização de nomes, validação de formato/tamanho, grid responsivo e substituição de banner (commit 2c4d51d).'],
            ],
        ],
        'Troca de propriedade e perfil Gerente Geral (nov/2026)' => [
            ['2026-11-05', '2026-11-05', 'Commits 17e047c, 1ed8aba, 33a91d4 e ff932a5. Seletor de propriedade e novo perfil de acesso.'],
            [
                ['Seletor de troca de propriedade no cabeçalho', 8, 'Endpoint de troca com validação de permissão e propriedade, select no menu para usuários privilegiados e preservação da URL de retorno (commit 17e047c).'],
                ['Perfil Gerente Geral e hierarquia de administrador', 7, 'Migração de serviços/perfis, permissão dedicada de troca de propriedade e restrições para criar, editar ou excluir administradores (commit 1ed8aba).'],
                ['Correções de constantes, caminho e select escuro', 2, 'Ajustes no caminho do endpoint, definição única da constante de serviço e contraste do select no tema escuro (commits 33a91d4 e ff932a5).'],
            ],
        ],
        'Correções, relatórios e ajustes de portal (out–nov/2026)' => [
            ['2026-10-16', '2026-11-05', 'Commits 3eea042, 4dd140e, 1d12d9e, 6094fba e d63e354. Correções de interface, relatório de modificações e ajustes do portal.'],
            [
                ['Correção do dropdown de ações e exibição do brinco', 4, 'Remoção do Bootstrap duplicado que quebrava o dropdown e correção do brinco em abate/óbito (commit 3eea042).'],
                ['Relatório de modificações 13–16/10 e liberação de acesso', 3, 'Relatório HTML das quatro frentes de trabalho e regra de acesso ao diretório de modificações (commits 4dd140e e 1d12d9e).'],
                ['Ajustes do portal e .gitignore de rastreamento', 1, 'Comentário do vídeo antigo do sistema Ovinos no portal e exclusão de subpastas de rastreamento do versionamento (commits 6094fba e d63e354).'],
            ],
        ],
    ];

    public function run(): void
    {
        $project = Project::where('title', 'Porcos Crioulos')->firstOrFail();

        $sentinel = array_key_first(self::DELIVERABLES);

        if (Deliverable::where('project_id', $project->id)->where('name', $sentinel)->exists()) {
            $this->command->warn('As tarefas recentes do Porcos Crioulos já foram importadas — nada foi feito.');

            return;
        }

        $tasks = 0;
        $hours = 0;

        foreach (self::DELIVERABLES as $name => [$period, $items]) {
            [$startDate, $endDate, $description] = $period;

            $deliverable = Deliverable::create([
                'project_id' => $project->id,
                'name' => $name,
                'description' => $description,
                'status' => 'concluido',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'hours_estimated' => array_sum(array_column($items, 1)),
            ]);

            foreach ($items as $index => [$title, $taskHours, $taskDescription]) {
                DeliverableTask::create([
                    'project_id' => $project->id,
                    'deliverable_id' => $deliverable->id,
                    'title' => $title,
                    'description' => $taskDescription,
                    'status' => 'concluido',
                    'priority' => 'media',
                    'phase' => self::PHASE_BY_DELIVERABLE[$name] ?? '6',
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'hours_estimated' => $taskHours,
                    'hours_actual' => $taskHours,
                    'hourly_rate' => self::HOURLY_RATE,
                    'order' => $index + 1,
                ]);

                $tasks++;
                $hours += $taskHours;
            }
        }

        $this->command->info(sprintf(
            'Porcos Crioulos (commits recentes): %d entregas e %d tarefas importadas — %s h — R$ %s (R$ %d/h).',
            count(self::DELIVERABLES),
            $tasks,
            number_format($hours, 0, ',', '.'),
            number_format($hours * self::HOURLY_RATE, 2, ',', '.'),
            self::HOURLY_RATE,
        ));
    }
}
