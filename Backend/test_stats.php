<?php
$user = \App\Models\User::where('id_user', 3)->first();
request()->setUserResolver(function() use ($user) { return $user; });
$controller = new \App\Http\Controllers\ExpenseController();
$response = $controller->statistics(request());
echo json_encode($response->getData());
