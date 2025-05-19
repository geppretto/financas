<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('pagamentos_mensais', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('receita_id')->nullable();
        $table->unsignedBigInteger('despesa_id')->nullable();
        $table->string('mes'); // Exemplo: '2025-05'
        $table->boolean('pago')->default(false);
        $table->timestamps();

        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('receita_id')->references('id')->on('receitas')->onDelete('cascade');
        $table->foreign('despesa_id')->references('id')->on('despesas')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagamentos_mensais');
    }
};
