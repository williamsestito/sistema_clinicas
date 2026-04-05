<?php

use Illuminate\Support\Facades\Route;

// Auth Controllers
use App\Http\Controllers\Auth\{
    LoginController,
    RegisterController,
    ForgotPasswordController,
    ResetPasswordController
};

// Admin Controllers
use App\Http\Controllers\Admin\{
    AdminAgendaController,
    AdminPatientController,
    AdminProfessionalController,
    AdminServiceController
};

// Sistema Controllers
use App\Http\Controllers\{
    DashboardController,
    UserController,
    PacientController,
    ProfessionalDashboardController,
    ProfessionalProfileController,
    ProfessionalPacientController,
    ProfessionalProcedureController,
    ProfessionalReportController,
    ProfessionalAppointmentRequestController,

    ProfessionalScheduleController,
    ProfessionalScheduleConfigController,

    SchedulePeriodController,
    SchedulePeriodDayController,
    BlockedDateController
};


/*
|--------------------------------------------------------------------------
| ROTAS PÚBLICAS
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => redirect()->route('login'));

Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

Route::get('/register',  [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

Route::get('/forgot-password',  [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');

Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password',        [ResetPasswordController::class, 'reset'])->name('password.update');



/*
|--------------------------------------------------------------------------
| ÁREA AUTENTICADA (TODOS LOGADOS)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:web'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->middleware('role:admin,owner')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/agenda',              [AdminAgendaController::class, 'index'])->name('agenda');
        Route::get('/agenda/events',        [AdminAgendaController::class, 'events'])->name('agenda.events');
        Route::get('/agenda/blocked-dates', [AdminAgendaController::class, 'blockedDates'])->name('agenda.blocked-dates');
        Route::post('/agenda',              [AdminAgendaController::class, 'store'])->name('agenda.store');
        Route::put('/agenda/{id}',          [AdminAgendaController::class, 'update'])->name('agenda.update');
        Route::delete('/agenda/{id}',       [AdminAgendaController::class, 'destroy'])->name('agenda.destroy');

        // Cadastros — Pacientes
        Route::prefix('patients')->name('patients.')->group(function () {
            Route::get('/',            [AdminPatientController::class, 'index'])->name('index');
            Route::get('/create',      [AdminPatientController::class, 'create'])->name('create');
            Route::post('/',           [AdminPatientController::class, 'store'])->name('store');
            Route::get('/{id}/edit',   [AdminPatientController::class, 'edit'])->name('edit');
            Route::put('/{id}',        [AdminPatientController::class, 'update'])->name('update');
            Route::delete('/{id}',     [AdminPatientController::class, 'destroy'])->name('destroy');
        });

        // Cadastros — Profissionais
        Route::prefix('professionals')->name('professionals.')->group(function () {
            Route::get('/',            [AdminProfessionalController::class, 'index'])->name('index');
            Route::get('/create',      [AdminProfessionalController::class, 'create'])->name('create');
            Route::post('/',           [AdminProfessionalController::class, 'store'])->name('store');
            Route::get('/{id}/edit',   [AdminProfessionalController::class, 'edit'])->name('edit');
            Route::put('/{id}',        [AdminProfessionalController::class, 'update'])->name('update');
            Route::delete('/{id}',     [AdminProfessionalController::class, 'destroy'])->name('destroy');
        });

        // Cadastros — Serviços
        Route::prefix('services')->name('services.')->group(function () {
            Route::get('/',            [AdminServiceController::class, 'index'])->name('index');
            Route::get('/create',      [AdminServiceController::class, 'create'])->name('create');
            Route::post('/',           [AdminServiceController::class, 'store'])->name('store');
            Route::get('/{id}/edit',   [AdminServiceController::class, 'edit'])->name('edit');
            Route::put('/{id}',        [AdminServiceController::class, 'update'])->name('update');
            Route::delete('/{id}',     [AdminServiceController::class, 'destroy'])->name('destroy');
        });

        // CMS (placeholders — serão implementados na Fase 3)
        Route::view('/banners',       'admin.banners.index')->name('banners.index');
        Route::view('/sections',      'admin.sections.index')->name('sections.index');
        Route::view('/testimonials',  'admin.testimonials.index')->name('testimonials.index');

        // Configurações
        Route::view('/settings',      'admin.settings')->name('settings');
    });



    /*
    |--------------------------------------------------------------------------
    | COLABORADORES
    |--------------------------------------------------------------------------
    */
    Route::prefix('employees')->name('employees.')->middleware('role:admin,owner')->group(function () {
        Route::get('/',            [UserController::class, 'listView'])->name('index');
        Route::get('/create',      [UserController::class, 'create'])->name('create');
        Route::post('/',           [UserController::class, 'store'])->name('store');
        Route::get('/{id}/edit',   [UserController::class, 'edit'])->name('edit');
        Route::put('/{id}',        [UserController::class, 'update'])->name('update');
        Route::delete('/{id}',     [UserController::class, 'destroy'])->name('destroy');
    });



    /*
    |--------------------------------------------------------------------------
    | ÁREA DO PROFISSIONAL
    |--------------------------------------------------------------------------
    */
    Route::prefix('professional')->name('professional.')->group(function () {

        // Dashboard
        Route::get('/dashboard', [ProfessionalDashboardController::class, 'index'])->name('dashboard');

        // Pacientes
        Route::get('/pacients',        [ProfessionalPacientController::class, 'index'])->name('pacients');
        Route::get('/pacients/{id}',   [ProfessionalPacientController::class, 'show'])->name('pacients.show');

        // Agenda diária
        Route::get('/schedule', [ProfessionalScheduleController::class, 'index'])->name('schedule');



        /*
        |--------------------------------------------------------------------------
        | CONFIGURAÇÃO COMPLETA DA AGENDA
        |--------------------------------------------------------------------------
        */
        Route::get('/schedule/config', [ProfessionalScheduleConfigController::class, 'index'])
            ->name('schedule.config');



        /*
        |--------------------------------------------------------------------------
        | PERÍODOS
        |--------------------------------------------------------------------------
        */
        Route::prefix('schedule/period')->name('schedule.period.')->group(function () {
            Route::get('/',          [SchedulePeriodController::class, 'index'])->name('index');
            Route::post('/',         [SchedulePeriodController::class, 'store'])->name('store');
            Route::put('/{id}',      [SchedulePeriodController::class, 'update'])->name('update');
            Route::delete('/{id}',   [SchedulePeriodController::class, 'destroy'])->name('destroy');
        });



        /*
        |--------------------------------------------------------------------------
        | HORÁRIOS POR DIA (Scheduler)
        |--------------------------------------------------------------------------
        */
        Route::prefix('schedule/day')->name('schedule.day.')->group(function () {
            Route::get('/{periodId}',       [SchedulePeriodDayController::class, 'index'])->name('index');
            Route::post('/{periodId}',      [SchedulePeriodDayController::class, 'store'])->name('store');
            Route::put('/update/{dayId}',   [SchedulePeriodDayController::class, 'update'])->name('update');
            Route::delete('/delete/{dayId}',[SchedulePeriodDayController::class, 'destroy'])->name('destroy');
        });


        /*
        |--------------------------------------------------------------------------
        | HORÁRIOS SEMANAIS (Weekly - tela Configurar Agenda)
        |--------------------------------------------------------------------------
        */
        Route::post('/schedule/weekly', [ProfessionalScheduleConfigController::class, 'storeWeekly'])
            ->name('schedule.weekly.store');



        /*
        |--------------------------------------------------------------------------
        | DIAS BLOQUEADOS
        |--------------------------------------------------------------------------
        */
        Route::prefix('schedule/blocked')->name('schedule.blocked.')->group(function () {
            Route::get('/',        [BlockedDateController::class, 'index'])->name('index');
            Route::post('/',       [BlockedDateController::class, 'store'])->name('store');
            Route::delete('/{id}', [BlockedDateController::class, 'destroy'])->name('destroy');
        });



        /*
        |--------------------------------------------------------------------------
        | Solicitações de Agendamento
        |--------------------------------------------------------------------------
        */
        Route::prefix('appointments')->name('appointments.')->group(function () {
            Route::get('/requests',       [ProfessionalAppointmentRequestController::class, 'index'])->name('requests');
            Route::post('/{id}/approve',  [ProfessionalAppointmentRequestController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject',   [ProfessionalAppointmentRequestController::class, 'reject'])->name('reject');
        });



        /*
        |--------------------------------------------------------------------------
        | Procedimentos
        |--------------------------------------------------------------------------
        */
        Route::prefix('procedures')->name('procedures.')->group(function () {
            Route::get('/',           [ProfessionalProcedureController::class, 'index'])->name('index');
            Route::post('/store',     [ProfessionalProcedureController::class, 'store'])->name('store');
            Route::delete('/{id}',    [ProfessionalProcedureController::class, 'destroy'])->name('destroy');
        });



        /*
        |--------------------------------------------------------------------------
        | Relatórios
        |--------------------------------------------------------------------------
        */
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/appointments', [ProfessionalReportController::class, 'appointments'])->name('appointments');
            Route::get('/finance',      [ProfessionalReportController::class, 'finance'])->name('finance');
        });



        /*
        |--------------------------------------------------------------------------
        | Perfil do Profissional
        |--------------------------------------------------------------------------
        */
        Route::get('/profile',               [ProfessionalProfileController::class, 'index'])->name('profile');
        Route::post('/profile/update',       [ProfessionalProfileController::class, 'update'])->name('profile.update');
    });



    /*
    |--------------------------------------------------------------------------
    | Logout Web
    |--------------------------------------------------------------------------
    */
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

});



/*
|--------------------------------------------------------------------------
| ÁREA CLIENTE
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:client'])
    ->prefix('client')
    ->name('client.')
    ->group(function () {

        Route::view('/dashboard',    'client.dashboard')->name('dashboard');
        Route::view('/appointments', 'client.appointments')->name('appointments');
        Route::view('/schedule',     'client.schedule')->name('schedule');
        Route::view('/profile',      'client.profile')->name('profile');

        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    });

