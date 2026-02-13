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
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|regex:/^[0-9]{9,15}$/',
            'registration_date' => 'required|date',
            'password' => 'required|string|min:8|max:50'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre es obligatorio',
            'name.min' => 'El nombre debe tener al menos 2 caracteres',
            'email.required' => 'El correo es obligatorio',
            'email.email' => 'Formato de correo inválido',
            'email.unique' => 'Este correo ya está registrado',
            'phone.required' => 'El teléfono es obligatorio',
            'phone.regex' => 'El teléfono debe tener entre 9 y 15 dígitos',
            'registration_date.required' => 'La fecha de registro es obligatoria',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres'
        ];
    }
}