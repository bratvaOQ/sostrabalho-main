<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Só o que o sistema PRECISA pra funcionar (as categorias).
     * Usuários e vagas agora são reais: nascem do cadastro no site ou no app.
     *
     * Se quiser dados de exemplo pra testar:  php artisan db:seed --class=DemoSeeder
     */
    public function run(): void
    {
        $this->call([
            CategoriasSeeder::class,
        ]);
    }
}
