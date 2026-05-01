<?php

use Illuminate\Support\Facades\Route;
use Modules\Tev\Http\Controllers\TevController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('tevs', TevController::class)->names('tev');
});
