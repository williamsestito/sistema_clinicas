<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\{
    LoginController,
    RegisterController,
    ForgotPasswordController,
    ResetPasswordController
};
use App\Http\Controllers\{
    UserController,
    PacientController,
    ProfessionalDashboardController,
    ProfessionalProfileController,
    ProfessionalScheduleController,
    ProfessionalBlockedController,
    ProfessionalPacientController,
    ProfessionalProcedureController,
    ProfessionalScheduleConfigController,
    ProfessionalReportController
};

/*
|--------------------------------------------------------------------------
| Rotas Públicas
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => redirect()->route('login'));

// Autenticação e registro
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

// Recuperação de senha
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

/*
|--------------------------------------------------------------------------
| Áreas autenticadas
|--------------------------------------------------------------------------
*/

// ===================================
// ADMIN E PROFISSIONAIS (Guard: web)
// ===================================
Route::middleware(['auth:web'])->group(function () {

    // Dashboard / Agenda
    Route::view('/admin/dashboard', 'admin.dashboard')->name('dashboard');
    Route::view('/admin/agenda', 'admin.agenda')->name('agenda');

    /*
    |--------------------------------------------------------------------------
    | Colaboradores (Admin)
    |--------------------------------------------------------------------------
    */
    Route::prefix('employees')->group(function () {
        Route::get('/', [UserController::class, 'listView'])->name('employees.index');
        Route::get('/create', [UserController::class, 'create'])->name('employees.create');
        Route::post('/', [UserController::class, 'store'])->name('employees.store');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('employees.edit');
        Route::put('/{id}', [UserController::class, 'update'])->name('employees.update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('employees.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Pacientes (Admin)
    |--------------------------------------------------------------------------
    */
    Route::prefix('pacients')->group(function () {
        Route::get('/', [PacientController::class, 'index'])->name('pacients.index');
        Route::get('/create', [PacientController::class, 'create'])->name('pacients.create');
        Route::post('/', [PacientController::class, 'store'])->name('pacients.store');
        Route::get('/{id}/edit', [PacientController::class, 'edit'])->name('pacients.edit');
        Route::put('/{id}', [PacientController::class, 'update'])->name('pacients.update');
        Route::delete('/{id}', [PacientController::class, 'destroy'])->name('pacients.destroy');
        Route::get('/{id}', [PacientController::class, 'show'])->name('pacients.show');
    });

    /*
    |--------------------------------------------------------------------------
    | Área do Profissional
    |--------------------------------------------------------------------------
    */
    Route::prefix('professional')->name('professional.')->group(function () {

        // Dashboard
        Route::get('/dashboard', [ProfessionalDashboardController::class, 'index'])->name('dashboard');

        // Pacientes
        Route::get('/pacients', [ProfessionalPacientController::class, 'index'])->name('pacients');
        Route::get('/pacients/{id}', [ProfessionalPacientController::class, 'show'])->name('pacients.show');

        // Agenda e configuração
        Route::get('/schedule', [ProfessionalScheduleController::class, 'index'])->name('schedule');
        Route::post('/schedule/store', [ProfessionalScheduleController::class, 'store'])->name('schedule.store');
        Route::post('/schedule/update', [ProfessionalScheduleController::class, 'update'])->name('schedule.update');
        Route::get('/schedule/config', [ProfessionalScheduleConfigController::class, 'index'])->name('schedule.config');
        Route::post('/schedule/config/update', [ProfessionalScheduleConfigController::class, 'update'])->name('schedule.config.update');

        // Dias bloqueados
        Route::get('/blocked', [ProfessionalBlockedController::class, 'index'])->name('blocked');
        Route::post('/blocked/store', [ProfessionalBlockedController::class, 'store'])->name('blocked.store');
        Route::delete('/blocked/{id}', [ProfessionalBlockedController::class, 'destroy'])->name('blocked.destroy');

        // Procedimentos
        Route::get('/procedures', [ProfessionalProcedureController::class, 'index'])->name('procedures');
        Route::post('/procedures/store', [ProfessionalProcedureController::class, 'store'])->name('procedures.store');
        Route::delete('/procedures/{id}', [ProfessionalProcedureController::class, 'destroy'])->name('procedures.destroy');

        // Relatórios
        Route::get('/reports/appointments', [ProfessionalReportController::class, 'appointments'])->name('reports.appointments');
        Route::get('/reports/finance', [ProfessionalReportController::class, 'finance'])->name('reports.finance');

        // Perfil
        Route::get('/profile', [ProfessionalProfileController::class, 'index'])->name('profile');
        Route::post('/profile/update', [ProfessionalProfileController::class, 'update'])->name('profile.update');
    });

    // Logout unificado
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// ===================================
// PACIENTES (Guard: client)
// ===================================
Route::middleware(['auth:client'])->prefix('client')->name('client.')->group(function () {

    Route::view('/dashboard', 'client.dashboard')->name('dashboard');
    Route::view('/appointments', 'client.appointments')->name('appointments');
    Route::view('/schedule', 'client.schedule')->name('schedule');
    Route::view('/profile', 'client.profile')->name('profile');

    // Logout para clientes
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
