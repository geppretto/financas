<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Receita extends Model
{
    protected $fillable = ['descricao', 'valor', 'data', 'pago', 'user_id', 'category_id', 'conta'];

    protected $casts = [
        'data' => 'date',
        'pago' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Receitas com data do mês informado + receitas mensais (sem data)
     * já existentes até o fim desse mês.
     */
    public function scopeDoMes(Builder $query, string $mes, string $ano): Builder
    {
        $fimDoMes = Carbon::create((int) $ano, (int) $mes, 1)->endOfMonth();

        return $query->where(function (Builder $q) use ($mes, $ano, $fimDoMes) {
            $q->where(function (Builder $q2) use ($mes, $ano) {
                $q2->whereMonth('data', $mes)->whereYear('data', $ano);
            })->orWhere(function (Builder $q2) use ($fimDoMes) {
                $q2->whereNull('data')
                    ->where(function (Builder $q3) use ($fimDoMes) {
                        $q3->whereNull('created_at')->orWhere('created_at', '<=', $fimDoMes);
                    });
            });
        });
    }

    public function vigenteNoMes(int $ano, int $mes): bool
    {
        return $this->created_at === null
            || $this->created_at->lte(Carbon::create($ano, $mes, 1)->endOfMonth());
    }
}
