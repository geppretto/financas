<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('tipo', 10)->default('despesa')->after('name');
            $table->string('icone', 40)->default('landmark')->after('tipo');
            $table->string('cor', 7)->default('#2563eb')->after('icone');
            $table->decimal('teto_mensal', 10, 2)->nullable()->after('cor');
        });

        // O ajuste anterior pode ter removido a coluna; receitas passam a usar categoria.
        if (!Schema::hasColumn('receitas', 'category_id')) {
            Schema::table('receitas', function (Blueprint $table) {
                $table->foreignId('category_id')->nullable()->after('valor')->constrained('categories')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('receitas', 'category_id')) {
            Schema::table('receitas', function (Blueprint $table) {
                $table->dropConstrainedForeignId('category_id');
            });
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'icone', 'cor', 'teto_mensal']);
        });
    }
};
