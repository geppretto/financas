<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // category_id era string e sem FK: normaliza valores inválidos antes de converter.
        DB::table('despesas')
            ->whereNotNull('category_id')
            ->whereNotIn('category_id', DB::table('categories')->select('id'))
            ->update(['category_id' => null]);

        DB::table('despesas')
            ->whereNotNull('user_id')
            ->whereNotIn('user_id', DB::table('users')->select('id'))
            ->update(['user_id' => null]);

        DB::table('receitas')
            ->whereNotNull('user_id')
            ->whereNotIn('user_id', DB::table('users')->select('id'))
            ->update(['user_id' => null]);

        Schema::table('despesas', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->change();
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        Schema::table('despesas', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('receitas', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        // receitas.category_id nunca foi usado pela aplicação.
        if (Schema::hasColumn('receitas', 'category_id')) {
            Schema::table('receitas', function (Blueprint $table) {
                $table->dropColumn('category_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('receitas', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->string('category_id')->nullable()->after('valor');
        });

        Schema::table('despesas', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('despesas', function (Blueprint $table) {
            $table->string('category_id')->nullable()->change();
        });
    }
};
