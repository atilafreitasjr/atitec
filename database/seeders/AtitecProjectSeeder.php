<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Deliverable;
use App\Models\DeliverableTask;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Projeto interno da ATITEC (site institucional, cartão digital e workspace),
 * com o cadastro da empresa e as tarefas históricas já executadas.
 *
 * Horas e valores seguem o mesmo critério do projeto Porcos Crioulos:
 * um desenvolvedor a R$ 50,00/h, com horas realizadas iguais às estimadas
 * porque o trabalho já está em produção. Idempotente: se as entregas já
 * existirem, apenas o cadastro do projeto/empresa é atualizado.
 */
class AtitecProjectSeeder extends Seeder
{
    private const HOURLY_RATE = 50;

    /** entregas => [ [título, horas, descrição], ... ] */
    private const DELIVERABLES = [
        'Fundação do projeto e infraestrutura' => [
            ['Estrutura Laravel 13 com Sail (Docker, MariaDB, Redis e phpMyAdmin)', 10, 'Projeto base, containers de desenvolvimento e paridade com o servidor de produção.'],
            ['Tema AdminLTE 4 preto/amarelo e build Vite + Tailwind v4', 12, 'Identidade visual da ATITEC aplicada ao painel administrativo e ao workspace do cliente.'],
            ['Deploy na Hostinger com public_html simbólico e checklist de produção', 10, 'Clone do repositório fora do docroot, symlink para public, variáveis de ambiente e permissões.'],
            ['Contorno do build Vite no servidor (build local + rsync)', 6, 'Publicação do build gerado localmente para evitar a falha do rolldown na hospedagem compartilhada.'],
            ['Cache, sessão e filas em banco no ambiente compartilhado', 5, 'Configuração sem Redis para cache, sessões e processamento de filas no servidor.'],
            ['Subdomínio do cartão e shim do adocao.atitec.com.br', 8, 'Rota por domínio para o cartão digital e porta de entrada segura para o sistema de adoção.'],
            ['Documentação de ambiente, deploy e AGENTS.md', 6, 'Runbook operacional com comandos, portas, banco, permissões e cuidados de produção.'],
        ],
        'Site institucional' => [
            ['Layout institucional responsivo, menu mobile e identidade visual', 12, 'Header, navegação mobile, rodapé e componentes base do site público.'],
            ['Home com hero, serviços, destaques do portfólio e CTAs', 10, 'Página inicial com posicionamento, cases em destaque e chamadas para orçamento e WhatsApp.'],
            ['Páginas de serviços: desenvolvimento, consultoria e automação', 8, 'Conteúdo comercial das três frentes de atuação, com escopo e diferenciais.'],
            ['Portfólio e páginas de case com dados dos projetos', 8, 'Listagem pública e páginas individuais com problema, solução, resultados e tecnologias.'],
            ['Página Sobre, missão/visão/valores e stack', 5, 'Apresentação institucional da ATITEC e cadastro público da empresa.'],
            ['Formulário de contato integrado a leads e pré-preenchimento', 8, 'Registro de contatos no CRM, validação e preenchimento automático para usuários logados.'],
            ['Orçamento estruturado com parâmetros e cálculo no servidor', 12, 'Wizard com tipo de projeto, necessidades, infraestrutura, prazo e recálculo confiável no backend.'],
            ['Estimador de investimento ao vivo e sugestão de faixa', 8, 'Cálculo no navegador, faixa de investimento e memória de cálculo gravada no lead.'],
            ['Editor e renderização segura de Markdown nos orçamentos', 6, 'Barra de formatação no formulário e sanitização do HTML na exibição administrativa.'],
            ['Máscaras de CPF/CNPJ/CEP/telefone e busca de CEP com fallback', 8, 'Máscaras no cliente e consulta ViaCEP com fallback para BrasilAPI nos cadastros.'],
            ['Dados oficiais da empresa no site (CNPJ, endereço e canais)', 4, 'CNPJ, razão social, endereço e WhatsApp oficiais no rodapé, contato e página Sobre.'],
        ],
        'Cartão digital' => [
            ['Página do cartão com foto, identidade e ações diretas', 10, 'Cartão de visita digital com WhatsApp, ligação, e-mail e SMS.'],
            ['vCard para salvar o contato no celular', 4, 'Download de arquivo .vcf com nome, cargo, empresa, telefone e e-mail.'],
            ['Portfólio no cartão e formulário que gera lead', 6, 'Cases em produção e captação de contatos com registro no CRM.'],
            ['Abertura direta no subdomínio cartao.atitec.com.br', 4, 'Rota por domínio abrindo o cartão na raiz e URLs absolutas para o site principal.'],
            ['Compartilhamento, links absolutos e QR de WhatsApp', 6, 'Botões de compartilhar/salvar contato e acesso direto ao WhatsApp por QR code.'],
        ],
        'Autenticação e cadastro de clientes' => [
            ['Login, logout e proteção de rotas por papel', 8, 'Autenticação por sessão, middleware de perfil e redirecionamento por tipo de usuário.'],
            ['Cadastro com CPF, telefone e endereço estruturado', 10, 'Formulário público com validação e criação transacional de usuário e cliente.'],
            ['Empresa opcional com CNPJ e endereço próprio ou do cliente', 10, 'Cadastro de pessoa jurídica com CNPJ validado e endereço empresarial.'],
            ['Validação de CPF/CNPJ, unicidade e mensagens em pt-BR', 8, 'Regras próprias, tratamento de duplicidade e tradução dos erros.'],
            ['Login com Google (OAuth/Socialite) e vinculação pelo e-mail', 12, 'Entrada social, criação automática de cliente e vínculo com cadastros existentes.'],
            ['Fluxo de completar cadastro para novos usuários do Google', 8, 'Complemento de CPF, telefone e endereço antes de liberar o workspace.'],
            ['Política de senha, checklist e toggle mostrar/ocultar', 6, 'Regras centralizadas e feedback em tempo real nos formulários.'],
        ],
        'Portal do cliente' => [
            ['Workspace em AdminLTE com menu específico do cliente', 10, 'Área autenticada com o mesmo tema do admin e navegação adaptada ao cliente.'],
            ['Dashboard com projetos, faturas e propostas', 8, 'Visão resumida do relacionamento, financeiro e pendências do cliente.'],
            ['Projetos, entregas e progresso detalhado', 10, 'Lista de projetos e página individual com entregas, tarefas e andamento.'],
            ['Kanban read-only com as fases do projeto', 8, 'Acompanhamento visual das fases sem permissão de alteração pelo cliente.'],
            ['Financeiro com faturas, pagamentos e cortesia', 10, 'Faturas, saldo, pagamentos parciais, cortesia da ATITEC e download de boleto.'],
            ['Propostas com aceite/recusa e conversão em projeto', 10, 'Resposta do cliente no workspace e conversão da proposta aceita em projeto.'],
            ['Documentos privados com visibilidade controlada', 8, 'Upload no projeto e download autenticado apenas de arquivos liberados ao cliente.'],
            ['Mensagens 1:1, comunicados, leitura e encerramento', 12, 'Thread com o cliente, comunicados gerais, marcação de leitura e reabertura.'],
            ['Central de ajuda e guia do cliente', 8, 'Documentação navegável dentro do workspace, com links entre as páginas.'],
            ['Pré-preenchimento e experiência responsiva', 8, 'Dados do cliente nos formulários públicos e ajustes de usabilidade no portal.'],
        ],
        'Administração e CRM' => [
            ['Dashboard operacional: a faturar, a receber, vencidos e propostas', 12, 'Indicadores de tarefas prontas para faturar, saldo, inadimplência e leads sem vínculo.'],
            ['CRUD de clientes com vínculo ao usuário e endereço', 10, 'Cadastro administrativo completo, máscaras e associação ao acesso do cliente.'],
            ['CRUD de projetos com arquivamento e proteção de histórico', 10, 'Gestão de projetos, ordenação, destaque, status e bloqueio de exclusão com histórico.'],
            ['CRUD de faturas com tarefas faturáveis e boleto', 12, 'Emissão de cobranças, vínculo de tarefas, anexo de boleto e status financeiro.'],
            ['Pagamentos parciais com edição, remoção e recálculo', 8, 'Registro de recebimentos e atualização automática de saldo e status.'],
            ['Leads com associação automática/manual ao cliente', 8, 'Vinculação pelo e-mail no recebimento e associação manual pelo admin.'],
            ['Proposta comercial no lead e aviso pelo WhatsApp', 10, 'Valores, resumo, validade, aceite no portal e texto pronto para cobrança/proposta.'],
            ['Canais do site com máscara e ordenação', 5, 'CRUD dos canais exibidos no site e no cartão digital.'],
            ['Usuários e papéis Spatie com matriz de permissões', 15, 'Catálogo de permissões, papéis padrão, matriz editável e invariantes de segurança.'],
            ['Conversas e comunicados com navbar real', 8, 'Admin de conversas, contadores reais no topo e comunicados gerais.'],
        ],
        'Kanban e gestão de entregas' => [
            ['Models Deliverable e DeliverableTask com soft deletes', 8, 'Entregas e tarefas vinculadas ao projeto, com fases, horas e exclusão lógica.'],
            ['Components Livewire de entregas (index/create/edit/show)', 12, 'CRUD completo de entregas no escopo do projeto.'],
            ['Components Livewire de tarefas (index/create/edit)', 10, 'CRUD de tarefas com responsável, prioridade, fase, datas e valores.'],
            ['Board Kanban com fases, drag-and-drop e filtros', 14, 'Quadro por fases, movimentação de tarefas e filtros por entrega/responsável.'],
            ['Escopo por projeto e autorização com ScopedToProject', 8, 'Isolamento por projeto e revalidação de vínculo em todas as mutações.'],
            ['Rotas administrativas com scopeBindings e permissões', 6, 'Rotas aninhadas do projeto com middleware de permissão por ação.'],
            ['Importação de dados reais e seeders históricos', 10, 'Carga one-shot de entregas e tarefas de projetos existentes.'],
            ['Ajustes de UX do Kanban e das listagens', 7, 'Refinamento de filtros, ordenação e clareza das colunas.'],
        ],
        'Financeiro por tarefa' => [
            ['Horas realizadas, valor/hora e vínculo com a fatura', 10, 'Campos financeiros por tarefa e proteção após o faturamento.'],
            ['Fatura a partir das tarefas selecionadas', 10, 'Seleção de tarefas concluídas e geração automática da cobrança.'],
            ['Tarefas faturáveis no formulário de fatura (endpoint + JS)', 8, 'Carregamento por projeto, checkboxes, soma automática e sugestão de valor.'],
            ['Proteção de tarefas faturadas e desvínculo ao excluir fatura', 8, 'Bloqueio de edição financeira e retorno das tarefas à lista faturável.'],
            ['Custos estimado/realizado e saldo a faturar', 9, 'Indicadores de custo por projeto/entrega e valor pronto para cobrança.'],
        ],
        'Guia do cliente e documentação' => [
            ['Guia em Markdown com páginas por assunto', 10, 'Conteúdo de acesso, projetos, financeiro, documentos, mensagens e FAQ.'],
            ['Renderização segura e reescrita de links internos', 8, 'Markdown sem HTML perigoso e navegação correta entre as páginas.'],
            ['Ajuda dentro do workspace e navegação', 6, 'Acesso ao guia no portal do cliente e no painel administrativo.'],
        ],
        'Qualidade, segurança e evolução' => [
            ['Testes de fumaça do site e fluxos de autenticação', 10, 'Cobertura das páginas públicas, cadastro e login.'],
            ['Testes de workspace, faturas, mensagens e conversas', 10, 'Cenários dos principais fluxos do portal e do admin.'],
            ['Pint e padronização do estilo PHP', 4, 'Formatação automática e estilo consistente no código.'],
            ['Correções de autorização, upload e HTML', 10, 'Revisão de acessos, validação de arquivos e sanitização de conteúdo.'],
            ['Ajustes pós-deploy e suporte contínuo', 10, 'Correções após publicação, acompanhamento e evolução do ambiente.'],
            ['Evolução de orçamento, máscaras e experiência', 8, 'Melhorias incrementais no wizard, formulários e usabilidade.'],
        ],
    ];

