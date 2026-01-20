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
     * Funció per a registrar un Usuari
     *
     * @param UserRequest $request
     * @return json
     */
    public function createUser(UserRequest $request)
    {
        try {
            // Creació del usuari mitjançant les dades introduides pel formulari
            $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'registration_date' => $request->registration_date,
                    // Xifra la contrasenya per a que no siga una contrasenya en texet pla
                    'password' => Hash::make($request->password),
                ]);
            
            // Creem el token a partir de les dades de l'usuari
            $token = $user->createToken('api-token')->plainTextToken;

            // Insertem en les cookies el token creat abans
            $cookie = cookie('auth_token', $token, 9999, '/', null, false, true);

            // Retorna la contrasenya en JSON
            return response()->json([
                'status' => 'true',
                'message' => 'Usuari creat correctament',
                'token' => $token,
                'user' => $user,
            ], 200)->withCookie($cookie);
        } catch(Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => 'Error al crear el usuari',
                'error' => $e,
            ], 200);
        }
        
    }

    /**
     * Funció de login per a usuaris
     *
     * @param LoginUserRequest $request
     * @return json
     */
    public function loginUser(LoginUserRequest $request)
    {
        try {
            // Condicional d'error si l'usuari s'enganya al posar les credencials
            if (!Auth::attempt($request->only(['email', 'password']))) {
                // Resposta en JSON
                return response()->json([
                    'status' => 'false',
                    'message' => 'Credencials incorrectes',
                // Error 401: "Credencials incorrectes"
                ], 401);
            }
            // Verifica el usuari
            $user = User::where('email', $request->email)->first();

            // Creem el token a partir de les dades de l'usuari
            $token = $user->createToken('api-token')->plainTextToken;

            // Insertem el token en les cookies
            $cookie = cookie('auth_token', $token, 9999, '/', null, false, true);

            // Resposta en JSON
            return response()->json([
                'status' => 'true',
                'message' => 'Usuari autenticat correctament',
                'token' => $token,
                'user' => $user,
            ], 200)->withCookie($cookie);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => 'Error al autenticar el usuari',
                'error' => $e,
            ], 500);
        }
        
    }
}