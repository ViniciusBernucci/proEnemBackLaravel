<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matematica', function (Blueprint $table) {
            $table->id();
            $table->string('area');
            $table->string('disciplina');
            $table->string('topico');
            $table->integer('recorrencia');
            $table->integer('relevancia');
            $table->integer('tempo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matematica');
    }
};
