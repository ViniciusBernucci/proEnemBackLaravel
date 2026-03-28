<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'ciencias_humanas',
            'ciencias_natureza',
            'matematica',
            'linguagens'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tableAlter) {
                // Adicionando como nullable inicialmente para não quebrar tabelas já com dados
                $tableAlter->foreignId('disciplina_id')->nullable()->constrained('disciplinas');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'ciencias_humanas',
            'ciencias_natureza',
            'matematica',
            'linguagens'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tableAlter) {
                $tableAlter->dropForeign(['disciplina_id']);
                $tableAlter->dropColumn('disciplina_id');
            });
        }
    }
};
