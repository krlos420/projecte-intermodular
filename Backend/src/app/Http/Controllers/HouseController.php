<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\House;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;

class HouseController extends Controller
{
    /**
     * Funció per a crear una nova casa
     *
     * @param Request $request
     * @return json
     */
    public function store(Request $request)
    {
        try {
            // Validem les dades
            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            // Generem un codi d'invitació únic de 8 caràcters
            do {
                $inviteCode = strtoupper(Str::random(8));
            } while (House::where('invite_code', $inviteCode)->exists());

            // Creem la casa
            $house = House::create([
                'name' => $request->name,
                'invite_code' => $inviteCode,
            ]);

            // Assignem l'usuari actual a aquesta casa
            $user = $request->user();
            $user->house_id = $house->id;
            $user->save();

            return response()->json([
                'status' => 'true',
                'message' => 'Casa creada correctament',
                'house' => $house,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => 'Error al crear la casa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Funció per a unir-se a una casa amb el codi d'invitació
     *
     * @param Request $request
     * @return json
     */
    public function join(Request $request)
    {
        try {
            // Validem el codi d'invitació
            $validated = $request->validate([
                'invite_code' => 'required|string',
            ]);

            // Busquem la casa pel codi
            $house = House::where('invite_code', $request->invite_code)->first();

            if (!$house) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'Codi d\'invitació invàlid',
                ], 404);
            }

            // Assignem l'usuari a la casa
            $user = $request->user();
            $user->house_id = $house->id;
            $user->save();

            return response()->json([
                'status' => 'true',
                'message' => 'T\'has unit a la casa correctament',
                'house' => $house,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => 'Error al unir-se a la casa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Funció per a obtindre la informació de la casa de l'usuari
     *
     * @param Request $request
     * @return json
     */
    public function myHouse(Request $request)
    {
        try {
            $user = $request->user();

            // Comprovem si l'usuari té una casa assignada
            if (!$user->house_id) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'No estàs en cap casa',
                ], 404);
            }

            // Obtenim la casa amb els usuaris
            $house = House::with('users')->find($user->house_id);

            return response()->json([
                'status' => 'true',
                'house' => $house,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => 'Error al obtindre la casa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
