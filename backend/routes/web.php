<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AUTH CONTROLLERS
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

/*
|--------------------------------------------------------------------------
| CLIENT
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\ClientAppointmentController;

/*
|--------------------------------------------------------------------------
| PROFESSIONAL CONTROLLERS
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\UserController;
use App\Http\Controllers\PacientController;
use App\Http\Controllers\ProfessionalDashboardController;
use App\Http\Controllers\ProfessionalProfileController;
use App\Http\Controllers\ProfessionalPacientController;
use App\Http\Controllers\ProfessionalProcedureController;
use App\Http\Controllers\ProfessionalReportController;
use App\Http\Controllers\ProfessionalAppointmentRequestController;
use App\Http\Controllers\ProfessionalScheduleController;
use App\Http\Controllers\ProfessionalScheduleConfigController;
use App\Http\Controllers\SchedulePeriodController;
use App\Http\Controllers\SchedulePeriodDayController;
use App\Http\Controllers\BlockedDateController;

/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => redirect()->route('login'));


/*
|--------------------------------------------------------------------------
| AUTH (LOGIN / REGISTER / RESET PASSWORD)
|--------------------------------------------------------------------------
*/
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login')->name('login.post');
});

Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'showRegisterForm')->name('register');
    Route::post('/register', 'register')->name('register.post');
});

Route::controller(ForgotPasswordController::class)->group(function () {
    Route::get('/forgot-password', 'showForgotForm')->name('password.request');
    Route::post('/forgot-password', 'sendResetLink')->name('password.email');
});

Route::controller(ResetPasswordController::class)->group(function () {
    Route::get('/reset-password/{token}', 'showResetForm')->name('password.reset');
    Route::post('/reset-password', 'reset')->name('password.update');
});


/*
|--------------------------------------------------------------------------
| CLIENT AREA
|--------------------------------------------------------------------------
*/
Route::middleware('auth:client')
    ->prefix('client')
    ->name('client.')
    ->group(function () {

        Route::view('/dashboard', 'client.dashboard')->name('dashboard');
        Route::view('/profile', 'client.profile')->name('profile');
        Route::view('/appointments', 'client.appointments')->name('appointments');
        Route::view('/schedule', 'client.schedule')->name('schedule');

        Route::get('/appointments/json', [ClientAppointmentController::class, 'indexJson'])->name('appointments.json');
        Route::post('/appointments',      [ClientAppointmentController::class, 'store'])->name('appointments.store');
        Route::post('/appointments/{id}/cancel',[ClientAppointmentController::class, 'cancel'])->name('appointments.cancel');

        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    });


/*
|--------------------------------------------------------------------------
| INTERNAL AREA (WEB LOGIN) - PROFESSIONAL + ADMIN
|--------------------------------------------------------------------------
*/
Route::middleware('auth:web')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
        Route::view('/agenda',    'admin.agenda')->name('agenda');
    });


    /*
    |--------------------------------------------------------------------------
    | EMPLOYEES
    |--------------------------------------------------------------------------
    */
    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('/',            [UserController::class, 'listView'])->name('index');
        Route::get('/create',      [UserController::class, 'create'])->name('create');
        Route::post('/',           [UserController::class, 'store'])->name('store');
        Route::get('/{id}/edit',   [UserController::class, 'edit'])->name('edit');
        Route::put('/{id}',        [UserController::class, 'update'])->name('update');
        Route::delete('/{id}',     [UserController::class, 'destroy'])->name('destroy');
    });


    /*
    |--------------------------------------------------------------------------
    | PROFESSIONAL AREA
    |--------------------------------------------------------------------------
    */
    Route::prefix('professional')->name('professional.')->group(function () {

        Route::get('/dashboard',       [ProfessionalDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile',         [ProfessionalProfileController::class, 'index'])->name('profile');
        Route::post('/profile/update', [ProfessionalProfileController::class, 'update'])->name('profile.update');

        /* Patients */
        Route::get('/pacients',      [ProfessionalPacientController::class, 'index'])->name('pacients');
        Route::get('/pacients/{id}', [ProfessionalPacientController::class, 'show'])->name('pacients.show');

        /* Agenda */
        Route::get('/schedule',        [ProfessionalScheduleController::class, 'index'])->name('schedule');
        Route::get('/schedule/config', [ProfessionalScheduleConfigController::class, 'index'])->name('schedule.config');
        Route::post('/schedule/weekly',[ProfessionalScheduleConfigController::class, 'storeWeekly'])->name('schedule.weekly.store');

        /* 🔥 PERIODS */
        Route::prefix('schedule/period')->name('schedule.period.')->group(function () {
            Route::get('/',        [SchedulePeriodController::class, 'index'])->name('index');
            Route::post('/',       [SchedulePeriodController::class, 'store'])->name('store');
            Route::put('/{id}',    [SchedulePeriodController::class, 'update'])->name('update');
            Route::delete('/{id}', [SchedulePeriodController::class, 'destroy'])->name('destroy');
        });

        /* 🔥 DAYS IN PERIOD */
        Route::prefix('schedule/day')->name('schedule.day.')->group(function () {
            Route::get('/{periodId}',       [SchedulePeriodDayController::class, 'index'])->name('index');
            Route::post('/{periodId}',      [SchedulePeriodDayController::class, 'store'])->name('store');
            Route::put('/update/{dayId}',   [SchedulePeriodDayController::class, 'update'])->name('update');
            Route::delete('/delete/{dayId}',[SchedulePeriodDayController::class, 'destroy'])->name('destroy');
        });

        /* 🔥 BLOCKED DAYS (corrigido e agora funcional) */
        Route::prefix('schedule/blocked')->name('schedule.blocked.')->group(function () {
            Route::get('/',        [ProfessionalScheduleConfigController::class, 'indexBlocked'])->name('index');
            Route::post('/',       [ProfessionalScheduleConfigController::class, 'blockDate'])->name('store');
            Route::delete('/{id}', [ProfessionalScheduleConfigController::class, 'unblockDate'])->name('destroy');
        });


        /* Appointment requests */
        Route::prefix('appointments')->name('appointments.')->group(function () {
            Route::get('/requests',     [ProfessionalAppointmentRequestController::class, 'index'])->name('requests');
            Route::post('/{id}/approve',[ProfessionalAppointmentRequestController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [ProfessionalAppointmentRequestController::class, 'reject'])->name('reject');
            Route::post('/{id}/reschedule',[ProfessionalAppointmentRequestController::class, 'reschedule'])->name('reschedule');
        });


        /* Procedures */
        Route::prefix('procedures')->name('procedures.')->group(function () {
            Route::get('/',       [ProfessionalProcedureController::class, 'index'])->name('index');
            Route::post('/store', [ProfessionalProcedureController::class, 'store'])->name('store');
            Route::delete('/{id}',[ProfessionalProcedureController::class, 'destroy'])->name('destroy');
        });

        /* Reports */
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/appointments',[ProfessionalReportController::class, 'appointments'])->name('appointments');
            Route::get('/finance',     [ProfessionalReportController::class, 'finance'])->name('finance');
        });

    });

    /* Logout */
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
