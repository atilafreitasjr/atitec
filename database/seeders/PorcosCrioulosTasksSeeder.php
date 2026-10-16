<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Deliverable;
use App\Models\DeliverableTask;
use App\Models\Project;
use Illuminate\Database\Seeder;

/**
 * Importação one-shot das tarefas HISTÓRICAS do projeto Porcos Crioulos
 * (escopo do contrato CR Agro, histórico Git e relatórios de modificações).
 *
 * Estimativas de horas de um desenvolvedor com valor/hora de R$ 50,00.
 * As tarefas entram como concluídas, em produção, com horas realizadas iguais
 * às estimadas para refletir o custo do trabalho já executado.
 * Idempotente: se as entregas desta importação já existirem, nada é feito.
 */
class PorcosCrioulosTasksSeeder extends Seeder
{
    private const HOURLY_RATE = 50;

    /** entregas => [ [título, horas, descrição], ... ] */
    private const DELIVERABLES = [
        'Infraestrutura, ambiente e deploy' => [
            ['Dockerização do ambiente local', 8, 'Containers de app, banco e phpMyAdmin; conexão lendo variáveis de ambiente para paridade entre dev e produção.'],
            ['Alinhamento do ambiente local à produção (MariaDB 11.4 / PHP 8.3)', 6, 'Banco e PHP na mesma versão do servidor, sql_mode de produção e script de grants do banco.'],
            ['Publicação em produção e validação do deploy', 8, 'Deploy no servidor Hostinger, execução das migrações e conferência das páginas principais.'],
            ['Correções pós-deploy exclusivas do MariaDB de produção', 6, 'Reuso de conexão única por requisição (limite da hospedagem) e ONLY_FULL_GROUP_BY nos módulos Óbito e Peso.'],
            ['Migrações SQL versionadas e proteção de arquivos internos', 4, 'Migrações no repositório, .gitignore de segredos e .htaccess bloqueando acesso HTTP a arquivos internos.'],
        ],
        'Rebranding e portal institucional' => [
            ['Rebranding do sistema Ovinos → Porcos Crioulos', 16, 'Marca, domínio, contatos e copyright em mais de 70 arquivos entre views, functions, portal e relatórios.'],
            ['Reformulação da página inicial do portal', 10, 'Novo layout, consolidação dos cards de contato e revisão dos textos institucionais.'],
            ['Carrossel de posts do Instagram e atualização das imagens', 6, 'Carrossel no portal, novo conjunto de imagens e remoção das fotos antigas.'],
            ['Cadastro público com ativação automática e segurança do formulário', 8, 'Serialização e escape dos campos, validação de e-mail e proteção contra injeção de cabeçalho.'],
        ],
        'Identidade visual e layout responsivo (mobile)' => [
            ['Centralização das bibliotecas front-end', 6, 'Bootstrap 5, jQuery e plugins em pastas únicas, com camada de compatibilidade do Bootstrap 3.'],
            ['Navegação responsiva com offcanvas', 12, 'Reescrita do menu em Bootstrap 5 com offcanvas para uso no campo e correção de warnings após logout.'],
            ['Correção do dropdown "Ações" e overflow das listagens', 5, 'Bootstrap carregado em duplicidade cancelava o dropdown; ajuste de CSS para o menu não ser cortado.'],
            ['Painel de controle com novo layout', 8, 'Redesenho da tela inicial do sistema com fundo, cartões e organização das informações.'],
            ['Fundo aleatório nos módulos', 6, 'Sorteio de uma das 34 imagens a cada carregamento, aplicado nos layouts moderno, legado e páginas independentes.'],
        ],
        'Modernização Bootstrap 5 dos módulos de manejo' => [
            ['Módulo Animais: cadastro, edição e listagem em Bootstrap 5', 14, 'Formulários, pesquisa e listagem migrados do HTML em tabelas para cards responsivos.'],
            ['Formulários de cadastro/edição: Peso, Famacha, ECC e Óbito', 12, 'Migração dos quatro módulos para o padrão de cards com grid responsivo.'],
            ['Formulários de cadastro/edição: Abate, Contábil, Dieta e Grupo', 12, 'Migração dos quatro módulos para o padrão de cards com grid responsivo.'],
            ['Formulários de pesquisa padronizados (Pesquisar/Limpar) nos 8 módulos', 10, 'Botões padronizados e ação server-side limpar_pesquisa em Peso, ECC, Famacha, Óbito, Abate, Contábil, Cobertura e Animais.'],
            ['Módulo Cobertura: refatoração com views separadas e layout Bootstrap 5', 10, 'Separação das views de listagem, formulário e pesquisa; correção do campo de fêmea doadora.'],
            ['Substituição do datepicker jQuery UI por input type="date"', 4, 'Remoção dos plugins de datepicker e padronização dos campos de data em todos os módulos.'],
        ],
        'Rebanho suíno (Porco Moura)' => [
            ['Adaptação do sistema para suínos / raça Porco Moura', 16, 'Espécie, raças, categorias e menus adequados à suinocultura.'],
            ['Passagem automática de categoria por idade e peso', 12, 'Critérios por porte de raça (médias/grandes e pequenas), tratamento de NULL com ELSE e Marrã fora da automação.'],
            ['Sexo individual dos animais', 12, 'Coluna sexo, inferência por categoria, exigência em categorias neutras e exibição em listagens e exportação.'],
            ['Cobertura: confirmação de prenhes e previsão de nascimento', 10, 'Checkbox de confirmação (+21 dias) e previsão calculada pela gestação da raça (padrão de 114 dias).'],
            ['Famacha condicionado à espécie', 4, 'Menu, listagens e QR Code só exibem Famacha quando existem ovinos/caprinos ativos.'],
            ['Árvore genealógica aprimorada e identificação do animal', 8, 'Melhoria da árvore, inclusão de nome e número de registro do animal.'],
        ],
        'Rastreabilidade e QR Code' => [
            ['Página de QR Code em HTML', 8, 'QR em base64 com brinco, botão "Ver animal" e link dinâmico (local em HTTP, produção em HTTPS).'],
            ['Tatuagem da carcaça', 8, 'Campo opcional no cadastro do abate, coluna na listagem de rendimento e exibição no QR Code.'],
            ['Dietas: descrição técnica e histórico de pesagens no QR Code', 6, 'Descrição pública x técnica e histórico de pesagens na página do QR Code.'],
        ],
        'Medicamentos e catálogos' => [
            ['Módulo de medicamentos com formulário por etapas', 14, 'Espécie, especificações, laboratórios, princípios ativos e bula em cadastro guiado.'],
            ['Cadastro e listagem de laboratórios', 5, 'CRUD com estados de aprovação e associação a medicamentos.'],
            ['Cadastro e listagem de princípios ativos', 5, 'CRUD com estados de aprovação e associação a medicamentos.'],
            ['Migrações e associações com controle de aprovação', 8, 'Tabelas com chaves estrangeiras, exclusão em cascata e validação de propriedade/administrador nas edições.'],
        ],
        'Índices, relatórios e indicadores' => [
            ['Reformulação do módulo de índices', 12, 'Gráfico de pizza por categoria, cálculo de prolificidade e indicadores do rebanho.'],
            ['Gráficos de pesagem por grupo e resumo mensal', 10, 'Quantidade e média dos últimos 12 meses, com correção de ganho de peso e divisão por zero.'],
            ['Indicadores de natalidade, mortalidade e evolução do rebanho', 8, 'Painel gerencial com os indicadores zootécnicos previstos no contrato.'],
            ['Exportação Excel com os novos campos', 4, 'Coluna de sexo e demais campos recentes incluídos nas exportações.'],
            ['Documentação técnica (passagem de categoria e taxa de concepção bayesiana)', 6, 'Explicação dos critérios de passagem automática e da interpretação do indicador bayesiano.'],
        ],
        'Autenticação, usuários e segurança' => [
            ['Correção do fluxo de login do portal e navegação', 10, 'Login estável pelo portal, ajustes de sessão e navegação entre módulos.'],
            ['Recuperação de senha por e-mail (token de uso único via SMTP)', 8, 'Fluxo completo de recuperação com token de uso único enviado por e-mail.'],
            ['Login com Google (OAuth/OIDC) e hash com password_hash', 12, 'Entrada com Google no portal, correção de falha de comunicação e migração do hash de senha.'],
            ['Máscaras de CPF/CEP/telefone e melhorias no módulo de usuários', 6, 'Máscaras nos formulários e controle de acesso por serviço.'],
            ['Permissões por endpoint e validação de propriedade', 12, 'Edição/exclusão restritas à propriedade do usuário e permissões separadas por ação (manter, editar, excluir, ativar).'],
            ['Correções de segurança (escape SQL/HTML e injeção de cabeçalhos)', 6, 'Escape de saída e de consultas, validação de sessão e troca de animais restrita à propriedade.'],
        ],
        'Chat IA e suporte' => [
            ['Widget de Chat IA com contexto do rebanho', 19, 'Widget flutuante, sugestões por módulo e endpoints autenticados com a permissão do serviço na API DeepSeek.'],
            ['Formulário de suporte e contato corrigidos', 3, 'Envio via POST, validação, escape das mensagens e resposta de sucesso alinhada entre formulário e endpoint.'],
        ],
        'Correções gerais e qualidade' => [
            ['Compatibilidade com PHP 8 (warnings e deprecations)', 10, 'Correção de variáveis indefinidas, recursos depreciados e tipagem nos controllers e functions.'],
            ['Correção da listagem de animais (LEFT JOIN, FBB e categorias)', 6, 'Animais sem grupo voltaram a aparecer, limite do FBB ampliado e novas categorias nos selects.'],
            ['Correção de encoding UTF-8 do banco e dados corrompidos', 4, 'Conexão com utf8 e correção dos registros duplamente codificados.'],
            ['Limpeza de código morto, menus antigos e imagens sem uso', 4, 'Remoção de arquivos e imagens sem referência e de menus legados.'],
        ],
        'Testes e publicação' => [
            ['Testes de campo e publicação em homologação', 12, 'Validação das funcionalidades em ambiente de homologação e ajustes apontados.'],
            ['Correções pós-homologação e período de garantia', 8, 'Correção de bugs apontados após a entrega, dentro da garantia contratual.'],
        ],
    ];

