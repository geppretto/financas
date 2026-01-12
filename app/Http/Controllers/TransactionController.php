<?php
// app/Http/Controllers/TransactionController.php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::whereNull('parent_transaction_id')
            ->orWhere('current_installment', '>', 0);

        if ($request->filled('month') && $request->filled('year')) {
            $query->whereYear('due_date', $request->year)
                  ->whereMonth('due_date', $request->month);
        } elseif ($request->filled('year')) {
            $query->whereYear('due_date', $request->year);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $transactions = $query->orderBy('due_date', 'desc')->get();

        $summary = [
            'total_entradas' => $transactions->where('type', 'entrada')->where('status', 'pago')->sum('amount'),
            'total_saidas' => $transactions->where('type', 'saida')->where('status', 'pago')->sum('amount'),
            'pendentes' => $transactions->where('status', 'pendente')->sum('amount'),
            'saldo' => 0
        ];

        $summary['saldo'] = $summary['total_entradas'] - $summary['total_saidas'];

        $categories = Transaction::distinct()->pluck('category')->filter();

        return view('transactions.index', compact('transactions', 'summary', 'categories'));
    }

    public function create()
    {
        $categories = Transaction::distinct()->pluck('category')->filter();
        return view('transactions.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:entrada,saida',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'payment_type' => 'required|in:avista,parcelado',
            'installments' => 'required_if:payment_type,parcelado|integer|min:1|max:60',
            'transaction_date' => 'required|date',
            'category' => 'nullable|string|max:100',
            'notes' => 'nullable|string'
        ]);

        if ($validated['payment_type'] === 'avista') {
            $validated['installments'] = 1;
            $validated['status'] = 'pago';
            $validated['due_date'] = $validated['transaction_date'];
            Transaction::create($validated);
        } else {
            Transaction::createWithInstallments($validated);
        }

        return redirect()->route('transactions.index')
            ->with('success', 'Transação cadastrada com sucesso!');
    }

    public function edit(Transaction $transaction)
    {
        $categories = Transaction::distinct()->pluck('category')->filter();
        return view('transactions.edit', compact('transaction', 'categories'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'due_date' => 'required|date',
            'status' => 'required|in:pago,pendente',
            'category' => 'nullable|string|max:100',
            'notes' => 'nullable|string'
        ]);

        $transaction->update($validated);

        return redirect()->route('transactions.index')
            ->with('success', 'Transação atualizada com sucesso!');
    }

    public function destroy(Transaction $transaction)
    {
        if ($transaction->parent_transaction_id === null && $transaction->installmentTransactions()->count() > 0) {
            $transaction->installmentTransactions()->delete();
        }
        
        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('success', 'Transação excluída com sucesso!');
    }

    public function updateStatus(Transaction $transaction)
    {
        $transaction->update([
            'status' => $transaction->status === 'pago' ? 'pendente' : 'pago'
        ]);

        return back()->with('success', 'Status atualizado com sucesso!');
    }

    public function report(Request $request)
    {
        $year = $request->get('year', date('Y'));
        
        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $entradas = Transaction::where('type', 'entrada')
                ->where('status', 'pago')
                ->whereYear('due_date', $year)
                ->whereMonth('due_date', $month)
                ->whereNotNull('current_installment')
                ->sum('amount');

            $saidas = Transaction::where('type', 'saida')
                ->where('status', 'pago')
                ->whereYear('due_date', $year)
                ->whereMonth('due_date', $month)
                ->whereNotNull('current_installment')
                ->sum('amount');

            $monthlyData[$month] = [
                'entradas' => $entradas,
                'saidas' => $saidas,
                'saldo' => $entradas - $saidas
            ];
        }

        return view('transactions.report', compact('monthlyData', 'year'));
    }
}