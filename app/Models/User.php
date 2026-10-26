<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'tipo',
        'name',
        'cnpj',
        'email',
        'password',
        'cargo',
        'biografia',
        'cidade',
        'estado',
        'sigla',
        'foto',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relações
    public function vagas()
    {
        return $this->hasMany(Vaga::class, 'usuario_id');
    }

    public function candidaturas()
    {
        return $this->hasMany(Candidatura::class, 'usuario_id');
    }

    public function favoritos()
    {
        return $this->hasMany(Favorito::class, 'usuario_id');
    }

    public function avaliacoesFeitas()
    {
        return $this->hasMany(Avaliacao::class, 'avaliador_id');
    }

    public function avaliacoesRecebidas()
    {
        return $this->hasMany(Avaliacao::class, 'avaliado_id');
    }

    public function mensagensEnviadas()
    {
        return $this->hasMany(Mensagem::class, 'remetente_id');
    }

    public function mensagensRecebidas()
    {
        return $this->hasMany(Mensagem::class, 'destinatario_id');
    }

    public function notificacoes()
    {
        return $this->hasMany(Notificacao::class, 'usuario_id');
    }

    // Métodos
    public function ehEmpresa()
    {
        return $this->tipo === 'empresa';
    }

    public function ehPessoa()
    {
        return $this->tipo === 'pessoa';
    }

    public function getMediaAvaliacoes()
    {
        return round($this->avaliacoesRecebidas()->avg('nota') ?? 0, 1);
    }
}
