<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('receitas', function (Blueprint $table) {
            $table->string('category_id')->nullable()->after('valor');
        });

        Schema::table('despesas', function (Blueprint $table) {
            $table->string('category_id')->nullable()->after('valor');
        });
    }

    public function down(): void
    {
        Schema::table('receitas', function (Blueprint $table) {
            $table->dropColumn('category_id');
        });

        Schema::table('despesas', function (Blueprint $table) {
            $table->dropColumn('category_id');
        });
    }
};
