<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Request/Petició
class LoginUserRequest extends FormRequest
{
    /**
     * Defineix els camps a omplir per al formulari
     * email: String obligatori màxim 255 caracters
     * password: String obligatori
     */
    public function rules()
    {
        return [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'El correo es obligatorio',
            'email.email' => 'Formato de correo inválido',
            'password.required' => 'La contraseña es obligatoria'
        ];
    }
}
