<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CandidaturaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('candidaturas')->insert([
            [
                'usuario_id' => 1, // João
                'vaga_id' => 3, // Ajudante de Pedreiro
                'mensagem' => 'Tenho experiência com construção, posso ajudar!',
                'status' => 'pendente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => 2, // Maria
                'vaga_id' => 1, // Desenvolvedor Front-End
                'mensagem' => 'Sou jornalista mas estou aprendendo programação.',
                'status' => 'pendente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => 3, // Pedro
                'vaga_id' => 4, // Padeiro
                'mensagem' => 'Posso trabalhar de madrugada.',
                'status' => 'aprovado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
