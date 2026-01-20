<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Request/Petició
// Exten de FormRequest
class UserRequest extends FormRequest
{
    /**
     * Defineix els camps a omplir en el formulari:
     * nom: String obligatori màxim 255 caracters
     * email: String obligatori màxim 255 caracters que no es pot tornar a repetir
     * password: String obligatori
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'registration_date' => 'required|date',
            'password' => 'required|string'
        ];
    }
}