<?php

namespace App\Support;

use App\Models\ClientMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Dados reais para os dropdowns do topo do AdminLTE (mensagens e notificações).
 * Substitui os dados de demonstração do pacote quando as tabelas existem.
 */
class AdminNavbar
{
    public static function messages(int $limit = 5): array
    {
        return ClientMessage::query()
            ->whereNull('read_at')
            ->where('user_id', '!=', Auth::id())
            ->with(['conversation', 'user'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (ClientMessage $message) => [
                'name' => $message->user?->name ?? 'Cliente',
                'conversation' => $message->conversation?->subject ?? 'Conversa',
                'text' => Str::limit($message->body, 70),
                'time' => $message->created_at?->diffForHumans() ?? '',
                'url' => $message->conversation
                    ? route('admin.conversations.show', $message->conversation)
                    : '#',
            ])
            ->all();
    }

    public static function messageCount(): int
    {
        return ClientMessage::query()
            ->whereNull('read_at')
            ->where('user_id', '!=', Auth::id())
            ->count();
    }

    public static function notifications(int $limit = 5): array
    {
        $user = Auth::user();

        if ($user === null || ! Schema::hasTable('notifications') || ! method_exists($user, 'unreadNotifications')) {
            return [];
        }

        return $user->unreadNotifications()->latest()->limit($limit)->get()
            ->map(fn ($notification) => [
                'icon' => $notification->data['icon'] ?? 'bi bi-bell-fill',
                'text' => $notification->data['message'] ?? ($notification->data['title'] ?? 'Notificação'),
                'time' => $notification->created_at?->diffForHumans() ?? '',
                'url' => $notification->data['url'] ?? '#',
            ])
            ->all();
    }

    public static function notificationCount(): int
    {
        $user = Auth::user();

        if ($user === null || ! Schema::hasTable('notifications') || ! method_exists($user, 'unreadNotifications')) {
            return 0;
        }

        return $user->unreadNotifications()->count();
    }
}
