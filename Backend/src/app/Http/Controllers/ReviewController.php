<?php

namespace App\Http\Controllers;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Funció per a crear una review
     *
     * @param Request $request
     * @return json
     */
    public function store(Request $request)
    {
        // Obtenim la data de hui per a la review_date
        $fecha = Carbon::now('Europe/Madrid')->format('Y-m-d');

        // Validem les dades abans d'introduir-les en la base de dades
        $validated = $request->validate([
            'id_sale' => 'required|integer|exists:sales,id_sale',
            'calification' => 'required|integer',
            'comment' => 'required'
        ]);

        // Insertem les dades definitivament en la base de dades
        $review = Review::create([
            'id_sale' => $request -> id_sale,
            'calification' => $request -> calification,
            'comment' => $request -> comment,
            'review_date' => $fecha
        ]);

        // Retornem una resposta com que ha anat tot be
        return response() ->json([
            'status' => 'true',
            'message' => 'review creat correctament',
        ], 200);
    }

    /**
     * Funció per a obtindre totes les reviews
     *
     * @return json
     */
    public function index()
    {
        // Obtenim totes les reviews
        $review = Review::all();

        // Retornem la llista de reviews
        return response() ->json($review, 200);
    }

    /**
     * Funció per a obtindre només una review
     *
     * @param numeric $id
     * @return json
     */
    public function show($id)
    {
        // Obtenim la review per el seu id
        $review = Review::findOrFail($id);

        // Retornem la review en format json
        return response() -> json($review, 200);
    }

    /**
     * Funció per a actualitzar una review
     *
     * @param Request $request
     * @param numeric $id
     * @return json
     */
    public function update(Request $request, $id)
    {
        // Obtenim la review per el seu id
        $review = Review::findOrFail($id);

        // Validem les dades abans d'introduir-les en la base de dades
        $validated = $request->validate([
            'id_sale' => 'required|exists:sales,id_sale',
            'calification' => 'required|integer',
            'comment' => 'required'
        ]);

        // Insertem les dades definitivament en la base de dades
        $review ->update([
            'id_sale' => $request -> id_sale,
            'calification' => $request -> calification,
            'comment' => $request -> comment
        ]);

        // Retornem la resposta afirmativa en format json
        return response() -> json([
            'message' => 'review actualizada'
        ], 200);
    }

    /**
     * Funció per a eliminar una review
     *
     * @param numeric $id
     * @return json
     */
    public function destroy($id)
    {
        // Obtenim la review pel seu id
        $review = Review::findOrFail($id);

        // Borrem definitivament la review
        $review->delete();

        // Retornem la resposta afirmativa en format json
        return response()-> json([
            'message' => 'Venta eliminada'
        ], 200);
    }
}