    public function run(): void
    {
        $project = Project::where('title', 'Porcos Crioulos')->firstOrFail();

        $sentinel = array_key_first(self::DELIVERABLES);

        if (Deliverable::where('project_id', $project->id)->where('name', $sentinel)->exists()) {
            $this->command->warn('As tarefas históricas do Porcos Crioulos já foram importadas — nada foi feito.');

            return;
        }

        if (! $project->client_id) {
            $project->update(['client_id' => Client::where('company', 'CR Agro')->value('id')]);
        }

        $tasks = 0;
        $hours = 0;

        foreach (self::DELIVERABLES as $name => $items) {
            $deliverable = Deliverable::create([
                'project_id' => $project->id,
                'name' => $name,
                'status' => 'concluido',
                'hours_estimated' => array_sum(array_column($items, 1)),
            ]);

            foreach ($items as $index => [$title, $taskHours, $description]) {
                DeliverableTask::create([
                    'project_id' => $project->id,
                    'deliverable_id' => $deliverable->id,
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
            'Porcos Crioulos: %d entregas e %d tarefas importadas — %s h — R$ %s (R$ %d/h).',
            count(self::DELIVERABLES),
            $tasks,
            number_format($hours, 0, ',', '.'),
            number_format($hours * self::HOURLY_RATE, 2, ',', '.'),
            self::HOURLY_RATE,
        ));
    }
}
