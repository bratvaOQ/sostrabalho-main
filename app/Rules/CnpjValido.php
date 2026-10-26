<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CnpjValido implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Remove tudo que não é número
        $cnpj = preg_replace('/\D/', '', $value);

        // Verifica se tem 14 dígitos
        if (strlen($cnpj) != 14) {
            $fail('O CNPJ deve ter 14 dígitos.');
            return;
        }

        // Verifica se todos os dígitos são iguais (ex: 11111111111111)
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            $fail('CNPJ inválido.');
            return;
        }

        // Valida primeiro dígito verificador
        $soma = 0;
        $multiplicador = 5;
        for ($i = 0; $i < 12; $i++) {
            $soma += intval($cnpj[$i]) * $multiplicador;
            $multiplicador = $multiplicador == 2 ? 9 : $multiplicador - 1;
        }
        $resto = $soma % 11;
        $digito1 = $resto < 2 ? 0 : 11 - $resto;

        if (intval($cnpj[12]) != $digito1) {
            $fail('CNPJ inválido.');
            return;
        }

        // Valida segundo dígito verificador
        $soma = 0;
        $multiplicador = 6;
        for ($i = 0; $i < 13; $i++) {
            $soma += intval($cnpj[$i]) * $multiplicador;
            $multiplicador = $multiplicador == 2 ? 9 : $multiplicador - 1;
        }
        $resto = $soma % 11;
        $digito2 = $resto < 2 ? 0 : 11 - $resto;

        if (intval($cnpj[13]) != $digito2) {
            $fail('CNPJ inválido.');
            return;
        }
    }
}
