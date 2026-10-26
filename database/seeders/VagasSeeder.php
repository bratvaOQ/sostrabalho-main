<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VagasSeeder extends Seeder
{
    public function run(): void
    {
        $vagas = [
            [
                'titulo' => 'Desenvolvedor de Front-End',
                'empresa' => 'TechNova Soluções',
                'descricao_completa' => 'Responsável pelo desenvolvimento de interfaces web modernas.',
                'requisito' => 'HTML, CSS, JavaScript e React.',
                'beneficio' => 'Vale alimentação, plano de saúde e home office.',
                'salario' => 4500.00,
                'expires_at' => now()->addDays(30),
                'is_expired' => false,
                'categorias_id' => 1,
                'usuario_id' => 5,
                'cidade' => 'São Paulo',
                'bairro' => 'Vila Olímpia',
                'estado' => 'São Paulo',
                'sigla' => 'SP',
                'latitude' => -23.5955,
                'longitude' => -46.6867,
                'descricao_resumida' => 'Vaga para desenvolvedor Front-End com experiência em React.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'titulo' => 'Analista de Suporte',
                'empresa' => 'TechNova Soluções',
                'descricao_completa' => 'Atendimento a usuários e resolução de problemas técnicos.',
                'requisito' => 'Conhecimentos em redes e sistemas operacionais.',
                'beneficio' => 'Vale transporte e assistência médica.',
                'salario' => 2800.00,
                'expires_at' => now()->addDays(45),
                'is_expired' => false,
                'categorias_id' => 2,
                'usuario_id' => 5,
                'cidade' => 'Campinas',
                'bairro' => null,
                'estado' => 'São Paulo',
                'sigla' => 'SP',
                'latitude' => -22.9099,
                'longitude' => -47.0626,
                'descricao_resumida' => 'Suporte técnico para usuários internos.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'titulo' => 'Ajudante de Pedreiro (Bico)',
                'empresa' => null,
                'descricao_completa' => 'Preciso de alguém pra ajudar a erguer um muro no fundo de casa. Serviço de 2 dias.',
                'requisito' => 'Ter disposição e experiência básica com massa e tijolo.',
                'beneficio' => 'Almoço no local.',
                'salario' => 200.00,
                'expires_at' => now()->addDays(7),
                'is_expired' => false,
                'categorias_id' => 7,
                'usuario_id' => 3,
                'cidade' => 'Marília',
                'bairro' => 'Centro',
                'estado' => 'São Paulo',
                'sigla' => 'SP',
                'latitude' => -22.2154,
                'longitude' => -49.9457,
                'descricao_resumida' => 'Ajudante para erguer muro - 2 dias de trabalho.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'titulo' => 'Padeiro',
                'empresa' => 'Padaria do seu Zé',
                'descricao_completa' => 'Preciso de padeiro para trabalhar de madrugada.',
                'requisito' => 'Experiência com pães, doces e salgados.',
                'beneficio' => 'Café da manhã e refeição.',
                'salario' => 2200.00,
                'expires_at' => now()->addDays(20),
                'is_expired' => false,
                'categorias_id' => 4,
                'usuario_id' => 4,
                'cidade' => 'Marília',
                'bairro' => null,
                'estado' => 'São Paulo',
                'sigla' => 'SP',
                'latitude' => -22.2154,
                'longitude' => -49.9457,
                'descricao_resumida' => 'Padeiro para turno da madrugada.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'titulo' => 'Atendente de Balcão',
                'empresa' => 'Padaria do seu Zé',
                'descricao_completa' => 'Atendimento no balcão da padaria, empacotamento e caixa.',
                'requisito' => 'Boa comunicação, ensino médio.',
                'beneficio' => 'Vale transporte, refeição.',
                'salario' => 1600.00,
                'expires_at' => now()->addDays(15),
                'is_expired' => false,
                'categorias_id' => 10,
                'usuario_id' => 4,
                'cidade' => 'Marília',
                'bairro' => null,
                'estado' => 'São Paulo',
                'sigla' => 'SP',
                'latitude' => -22.2154,
                'longitude' => -49.9457,
                'descricao_resumida' => 'Atendente para balcão da padaria.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // ✨ Insere UM por UM (evita erro do SQLite)
        foreach ($vagas as $vaga) {
            DB::table('vagas')->insert($vaga);
        }
    }
}
