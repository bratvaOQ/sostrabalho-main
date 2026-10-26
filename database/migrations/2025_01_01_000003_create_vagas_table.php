<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vagas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 100);
            $table->string('empresa', 120)->nullable();
            $table->string('descricao_completa', 5500);
            $table->string('requisito', 600)->nullable();
            $table->string('beneficio', 1000)->nullable();
            $table->decimal('salario', 8, 2)->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->boolean('is_expired')->default(false);
            $table->unsignedBigInteger('categorias_id');
            $table->unsignedBigInteger('empresas_id')->nullable();
            $table->unsignedBigInteger('usuario_id');
            $table->string('cidade', 100);
            $table->string('estado', 60);
            $table->string('sigla', 2);
            $table->string('descricao_resumida', 260)->nullable();
            $table->timestamps();

            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('categorias_id')->references('id')->on('categorias')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vagas');
    }
};
