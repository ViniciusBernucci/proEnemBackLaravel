<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->comment('Nome interno ex: study_plan.edit');
            $table->string('display_name', 150)->nullable();
            $table->text('description')->nullable();
            $table->string('resource', 100)->nullable()->comment('Ex: study_plans, users, materials');
            $table->string('action', 50)->nullable()->comment('Ex: view, create, edit, delete, export');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
