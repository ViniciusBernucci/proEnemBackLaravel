<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Cria ou atualiza o usuário Master para garantir idempotência baseada no e-mail
        User::updateOrCreate(
            ['email' => 'viniciusbernucci@gmail.com'],
            [
                'name' => 'vinicius bernucci',
                'password' => Hash::make('123'),
            ]
        );

        $this->call([
            TopicosSeeder::class,
        ]);
    }
}
