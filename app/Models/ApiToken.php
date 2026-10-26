<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiToken extends Model
{
    protected $table = 'api_tokens';

    protected $fillable = ['user_id', 'nome', 'token', 'last_used_at', 'expires_at'];

    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Cria um token novo para o usuário e devolve o texto puro (só existe agora).
     */
    public static function emitir(User $user, string $nome = 'app', int $dias = 90): string
    {
        $plain = Str::random(60);

        static::create([
            'user_id' => $user->id,
            'nome' => $nome,
            'token' => hash('sha256', $plain),
            'expires_at' => now()->addDays($dias),
        ]);

        return $plain;
    }
}
