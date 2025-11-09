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

// Rotas públicas (login, registro, recuperação de senha)
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

// Rotas autenticadas
Route::middleware(['auth'])->group(function () {

    // Dashboard e Agenda (Admin)
    Route::get('/admin/dashboard', fn() => view('admin.dashboard'))->name('dashboard');
    Route::get('/admin/agenda', fn() => view('admin.agenda'))->name('agenda');

    // Colaboradores
    Route::prefix('employees')->group(function () {
        Route::get('/', [UserController::class, 'listView'])->name('employees.index');
        Route::get('/create', [UserController::class, 'create'])->name('employees.create');
        Route::post('/', [UserController::class, 'store'])->name('employees.store');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('employees.edit');
        Route::put('/{id}', [UserController::class, 'update'])->name('employees.update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('employees.destroy');
    });

    // Pacientes
    Route::prefix('pacients')->group(function () {
        Route::get('/', [PacientController::class, 'index'])->name('pacients.index');
        Route::get('/create', [PacientController::class, 'create'])->name('pacients.create');
        Route::post('/', [PacientController::class, 'store'])->name('pacients.store');
        Route::get('/{id}/edit', [PacientController::class, 'edit'])->name('pacients.edit');
        Route::put('/{id}', [PacientController::class, 'update'])->name('pacients.update');
        Route::delete('/{id}', [PacientController::class, 'destroy'])->name('pacients.destroy');
        Route::get('/{id}', [PacientController::class, 'show'])->name('pacients.show');
    });

    // Área do profissional
    Route::prefix('professional')->name('professional.')->group(function () {

        // Dashboard
        Route::get('/dashboard', [ProfessionalDashboardController::class, 'index'])->name('dashboard');

        // Pacientes
        Route::get('/pacients', [ProfessionalPacientController::class, 'index'])->name('pacients');
        Route::get('/pacients/{id}', [ProfessionalPacientController::class, 'show'])->name('pacients.show');

        // Agenda
        Route::get('/schedule', [ProfessionalScheduleController::class, 'index'])->name('schedule');
        Route::post('/schedule/store', [ProfessionalScheduleController::class, 'store'])->name('schedule.store');
        Route::post('/schedule/update', [ProfessionalScheduleController::class, 'update'])->name('schedule.update');

        // Configuração da agenda
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

    // Área do paciente logado
    Route::prefix('pacient')->name('pacient.')->group(function () {
        Route::get('/appointments', fn() => view('pacient.appointments'))->name('appointments');
        Route::get('/schedule', fn() => view('pacient.schedule'))->name('schedule');
        Route::get('/profile', fn() => view('pacient.profile'))->name('profile');
    });

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
