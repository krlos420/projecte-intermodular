<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Delivery_Point;

class Delivery_pointController extends Controller
{
    /**
     * Funció per a crear un punt d'entrega
     *
     * @param Request $request
     * @return json
     */
    public function store(Request $request)
    {
        // Validem les dades abans d'insertar el punt de venta
        $validated = $request->validate([
            'id_user' => 'required|integer|exists:users,id_user',
            'name' => 'required|string',
            'direction' => 'required|string',
            'latitude' => 'required|numeric',
            'length' => 'required|numeric'
        ]);

        // Creem definitivament el punt d'entrega
        $delivery = Delivery_Point::create([
            'id_user' => $request -> id_user,            
            'name' => $request -> name,
            'direction' => $request -> direction,
            'latitude' => $request -> latitude,
            'length' => $request -> length,
        ]);

        // En cas afirmatiu, retornem la resposta en json
        return response() -> json([
            'status' => 'true',
            'message' => 'punto de venta creat correctament',
        ], 200);
    }

    /**
     * Funció per a obtindre tots els punts d'entrega
     *
     * @return json
     */
    public function index()
    {
        // Obtenim tots els punts d'entrega amb les seues claus associades
        $delivery_point = Delivery_Point::with('user:id_user:name') -> get();
        
        // Retornem la llista dels punts d'entrega
        return response() ->json($delivery_point, 200);
    }

    /**
     * Funció per a obtindre un punt d'entrega
     *
     * @param numeric $id
     * @return json
     */
    public function show($id)
    {
        // Obtenim el punt d'entrega amb el seu id i amb les seues claus associades
        $delivery_point = Delivery_Point::with('user:id_user:name') -> findOrFail($id);
        
        // Retornem el punt d'entrega en json
        return response() ->json($delivery_point, 200);
    }

    /**
     * Funció per a actualitzar un punt d'entrega
     *
     * @param Request $request
     * @param numeric $id
     * @return json
     */
    public function update(Request $request, $id)
    {
        // Obtenim el punt d'entrega per mig del seu id
        $delivery_point  = Delivery_Point::findOrFail($id);

        // Validem les dades abans d'insertar-les
        $request->validate([
            'name' => 'required|string',
            'direction' => 'required|string',
            'latitude' => 'required|numeric',
            'length' => 'required|numeric'
        ]);

        // Insertem les dades si no hi ha cap error en la base de dades
        $delivery_point ->update([
            'name' => $request -> name,
            'direction' => $request -> direcction,
            'latitude' => $request -> latitude,
            'length' => $request -> length,
        ]);

        // Retornem resposta en json en cas de que no hi haga error
        return response() -> json([
            'message' => 'punto de venta actualizada',
            'Delivery_Point' => $delivery_point
        ], 200);
    }

    /**
     * Funció per a eliminar un punt d'entrega
     *
     * @param numeric $id
     * @return json
     */
    public function destroy($id)
    {
        // Obtenim el punt d'entrega per mig del seu id
        $delivery_point = Delivery_Point::findOrFail($id);

        // Eliminem el punt d'entrega
        $delivery_point -> delete();

        // Retornem una resposta de que ha anat tot bé
        return response()-> json([
            'message' => 'punto de venta eliminado'
        ], 200);
    }
}