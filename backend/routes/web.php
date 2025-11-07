<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Rotas Web — Clínica Fácil
|--------------------------------------------------------------------------
| Este arquivo contém as rotas principais do sistema, divididas entre:
| - Área pública (login, registro, recuperação de senha)
| - Área autenticada (dashboard, agenda, equipe, etc.)
|
| As rotas privadas exigem autenticação via middleware 'auth'.
|
*/

//
// 🔓 ÁREA PÚBLICA — Autenticação e Registro
//
Route::get('/', fn() => redirect()->route('login'));

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');

Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');


//
// 🔒 ÁREA AUTENTICADA — Somente usuários logados
//
Route::middleware(['auth'])->group(function () {

    //
    // 📊 Dashboard e módulos principais
    //
    Route::get('/admin/dashboard', fn() => view('admin.dashboard'))->name('dashboard');
    Route::get('/admin/agenda', fn() => view('admin.agenda'))->name('agenda');

    //
    // 👥 CRUD de Colaboradores (Equipe interna)
    //
    Route::prefix('employees')->group(function () {
        Route::get('/', [UserController::class, 'listView'])->name('employees.index');      // Listagem
        Route::get('/create', [UserController::class, 'create'])->name('employees.create'); // Formulário de novo
        Route::post('/', [UserController::class, 'store'])->name('employees.store');        // Salvar novo
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('employees.edit');  // Editar existente
        Route::put('/{id}', [UserController::class, 'update'])->name('employees.update');   // Atualizar
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('employees.destroy'); // Excluir
    });

    //
    // 🧩 API interna (usada por relatórios e JS)
    //
    Route::get('/api/users', [UserController::class, 'index'])->name('api.users.index');

    //
    // 🚪 Logout
    //
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
