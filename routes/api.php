<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WaterFlowController;

Route::get('/device/health', function () {
    return response()->json([
        'message' => 'Laravel water monitoring API is working.',
        'status' => 'online',
    ]);
});

Route::post('/water-flow', [WaterFlowController::class, 'store']);