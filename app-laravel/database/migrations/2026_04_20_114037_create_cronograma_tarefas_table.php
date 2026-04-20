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
        Schema::create('cronograma_tarefas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cronograma_id')->constrained()->cascadeOnDelete();
            
            $table->date('data');
            $table->string('dia_semana', 20);
            $table->integer('slot');
            
            $table->string('disciplina');
            $table->text('topico');
            $table->string('tipo', 50); // conteudo_novo, revisao, simulado, redacao
            $table->integer('duracao_minutos');
            
            $table->boolean('completada')->default(false);
            
            $table->timestamps();

            // Índices super importantes para a otimização das buscas no tracker
            $table->index(['cronograma_id', 'data']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cronograma_tarefas');
    }
};
