<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Avaliacao extends Model
{
    protected $table = 'avaliacoes';

    protected $fillable = [
        'avaliador_id',
        'avaliado_id',
        'vaga_id',
        'tipo',
        'nota',
        'comentario'
    ];

    public function avaliador()
    {
        return $this->belongsTo(User::class, 'avaliador_id');
    }

    public function avaliado()
    {
        return $this->belongsTo(User::class, 'avaliado_id');
    }
}
