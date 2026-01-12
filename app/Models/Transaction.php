<?php
// app/Models/Transaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'description',
        'amount',
        'payment_type',
        'installments',
        'current_installment',
        'transaction_date',
        'due_date',
        'status',
        'category',
        'notes',
        'parent_transaction_id'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2'
    ];

    public function parent()
    {
        return $this->belongsTo(Transaction::class, 'parent_transaction_id');
    }

    public function installmentTransactions()
    {
        return $this->hasMany(Transaction::class, 'parent_transaction_id');
    }

    public static function createWithInstallments($data)
    {
        if ($data['payment_type'] === 'parcelado' && $data['installments'] > 1) {
            $parentTransaction = self::create([
                'type' => $data['type'],
                'description' => $data['description'],
                'amount' => $data['amount'],
                'payment_type' => 'parcelado',
                'installments' => $data['installments'],
                'current_installment' => 0,
                'transaction_date' => $data['transaction_date'],
                'due_date' => $data['transaction_date'],
                'status' => 'pago',
                'category' => $data['category'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $installmentAmount = round($data['amount'] / $data['installments'], 2);
            $dueDate = Carbon::parse($data['transaction_date']);

            for ($i = 1; $i <= $data['installments']; $i++) {
                $amount = $installmentAmount;
                if ($i === $data['installments']) {
                    $amount = $data['amount'] - ($installmentAmount * ($data['installments'] - 1));
                }

                self::create([
                    'type' => $data['type'],
                    'description' => $data['description'] . " - Parcela {$i}/{$data['installments']}",
                    'amount' => $amount,
                    'payment_type' => 'parcelado',
                    'installments' => $data['installments'],
                    'current_installment' => $i,
                    'transaction_date' => $data['transaction_date'],
                    'due_date' => $dueDate->copy()->addMonths($i - 1),
                    'status' => 'pendente',
                    'category' => $data['category'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'parent_transaction_id' => $parentTransaction->id
                ]);
            }

            return $parentTransaction;
        }

        return self::create($data);
    }
}