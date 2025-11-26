<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\AuthClientController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\SchedulePeriodController;
use App\Http\Controllers\ClientScheduleController;


/*
|--------------------------------------------------------------------------
| AUTH — BACKOFFICE (ADMIN / OWNER / PROFISSIONAL)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {

    // Login / Registro do painel administrativo
    Route::post('/login',    [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    // Área autenticada via token Sanctum (guard:api)
    Route::middleware('auth:api')->group(function () {

        Route::get('/me',     [AuthController::class, 'me']);
        Route::post('/logout',[AuthController::class, 'logout']);
    });
});


/*
|--------------------------------------------------------------------------
| AUTH — CLIENTE / PACIENTE (API / APP)
|--------------------------------------------------------------------------
| 🔥 IMPORTANTE:
| O cliente usa token Sanctum SOMENTE no app.
| NO SITE, ele acessa via sessão (auth:client) → web.php
|--------------------------------------------------------------------------
*/
Route::prefix('client/auth')->group(function () {

    // Login que gera token Sanctum (usado em apps nativos)
    Route::post('/login', [AuthClientController::class, 'login']);

    Route::middleware('auth:client_api')->group(function () {

        Route::get('/me',     [AuthClientController::class, 'me']);
        Route::post('/logout',[AuthClientController::class, 'logout']);
    });
});


/*
|--------------------------------------------------------------------------
| BACKOFFICE — ÁREA AUTENTICADA (ADMIN / PROFISSIONAL via API)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:api')->group(function () {

    // Usuários internos
    Route::apiResource('users', UserController::class);
    Route::patch('users/{id}/deactivate', [UserController::class, 'deactivate']);
    Route::patch('users/{id}/reactivate', [UserController::class, 'reactivate']);

    // Conteúdo do site
    Route::apiResource('banners', BannerController::class);
    Route::apiResource('sections', SectionController::class);
    Route::apiResource('testimonials', TestimonialController::class);

    // Configuração de agenda
    Route::get('/schedule-periods',         [SchedulePeriodController::class, 'index']);
    Route::post('/schedule-periods',        [SchedulePeriodController::class, 'store']);
    Route::put('/schedule-periods/{id}',    [SchedulePeriodController::class, 'update']);
    Route::delete('/schedule-periods/{id}', [SchedulePeriodController::class, 'destroy']);
});


/*
|--------------------------------------------------------------------------
| CLIENTE — API PÚBLICA (Dados sem login)
|--------------------------------------------------------------------------
*/
Route::prefix('client/public')->group(function () {

    Route::get('/estados',        [ClientScheduleController::class, 'estados']);
    Route::get('/cidades',        [ClientScheduleController::class, 'cidades']);
    Route::get('/especialidades', [ClientScheduleController::class, 'especialidades']);
    Route::get('/procedimentos',  [ClientScheduleController::class, 'procedimentos']);
    Route::get('/profissionais',  [ClientScheduleController::class, 'profissionais']);
    Route::get('/horarios/{id}',  [ClientScheduleController::class, 'horarios']);
});


/*
|--------------------------------------------------------------------------
| ⚠️ CLIENTE — AÇÕES AUTENTICADAS DO SITE
|--------------------------------------------------------------------------
| 🔥 Todas as rotas do cliente que dependem de sessão web foram movidas
| para web.php, pois usam auth:client (guard WEB).
|
| Nada referente a pré-agendamento deve permanecer aqui.
|--------------------------------------------------------------------------
*/

