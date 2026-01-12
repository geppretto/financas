<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['entrada', 'saida']);
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_type', ['avista', 'parcelado']);
            $table->integer('installments')->default(1);
            $table->integer('current_installment')->default(1);
            $table->date('transaction_date');
            $table->date('due_date');
            $table->enum('status', ['pago', 'pendente'])->default('pendente');
            $table->string('category')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('parent_transaction_id')->nullable()->constrained('transactions')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};