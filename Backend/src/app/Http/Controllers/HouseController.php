<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\House;
use App\Models\User;
use App\Models\JoinRequest;
use Exception;
use Illuminate\Support\Str;

class HouseController extends Controller
{
    /**
     * Crea una nueva casa y asigna al usuario como administrador
     *
     * @param Request $request
     * @return json
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|min:3|max:100',
            'max_capacity' => 'nullable|integer|min:1',
            'total_rent' => 'nullable|numeric|min:0',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric'
        ], [
            'name.required' => 'El nombre de la casa es obligatorio',
            'name.min' => 'El nombre debe tener al menos 3 caracteres'
        ]);
        try {
            $user = $request->user();

            // Comprobamos que el usuario no pertenece ya a otra casa
            if ($user->house_id) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'Ya estás en una casa. Sal primero si quieres crear otra.',
                ], 400);
            }

            // Generamos un código de invitación único de 8 caracteres
            do {
                $inviteCode = strtoupper(Str::random(8));
            } while (House::where('invite_code', $inviteCode)->exists());

            // Creamos la casa
            $house = House::create([
                'name' => $request->name,
                'invite_code' => $inviteCode,
                'creator_id' => $user->id_user,
                'max_capacity' => $request->max_capacity ?? 4,
                'total_rent' => $request->total_rent ?? 0,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            // Asignamos el usuario a la casa recién creada
            $user->house_id = $house->id;
            $user->save();

            return response()->json([
                'status' => 'true',
                'message' => 'Casa creada correctamente',
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
     * Une a un usuario a una casa mediante el código de invitación
     *
     * @param Request $request
     * @return json
     */
    public function join(Request $request)
    {
        $validatedData = $request->validate([
            'invite_code' => 'required|string|size:8'
        ], [
            'invite_code.required' => 'El código de invitación es obligatorio',
            'invite_code.size' => 'El código debe tener exactamente 8 caracteres'
        ]);
        try {
            $user = $request->user();

            // Comprobamos que el usuario no pertenece ya a otra casa
            if ($user->house_id) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'Ya estás en una casa. Sal primero si quieres unirte a otra.',
                ], 400);
            }

            // Buscamos la casa por el código de invitación
            $house = House::where('invite_code', $request->invite_code)->first();

            if (!$house) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'Código de invitación inválido',
                ], 404);
            }

            // Asignamos el usuario a la casa
            $user = $request->user();
            $user->house_id = $house->id;
            $user->save();

            return response()->json([
                'status' => 'true',
                'message' => 'Te has unido a la casa correctamente',
                'house' => $house,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => 'Error al unirse a la casa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Devuelve la información de la casa del usuario autenticado
     *
     * @param Request $request
     * @return json
     */
    public function myHouse(Request $request)
    {
        try {
            $user = $request->user();

            // Comprobamos que el usuario pertenece a una casa
            if (!$user->house_id) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'No estás en ninguna casa',
                ], 404);
            }

            // Obtenemos la casa junto con todos sus usuarios
            $house = House::with('users')->find($user->house_id);

            return response()->json([
                'status' => 'true',
                'house' => $house,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => 'Error al obtener la casa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Permite a un inquilino salir de la casa (comprueba deudas pendientes)
     *
     * @param Request $request
     * @return json
     */
    public function leave(Request $request)
    {
        try {
            $user = $request->user();

            // Comprobamos que el usuario pertenece a una casa
            if (!$user->house_id) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'No estás en ninguna casa',
                ], 404);
            }

            $house = \App\Models\House::find($user->house_id);
            if ($house && $house->creator_id === $user->id_user) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'El administrador no puede abandonar la casa. Si deseas salir, debes eliminar la casa completa.',
                ], 403);
            }

            // Calculamos si el usuario tiene deudas pendientes este mes
            $currentMonth = now()->month;
            $currentYear = now()->year;

            $monthExpenses = \App\Models\Expense::where('house_id', $user->house_id)
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->get();

            $totalMonth = $monthExpenses->sum('amount');

            // Excluimos al administrador del cálculo de cuotas
            $house = \App\Models\House::find($user->house_id);
            $numUsers = User::where('house_id', $user->house_id)
                ->where('id_user', '!=', $house->creator_id)
                ->count();
            $perPerson = $numUsers > 0 ? $totalMonth / $numUsers : 0;
            
            $userExpenses = $monthExpenses->where('payer_id', $user->id_user);
            $totalPaid = $userExpenses->sum('amount');
            $balance = round($totalPaid - $perPerson, 2);

            if ($balance < 0) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'No puedes salir de la casa porque tienes deudas pendientes (' . abs($balance) . '€).'
                ], 400);
            }

            // Desvinculamos al usuario de la casa
            $user->house_id = null;
            $user->save();

            return response()->json([
                'status' => 'true',
                'message' => 'Has salido de la casa correctamente',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => 'Error al salir de la casa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Expulsa a un inquilino de la casa (solo el administrador)
     *
     * @param Request $request
     * @param numeric $userId
     * @return json
     */
    public function removeUser(Request $request, $userId)
    {
        try {
            $admin = $request->user();

            if (!$admin->house_id) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'No estás en ninguna casa',
                ], 404);
            }

            $house = \App\Models\House::find($admin->house_id);
            if ($house->creator_id !== $admin->id_user) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'Solo el administrador puede expulsar usuarios',
                ], 403);
            }

            if ($house->creator_id == $userId) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'No puedes expulsarte a ti mismo',
                ], 400);
            }

            $userToRemove = \App\Models\User::find($userId);
            if (!$userToRemove || $userToRemove->house_id !== $admin->house_id) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'El usuario no pertenece a tu casa',
                ], 404);
            }

            // Desvinculamos al usuario de la casa
            $userToRemove->house_id = null;
            $userToRemove->save();

            return response()->json([
                'status' => 'true',
                'message' => 'Usuario expulsado correctamente',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => 'Error al expulsar al usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Elimina la casa completa (solo el administrador/creador)
     *
     * @param Request $request
     * @return json
     */
    public function destroy(Request $request)
    {
        try {
            $user = $request->user();

            // Comprobamos que el usuario pertenece a una casa
            if (!$user->house_id) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'No estás en ninguna casa',
                ], 404);
            }

            $house = House::find($user->house_id);

            if (!$house) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'Casa no encontrada',
                ], 404);
            }

            // Solo el creador puede eliminar la casa
            if ($house->creator_id !== $user->id_user) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'Solo el creador puede eliminar la casa',
                ], 403);
            }

            // Desvinculamos a todos los usuarios de la casa antes de eliminarla
            User::where('house_id', $house->id)->update(['house_id' => null]);

            // Eliminamos la casa
            $house->delete();

            return response()->json([
                'status' => 'true',
                'message' => 'Casa eliminada correctamente',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => 'Error al eliminar la casa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualiza el nombre de la casa (solo el administrador)
     *
     * @param Request $request
     * @return json
     */
    public function updateName(Request $request)
    {
        $user = $request->user();

        // Comprobamos que el usuario pertenece a una casa
        if (!$user->house_id) {
            return response()->json([
                'status' => 'false',
                'message' => 'No estás en ninguna casa',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $house = House::find($user->house_id);

            if (!$house) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'Casa no encontrada',
                ], 404);
            }

            // Actualizamos el nombre de la casa
            $house->name = $request->name;
            $house->save();

            return response()->json([
                'status' => 'true',
                'message' => 'Nombre de la casa actualizado correctamente',
                'house' => $house,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => 'Error al actualizar el nombre de la casa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualiza el aforo máximo y el alquiler total de la casa (solo el administrador)
     *
     * @param Request $request
     * @return json
     */
    public function updateDetails(Request $request)
    {
        $user = $request->user();
        if (!$user->house_id) {
            return response()->json(['status' => 'false', 'message' => 'No estás en ninguna casa'], 404);
        }

        $validated = $request->validate([
            'max_capacity' => 'nullable|integer|min:1',
            'total_rent' => 'nullable|numeric|min:0',
        ]);

        try {
            $house = House::find($user->house_id);
            if (!$house || $house->creator_id !== $user->id_user) {
                return response()->json(['status' => 'false', 'message' => 'No autorizado'], 403);
            }

            if ($request->has('max_capacity')) $house->max_capacity = $request->max_capacity;
            if ($request->has('total_rent')) $house->total_rent = $request->total_rent;
            $house->save();

            return response()->json([
                'status' => 'true',
                'message' => 'Configuración de la casa actualizada',
                'house' => $house,
            ], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'false', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Devuelve las casas disponibles para el mapa interactivo
     * El aforo excluye al administrador ya que no es un inquilino
     *
     * @param Request $request
     * @return json
     */
    public function availableHouses(Request $request)
    {
        try {
            // Contamos los usuarios de cada casa excluyendo al administrador
            $houses = House::withCount(['users as users_count' => function($q) {
                    $q->whereColumn('users.id_user', '!=', 'houses.creator_id');
                }])
                ->havingRaw('users_count < max_capacity')
                ->get();
            return response()->json([
                'status' => 'true',
                'houses' => $houses,
            ], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'false', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Envía una solicitud de unión a una casa desde el mapa
     *
     * @param Request $request
     * @return json
     */
    public function requestJoin(Request $request)
    {
        $request->validate(['house_id' => 'required|exists:houses,id']);
        try {
            $user = $request->user();
            if ($user->house_id) {
                return response()->json(['status' => 'false', 'message' => 'Ya estás en una casa.'], 400);
            }

            $existing = JoinRequest::where('user_id', $user->id_user)->where('house_id', $request->house_id)->where('status', 'pending')->first();
            if ($existing) {
                return response()->json(['status' => 'false', 'message' => 'Ya has solicitado unirte a esta casa.'], 400);
            }

            $joinRequest = JoinRequest::create([
                'user_id' => $user->id_user,
                'house_id' => $request->house_id,
                'status' => 'pending'
            ]);

            return response()->json(['status' => 'true', 'message' => 'Solicitud enviada correctamente.'], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'false', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtiene las solicitudes de unión pendientes de una casa (solo el administrador)
     *
     * @param Request $request
     * @return json
     */
    public function getJoinRequests(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user->house_id) {
                return response()->json(['status' => 'false', 'message' => 'No tienes casa.'], 404);
            }
            $house = House::find($user->house_id);
            if ($house->creator_id !== $user->id_user) {
                return response()->json(['status' => 'false', 'message' => 'No autorizado.'], 403);
            }

            $requests = JoinRequest::with('user')->where('house_id', $house->id)->where('status', 'pending')->get();
            return response()->json(['status' => 'true', 'requests' => $requests], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'false', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Acepta o rechaza una solicitud de unión (solo el administrador)
     *
     * @param Request $request
     * @param numeric $id
     * @return json
     */
    public function handleJoinRequest(Request $request, $id)
    {
        $request->validate(['action' => 'required|in:accept,reject']);
        try {
            $user = $request->user();
            $joinRequest = JoinRequest::find($id);
            
            if (!$joinRequest) return response()->json(['status' => 'false', 'message' => 'Petición no encontrada.'], 404);
            
            $house = House::find($joinRequest->house_id);
            if ($house->creator_id !== $user->id_user) return response()->json(['status' => 'false', 'message' => 'No autorizado.'], 403);
            
            if ($request->action === 'accept') {
                $joinRequest->status = 'accepted';
                $joinRequest->save();
                
                $requester = User::find($joinRequest->user_id);
                $requester->house_id = $house->id;
                $requester->save();
                
                // Rechazamos las otras solicitudes pendientes de este usuario
                JoinRequest::where('user_id', $requester->id_user)->where('status', 'pending')->update(['status' => 'rejected']);
                
                return response()->json(['status' => 'true', 'message' => 'Usuario aceptado en la casa.'], 200);
            } else {
                $joinRequest->status = 'rejected';
                $joinRequest->save();
                return response()->json(['status' => 'true', 'message' => 'Solicitud rechazada.'], 200);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'false', 'message' => $e->getMessage()], 500);
        }
    }
}
