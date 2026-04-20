<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cronogramas', function (Blueprint $table) {
            // Apenas UM cronograma por usuário pode estar ativo por vez.
            // A lógica de exclusividade é enforçada no controller.
            $table->boolean('ativo')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('cronogramas', function (Blueprint $table) {
            $table->dropColumn('ativo');
        });
    }
};
