<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Registro de un nuevo usuario
     *
     * @param UserRequest $request
     * @return json
     */
    public function createUser(UserRequest $request)
    {
        $validatedData = $request->validate([
            'name'=>'required|string|min:2|max:100',
            'email'=>'required|email|unique:users,email|max:255',
            'phone'=>'required|string|regex:/^[0-9]{9,15}$/',
            'password'=>'required|string|min:8|max:50',
            'registration_date'=>'required|date'
        ],[
            'name.required'=>'El nombre es obligatorio',
            'name.min'=>'El nombre debe tener al menos 2 caracteres',
            'email.required'=>'El correo es obligatorio',
            'email.email'=>'Formato de correo inválido',
            'email.unique'=>'Este correo ya está registrado',
            'phone.required'=>'El teléfono es obligatorio',
            'phone.regex' => 'El teléfono debe tener entre 9 y 15 dígitos',
            'password.required'=>'La contraseña es obligatoria',
            'password.min'=>'La contraseña debe tener al menos 8 caracteres'
        ]);
        try {
            // Creamos el usuario con los datos del formulario
            $user = User::create([
                    'name' => $validatedData['name'],
                    'email' => $validatedData['email'],
                    'phone' => $validatedData['phone'],
                    'registration_date' => $validatedData['registration_date'],
                    // Ciframos la contraseña para no guardarla en texto plano
                    'password' => Hash::make($validatedData['password']),
                ]);
            
            // Generamos el token de autenticación
            $token = $user->createToken('api-token')->plainTextToken;

            // Guardamos el token en una cookie httpOnly
            $cookie = cookie('auth_token', $token, 9999, '/', null, false, true);

            return response()->json([
                'status' => 'true',
                'message' => 'Usuario creado correctamente',
                'token' => $token,
                'user' => $user,
            ], 200)->withCookie($cookie);
        } catch(Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => 'Error al crear el usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
        
    }

    /**
     * Inicio de sesión de usuario
     *
     * @param LoginUserRequest $request
     * @return json
     */
    public function loginUser(LoginUserRequest $request)
    {
        $validatedData = $request->validate([
            'email'=>'required|email',
            'password'=>'required|string|min:5'
        ],[
            'email.required' => 'El correo es obligatorio',
            'email.email'=>'Formato de correo inválido',
            'password.required'=>'La contraseña es obligatoria'

        ]);
        try {
            // Si las credenciales son incorrectas devolvemos error 401
            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'Credenciales incorrectas',
                ], 401);
            }

            // Verificamos el usuario y generamos el token
            $user = User::where('email', $request->email)->first();
            $token = $user->createToken('api-token')->plainTextToken;

            // Guardamos el token en una cookie httpOnly
            $cookie = cookie('auth_token', $token, 9999, '/', null, false, true);

            return response()->json([
                'status' => 'true',
                'message' => 'Usuario autenticado correctamente',
                'token' => $token,
                'user' => $user,
            ], 200)->withCookie($cookie);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => 'Error al autenticar el usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
        
    }

    /**
     * Cierre de sesión del usuario
     *
     * @param Request $request
     * @return json
     */
    public function logout(Request $request)
    {
        try {
            // Eliminamos todos los tokens del usuario
            $request->user()->tokens()->delete();

            // Eliminamos la cookie del token
            $cookie = cookie()->forget('auth_token');

            return response()->json([
                'status' => 'true',
                'message' => 'Sesión cerrada correctamente',
            ], 200)->withCookie($cookie);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => 'Error al cerrar la sesión',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}