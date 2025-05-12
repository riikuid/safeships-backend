<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [AuthController::class, 'getUserByToken']);
    Route::post('logout', [AuthController::class, 'logout']);

    // DOKUMENTASI K3
    Route::name('documents.')->prefix('documents')->group(function () {
        Route::post('/', [DocumentController::class, 'store']);
        Route::get('/', [DocumentController::class, 'index']);
        Route::get('/managerial', [DocumentController::class, 'documentsManagerial']);
        Route::get('/categories', [DocumentController::class, 'getAllCategories']);
        Route::get('/{id}/detail', [DocumentController::class, 'show']);
        Route::post('/{id}/approve', [DocumentController::class, 'approve'])->middleware('role:super_admin,manager');;
        Route::post('/{id}/reject', [DocumentController::class, 'reject'])->middleware('role:super_admin,manager');;
        Route::post('/{id}/request-update', [DocumentController::class, 'requestUpdate'])->middleware('role:super_admin,manager');
        Route::get('/my-submissions', [DocumentController::class, 'mySubmissions']);
        Route::get('/level3', [DocumentController::class, 'getLevel3CategoriesAndDocuments']);
        Route::delete('/{id}', [DocumentController::class, 'destroy']);
        Route::delete('/level3/{category_id}', [DocumentController::class, 'destroyByCategory']);
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->middleware('role:super_admin');
        Route::get('/managers', [UserController::class, 'getManagers']);
        Route::get('{id}', [UserController::class, 'show'])->middleware('role:super_admin');
        Route::post('/', [UserController::class, 'store'])->middleware('role:super_admin');
        Route::put('{id}', [UserController::class, 'update'])->middleware('role:super_admin');
        Route::delete('{id}', [UserController::class, 'destroy'])->middleware('role:super_admin');
    });
    Route::get('notifications', [NotificationController::class, 'index']);
});
