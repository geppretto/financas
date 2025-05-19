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
    Schema::table('despesas', function (Illuminate\Database\Schema\Blueprint $table) {
        $table->unsignedBigInteger('user_id')->nullable()->after('id');
        // Não adicionamos a foreign key aqui
    });
}

public function down()
{
    Schema::table('despesas', function (Illuminate\Database\Schema\Blueprint $table) {
        $table->dropColumn('user_id');
    });
}

};
