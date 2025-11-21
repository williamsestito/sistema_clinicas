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
use App\Http\Controllers\ClientAppointmentController;

/*
|--------------------------------------------------------------------------
| AUTENTICAÇÃO — USUÁRIOS INTERNOS (ADMIN / OWNER / PROFISSIONAL)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {

    Route::post('/login',    [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    Route::middleware('auth:api')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

/*
|--------------------------------------------------------------------------
| AUTENTICAÇÃO — CLIENTE / PACIENTE
|--------------------------------------------------------------------------
*/
Route::prefix('client/auth')->group(function () {

    Route::post('/login', [AuthClientController::class, 'login']);

    Route::middleware('auth:client_api')->group(function () {
        Route::get('/me', [AuthClientController::class, 'me']);
        Route::post('/logout', [AuthClientController::class, 'logout']);
    });
});

/*
|--------------------------------------------------------------------------
| BACKOFFICE API (ADMIN / OWNER / PROFISSIONAL)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:api')->group(function () {

    Route::apiResource('users', UserController::class);
    Route::patch('users/{id}/deactivate', [UserController::class, 'deactivate']);
    Route::patch('users/{id}/reactivate', [UserController::class, 'reactivate']);

    Route::apiResource('banners', BannerController::class);
    Route::apiResource('sections', SectionController::class);
    Route::apiResource('testimonials', TestimonialController::class);

    Route::get('/schedule-periods', [SchedulePeriodController::class, 'index']);
    Route::post('/schedule-periods', [SchedulePeriodController::class, 'store']);
    Route::put('/schedule-periods/{id}', [SchedulePeriodController::class, 'update']);
    Route::delete('/schedule-periods/{id}', [SchedulePeriodController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| CLIENTE — API PÚBLICA (sem login)
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
| CLIENTE — AÇÕES AUTENTICADAS (APP)
|--------------------------------------------------------------------------
*/
Route::prefix('client')->middleware('auth:client_api')->group(function () {

    Route::get('/appointments', [ClientAppointmentController::class, 'indexJson']);
    Route::post('/appointments/{id}/cancel', [ClientAppointmentController::class, 'cancel']);
    Route::post('/pre-agendar', [ClientAppointmentController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| PÚBLICO DO SITE (sem login)
|--------------------------------------------------------------------------
*/
Route::prefix('public')->group(function () {
    Route::get('{tenantId}/banners',     [BannerController::class, 'publicBanners']);
    Route::get('{tenantId}/sections',    [SectionController::class, 'publicSections']);
    Route::get('{tenantId}/testimonials',[TestimonialController::class, 'publicTestimonials']);
});
