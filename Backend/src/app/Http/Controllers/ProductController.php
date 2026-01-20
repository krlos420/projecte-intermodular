<?php

namespace App\Http\Controllers;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ProductController extends Controller
{
    /**
     * Funció per a obtindre els productes d'un usuari
     *
     * @param Request $request
     * @return json
     */
    public function myProducts(Request $request)
    {
        // Obtenim l'ID de l'usuari des del token
        $userId = $request->user()->id_user;

        // Busquem els seus productes amb les relacions necesàries
        $products = Product::where('id_user', $userId)
            ->with(['categories', 'delivery_point:id_delivery_point,name'])
            ->orderBy('publication_date', 'desc')
            ->get();

        // Retornem la resposta en format json
        return response()->json([
            'status' => 'true',
            'data' => $products
        ], 200);
    }

    /**
     * Funció que retorna tots els productes
     * 
     * @return json
     */
    public function index()
    {
        // Obtenim els productes amb els camps relacionats de les altres taules de la base de dades
        $products = Product::with(['user:id_user,name', 'delivery_point:id_delivery_point,name', 'categories'])->get();

        // Retornme la resposta en JSON
        return response()->json($products);
    }

    /**
     * Funció que retorna un producte
     * 
     * @param int $id
     * @return json
     */
    public function show($id)
    {
        // Obtenim el producte amb els camps relacionats de les altres taules de la base de dades buscat per id
        $product = Product::with(['user:id_user,name', 'delivery_point:id_delivery_point,name', 'categories'])->findOrFail($id);

        // Retornem la resposta en JSON
        return response()->json($product);
    }

    /**
     * Funció per a crear un producte
     * 
     * @param Request $request
     * @return json
     */
    public function store(Request $request)
    {
        // Seleccionamos la fecha de hoy
        $fecha = Carbon::now('Europe/Madrid')->format('Y-m-d');

        // Obtenim l'id de l'usuari per mig del token
        $userId = $request->user()->id_user;

        // Validem les dades abans d'insertar el producte en la base de dades 
        $validated = $request->validate([
            'id_user' => 'required|integer|exists:users,id_user',
            'id_delivery_point'  => 'required|integer|exists:delivery_points,id_delivery_point',
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'type_stock' => 'required|in:Kg,Unidad',
            'state' => 'required|in:Agotado,Reservado,Disponible',
            'categories' => 'nullable|exists:categories,id_category'
        ]);

        // Inicialitzem la variable de la ruta de la imatge
        $rutaImagen = null;

        // Comprobem que la imatge haja sigut enviada
        if ($request->hasFile('image')) {

            // Guardem l'imatge en la carpeta Storage i guardem la ruta en la base de dades
            $rutaImagen = $request->file('image')->store('products', 'public');
        }

        // Creem el producte amb els camps: id_user, id_delivery_point, id_category, name, description, price, stock, image, type_stock, state, publication_date
        $product = Product::create([
            'id_user' => $request->id_user,
            'id_delivery_point' => $request->id_delivery_point,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'image' => $rutaImagen,
            'type_stock' => $request->type_stock,
            'state' => $request->state,
            'publication_date' => $fecha
        ]);

        // Si te categories, les introduim en la taula intermitja entre Productes i Categories
        if ($request->has('categories')) {
            $product->categories()->attach($request->categories);
        }

        // Retornem la resposta en JSON en cas de que no done error
        return response()->json([
            'status' => 'true',
            'message' => 'Producte creat correctament'
        ], 200);
    }

    /**
     * Funció per a actualitzar un producte
     * 
     * @param Request $request
     * @param int $id
     */
    public function update(Request $request, $id)
    {
        // Busquem el producte per id
        $product = Product::findOrFail($id);
        // Completem les dades que no venen del frontend
        $request->merge([
            'id_user' => $product->id_user,
            'id_delivery_point' => $product->id_delivery_point,
        ]);
        // Validem les dades enviades del formulari
        $validated = $request->validate([
            'id_user' => 'required|integer|exists:users,id_user',
            'id_delivery_point'  => 'required|integer|exists:delivery_points,id_delivery_point',
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'type_stock' => 'required|in:Kg,Unidad',
            'state' => 'required|in:Agotado,Reservado,Disponible',
            'categories' => 'nullable|exists:categories,id_category'
        ]);

        // Assignem la ruta a la imatge que ja teniem abans
        $rutaImagen = $product->image;

        // Comprobem si s'ha enviat una imatge nova
        if ($request->hasFile('image')) {

            // Eliminem la imatge anterior per a reservar espai
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            // Guardem la nova imatge
            $rutaImagen = $request->file('image')->store('products', 'public');
        }

        // Actualitzem les dades del producte
        $product->update([
            'id_user' => $request->id_user,
            'id_delivery_point' => $request->id_delivery_point,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'image' => $rutaImagen,
            'type_stock' => $request->type_stock,
            'state' => $request->state
        ]);

        // Comprobem que te categories, si es així, eliminem les anteriors i guardem les noves
        if ($request->has('categories')) {
            $product->categories()->sync($request->categories);
        }

        // Retornem en cas afirmatiu un json
        return response()->json([
            'message' => 'Producte actualitzat correctament.'
        ], 200);
    }

    /**
     * Funció per a eliminar un producte per id
     * 
     * @param int $id
     * @return json
     */
    public function destroy($id)
    {
        // Busquem el producte per id
        $product = Product::findOrFail($id);

        // Eliminem l'imatge que ja existía
        // Storage::disk('public')->delete($product->image);

        // Eliminem el producte de la base de dades
        $product->delete();

        // En cas de que no done error retornem una resposta json
        return response()->json([
            'status' => 'true',
            'message' => 'Producte eliminat correctament'
        ], 200);
    }
}
