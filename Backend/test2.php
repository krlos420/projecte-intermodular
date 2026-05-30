<?php
// 1. Clear expenses and settlements
\App\Models\Expense::truncate();
\App\Models\Settlement::truncate();

// 2. Create an expense paid by Carlos (user 2)
\App\Models\Expense::create([
    'title' => 'Test',
    'amount' => 200,
    'payer_id' => 2,
    'house_id' => 1,
    'date' => now()
]);

// 3. Get statistics
$user = \App\Models\User::where('id_user', 3)->first();
request()->setUserResolver(function() use ($user) { return $user; });
$controller = new \App\Http\Controllers\ExpenseController();
$response = $controller->statistics(request());
echo "STATS FOR CARLOS PAYS EVERYTHING:\n";
echo json_encode($response->getData(), JSON_PRETTY_PRINT) . "\n\n";

// 4. Clear and test Pending Expense
\App\Models\Expense::truncate();
\App\Models\Expense::create([
    'title' => 'Test Pending',
    'amount' => 200,
    'payer_id' => null,
    'house_id' => 1,
    'date' => now()
]);

$response2 = $controller->statistics(request());
echo "STATS FOR PENDING EXPENSE:\n";
echo json_encode($response2->getData(), JSON_PRETTY_PRINT) . "\n\n";

