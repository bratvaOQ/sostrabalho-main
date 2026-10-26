<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cidade extends Model
{
    use HasFactory;

    protected $table = 'cidades';

    protected $fillable = [
        'cidade',
        'sigla',
        'codigo_ibge',
        'latitude',
        'longitude'
    ];

    public function vagas()
    {
        return $this->hasMany(Vaga::class, 'cidade_id');
    }
}
