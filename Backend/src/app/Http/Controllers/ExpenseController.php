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
                    'message' => 'No estás en ninguna casa',
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
                'message' => 'Error al obtener los gastos',
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
            'title.required' => 'El título es obligatorio',
            'title.min' => 'El título debe tener al menos 3 caracteres',
            'amount.required' => 'La cantidad es obligatoria',
            'amount.min' => 'La cantidad debe ser mayor que 0',
            'amount.numeric' => 'La cantidad debe ser un número',
            'date.required' => 'La fecha es obligatoria',
            'date.before_or_equal' => 'La fecha no puede ser en el futuro'
        ]);
        try {
            $user = $request->user();

            // Comprovem si l'usuari té una casa assignada
            if (!$user->house_id) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'No estás en ninguna casa',
                ], 404);
            }



            // Validar que no sea el administrador
            $house = \App\Models\House::find($user->house_id);
            if ($house && $house->creator_id === $user->id_user) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'El administrador no reside en la casa y no puede crear gastos',
                ], 403);
            }

            $payerId = $request->has('is_pending') && $request->is_pending ? null : $user->id_user;

            // Creem el gasto
            $expense = Expense::create([
                'title' => $request->title,
                'amount' => $request->amount,
                'payer_id' => $payerId,
                'house_id' => $user->house_id,
                'date' => $request->date,
            ]);

            return response()->json([
                'status' => 'true',
                'message' => 'Gasto creado correctamente',
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
                    'message' => 'Gasto no encontrado',
                ], 404);
            }

            // Validació de seguretat: verificar que el gasto pertany a la casa de l'usuari
            if ($expense->house_id !== $user->house_id) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'No tienes permiso para ver este gasto',
                ], 403);
            }

            return response()->json([
                'status' => 'true',
                'expense' => $expense,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => 'Error al obtener el gasto',
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
            'title.required' => 'El título es obligatorio',
            'title.min' => 'El título debe tener al menos 3 caracteres',
            'amount.required' => 'La cantidad es obligatoria',
            'amount.min' => 'La cantidad debe ser mayor que 0',
            'date.before_or_equal' => 'La fecha no puede ser en el futuro'
        ]);

        try {
            $user = $request->user();
            $expense = Expense::find($id);

            if (!$expense) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'Gasto no encontrado',
                ], 404);
            }

            // Validació de seguretat: verificar que el gasto pertany a la casa de l'usuari
            if ($expense->house_id !== $user->house_id) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'No tienes permiso para editar este gasto',
                ], 403);
            }



            // Actualitzem el gasto
            $updateData = $request->only(['title', 'amount', 'date']);
            
            // Si pasan is_pending explícitamente y es falso, se lo asignamos a este usuario (lo ha pagado él)
            // Si es true, lo dejamos a null (sigue pendiente o pasa a pendiente)
            if ($request->has('is_pending')) {
                $updateData['payer_id'] = $request->is_pending ? null : $user->id_user;
            }

            $expense->update($updateData);

            return response()->json([
                'status' => 'true',
                'message' => 'Gasto actualizado correctamente',
                'expense' => $expense,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => 'Error al actualizar el gasto',
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
                    'message' => 'Gasto no encontrado',
                ], 404);
            }

            // Validació de seguretat: verificar que el gasto pertany a la casa de l'usuari
            if ($expense->house_id !== $user->house_id) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'No tienes permiso para eliminar este gasto',
                ], 403);
            }

            // Validar que el usuario es el administrador de la casa
            $house = \App\Models\House::find($user->house_id);
            if ($house->creator_id !== $user->id_user) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'Solo el administrador de la casa puede eliminar gastos',
                ], 403);
            }

            $expenseMonth = date('m', strtotime($expense->date));
            $expenseYear = date('Y', strtotime($expense->date));
            $expense->delete();

            // Si el gasto tenía pagador, hay que limpiar los settlements de ese mes
            // para que los balances queden en cero y no haya liquidaciones fantasma
            \App\Models\Settlement::where('house_id', $user->house_id)
                ->whereMonth('created_at', $expenseMonth)
                ->whereYear('created_at', $expenseYear)
                ->delete();

            return response()->json([
                'status' => 'true',
                'message' => 'Gasto eliminado correctamente',
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

            // Obtener todos los usuarios de la casa EXCLUYENDO al administrador
            $house = \App\Models\House::find($user->house_id);
            $usersInHouse = \App\Models\User::where('house_id', $user->house_id)
                ->where('id_user', '!=', $house->creator_id)
                ->get();
            $numUsers = $usersInHouse->count();
            $perPerson = $numUsers > 0 ? $totalMonth / $numUsers : 0;

            // Calcular cuánto pagó cada usuario y estructurar como lo espera el frontend (payments_by_user)
            $paymentsByUser = $usersInHouse->map(function ($u) use ($monthExpenses) {
                $userExpenses = $monthExpenses->where('payer_id', $u->id_user);
                return [
                    'user_id' => $u->id_user,
                    'user_name' => $u->name,
                    'total_paid' => round($userExpenses->sum('amount'), 2),
                    'expenses_count' => $userExpenses->count(),
                ];
            })->values();

            $settlements = \App\Models\Settlement::where('house_id', $user->house_id)
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->get();

            if ($monthExpenses->count() === 0) {
                $balances = collect([]);
            } else {
                // Calcular balances para cada compañero
                $balances = $paymentsByUser->map(function ($p) use ($perPerson, $settlements) {
                    $balance = round($p['total_paid'] - $perPerson, 2); // Positiu = ha pagat de més, negatiu = deu diners

                    // Sumar pagos directos que este usuario ha hecho a otros
                    $paidToOthers = $settlements->where('payer_id', $p['user_id'])->sum('amount');
                    // Restar pagos directos que este usuario ha recibido de otros
                    $receivedFromOthers = $settlements->where('receiver_id', $p['user_id'])->sum('amount');

                    $balance += $paidToOthers;
                    $balance -= $receivedFromOthers;

                    return [
                        'user_id' => $p['user_id'],
                        'user_name' => $p['user_name'],
                        'balance' => round($balance, 2),
                    ];
                });
            }

            return response()->json([
                'status' => 'true',
                'statistics' => [
                    'total_month' => round($totalMonth, 2),
                    'expenses_count' => $monthExpenses->count(),
                    'per_person' => round($perPerson, 2),
                    'payments_by_user' => $paymentsByUser,
                    'balances' => $balances,
                    'month' => now()->format('F Y'),
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'false',
                'message' => 'Error al obtener las estadísticas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
