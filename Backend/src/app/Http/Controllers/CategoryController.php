<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Funció que retorna totes les categoríes
     *
     * @return json
     */
    public function index()
    {
        // Obtenim totes les categories
        $categories = Category::all();

        // Retornem les categories en format JSON
        return response()->json($categories);
    }

    /**
     * Funció que retorna una categoria
     *
     * @param int $id
     * @return json
     */
    public function show($id)
    {
        // Busquem la categoria per id
        $category = Category::findOrFail($id);

        // Retornem esta categoria en format JSON
        return response()->json($category);
    }

    /**
     * Funció per a crear una categoria mitjançant:
     * - name
     * 
     * @param Request $request
     * @return json
     */
    public function store(Request $request)
    {
        // Creem la categoria amb el camp name
        $category = Category::create([
            'name' => $request->name
        ]);

        // Retornem una resposta afirmativa en casa de que no done error
        return response()->json([
            'status' => 'true',
            'message' => 'Categoria creada correctamente'
        ], 200);
    }

    /**
     * Funció per a actualitzar una categoria mitjançant:
     * - name
     * 
     * @param Request $request
     * @param int $id_categorie
     * @return json
     */
    public function update(Request $request, $id)
    {
        // Busquem la categoria per id
        $category = Category::findOrFail($id);

        // Comprobem que la categoria existeix
        if (!$category) {
            return response()->json(['message' => 'Esta categoria no existeix']);
        }

        // Actualitzem la categoria amb el camp name
        $category->update([
            'name' => $request->name
        ]);

        // Retornem una resposta afirmativa en cas de que no done error
        return response()->json([
            'status' => 'true',
            'message' => 'Categoria actualitzada correctament'
        ], 200);
    }

    /**
     * Funció per a eliminar una categoria per Id
     * 
     * @param int $id
     * @return json
     */
    public function destroy($id)
    {
        // Busquem la categoria per id
        $category = Category::findOrFail($id);

        // Comprobem que la categoria existeix
        if(!$category) {
            return response()->json([
                'status' => 'false',
                'message' => 'Esta categoria no existeix'
            ], 404);
        }

        // Eliminem la categoria
        $category->delete();

        // Retornem una resposta afirmativa en cas de que no done error
        return response()->json([
            'status' => 'true',
            'message' => 'Categoria eliminada correctament'
        ], 200);
    }
}
