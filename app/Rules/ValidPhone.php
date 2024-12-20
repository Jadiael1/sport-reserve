<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPhone implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Remove caracteres não numéricos (espaços, parênteses, traços)
        $phone = preg_replace('/\D/', '', $value);

        // Verifica se o telefone tem exatamente 11 dígitos (DD + 9 dígitos)
        if (strlen($phone) != 11) {
            $fail(__('The :attribute field must have 11 digits.'));
            return;
        }

        // Verifica se o número é válido (não pode começar com 0 ou 1)
        if (in_array($phone[2], ['0', '1'])) {
            $fail(__('The :attribute field cannot start with 0 or 1.'));
            return;
        }
    }
}
