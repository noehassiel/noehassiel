<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class Os1ReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:500'],
            'language' => ['sometimes', 'string', 'in:en,es'],
            'conversation' => ['required', 'uuid'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        if ($this->input('language') === 'es') {
            return [
                'message.required' => 'No alcancé a escucharte.',
                'message.max' => 'Eso fue mucho de golpe, ¿me lo dices más corto?',
                'language.in' => 'Solo hablo inglés y español.',
            ];
        }

        return [
            'message.required' => "I didn't catch that.",
            'message.max' => 'That was a lot at once. Could you say it shorter?',
            'language.in' => 'I only speak English and Spanish.',
        ];
    }
}
