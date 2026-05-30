<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Obtener todos los usuarios
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    // Obtener usuario autenticado
    public function show(Request $request)
    {
        return response()->json($request->user());
    }

    // Actualizar usuario
    public function update(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|string|email|unique:users,email,' . $user->id_user . ',id_user',
            'phone' => 'required|string'
        ], [
            'name.required' => 'El nombre es obligatorio',
            'name.min' => 'El nombre debe tener al menos 3 caracteres',
            'email.required' => 'El correo es obligatorio',
            'email.email' => 'Formato de correo inválido',
            'email.unique' => 'Este correo ya está registrado',
            'phone.required' => 'El teléfono es obligatorio'
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone
        ]);

        return response()->json([
            'message' => 'Usuario actualizado',
            'user' => $user
        ], 200);
    }

    // Eliminar usuario
    public function destroy(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'Usuario eliminado'], 200);
    }
}
