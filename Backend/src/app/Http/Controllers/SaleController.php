<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;


class SaleController extends Controller
{
    /**
     * Funció per a crear una venta
     *
     * @param Request $request
     * @return json
     */
    public function store(Request $request)
    {
        // Validem les dades abans d'insertar-les
        $validated = $request->validate([
            'id_product' => 'required|integer|exists:products,id_product',
            'id_buyer' => 'required|integer|exists:users,id_user',
            'id_seller' => 'required|integer|exists:users,id_user',
            'id_delivery_point' => 'required|integer|exists:delivery_points,id_delivery_point',
            'total' => 'required|numeric'
        ]);

        // Obtenim el producte pel seu id
        $product = Product::findOrFail($request->id_product);

        // Comprobem que el stock siga major que la quantitat sol·licitada
        if ($product->stock < $request->quantity) {
            return response()->json([
                'status' => 'false',
                'message' => 'Stock insuficiente'
            ], 400);
        }

        // Obtenim del token l'id del comprador
        $compradorId = $request->user()->id_user;

        // Comprobem que l'usuari no intenta comprar el seu mateix producte
        if ($product->id_user == $compradorId) {
            return response()->json([
                'status' => 'false',
                'message' => 'No puedes comprar tus propios productos'
            ], 403);
        }

        // Insertem les dades definitivament
        $sale = Sale::create([
            'id_product' => $request->id_product,
            'id_buyer' => $compradorId,
            'id_seller' => $product->id_user,
            'id_delivery_point' => $request->id_delivery_point,
            'sale_date' => Carbon::now('Europe/Madrid')->format('Y-m-d'),
            'total' => $request->total
        ]);

        // Retornem les dades definitivament en la base de dades
        return response()->json([
            'status' => 'true',
            'message' => 'Venta creada correctamente',
        ], 200);
    }

    /**
     * Funció per a obtindre totes les ventes d'un usuari
     *
     * @return json
     */
    public function myOrders(Request $request)
    {
        // Obtenim del token el id de l'usuari
        $userId = $request->user()->id_user;

        // Obtenim la venta en tots els seus camps associats
        $sales = Sale::where('id_buyer', $userId)
            ->with([
                'product:id_product,name,image,price', 
                'seller:id_user,name', 
                'delivery_point:id_delivery_point,name,direction'
            ])
            ->get();
        
        // Retornem la resposta amb les ventes
        return response()->json($sales, 200);
    }

    /**
     * Funció per a obtindre una venta
     *
     * @param numeric $id
     * @return json
     */
    public function show($id)
    {
        // Obtenim la venta amb totes les seues dades associades
        $sale = Sale::with([
            'product:id_product,name', 'buyer:id_user,name', 'seller:id_user,name', 'delivery_point:id_delivery_point,name'
        ])->find($id);

        // Comprobem que la venta existeix
        if (!$sale) {
            return response()->json(['message' => 'No se ha encontrado la venta'], 404);
        }

        // Retornem la venta en format json
        return response()->json($sale, 200);
    }

    /**
     * Funció per a actualitzar una venta
     *
     * @param request $request
     * @param numeric $id
     * @return json
     */
    public function update(request $request, $id)
    {
        // Funció per a obtindre la venta per id
        $sale = Sale::find($id);

        // Comprobem que la venta existeix
        if (!$sale){
            return response() -> json(['message' => 'no se ha encontrado la venta'],404 );
        }

        // Validem les dades que hem rebut
        $validated = $request->validate([
            'total' => 'required|numeric'
        ]);

        // Actualitzem els camps definitivament
        $sale->update($request->all());
        return response()->json(['message' => 'Actualizado', 'sale' => $sale]);
    }

    /**
     * Funció per a eliminar una venta
     *
     * @param numeric $id
     * @return json
     */
    public function destroy($id)
    {
        // Obtenim una venta per id
        $sale = Sale::findOrFail($id);

        // Eliminem la venta
        $sale->delete();

        // Retornem una resposta en format json
        return response()-> json([
            'message' => 'Venta eliminada'
        ], 200);
    }
}