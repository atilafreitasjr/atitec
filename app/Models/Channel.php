<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $fillable = [
        'type', 'label', 'value', 'url', 'icon', 'sort_order', 'active',
    ];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }
}
