<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avaliacoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('avaliador_id');
            $table->unsignedBigInteger('avaliado_id');
            $table->unsignedBigInteger('vaga_id')->nullable();
            $table->enum('tipo', ['trabalhador', 'empresa']);
            $table->unsignedTinyInteger('nota'); // 1-5
            $table->text('comentario')->nullable();
            $table->timestamps();

            $table->foreign('avaliador_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('avaliado_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('vaga_id')->references('id')->on('vagas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avaliacoes');
    }
};
