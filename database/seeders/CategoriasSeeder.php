<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriasSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            // ─── TECNOLOGIA ───
            ['titulo' => 'Tecnologia', 'descricao' => 'Desenvolvimento, TI e Software'],
            ['titulo' => 'Design', 'descricao' => 'Design gráfico, UI/UX, ilustração'],
            ['titulo' => 'Suporte Técnico', 'descricao' => 'Suporte a usuários e sistemas'],

            // ─── CONSTRUÇÃO ───
            ['titulo' => 'Construção', 'descricao' => 'Pedreiro, eletricista, pintor, encanador'],
            ['titulo' => 'Reformas', 'descricao' => 'Reformas gerais em residências'],

            // ─── SERVIÇOS DOMÉSTICOS ───
            ['titulo' => 'Serviços Domésticos', 'descricao' => 'Diarista, faxineira, empregada'],

            // ─── ALIMENTAÇÃO ───
            ['titulo' => 'Alimentação', 'descricao' => 'Cozinheiro, garçom, padeiro'],

            // ─── VENDAS E ATENDIMENTO ───
            ['titulo' => 'Vendas', 'descricao' => 'Vendedor, representante comercial'],
            ['titulo' => 'Atendimento ao Cliente', 'descricao' => 'Telemarketing, SAC'],

            // ─── BELEZA E ESTÉTICA ───
            ['titulo' => 'Beleza', 'descricao' => 'Cabeleireiro, manicure, esteticista'],

            // ─── TRANSPORTE ───
            ['titulo' => 'Transporte/Mudanças', 'descricao' => 'Motorista, taxista, uber'],

            // ─── EDUCAÇÃO ───
            ['titulo' => 'Educação', 'descricao' => 'Professor, monitor, aulas particulares'],
            ['titulo' => 'Idiomas', 'descricao' => 'Professor de línguas'],
            ['titulo' => 'Música', 'descricao' => 'Aulas de instrumentos'],

            // ─── SAÚDE ───
            ['titulo' => 'Saúde', 'descricao' => 'Enfermeiro, técnico, cuidador'],

            // ─── ADMINISTRATIVO ───
            ['titulo' => 'Administrativo', 'descricao' => 'Auxiliar, assistente, secretária'],

            // ─── PRODUÇÃO ───
            ['titulo' => 'Produção', 'descricao' => 'Operário, ajudante de produção'],

            // ─── AGRICULTURA ───
            ['titulo' => 'Agricultura', 'descricao' => 'Trabalhador rural, colheita'],
            ['titulo' => 'Jardinagem', 'descricao' => 'Jardineiro, paisagismo'],

            // ─── LIMPEZA ───
            ['titulo' => 'Limpeza', 'descricao' => 'Limpeza'],

            // ─── SEGURANÇA ───
            ['titulo' => 'Segurança', 'descricao' => 'Segurança, porteiro, vigilante'],

            // ─── EVENTOS ───
            ['titulo' => 'Eventos', 'descricao' => 'Organização, buffet, DJ'],
            ['titulo' => 'Fotografia', 'descricao' => 'Fotógrafo, filmagem'],

            // ─── OUTROS ───
            ['titulo' => 'Freelancer', 'descricao' => 'Trabalhos autônomos diversos'],
            ['titulo' => 'Outros', 'descricao' => 'Outras categorias não listadas'],
        ];

        foreach ($categorias as $categoria) {
            DB::table('categorias')->insert([
                'titulo' => $categoria['titulo'],
                'descricao' => $categoria['descricao'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
