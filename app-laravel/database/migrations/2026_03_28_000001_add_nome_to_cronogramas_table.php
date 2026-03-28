<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cronogramas', function (Blueprint $table) {
            $table->string('nome', 100)->default('Sem nome')->after('user_id');
        });

        // Remove o default após preencher os registros existentes
        Schema::table('cronogramas', function (Blueprint $table) {
            $table->string('nome', 100)->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('cronogramas', function (Blueprint $table) {
            $table->dropColumn('nome');
        });
    }
};
