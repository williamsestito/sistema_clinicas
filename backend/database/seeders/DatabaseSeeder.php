<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{
    Tenant,
    User,
    Client,
    Professional,
    ProfessionalProcedure,
    SchedulePeriod,
    SchedulePeriodDay,
    BlockedDate,
    Appointment,
    AppointmentLog
};
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | TENANT PADRÃO
        |--------------------------------------------------------------------------
        */
        $tenant = Tenant::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Clínica Principal',
                'primary_color' => '#2E7D32',
                'secondary_color' => '#81C784',
                'settings' => ['domains' => ['localhost']],
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | OWNER DO SISTEMA
        |--------------------------------------------------------------------------
        */
        $owner = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Dono do Sistema',
            'email' => 'owner@admin.com',
            'password' => Hash::make('123123'),
            'role' => 'owner',
            'active' => true,
        ]);

        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */
        $admin = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Administrador',
            'email' => 'admin@admin.com',
            'password' => Hash::make('123123'),
            'role' => 'admin',
            'active' => true,
        ]);

        /*
        |--------------------------------------------------------------------------
        | PROFISSIONAIS
        |--------------------------------------------------------------------------
        */
        $profUsers = [
            [
                'name' => 'Dra. Juliana Souza',
                'email' => 'juliana@clinica.com',
                'state' => 'SC',
                'city' => 'Joinville',
                'specialty' => ['Dermatologia', 'Estética Facial'],
                'bio' => 'Especialista em pele e tratamentos avançados.',
            ],
            [
                'name' => 'Dr. Marcos Lima',
                'email' => 'marcos@clinica.com',
                'state' => 'SC',
                'city' => 'Blumenau',
                'specialty' => ['Ortopedia'],
                'bio' => 'Especialista em lesões esportivas e articulares.',
            ],
            [
                'name' => 'Dra. Carla Almeida',
                'email' => 'carla@clinica.com',
                'state' => 'SP',
                'city' => 'São Paulo',
                'specialty' => ['Nutrição', 'Emagrecimento'],
                'bio' => 'Especialista em emagrecimento saudável.',
            ],
        ];

        $professionals = [];

        foreach ($profUsers as $prof) {

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name'      => $prof['name'],
                'email'     => $prof['email'],
                'password'  => Hash::make('123123'),
                'role'      => 'professional',
                'active'    => true,
            ]);

            $professional = Professional::create([
                'tenant_id' => $tenant->id,
                'user_id'   => $user->id,
                'specialty' => $prof['specialty'],
                'bio'       => $prof['bio'],
                'state'     => $prof['state'],
                'city'      => $prof['city'],
                'photo_url' => null,
                'show_prices' => true,
                'default_start_hour' => '08:00',
                'default_end_hour'   => '17:00',
                'default_consultation_time' => 30,
                'active'    => true,
            ]);

            $professionals[] = $professional;
        }

        /*
        |--------------------------------------------------------------------------
        | PERÍODO + DIAS
        |--------------------------------------------------------------------------
        */
        foreach ($professionals as $pro) {

            $period = SchedulePeriod::create([
                'tenant_id' => $tenant->id,
                'professional_id' => $pro->id,
                'start_date' => now()->toDateString(),
                'end_date'   => now()->addDays(60)->toDateString(),
                'active_days' => [1,2,3,4,5], // segunda–sexta
            ]);

            foreach ([1,2,3,4,5] as $weekday) {
                SchedulePeriodDay::create([
                    'tenant_id' => $tenant->id,
                    'professional_id' => $pro->id,
                    'period_id' => $period->id,
                    'weekday' => $weekday,
                    'start_time' => '08:00',
                    'end_time'   => '17:00',
                    'break_start' => '12:00',
                    'break_end' => '13:00',
                    'duration' => 30,
                    'available' => true,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | CLIENTES
        |--------------------------------------------------------------------------
        */
        $clients = [];

        foreach ([
            ['name' => 'Maria Silva', 'email' => 'maria@cliente.com'],
            ['name' => 'João Souza',  'email' => 'joao@cliente.com'],
        ] as $c) {

            $clients[] = Client::create([
                'tenant_id' => $tenant->id,
                'name' => $c['name'],
                'email' => $c['email'],
                'password' => Hash::make('123123'),
                'active' => true,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | LOG FINAL PARA DEV
        |--------------------------------------------------------------------------
        */

        $this->command->info("\n===============================");
        $this->command->info(" USUÁRIOS GERADOS NO SEEDER");
        $this->command->info("===============================\n");

        // OWNER
        $this->command->info("OWNER:");
        $this->command->warn("  Email: {$owner->email} | Senha: 123123\n");

        // ADMIN
        $this->command->info("ADMIN:");
        $this->command->warn("  Email: {$admin->email} | Senha: 123123\n");

        // PROFISSIONAIS
        $this->command->info("PROFISSIONAIS:");
        foreach ($professionals as $pro) {
            $this->command->line(" - {$pro->user->name} ({$pro->city}/{$pro->state})");
            $this->command->warn("    Email: {$pro->user->email} | Senha: 123123\n");
        }

        // CLIENTES
        $this->command->info("CLIENTES:");
        foreach ($clients as $cli) {
            $this->command->line(" - {$cli->name}");
            $this->command->warn("    Email: {$cli->email} | Senha: 123123\n");
        }

        $this->command->info("\nSeeder executado com sucesso!\n");
    }
}
