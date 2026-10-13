<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public const ICONES = [
        'landmark', 'credit-card', 'clapperboard', 'banknote', 'users', 'gamepad-2',
        'shopping-bag', 'utensils', 'car', 'house', 'arrow-left-right', 'briefcase-medical',
    ];

    public const CORES = [
        '#2563eb', '#4f46e5', '#9333ea', '#e11d48', '#dc2626', '#d97706', '#eab308', '#059669', '#0891b2', '#334155',
    ];

    protected $fillable = ['name', 'user_id', 'tipo', 'icone', 'cor', 'teto_mensal'];

    protected $casts = [
        'teto_mensal' => 'decimal:2',
    ];

    public function despesas()
    {
        return $this->hasMany(Despesa::class);
    }

    public function receitas()
    {
        return $this->hasMany(Receita::class);
    }
}
