<?php

/**
 * Parâmetros do estimador de orçamento do site (/orcamento).
 *
 * O formulário público monta as perguntas, o cálculo em tempo real e a faixa
 * de investimento sugerida a partir desta configuração — ajuste valores e
 * horas aqui para calibrar a estimativa apresentada ao cliente.
 */
return [
    // Valor da hora de desenvolvimento da ATITEC (R$).
    'hourly_rate' => 50,

    // Margem aplicada em volta do valor calculado para sugerir a faixa.
    'range' => [
        'min_factor' => 0.80,
        'max_factor' => 1.25,
        'round_to' => 100,
    ],

    // Horas-base por tipo de projeto (mesmos valores do select do formulário).
    'types' => [
        'Software sob medida' => 120,
        'Consultoria comercial' => 18,
        'Automatização de atendimento' => 80,
        'Suporte técnico' => 4,
        'Outro' => 20,
    ],

    // Necessidades do sistema (checkboxes) — horas de desenvolvimento e custos recorrentes.
    // Custo exibido = horas × valor/hora (antes do multiplicador de prazo).
    'scope' => [
        'painel' => ['label' => 'Painel administrativo', 'hours' => 20],
        'area_cliente' => ['label' => 'Área do cliente com login', 'hours' => 16],
        'cadastros_relatorios' => ['label' => 'Cadastros, filtros e relatórios', 'hours' => 16],
        'usuarios_permissoes' => ['label' => 'Usuários e níveis de permissão', 'hours' => 8],
        'whatsapp' => ['label' => 'Integração com WhatsApp', 'hours' => 12],
        'pagamentos' => ['label' => 'Pagamentos online (PIX/cartão)', 'hours' => 16],
        'ia' => ['label' => 'Recursos de IA (chat/automações)', 'hours' => 40, 'monthly' => 250],
        'app_mobile' => ['label' => 'Aplicativo mobile', 'hours' => 60],
        'integracoes' => ['label' => 'Integração com sistemas existentes (ERP/planilhas)', 'hours' => 40],
        'site' => ['label' => 'Site institucional junto do sistema', 'hours' => 26],
    ],

    // Infraestrutura e serviços recorrentes — implantação (setup) e mensalidade.
    'infrastructure' => [
        'servidor' => ['label' => 'Servidor e banco de dados dedicados (implantação)', 'setup' => 2000, 'monthly' => 150],
        'dominio' => ['label' => 'Domínio próprio', 'setup' => 50],
        'email' => ['label' => 'E-mails profissionais no domínio', 'setup' => 60],
        'manutencao' => ['label' => 'Manutenção e suporte contínuos', 'monthly' => 250],
        'backup' => ['label' => 'Rotina de backup e monitoramento', 'monthly' => 80],
    ],

    // Prazo desejado — multiplicador aplicado sobre o desenvolvimento.
    'deadlines' => [
        'flexivel' => ['label' => 'Sem urgência (posso aguardar)', 'multiplier' => 1.0],
        'normal' => ['label' => '1 a 3 meses', 'multiplier' => 1.1],
        'urgente' => ['label' => 'Urgente (até 1 mês)', 'multiplier' => 1.3],
    ],
];
