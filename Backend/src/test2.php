<?php
\App\Models\Expense::truncate();
\App\Models\Settlement::truncate();
\App\Models\Expense::create([
    'title' => 'Test',
    'amount' => 200,
    'payer_id' => 2,
    'house_id' => 1,
    'date' => now()
]);
$user = \App\Models\User::where('id_user', 3)->first();
request()->setUserResolver(function() use ($user) { return $user; });
$controller = new \App\Http\Controllers\ExpenseController();
echo json_encode($controller->statistics(request())->getData());
