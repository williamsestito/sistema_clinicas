<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\AuthClientController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\SchedulePeriodController;

/*
|--------------------------------------------------------------------------
| Rotas de Autenticação Geral (Usuários Internos)
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

/*
|--------------------------------------------------------------------------
| Rotas de Autenticação do Cliente (Pacientes)
|--------------------------------------------------------------------------
*/
Route::prefix('client')->group(function () {
    Route::post('/login', [AuthClientController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthClientController::class, 'me']);
        Route::post('/logout', [AuthClientController::class, 'logout']);
    });
});

/*
|--------------------------------------------------------------------------
| Área Administrativa / Backoffice
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    /*
    |------------------------------
    | Users
    |------------------------------
    */
    Route::apiResource('users', UserController::class);
    Route::patch('users/{id}/deactivate', [UserController::class, 'deactivate']);
    Route::patch('users/{id}/reactivate', [UserController::class, 'reactivate']);


    /*
    |------------------------------
    | Conteúdo do Site (Admin)
    |------------------------------
    */
    Route::apiResource('banners', BannerController::class);
    Route::apiResource('sections', SectionController::class);
    Route::apiResource('testimonials', TestimonialController::class);


    /*
    |------------------------------
    | Schedule Periods (Agenda do Profissional)
    |------------------------------
    |
    | GET    /schedule-periods
    | POST   /schedule-periods
    | PUT    /schedule-periods/{id}
    | DELETE /schedule-periods/{id}
    |
    */
    Route::get('/schedule-periods', [SchedulePeriodController::class, 'index']);
    Route::post('/schedule-periods', [SchedulePeriodController::class, 'store']);
    Route::put('/schedule-periods/{id}', [SchedulePeriodController::class, 'update']);
    Route::delete('/schedule-periods/{id}', [SchedulePeriodController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Rotas Públicas (Site)
|--------------------------------------------------------------------------
*/
Route::prefix('public')->group(function () {
    Route::get('{tenantId}/banners', [BannerController::class, 'publicBanners']);
    Route::get('{tenantId}/sections', [SectionController::class, 'publicSections']);
    Route::get('{tenantId}/testimonials', [TestimonialController::class, 'publicTestimonials']);
});
