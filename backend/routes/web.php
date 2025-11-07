<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\UserController;



Route::get('/', fn() => redirect()->route('login'));

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');

Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');





Route::middleware(['auth'])->group(function () {

    
    
    
    Route::get('/admin/dashboard', fn() => view('admin.dashboard'))->name('dashboard');
    Route::get('/admin/agenda', fn() => view('admin.agenda'))->name('agenda');

    
    
    
    Route::prefix('employees')->group(function () {
        Route::get('/', [UserController::class, 'listView'])->name('employees.index');      
        Route::get('/create', [UserController::class, 'create'])->name('employees.create'); 
        Route::post('/', [UserController::class, 'store'])->name('employees.store');        
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('employees.edit');  
        Route::put('/{id}', [UserController::class, 'update'])->name('employees.update');   
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('employees.destroy');
    });

    Route::get('/api/users', [UserController::class, 'index'])->name('api.users.index');


    
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
