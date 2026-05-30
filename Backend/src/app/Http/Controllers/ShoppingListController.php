<?php

namespace App\Http\Controllers;

use App\Models\ShoppingItem;
use App\Models\House;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ShoppingListController extends Controller
{
    /**
     * Obtener toda la lista de la compra de la casa del usuario.
     */
    public function index()
    {
        try {
            $user = Auth::user();
            // Obtenemos la casa del usuario
            // Asumimos que el usuario solo tiene una casa activa o usamos la relación myHouse
            // Si el sistema permite múltiples casas, habría que recibir house_id o usar la "actual"
            $house = House::where('id', $user->house_id)->first();

            if (!$house) {
                return response()->json(['status' => 'false', 'message' => 'No perteneces a ninguna casa'], 404);
            }

            $items = ShoppingItem::where('house_id', $house->id)
                ->with('user:id_user,name') // Cargar quién lo pidió
                ->orderBy('is_completed', 'asc') // Pendientes primero
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json(['status' => 'true', 'items' => $items]);

        } catch (\Exception $e) {
            Log::error('Error fetching shopping list: ' . $e->getMessage());
            return response()->json(['status' => 'false', 'message' => 'Error al obtener la lista'], 500);
        }
    }

    /**
     * Añadir un nuevo item a la lista.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'nullable|string|max:100',
        ], [
            'name.required' => 'El nombre del producto es obligatorio.',
            'name.max' => 'El nombre es demasiado largo.',
        ]);

        try {

            $user = Auth::user();
            if (!$user->house_id) {
                return response()->json(['status' => 'false', 'message' => 'Debes unirte a una casa primero'], 400);
            }

            $item = ShoppingItem::create([
                'house_id' => $user->house_id,
                'user_id' => $user->id_user,
                'name' => $request->name,
                'quantity' => $request->quantity,
                'is_completed' => false
            ]);

            // Cargar relación user paramostrar nombre inmediatamente en frontend
            $item->load('user:id_user,name');

            return response()->json(['status' => 'true', 'message' => 'Producto añadido', 'item' => $item], 201);

        } catch (\Exception $e) {
            Log::error('Error adding shopping item: ' . $e->getMessage());
            return response()->json(['status' => 'false', 'message' => 'Error al añadir producto'], 500);
        }
    }

    /**
     * Actualizar estado (completado) o editar item.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'quantity' => 'nullable|string|max:100',
            'is_completed' => 'sometimes|boolean',
        ], [
            'name.required' => 'El nombre del producto es obligatorio.',
            'name.max' => 'El nombre es demasiado largo.',
            'quantity.max' => 'La cantidad es demasiado larga.',
            'is_completed.boolean' => 'El estado de completado debe ser booleano.'
        ]);

        try {
            $user = Auth::user();
            $item = ShoppingItem::find($id);

            if (!$item) {
                return response()->json(['status' => 'false', 'message' => 'Producto no encontrado'], 404);
            }

            if ($item->house_id !== $user->house_id) {
                return response()->json(['status' => 'false', 'message' => 'No tienes permiso'], 403);
            }

            $item->update($request->only(['name', 'quantity', 'is_completed']));

            // Cargar relación para devolver los datos completos
            $item->load('user:id_user,name');

            return response()->json(['status' => 'true', 'message' => 'Producto actualizado', 'item' => $item]);

        } catch (\Exception $e) {
            Log::error('Error updating shopping item: ' . $e->getMessage());
            return response()->json(['status' => 'false', 'message' => 'Error al actualizar'], 500);
        }
    }

    /**
     * Eliminar un item.
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $item = ShoppingItem::find($id);

            if (!$item) {
                return response()->json(['status' => 'false', 'message' => 'Producto no encontrado'], 404);
            }

            if ($item->house_id !== $user->house_id) {
                return response()->json(['status' => 'false', 'message' => 'No tienes permiso'], 403);
            }

            $item->delete();

            return response()->json(['status' => 'true', 'message' => 'Producto eliminado']);

        } catch (\Exception $e) {
            Log::error('Error deleting shopping item: ' . $e->getMessage());
            return response()->json(['status' => 'false', 'message' => 'Error al eliminar'], 500);
        }
    }
}
