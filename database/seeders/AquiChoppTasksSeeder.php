<?php

namespace Database\Seeders;

use App\Models\Deliverable;
use App\Models\DeliverableTask;
use App\Models\Project;
use Illuminate\Database\Seeder;

/**
 * Importação one-shot das tarefas de todos os commits do projeto AquiChopp
 * (27/05/2026 a 06/11/2026 — 37 commits).
 *
 * Estimativas de horas de um desenvolvedor sênior a R$ 80,00/h, cobrindo
 * análise, implementação, testes e commit. Tarefas concluídas e em produção.
 * Idempotente: se a primeira entrega desta importação já existir, nada é feito.
 */
class AquiChoppTasksSeeder extends Seeder
{
    private const HOURLY_RATE = 80;

    /** entregas => [período, descrição, [ [título, horas, descrição], ... ]] */
    private const DELIVERABLES = [
        'Fundação e infraestrutura (mai/2026)' => [
            ['2026-05-27', '2026-05-27', 'Commits 30f5e52, 31c35ab e d54232a. Base do sistema em Laravel 13 com autenticação, permissões e ambiente Docker.'],
            [
                ['Base Laravel 13 com Jetstream, Spatie Permission, Google Login e Sail', 30, 'Scaffold completo da aplicação, autenticação (Fortify/Jetstream), login Google (Socialite), permissões Spatie, Docker/Sail, configurações e suíte inicial de testes (commit 30f5e52).'],
                ['README e ajustes iniciais do ambiente', 1, 'Correções de documentação e configuração inicial do projeto (commit 31c35ab).'],
                ['Correção do build de assets', 1, 'Ajuste da rotina de build após falha do npm e regeneração dos assets (commit d54232a).'],
            ],
        ],
        'Cadastros, pedidos e dashboard inicial (mai–jun/2026)' => [
            ['2026-05-28', '2026-06-02', 'Commits 7611271, 44f436b e 8240c1f. CRUDs do domínio comercial, dashboard com gráficos e detalhes do pedido.'],
            [
                ['CRUDs de empresas, marcas, capacidades, tipos, clientes, preços e pedidos', 42, 'Models, migrations, componentes Livewire e views de cadastro/listagem/edição para o domínio comercial, com endereço/telefone de clientes e itens de pedido (commit 7611271).'],
                ['Dashboard com gráficos e detalhes do pedido', 12, 'Indicadores e gráficos do dashboard, tela de detalhes do pedido e integração com a navegação (commit 7611271).'],
                ['Ajustes de documentação e remoção de dados fixos do dashboard', 2, 'Correção do README e limpeza de dados de exemplo do dashboard (commits 44f436b e 8240c1f).'],
            ],
        ],
        'Multiempresa e compartilhamento público (jun/2026)' => [
            ['2026-06-10', '2026-06-10', 'Commits 5f5d212, 99887fe e 5abe215. Isolamento por empresa e link público de acompanhamento do pedido.'],
            [
                ['Isolamento multiempresa e compartilhamento público de pedidos', 18, 'CompanyScope global, vínculo de usuários a empresas, códigos de compartilhamento e página pública do pedido (commit 5f5d212).'],
                ['Remoção de rota de debug', 1, 'Exclusão de rota temporária de depuração (commit 99887fe).'],
                ['Aviso de sistema experimental', 1, 'Mensagem informando que o sistema não está aberto ao público (commit 5abe215).'],
            ],
        ],
        'Precificação automática (jun/2026)' => [
            ['2026-06-10', '2026-06-10', 'Commit 42684ae. Cálculo automático do preço no pedido.'],
            [
                ['Auto-preenchimento do preço unitário por marca/tipo/capacidade', 4, 'Cálculo automático do preço ao selecionar marca, tipo e capacidade no pedido (commit 42684ae).'],
            ],
        ],
        'RBAC, despesas, produtos e estoque (jun–jul/2026)' => [
            ['2026-06-11', '2026-07-15', 'Commits e49fd92 e ebcbd88. Controle de acesso, módulo financeiro, produtos e estoque.'],
            [
                ['RBAC completo, autorização CRUD, páginas show e correção do gráfico', 32, 'Papéis e permissões, middleware de autorização, policies por módulo, telas de detalhe e correção do gráfico do dashboard (commit e49fd92).'],
                ['Módulo de despesas, produtos e estoque + melhorias de layout/permissões', 28, 'CRUD de despesas com relatório, cadastro de produtos, controle de estoque, ajustes de permissões e layout (commit ebcbd88).'],
            ],
        ],
        'Identidade visual e PWA (jun–jul/2026)' => [
            ['2026-06-17', '2026-07-17', 'Commits 7f662e4, c6ade9d, bdb4890 e 47c55b4. Logo por empresa, PWA e textos informativos.'],
            [
                ['Upload de logo da empresa com fallback para logo padrão', 10, 'Upload/armazenamento da logo por empresa e fallback no sistema, pedidos e compartilhamento público (commit 7f662e4).'],
                ['Filtro por período nos pedidos e botão de instalação PWA', 8, 'Filtros de data na listagem de pedidos e instalação do PWA pelo dashboard (commit c6ade9d).'],
                ['Correção da instalação PWA em iOS e frase do IBU', 4, 'Ícones PNG, suporte a iOS e texto explicativo do IBU (commits bdb4890 e 47c55b4).'],
            ],
        ],
        'Status, estoque e menu (ago/2026)' => [
            ['2026-08-01', '2026-08-26', 'Commits 4731a33, 633d846, 9faa317, 64a4fbb e 51637fd. Fluxo de status, baixa de estoque e navegação.'],
            [
                ['Simplificação do status do pedido e baixa automática de estoque', 12, 'Novo fluxo de status, serviço de baixa de estoque e atualização de dashboard/listagens (commit 4731a33).'],
                ['Reorganização do menu lateral em seções', 2, 'Agrupamento dos itens em Cadastros e Administração (commit 633d846).'],
                ['Menu responsivo e Modo Desktop', 6, 'Botão de Modo Desktop, correções do menu mobile e ajuste para exibição apenas no modo desktop (commits 9faa317, 64a4fbb e 51637fd).'],
            ],
        ],
        'Precificação de produtos e tradução pt_BR (ago/2026)' => [
            ['2026-08-02', '2026-08-13', 'Commits ca37d78, cb79941, 8a4df5c e 2be4e39. Preços por produto, idioma e limpeza do repositório.'],
            [
                ['Precificação de produtos e reorganização do menu', 20, 'Novo CRUD de preços por produto, migration de tipos chopp/bebida e integração ao menu (commit ca37d78).'],
                ['Tradução completa da interface para pt_BR', 10, 'Pacote de idioma, arquivos de validação e tradução das telas do sistema (commit cb79941).'],
                ['Limpeza de relatórios e arquivos fora do sistema', 2, 'Remoção de relatórios e arquivos de desenvolvimento do versionamento (commits 8a4df5c e 2be4e39).'],
            ],
        ],
        'Estoque, despesas e correções (ago–set/2026)' => [
            ['2026-08-25', '2026-09-23', 'Commits 0e6af14, 3d5d34d, 5bf6c7e, f64e11d e aa83e97. Correções e evolução de estoque/despesas.'],
            [
                ['Correções de estoque, exclusão de itens e compartilhamento de pedido', 3, 'Null check em stockable, exclusão de itens de estoque e correção do erro 500 na página pública (commits 0e6af14, 3d5d34d e 5bf6c7e).'],
                ['Múltiplos itens com quantidade e reposição de estoque nas despesas', 14, 'Itens de despesa com quantidade, integração com estoque e ajustes no relatório (commit aa83e97).'],
                ['Detalhes dos itens nos pedidos e seed de treinamento', 8, 'Exibição detalhada dos itens e dados de treinamento (commit f64e11d).'],
            ],
        ],
        'Segurança multi-tenant e refatoração (set/2026)' => [
            ['2026-09-29', '2026-09-29', 'Commits 40155ae e fcd7878. Endurecimento do isolamento por empresa e organização do front-end.'],
            [
                ['Segurança multi-tenant fail-closed e bloqueio de usuários sem empresa', 16, 'Cadastro sem vínculo automático, CompanyScope fail-closed, tela de espera e testes de isolamento (commit 40155ae).'],
                ['Refatoração de CSS/JS, remoção de filtros redundantes e limpeza', 18, 'Extração de CSS/JS (desktop-mode e dashboard), remoção de filtros company_id redundantes e limpeza de arquivos órfãos (commit fcd7878).'],
            ],
        ],
        'Estoque configurável (set/2026)' => [
            ['2026-09-30', '2026-09-30', 'Commits 5898bd4 e 664a489. Baixa de estoque configurável por prazo e status.'],
            [
                ['Baixa de estoque configurável por dias e status', 15, 'CompanySetting, comando agendado, rastreamento de baixa e tela de configurações (commit 5898bd4).'],
                ['Ajuste do painel de configurações', 1, 'Contagem apenas de pedidos com baixa de estoque pendente (commit 664a489).'],
            ],
        ],
        'Pagamentos, anexos e manual (nov/2026)' => [
            ['2026-11-06', '2026-11-06', 'Commits 1ed8720, cf5dac6 e b81f69c. Financeiro parcelado, documentos e autenticação.'],
            [
                ['Pagamento parcelado, boletos e anexos nas despesas', 40, 'Parcelas, status de pagamento, anexos com download protegido, telas e testes automatizados (commit 1ed8720).'],
                ['Manual de funcionalidades no menu do usuário', 6, 'Manual em Markdown/PDF e link no menu (commit cf5dac6).'],
                ['Correção de cadastro/login Google e recuperação de senha', 8, 'Fluxo Google, cadastro, recuperação de senha e testes de autenticação (commit b81f69c).'],
            ],
        ],
    ];

    public function run(): void
    {
        $project = Project::where('title', 'AquiChopp')->firstOrFail();

        $sentinel = array_key_first(self::DELIVERABLES);

        if (Deliverable::where('project_id', $project->id)->where('name', $sentinel)->exists()) {
            $this->command->warn('As tarefas do AquiChopp já foram importadas — nada foi feito.');

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
                    'phase' => '6',
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
            'AquiChopp: %d entregas e %d tarefas importadas — %s h — R$ %s (R$ %d/h).',
            count(self::DELIVERABLES),
            $tasks,
            number_format($hours, 0, ',', '.'),
            number_format($hours * self::HOURLY_RATE, 2, ',', '.'),
            self::HOURLY_RATE,
        ));
    }
}
