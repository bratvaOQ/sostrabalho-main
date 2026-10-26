<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'tipo' => 'pessoa',
                'name' => 'João Silva',
                'cnpj' => null,
                'cargo' => 'Editor',
                'biografia' => 'Editor responsável pela revisão das notícias.',
                'cidade' => 'Bauru',
                'estado' => 'São Paulo',
                'sigla' => 'SP',
                'email' => 'editor@email.com',
                'password' => Hash::make('12345678'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'pessoa',
                'name' => 'Maria Souza',
                'cnpj' => null,
                'cargo' => 'Jornalista',
                'biografia' => 'Jornalista especializado em notícias de tecnologia.',
                'cidade' => 'Marília',
                'estado' => 'São Paulo',
                'sigla' => 'SP',
                'email' => 'jornalista@email.com',
                'password' => Hash::make('12345678'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'pessoa',
                'name' => 'Pedro Santos',
                'cnpj' => null,
                'cargo' => 'Pedreiro',
                'biografia' => 'Pedreiro autônomo, aceito bicos e obras pequenas.',
                'cidade' => 'Marília',
                'estado' => 'São Paulo',
                'sigla' => 'SP',
                'email' => 'pedro@email.com',
                'password' => Hash::make('12345678'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'empresa',
                'name' => 'Padaria do seu Zé',
                'cnpj' => '12.345.678/0001-90',
                'cargo' => null,
                'biografia' => 'Melhor padaria da zona sul de Marília.',
                'cidade' => 'Marília',
                'estado' => 'São Paulo',
                'sigla' => 'SP',
                'email' => 'padaria@email.com',
                'password' => Hash::make('12345678'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'empresa',
                'name' => 'TechNova Soluções',
                'cnpj' => '98.765.432/0001-10',
                'cargo' => null,
                'biografia' => 'Tecnologia e inovação para o seu negócio.',
                'cidade' => 'São Paulo',
                'estado' => 'São Paulo',
                'sigla' => 'SP',
                'email' => 'technova@email.com',
                'password' => Hash::make('12345678'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
