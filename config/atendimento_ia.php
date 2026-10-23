<?php

/**
 * Atendimento público com IA (DeepSeek).
 *
 * O assistente responde dúvidas sobre serviços, orçamento e contratação da
 * ATITEC. Quando a resposta depende de uma decisão humana, a conversa é
 * encaminhada para a equipe no admin (módulo Atendimento IA).
 */
return [
    'enabled' => (bool) env('ATENDIMENTO_IA_ENABLED', true),

    'assistant_name' => 'Atendente ATITEC',

    'welcome' => 'Olá! Eu sou o atendente virtual da ATITEC. Posso explicar nossos serviços, como funciona o orçamento e os valores de referência. Como posso ajudar?',

    'quick_replies' => [
        'Quais serviços vocês oferecem?',
        'Quanto custa um sistema sob medida?',
        'Como funciona o orçamento?',
        'Qual é o prazo de entrega?',
        'Quero falar com um especialista',
    ],

    'disclaimer' => 'Respostas geradas por IA. Confirme valores, prazos e condições com a equipe da ATITEC.',

    'max_message_length' => 2000,

    // Quantas mensagens da conversa são reenviadas como contexto à IA.
    'history_limit' => 24,
];
