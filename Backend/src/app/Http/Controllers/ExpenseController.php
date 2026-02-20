<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use Exception;

class ExpenseController extends Controller
{
    /**
     * Funció per a obtindre tots els gastos de la casa de l'usuari
     *
     * @param Request $request
     * @return json
     */
    public function index(Request $request)
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

            // Obtenim els gastos de la casa amb la informació del pagador
            $expenses = Expense::where('house_id', $user->house_id)
                ->with('payer')
                ->orderBy('date', 'desc')
                ->get();

            return response()->json([
                'status' => 'true',
                'expenses' => $expenses,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => 'Error al obtindre els gastos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Funció per a crear un nou gasto
     *
     * @param Request $request
     * @return json
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|min:3|max:200',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'date' => 'required|date|before_or_equal:today'
        ], [
            'title.required' => 'El títol és obligatori',
            'title.min' => 'El títol ha de tindre almenys 3 caràcters',
            'amount.required' => 'La quantitat és obligatòria',
            'amount.min' => 'La quantitat ha de ser major que 0',
            'amount.numeric' => 'La quantitat ha de ser un número',
            'date.required' => 'La data és obligatòria',
            'date.before_or_equal' => 'La data no pot ser en el futur'
        ]);
        try {
            $user = $request->user();

            // Comprovem si l'usuari té una casa assignada
            if (!$user->house_id) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'No estàs en cap casa',
                ], 404);
            }



            // Creem el gasto
            $expense = Expense::create([
                'title' => $request->title,
                'amount' => $request->amount,
                'payer_id' => $user->id_user,
                'house_id' => $user->house_id,
                'date' => $request->date,
            ]);

            return response()->json([
                'status' => 'true',
                'message' => 'Gasto creat correctament',
                'expense' => $expense,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => 'Error al crear el gasto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Funció per a obtindre un gasto específic
     *
     * @param numeric $id
     * @return json
     */
    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();
            $expense = Expense::with('payer')->find($id);

            if (!$expense) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'Gasto no trobat',
                ], 404);
            }

            // Validació de seguretat: verificar que el gasto pertany a la casa de l'usuari
            if ($expense->house_id !== $user->house_id) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'No tens permís per veure aquest gasto',
                ], 403);
            }

            return response()->json([
                'status' => 'true',
                'expense' => $expense,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => 'Error al obtindre el gasto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Funció per a actualitzar un gasto
     *
     * @param Request $request
     * @param numeric $id
     * @return json
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|min:3|max:200',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'date' => 'required|date|before_or_equal:today'
        ], [
            'title.required' => 'El títol és obligatori',
            'title.min' => 'El títol ha de tindre almenys 3 caràcters',
            'amount.required' => 'La quantitat és obligatòria',
            'amount.min' => 'La quantitat ha de ser major que 0',
            'date.before_or_equal' => 'La data no pot ser en el futur'
        ]);

        try {
            $user = $request->user();
            $expense = Expense::find($id);

            if (!$expense) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'Gasto no trobat',
                ], 404);
            }

            // Validació de seguretat: verificar que el gasto pertany a la casa de l'usuari
            if ($expense->house_id !== $user->house_id) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'No tens permís per editar aquest gasto',
                ], 403);
            }



            // Actualitzem el gasto
            $expense->update($request->only(['title', 'amount', 'date']));

            return response()->json([
                'status' => 'true',
                'message' => 'Gasto actualitzat correctament',
                'expense' => $expense,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => 'Error al actualitzar el gasto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Funció per a eliminar un gasto
     *
     * @param numeric $id
     * @return json
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            $expense = Expense::find($id);

            if (!$expense) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'Gasto no trobat',
                ], 404);
            }

            // Validació de seguretat: verificar que el gasto pertany a la casa de l'usuari
            if ($expense->house_id !== $user->house_id) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'No tens permís per eliminar aquest gasto',
                ], 403);
            }

            $expense->delete();

            return response()->json([
                'status' => 'true',
                'message' => 'Gasto eliminat correctament',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => 'Error al eliminar el gasto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Funció per a obtindre estadístiques dels gastos de la casa
     *
     * @param Request $request
     * @return json
     */
    public function statistics(Request $request)
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

            // Obtenim els gastos del mes actual
            $currentMonth = now()->month;
            $currentYear = now()->year;

            $monthExpenses = Expense::where('house_id', $user->house_id)
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->with('payer')
                ->get();

            // Total del mes
            $totalMonth = $monthExpenses->sum('amount');

            // Total pagat per cada compañero
            $payerTotals = $monthExpenses->groupBy('payer_id')->map(function ($expenses) {
                return [
                    'payer' => $expenses->first()->payer->name,
                    'total' => $expenses->sum('amount'),
                    'count' => $expenses->count(),
                ];
            })->values();

            // Càlcul de qui deu a qui (repartiment equitatiu)
            $houseUsers = \App\Models\User::where('house_id', $user->house_id)->count();
            $perPerson = $houseUsers > 0 ? $totalMonth / $houseUsers : 0;

            $balances = $payerTotals->map(function ($payer) use ($perPerson) {
                return [
                    'name' => $payer['payer'],
                    'paid' => $payer['total'],
                    'should_pay' => round($perPerson, 2),
                    'balance' => round($payer['total'] - $perPerson, 2), // Positiu = ha pagat de més
                ];
            });

            return response()->json([
                'status' => 'true',
                'statistics' => [
                    'total_month' => round($totalMonth, 2),
                    'per_person' => round($perPerson, 2),
                    'payer_totals' => $payerTotals,
                    'balances' => $balances,
                    'month' => now()->format('F Y'),
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => 'Error al obtindre les estadístiques',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
