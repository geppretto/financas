<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PagamentoMensal extends Model
{
    use HasFactory;

    protected $table = 'pagamentos_mensais';

    protected $fillable = [
        'user_id',
        'receita_id',
        'despesa_id',
        'mes',
        'pago',
    ];

    protected $casts = [
        'pago' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function receita()
    {
        return $this->belongsTo(Receita::class);
    }

    public function despesa()
    {
        return $this->belongsTo(Despesa::class);
    }
}
