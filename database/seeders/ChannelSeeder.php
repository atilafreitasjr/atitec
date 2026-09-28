<?php

namespace Database\Seeders;

use App\Models\Channel;
use Illuminate\Database\Seeder;

class ChannelSeeder extends Seeder
{
    public function run(): void
    {
        $channels = [
            ['type' => 'whatsapp', 'label' => 'WhatsApp', 'value' => '(41) 99848-9868', 'url' => 'https://wa.me/qr/QMHNRWLWTKQQC1', 'icon' => 'bi bi-whatsapp', 'sort_order' => 1, 'active' => true],
            ['type' => 'email', 'label' => 'E-mail', 'value' => 'contato@atitec.com.br', 'url' => 'mailto:contato@atitec.com.br', 'icon' => 'bi bi-envelope', 'sort_order' => 2, 'active' => true],
            ['type' => 'phone', 'label' => 'Telefone', 'value' => '(41) 99848-9868', 'url' => 'tel:+5541998489868', 'icon' => 'bi bi-telephone', 'sort_order' => 3, 'active' => true],
            ['type' => 'instagram', 'label' => 'Instagram', 'value' => '@atitec', 'url' => 'https://instagram.com/atitec', 'icon' => 'bi bi-instagram', 'sort_order' => 4, 'active' => true],
            ['type' => 'address', 'label' => 'Endereço', 'value' => 'Palmeira — Paraná — Brasil', 'url' => null, 'icon' => 'bi bi-geo-alt', 'sort_order' => 5, 'active' => true],
        ];

        foreach ($channels as $channel) {
            Channel::updateOrCreate(['type' => $channel['type']], $channel);
        }
    }
}
