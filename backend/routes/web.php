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
    AdminBannerController,
    AdminClinicController,
    AdminFinancialController,
    AdminLogController,
    AdminPatientController,
    AdminProfileController,
    AdminProfessionalController,
    AdminReportController,
    AdminSectionController,
    AdminServiceController,
    AdminSettingsController,
    AdminTestimonialController,
    AdminWhatsAppController
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

        // CMS — Banners
        Route::prefix('banners')->name('banners.')->group(function () {
            Route::get('/',            [AdminBannerController::class, 'index'])->name('index');
            Route::get('/create',      [AdminBannerController::class, 'create'])->name('create');
            Route::post('/',           [AdminBannerController::class, 'store'])->name('store');
            Route::get('/{id}/edit',   [AdminBannerController::class, 'edit'])->name('edit');
            Route::put('/{id}',        [AdminBannerController::class, 'update'])->name('update');
            Route::delete('/{id}',     [AdminBannerController::class, 'destroy'])->name('destroy');
        });

        // CMS — Seções
        Route::prefix('sections')->name('sections.')->group(function () {
            Route::get('/',            [AdminSectionController::class, 'index'])->name('index');
            Route::get('/create',      [AdminSectionController::class, 'create'])->name('create');
            Route::post('/',           [AdminSectionController::class, 'store'])->name('store');
            Route::get('/{id}/edit',   [AdminSectionController::class, 'edit'])->name('edit');
            Route::put('/{id}',        [AdminSectionController::class, 'update'])->name('update');
            Route::delete('/{id}',     [AdminSectionController::class, 'destroy'])->name('destroy');
        });

        // CMS — Depoimentos
        Route::prefix('testimonials')->name('testimonials.')->group(function () {
            Route::get('/',            [AdminTestimonialController::class, 'index'])->name('index');
            Route::get('/create',      [AdminTestimonialController::class, 'create'])->name('create');
            Route::post('/',           [AdminTestimonialController::class, 'store'])->name('store');
            Route::get('/{id}/edit',   [AdminTestimonialController::class, 'edit'])->name('edit');
            Route::put('/{id}',        [AdminTestimonialController::class, 'update'])->name('update');
            Route::delete('/{id}',     [AdminTestimonialController::class, 'destroy'])->name('destroy');
        });

        // Configurações do Site (CMS)
        Route::get('/settings',  [AdminSettingsController::class, 'index'])->name('settings');
        Route::put('/settings',  [AdminSettingsController::class, 'update'])->name('settings.update');

        // Dados da Clínica (Tenant)
        Route::get('/clinic',  [AdminClinicController::class, 'index'])->name('clinic');
        Route::put('/clinic',  [AdminClinicController::class, 'update'])->name('clinic.update');

        // Perfil do Admin
        Route::get('/profile',  [AdminProfileController::class, 'index'])->name('profile');
        Route::put('/profile',  [AdminProfileController::class, 'update'])->name('profile.update');

        // Relatórios
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/appointments', [AdminReportController::class, 'appointments'])->name('appointments');
            Route::get('/financial',    [AdminReportController::class, 'financial'])->name('financial');
        });

        // Financeiro
        Route::prefix('financial')->name('financial.')->group(function () {
            Route::get('/',            [AdminFinancialController::class, 'index'])->name('index');
            Route::get('/create',      [AdminFinancialController::class, 'create'])->name('create');
            Route::post('/',           [AdminFinancialController::class, 'store'])->name('store');
            Route::get('/{id}/edit',   [AdminFinancialController::class, 'edit'])->name('edit');
            Route::put('/{id}',        [AdminFinancialController::class, 'update'])->name('update');
            Route::delete('/{id}',     [AdminFinancialController::class, 'destroy'])->name('destroy');
            Route::get('/categories',       [AdminFinancialController::class, 'categories'])->name('categories');
            Route::post('/categories',      [AdminFinancialController::class, 'storeCategory'])->name('categories.store');
            Route::delete('/categories/{id}', [AdminFinancialController::class, 'destroyCategory'])->name('categories.destroy');
        });

        // Logs
        Route::prefix('logs')->name('logs.')->group(function () {
            Route::get('/appointments',  [AdminLogController::class, 'appointments'])->name('appointments');
            Route::get('/notifications', [AdminLogController::class, 'notifications'])->name('notifications');
        });

        // WhatsApp
        Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
            Route::get('/',              [AdminWhatsAppController::class, 'dashboard'])->name('dashboard');
            Route::get('/messages',      [AdminWhatsAppController::class, 'messages'])->name('messages');
            Route::post('/send',         [AdminWhatsAppController::class, 'sendManual'])->name('send');
            Route::get('/campaigns',     [AdminWhatsAppController::class, 'campaigns'])->name('campaigns');
            Route::get('/campaigns/create', [AdminWhatsAppController::class, 'createCampaign'])->name('campaigns.create');
            Route::post('/campaigns',    [AdminWhatsAppController::class, 'storeCampaign'])->name('campaigns.store');
            Route::post('/campaigns/{campaign}/launch', [AdminWhatsAppController::class, 'launchCampaign'])->name('campaigns.launch');
            Route::post('/campaigns/{campaign}/cancel', [AdminWhatsAppController::class, 'cancelCampaign'])->name('campaigns.cancel');
            Route::delete('/campaigns/{campaign}', [AdminWhatsAppController::class, 'destroyCampaign'])->name('campaigns.destroy');
            Route::get('/settings',      [AdminWhatsAppController::class, 'settings'])->name('settings');
            Route::post('/connection',   [AdminWhatsAppController::class, 'updateConnection'])->name('connection.update');
            Route::post('/automation',   [AdminWhatsAppController::class, 'updateAutomation'])->name('automation.update');
        });
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

