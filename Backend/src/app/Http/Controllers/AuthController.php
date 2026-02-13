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
        $validatedData = $request->validate([
            'name'=>'required|string|min:2|max:100',
            'email'=>'required|email|unique:users,email|max:255',
            'phone'=>'required|string|regex:/^[0-9]{9,15}$/',
            'password'=>'required|string|min:8|max:50',
            'registration_date'=>'required|date'
        ],[
            'name.required'=>'El nom es obligatori',
            'name.min'=>'El nom ha de tindre almenys 2 caracters',
            'email.required'=>'El email es obligatori',
            'email.email'=>'Format de correu invàlid',
            'email.unique'=>'Este correu ja està registrat',
            'phone.required'=>'El telefon es obligatori',
            'phone.regex'=> 'El telefon ha de tindre entre 9 i 15 digits',
            'password.required'=>'La contrasenya es obligatoria',
            'password.min'=>'La conrasenya ha de tindre almenys 8 caracters'
        ]);
        try {
            // Creació del usuari mitjançant les dades introduides pel formulari
            $user = User::create([
                    'name' => $validatedData['name'],
                    'email' => $validatedData['email'],
                    'phone' => $validatedData['phone'],
                    'registration_date' => $validatedData['registration_date'],
                    // Xifra la contrasenya per a que no siga una contrasenya en texet pla
                    'password' => Hash::make($validatedData['password']),
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
        $validatedData = $request->validate([
            'email'=>'required|email',
            'password'=>'required|string|min:5'
        ],[
            'email.required'=> 'El correu es obligatori',
            'email.email'=>'Format de correu invàlid',
            'password.required'=>'La contrasenya es obligatoria'

        ]);
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

    /**
     * Funció per a tancar sessió de l'usuari
     *
     * @param Request $request
     * @return json
     */
    public function logout(Request $request)
    {
        try {
            // Eliminem tots els tokens de l'usuari
            $request->user()->tokens()->delete();

            // Eliminem la cookie del token
            $cookie = cookie()->forget('auth_token');

            return response()->json([
                'status' => 'true',
                'message' => 'Sessió tancada correctament',
            ], 200)->withCookie($cookie);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => 'Error al tancar sessió',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}