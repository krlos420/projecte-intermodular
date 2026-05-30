<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Settlement;
use Exception;

class SettlementController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id_user',
            'amount' => 'required|numeric|min:0.01'
        ]);

        try {
            $user = $request->user();
            if (!$user->house_id) {
                return response()->json(['status' => 'false', 'message' => 'No estás en una casa'], 404);
            }

            if ($user->id_user === $request->receiver_id) {
                return response()->json(['status' => 'false', 'message' => 'No puedes pagarte a ti mismo'], 400);
            }

            $settlement = Settlement::create([
                'payer_id' => $user->id_user,
                'receiver_id' => $request->receiver_id,
                'house_id' => $user->house_id,
                'amount' => $request->amount
            ]);

            return response()->json(['status' => 'true', 'message' => 'Liquidación registrada correctamente', 'settlement' => $settlement], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'false', 'message' => $e->getMessage()], 500);
        }
    }
}
