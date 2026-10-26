<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DADOS FALSOS, só pra testar em ambiente local (todos com senha 12345678).
 * ⚠️ Nunca rode isso em produção.
 *
 * Precisa das categorias antes:
 *   php artisan db:seed
 *   php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UsuarioSeeder::class,
            VagasSeeder::class,
            CandidaturaSeeder::class,
        ]);
    }
}
