<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $cases = [
            [
                'title' => 'Agricultura Familiar',
                'url' => 'https://agriculturafamiliar.atitec.com.br',
                'segment' => 'Agronegócio / Cooperativismo',
                'technologies' => 'Laravel, Livewire, MariaDB',
                'description' => 'Plataforma de gestão e comercialização para a agricultura familiar: organizações, produtos, vendas, compradores, diagnósticos e mapas.',
                'problem' => 'Cooperativas e associações precisavam organizar catálogo, vendas e diagnósticos comerciais em planilhas dispersas.',
                'solution' => 'Sistema web com CRM do agro, kanban de entregas, mapas, CEASA e relatórios com IA.',
                'results' => 'Operação centralizada, rastreabilidade comercial e relatórios gerenciais em minutos.',
                'status' => 'suporte',
                'progress' => 100,
                'featured' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Gestão de Estacionamento',
                'url' => null,
                'segment' => 'Serviços / Mobilidade',
                'technologies' => 'Laravel, MariaDB',
                'description' => 'Sistema de gerenciamento de estacionamento: controle de vagas, mensalistas, caixa e relatórios.',
                'problem' => 'Controle manual de entradas, saídas e mensalidades gerava filas e erros de caixa.',
                'solution' => 'Check-in/check-out ágil, tabelas de preço, mensalistas e fechamento de caixa.',
                'results' => 'Atendimento mais rápido e financeiro confiável.',
                'status' => 'entregue',
                'progress' => 100,
                'featured' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Porcos Crioulos',
                'url' => 'https://porcoscrioulos.com.br',
                'segment' => 'Agropecuária',
                'technologies' => 'Laravel, MariaDB',
                'description' => 'Gestão de criação de porcos crioulos: reprodução, manejo, sanidade e genealogias.',
                'problem' => 'Criadores sem registro consolidado de nascimentos, coberturas e sanidade do plantel.',
                'solution' => 'Fichas por animal, eventos de manejo, calendário sanitário e relatórios zootécnicos.',
                'results' => 'Rebanho rastreado e decisões de seleção embasadas em dados.',
                'status' => 'suporte',
                'progress' => 100,
                'featured' => true,
                'sort_order' => 3,
            ],
            [
                'title' => 'Simpósio do Leite',
                'url' => 'https://simposiodoleite.com.br',
                'segment' => 'Eventos',
                'technologies' => 'Laravel, MariaDB',
                'description' => 'Sistema de gerenciamento do evento Simpósio do Leite: inscrições, programação e certificados.',
                'problem' => 'Organização do evento com inscrições e certificados feitos à mão.',
                'solution' => 'Inscrições online, credenciamento, grade de palestras e emissão de certificados.',
                'results' => 'Evento profissionalizado com credenciamento ágil.',
                'status' => 'entregue',
                'progress' => 100,
                'featured' => false,
                'sort_order' => 4,
            ],
            [
                'title' => 'Sistema Ovinos',
                'url' => 'https://sistemaovinos.com.br',
                'segment' => 'Agropecuária / Rastreabilidade',
                'technologies' => 'Laravel, MariaDB',
                'description' => 'Gestão e rastreabilidade de cordeiros: do nascimento ao abate, com identidade individual.',
                'problem' => 'Falta de rastreabilidade individual comprometia a credibilidade da carne de cordeiro.',
                'solution' => 'Brincagem eletrônica, pesagens, manejos, lotes e etiquetas de rastreio.',
                'results' => 'Cadeia auditável e agregação de valor ao produto.',
                'status' => 'suporte',
                'progress' => 100,
                'featured' => true,
                'sort_order' => 5,
            ],
            [
                'title' => 'AquiChopp',
                'url' => 'https://aquichopp.com.br',
                'segment' => 'Varejo / Bebidas artesanais',
                'technologies' => 'Laravel, MariaDB',
                'description' => 'Sistema de gestão de vendas para empresa de bebidas artesanais: pedidos, estoque e entregas.',
                'problem' => 'Vendas por WhatsApp sem controle de estoque nem roteiro de entregas.',
                'solution' => 'Catálogo, pedidos, controle de barris/growlers, clientes e financeiro.',
                'results' => 'Vendas organizadas e ruptura de estoque reduzida.',
                'status' => 'entregue',
                'progress' => 100,
                'featured' => false,
                'sort_order' => 6,
            ],
            [
                'title' => 'Adoção ATITEC',
                'url' => 'https://adocao.atitec.com.br',
                'segment' => 'Social / ONGs',
                'technologies' => 'Laravel, MariaDB',
                'description' => 'Sistema de divulgação de animais perdidos e para adoção — doação da ATITEC às ONGs de Palmeira.',
                'problem' => 'ONGs divulgavam animais em grupos dispersos, sem vitrine única e atualizada.',
                'solution' => 'Vitrine pública com filtros, cadastro de animais, perdidos/encontrados e contato direto.',
                'results' => 'Mais visibilidade para adoção responsável na região.',
                'status' => 'suporte',
                'progress' => 100,
                'featured' => false,
                'sort_order' => 7,
            ],
        ];

        foreach ($cases as $case) {
            Project::updateOrCreate(
                ['slug' => Str::slug($case['title'])],
                [...$case, 'slug' => Str::slug($case['title'])]
            );
        }
    }
}