    public function run(): void
    {
        $admin = User::where('email', 'admin@atitec.com.br')->first();
        $cnpj = preg_replace('/\D+/', '', config('company.cnpj'));

        $client = Client::where('cnpj', $cnpj)->first()
            ?? Client::where('name', 'Empresa ATITEC')->first()
            ?? Client::where('email', 'admin@atitec.com.br')->first();

        $clientData = [
            'name' => 'Empresa ATITEC',
            'company' => 'ATITEC Consultoria em Informática Ltda',
            'email' => 'admin@atitec.com.br',
            'phone' => config('company.phone'),
            'cnpj' => $cnpj,
            'company_same_address' => true,
            'address' => 'Rua Coronel Otoni Ferreira Maciel',
            'address_number' => '840',
            'address_complement' => null,
            'neighborhood' => 'Centro',
            'city' => 'Palmeira',
            'state' => 'PR',
            'zip_code' => '84130000',
            'notes' => 'Cadastro interno da ATITEC. Contato público: '.config('company.email').' · '.config('company.whatsapp'),
            'active' => true,
        ];

        if ($client) {
            $client->update($clientData);
        } else {
            $client = Client::create($clientData);
        }

        if ($admin && ! $client->user_id) {
            $client->update(['user_id' => $admin->id]);
        }

        $project = Project::where('slug', 'atitec')->first();

        $totalHours = collect(self::DELIVERABLES)
            ->flatMap(fn (array $items) => array_column($items, 1))
            ->sum();

        $projectData = [
            'client_id' => $client->id,
            'title' => 'ATITEC',
            'slug' => 'atitec',
            'description' => 'Site institucional, cartão digital e workspace do cliente da ATITEC: portfólio, orçamento online, portal com projetos, kanban, documentos, propostas, mensagens e financeiro.',
            'problem' => 'A ATITEC precisava de uma presença digital própria e de um canal organizado para apresentar projetos, receber orçamentos e acompanhar o relacionamento com cada cliente.',
            'solution' => 'Plataforma Laravel com site institucional, cartão digital por QR code, CRM de leads, workspace do cliente, Kanban de entregas e financeiro por tarefa.',
            'results' => 'Processo comercial e operação centralizados, atendimento por WhatsApp e portal, e histórico completo de projetos, horas e cobranças.',
            'url' => 'https://atitec.com.br',
            'segment' => 'Tecnologia / Software house',
            'technologies' => 'Laravel 13, Livewire, AdminLTE 4, MariaDB, Tailwind CSS, Vite',
            'status' => 'suporte',
            'progress' => 100,
            'budget' => $totalHours * self::HOURLY_RATE,
            'featured' => true,
            'sort_order' => 0,
        ];

        if (! $project) {
            $project = Project::create($projectData);
        } else {
            $project->update($projectData);
        }

        $sentinel = array_key_first(self::DELIVERABLES);

        if (Deliverable::where('project_id', $project->id)->where('name', $sentinel)->exists()) {
            $this->command->warn('As tarefas históricas do projeto ATITEC já foram importadas — tarefas mantidas.');

            return;
        }

        $tasks = 0;
        $hours = 0;

        foreach (self::DELIVERABLES as $name => $items) {
            $deliverable = Deliverable::create([
                'project_id' => $project->id,
                'name' => $name,
                'description' => 'Frente de trabalho do projeto ATITEC.',
                'status' => 'concluido',
                'hours_estimated' => array_sum(array_column($items, 1)),
            ]);

            foreach ($items as $index => [$title, $taskHours, $description]) {
                DeliverableTask::create([
                    'project_id' => $project->id,
                    'deliverable_id' => $deliverable->id,
                    'user_id' => $admin?->id,
                    'title' => $title,
                    'description' => $description,
                    'status' => 'concluido',
                    'priority' => 'media',
                    'phase' => '6',
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
            'ATITEC: %d entregas e %d tarefas importadas — %s h — R$ %s (R$ %d/h).',
            count(self::DELIVERABLES),
            $tasks,
            number_format($hours, 0, ',', '.'),
            number_format($hours * self::HOURLY_RATE, 2, ',', '.'),
            self::HOURLY_RATE,
        ));
    }
}
