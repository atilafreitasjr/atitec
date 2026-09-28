<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = [
        'subject', 'client_id', 'created_by', 'is_broadcast', 'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_broadcast' => 'boolean',
            'closed_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ClientMessage::class)->orderBy('created_at');
    }
}
