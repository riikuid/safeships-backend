<?php

use App\Http\Controllers\SafetyInductionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SafetyPatrolController;
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
        Route::get('/assessment-progress', [DocumentController::class, 'getAssessmentProgress']);
        Route::get('/download-all', [DocumentController::class, 'downloadAllZip']);
        Route::delete('/delete-all', [DocumentController::class, 'deleteAll']);
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

    // SAFETY PATROL
    Route::name('safety-patrols.')->prefix('safety-patrols')->group(function () {
        Route::post('/', [SafetyPatrolController::class, 'store'])->name('store');
        Route::get('/', [SafetyPatrolController::class, 'index'])->name('index');
        Route::get('/report-data', [SafetyPatrolController::class, 'reportData'])->name('report-data');
        Route::get('/managerial', [SafetyPatrolController::class, 'managerial'])->name('managerial')->middleware('role:super_admin,manager');
        Route::get('/my-submissions', [SafetyPatrolController::class, 'mySubmissions'])->name('my-submissions');
        Route::get('/my-actions', [SafetyPatrolController::class, 'myActions'])->name('my-actions');
        Route::get('/{id}/detail', [SafetyPatrolController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [SafetyPatrolController::class, 'approve'])->name('approve')->middleware('role:super_admin,manager');
        Route::post('/{id}/reject', [SafetyPatrolController::class, 'reject'])->name('reject')->middleware('role:super_admin,manager');
        Route::post('/{id}/submit-feedback', [SafetyPatrolController::class, 'submitFeedback'])->name('submit-feedback');
        Route::post('/feedback/{id}/approve', [SafetyPatrolController::class, 'approveFeedback'])->name('approve-feedback')->middleware('role:super_admin,manager');
        Route::post('/feedback/{id}/reject', [SafetyPatrolController::class, 'rejectFeedback'])->name('reject-feedback')->middleware('role:super_admin,manager');
        Route::delete('/{id}', [SafetyPatrolController::class, 'destroy'])->name('destroy')->middleware('role:super_admin');
    });

    Route::name('safety-inductions.')->prefix('safety-patrols')->group(function () {
        Route::get('/locations', [SafetyInductionController::class, 'getLocations']);
        Route::post('/', [SafetyInductionController::class, 'store']);
        Route::get('/{id}/questions', [SafetyInductionController::class, 'getQuestions']);
        Route::post('/{id}/submit-answers', [SafetyInductionController::class, 'submitAnswers']);
        Route::get('/{id}/result', [SafetyInductionController::class, 'getResult']);
        Route::get('/{id}/certificate', [SafetyInductionController::class, 'getCertificate']);
        Route::get('/my-submissions', [SafetyInductionController::class, 'mySubmissions']);
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->middleware('role:super_admin,manager');
        Route::put('/update-profile', [UserController::class, 'updateProfile']);

        Route::get('/managers', [UserController::class, 'getManagers']);
        Route::get('{id}', [UserController::class, 'show'])->middleware('role:super_admin');
        Route::post('/', [UserController::class, 'store'])->middleware('role:super_admin');
        Route::put('{id}', [UserController::class, 'update'])->middleware('role:super_admin');
        Route::delete('{id}', [UserController::class, 'destroy'])->middleware('role:super_admin');
        Route::put('{id}/reset-password', [UserController::class, 'resetPassword']);
    });

    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/test', [NotificationController::class, 'test']);
});
