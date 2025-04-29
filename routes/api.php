<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    // DOKUMENTASI K3
    Route::name('documents.')->prefix('documents')->group(function () {
        Route::post('/', [DocumentController::class, 'store']);
        Route::get('/', [DocumentController::class, 'index']);
        Route::get('/categories', [DocumentController::class, 'getAllCategories']);
        Route::get('/{id}/detail', [DocumentController::class, 'show']);
        Route::post('/{id}/approve', [DocumentController::class, 'approve'])->middleware('role:super_admin,manager');;
        Route::post('/{id}/reject', [DocumentController::class, 'reject'])->middleware('role:super_admin,manager');;
        Route::post('/{id}/request-update', [DocumentController::class, 'requestUpdate'])->middleware('role:super_admin,manager');
        Route::get('/my-submissions', [DocumentController::class, 'mySubmissions']);
    });

    Route::get('notifications');
});
