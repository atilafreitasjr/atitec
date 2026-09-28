<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'company', 'project_type',
        'budget_range', 'details', 'status', 'source',
    ];

    public const STATUSES = [
        'novo' => 'Novo',
        'em_contato' => 'Em contato',
        'proposta' => 'Proposta',
        'fechado' => 'Fechado',
        'perdido' => 'Perdido',
    ];
}
