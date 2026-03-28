
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('topicos', 'ciencias_humanas');
    }

    public function down(): void
    {
        Schema::rename('ciencias_humanas', 'topicos');
    }
};
