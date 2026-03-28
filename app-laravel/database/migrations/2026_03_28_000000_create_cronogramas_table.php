<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cronogramas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->json('dias_semana');
            $table->boolean('estudar_feriados');
            $table->boolean('tirar_ferias');
            $table->json('disciplinas_selecionadas');
            $table->integer('minutos_estudo_por_dia');
            $table->enum('status', ['dados_salvos', 'calculo_pendente', 'calculo_concluido'])
                  ->default('dados_salvos');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cronogramas');
    }
};
