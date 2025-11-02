<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\TestimonialController;


// Registro e Login (públicos)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// 🔐 Requer token (usuário autenticado)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});


//  ADMIN / BACKOFFICE 

Route::middleware('auth:sanctum')->group(function () {

    // Usuários (Administração)
    Route::apiResource('users', UserController::class);

    // Rotas adicionais de controle de status do usuário
    Route::patch('users/{id}/deactivate', [UserController::class, 'deactivate']);
    Route::patch('users/{id}/reactivate', [UserController::class, 'reactivate']);

    // Banners (Gerenciamento interno)
    Route::apiResource('banners', BannerController::class);

    // 📚 Seções (Sobre, Serviços, Equipe etc.)
    Route::apiResource('sections', SectionController::class);

    // Depoimentos (Testemunhos de clientes)
    Route::apiResource('testimonials', TestimonialController::class);
});


//  ROTAS PÚBLICAS (SITE) 

Route::prefix('public')->group(function () {
    // Banners públicos
    Route::get('{tenantId}/banners', [BannerController::class, 'publicBanners']);

    // Seções públicas
    Route::get('{tenantId}/sections', [SectionController::class, 'publicSections']);

    // Depoimentos públicos
    Route::get('{tenantId}/testimonials', [TestimonialController::class, 'publicTestimonials']);
});

