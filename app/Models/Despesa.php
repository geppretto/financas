<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Despesa extends Model
{
    protected $fillable = [
        'descricao', 'valor', 'data', 'pago', 'user_id', 'category_id', 'origem', 'notas',
        'recorrente', 'recorrencia_fim',
    ];

    protected $casts = [
        'data' => 'date',
        'pago' => 'boolean',
        'recorrente' => 'boolean',
        'recorrencia_fim' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Despesas pontuais/parceladas do mês + recorrentes ativas nesse mês
     * (a data cadastrada é o início; repete todo mês até `recorrencia_fim`).
     */
    public function scopeDoMes(Builder $query, string $mes, string $ano): Builder
    {
        $inicio = Carbon::create((int) $ano, (int) $mes, 1)->startOfMonth();
        $fim = $inicio->copy()->endOfMonth();

        return $query->where(function (Builder $q) use ($mes, $ano, $inicio, $fim) {
            $q->where(function (Builder $q2) use ($mes, $ano) {
                $q2->where('recorrente', false)->whereMonth('data', $mes)->whereYear('data', $ano);
            })->orWhere(function (Builder $q2) use ($inicio, $fim) {
                $q2->where('recorrente', true)
                    ->where('data', '<=', $fim->toDateString())
                    ->where(function (Builder $q3) use ($inicio) {
                        $q3->whereNull('recorrencia_fim')->orWhere('recorrencia_fim', '>=', $inicio->toDateString());
                    });
            });
        });
    }

    public function ocorreNoMes(int $ano, int $mes): bool
    {
        if ($this->data === null) {
            return false;
        }

        if (!$this->recorrente) {
            return (int) $this->data->format('Y') === $ano && (int) $this->data->format('m') === $mes;
        }

        $inicio = Carbon::create($ano, $mes, 1)->startOfMonth();
        $fim = $inicio->copy()->endOfMonth();

        if ($this->data->gt($fim)) {
            return false;
        }

        return $this->recorrencia_fim === null || $this->recorrencia_fim->gte($inicio);
    }

    /** Data de vencimento da ocorrência no mês (ajusta para meses mais curtos). */
    public function dataNoMes(int $ano, int $mes): Carbon
    {
        if (!$this->recorrente || $this->data === null) {
            return $this->data;
        }

        $dia = min($this->data->day, Carbon::create($ano, $mes, 1)->daysInMonth);

        return Carbon::create($ano, $mes, $dia);
    }
}
