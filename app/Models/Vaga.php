<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vaga extends Model
{
    use HasFactory;

    protected $table = 'vagas';

    protected $fillable = [
        'usuario_id',
        'titulo',
        'descricao_resumida',
        'descricao_completa',
        'requisito',
        'beneficio',
        'salario',
        'categorias_id',
        'cidade',
        'bairro',
        'latitude',
        'longitude',
        'estado',
        'sigla',
        'empresa',
        'expires_at',
        'is_expired',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected $appends = ['dias_restantes', 'esta_expirada'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categorias_id');
    }

    public function candidaturas()
    {
        return $this->hasMany(Candidatura::class, 'vaga_id');
    }

    public function favoritos()
    {
        return $this->hasMany(Favorito::class, 'vaga_id');
    }

    public function avaliacoes()
    {
        return $this->hasMany(Avaliacao::class, 'vaga_id');
    }

    public function mensagens()
    {
        return $this->hasMany(Mensagem::class, 'vaga_id');
    }

    // Atributos
    public function getDiasRestantesAttribute()
    {
        if ($this->expires_at) {
            return now()->diffInDays($this->expires_at, false);
        }
        return null;
    }

    public function getEstaExpiradaAttribute()
    {
        return $this->expires_at && $this->expires_at < now();
    }

    // Métodos
    public function renovar($dias = 30)
    {
        $this->expires_at = now()->addDays($dias);
        $this->is_expired = false;
        $this->save();
        return $this;
    }

    public function ehFavoritadaPor($userId)
    {
        return $this->favoritos()->where('usuario_id', $userId)->exists();
    }

    public function getMediaAvaliacoes()
    {
        return round($this->avaliacoes()->avg('nota') ?? 0, 1);
    }

    // Scopes
    public function scopeAtivas($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        })->where('is_expired', false);
    }
}
