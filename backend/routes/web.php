<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PacientController;

/*
|--------------------------------------------------------------------------
| 🔐 Rotas Públicas (Login, Registro, Recuperação)
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => redirect()->route('login'));

// --- Autenticação ---
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

// --- Recuperação de Senha ---
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

/*
|--------------------------------------------------------------------------
| 🔒 Rotas Protegidas (Usuário Autenticado)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // ===================================================
    // 🏠 Dashboard e Agenda
    // ===================================================
    Route::get('/admin/dashboard', fn() => view('admin.dashboard'))->name('dashboard');
    Route::get('/admin/agenda', fn() => view('admin.agenda'))->name('agenda');

    // ===================================================
    // 👥 Colaboradores (UserController)
    // ===================================================
    Route::prefix('employees')->group(function () {
        Route::get('/', [UserController::class, 'listView'])->name('employees.index');
        Route::get('/create', [UserController::class, 'create'])->name('employees.create');
        Route::post('/', [UserController::class, 'store'])->name('employees.store');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('employees.edit');
        Route::put('/{id}', [UserController::class, 'update'])->name('employees.update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('employees.destroy');
    });

    Route::get('/api/users', [UserController::class, 'index'])->name('api.users.index');

    // ===================================================
    // 🧑‍⚕️ Pacientes (PacientController)
    // ===================================================
    Route::prefix('pacients')->group(function () {
        // CRUD principal
        Route::get('/', [PacientController::class, 'index'])->name('pacients.index');
        Route::get('/create', [PacientController::class, 'create'])->name('pacients.create');
        Route::post('/', [PacientController::class, 'store'])->name('pacients.store');
        Route::get('/{id}/edit', [PacientController::class, 'edit'])->name('pacients.edit');
        Route::put('/{id}', [PacientController::class, 'update'])->name('pacients.update');
        Route::delete('/{id}', [PacientController::class, 'destroy'])->name('pacients.destroy');
        Route::get('/{id}', [PacientController::class, 'show'])->name('pacients.show');

        // Ações auxiliares de gestão
        Route::get('/search', [PacientController::class, 'search'])->name('pacients.search');
        Route::post('/{id}/toggle-active', [PacientController::class, 'toggleActive'])->name('pacients.toggle');
        Route::get('/export', [PacientController::class, 'export'])->name('pacients.export');
        Route::post('/import', [PacientController::class, 'import'])->name('pacients.import');
        Route::get('/{id}/print', [PacientController::class, 'print'])->name('pacients.print');

        // Submódulos futuros
        Route::get('/{id}/appointments', [PacientController::class, 'appointments'])->name('pacients.appointments');
        Route::get('/{id}/history', [PacientController::class, 'history'])->name('pacients.history');
        Route::get('/{id}/anamnesis', [PacientController::class, 'anamnesis'])->name('pacients.anamnesis');
        Route::get('/{id}/record', [PacientController::class, 'record'])->name('pacients.record');
    });

    // ===================================================
    // 💚 Área do Paciente (Client)
    // ===================================================
    Route::middleware(['auth'])->prefix('pacient')->name('pacient.')->group(function () {
        Route::get('/appointments', fn() => view('pacient.appointments'))->name('appointments');
        Route::get('/schedule', fn() => view('pacient.schedule'))->name('schedule');
        Route::get('/profile', fn() => view('pacient.profile'))->name('profile');
    });

    // ===================================================
    // 🚪 Logout
    // ===================================================
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
