<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{
    Tenant,
    User,
    Professional,
    SchedulePeriod,
    SchedulePeriodDay,
    BlockedDate,
    Service,
    Client,
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
        | 1) Tenant padrão
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
        | 2) Admin
        |--------------------------------------------------------------------------
        */
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Administrador do Sistema',
                'password' => Hash::make('123123'),
                'role' => 'admin',
                'active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | 3) Profissionais
        |--------------------------------------------------------------------------
        */
        $prof1 = User::firstOrCreate(
            ['email' => 'dra.juliana@clinica.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Dra. Juliana',
                'password' => Hash::make('123123'),
                'role' => 'professional',
                'active' => true,
            ]
        );

        $prof2 = User::firstOrCreate(
            ['email' => 'dr.marcos@clinica.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Dr. Marcos',
                'password' => Hash::make('123123'),
                'role' => 'professional',
                'active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | 4) Perfil Profissional
        |--------------------------------------------------------------------------
        */
        $professional1 = Professional::firstOrCreate([
            'tenant_id' => $tenant->id,
            'user_id'   => $prof1->id,
            'specialty' => 'Dermatologia',
            'bio'       => 'Especialista em estética facial e cuidados com a pele.',
            'active'    => true,
        ]);

        $professional2 = Professional::firstOrCreate([
            'tenant_id' => $tenant->id,
            'user_id'   => $prof2->id,
            'specialty' => 'Ortopedia',
            'bio'       => 'Atendimento especializado em lesões musculares e articulares.',
            'active'    => true,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 5) Serviços
        |--------------------------------------------------------------------------
        */
        $service1 = Service::firstOrCreate([
            'tenant_id'       => $tenant->id,
            'professional_id' => $professional1->id,
            'name'            => 'Consulta de Dermatologia',
            'description'     => 'Avaliação de pele, tratamento de acne e manchas.',
            'duration_min'    => 30,
            'price'           => 250.00,
            'active'          => true,
        ]);

        $service2 = Service::firstOrCreate([
            'tenant_id'       => $tenant->id,
            'professional_id' => $professional2->id,
            'name'            => 'Avaliação Ortopédica',
            'description'     => 'Consulta de diagnóstico e acompanhamento ortopédico.',
            'duration_min'    => 45,
            'price'           => 300.00,
            'active'          => true,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 6) PERÍODOS (modelo novo — sem schedule_period_days)
        |--------------------------------------------------------------------------
        */
        $period1 = SchedulePeriod::create([
            'tenant_id'       => $tenant->id,
            'professional_id' => $professional1->id,
            'start_date'      => now()->toDateString(),
            'end_date'        => now()->addDays(60)->toDateString(),
            'active_days'     => [1,2,3,4,5],
        ]);

        $period2 = SchedulePeriod::create([
            'tenant_id'       => $tenant->id,
            'professional_id' => $professional2->id,
            'start_date'      => now()->toDateString(),
            'end_date'        => now()->addDays(60)->toDateString(),
            'active_days'     => [1,2,3,4,5],
        ]);

        /*
        |--------------------------------------------------------------------------
        | 7) HORÁRIOS FIXOS POR DIA (versão correta)
        |--------------------------------------------------------------------------
        */
        foreach ([1,2,3,4,5] as $weekday) {
            SchedulePeriodDay::create([
                'tenant_id'       => $tenant->id,
                'professional_id' => $professional1->id,
                'weekday'         => $weekday,
                'start_time'      => '09:00',
                'end_time'        => '17:00',
                'break_start'     => '12:00',
                'break_end'       => '13:00',
                'duration'        => 30,
                'available'       => true,
            ]);
        }

        foreach ([1,2,3,4,5] as $weekday) {
            SchedulePeriodDay::create([
                'tenant_id'       => $tenant->id,
                'professional_id' => $professional2->id,
                'weekday'         => $weekday,
                'start_time'      => '08:00',
                'end_time'        => '16:00',
                'break_start'     => '12:00',
                'break_end'       => '12:45',
                'duration'        => 45,
                'available'       => true,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 8) Bloqueio exemplo
        |--------------------------------------------------------------------------
        */
        BlockedDate::updateOrCreate([
            'tenant_id'       => $tenant->id,
            'professional_id' => $professional1->id,
            'date'            => now()->addDays(2)->toDateString(),
        ], [
            'reason'          => 'Congresso Médico',
        ]);

        /*
        |--------------------------------------------------------------------------
        | 9) Clientes
        |--------------------------------------------------------------------------
        */
        $clients = collect([
            ['name' => 'Maria Silva', 'email' => 'maria@cliente.com', 'phone' => '11999998888'],
            ['name' => 'João Souza',  'email' => 'joao@cliente.com',  'phone' => '11988887777'],
            ['name' => 'Ana Pereira', 'email' => 'ana@cliente.com',   'phone' => '11977776666'],
        ])->map(function ($c) use ($tenant) {
            return Client::firstOrCreate(
                ['email' => $c['email']],
                [
                    'tenant_id' => $tenant->id,
                    'name'      => $c['name'],
                    'phone'     => $c['phone'],
                    'birthdate' => '1990-05-12',
                    'consent_marketing' => true,
                    'password'  => Hash::make('123123'),
                ]
            );
        });

        /*
        |--------------------------------------------------------------------------
        | 10) Agendamentos
        |--------------------------------------------------------------------------
        */
        $now = Carbon::now();

        $appointments = [
            [
                'client_id'       => $clients[0]->id,
                'professional_id' => $professional1->id,
                'service_id'      => $service1->id,
                'start_at'        => $now->copy()->addDays(1)->setTime(9, 0),
                'end_at'          => $now->copy()->addDays(1)->setTime(9, 30),
                'status'          => 'pending',
                'notes'           => 'Primeira consulta de avaliação facial.',
            ],
            [
                'client_id'       => $clients[1]->id,
                'professional_id' => $professional2->id,
                'service_id'      => $service2->id,
                'start_at'        => $now->copy()->addDays(2)->setTime(10, 0),
                'end_at'          => $now->copy()->addDays(2)->setTime(10, 45),
                'status'          => 'confirmed',
                'notes'           => 'Avaliação de dor no joelho.',
            ],
            [
                'client_id'       => $clients[2]->id,
                'professional_id' => $professional1->id,
                'service_id'      => $service1->id,
                'start_at'        => $now->copy()->subDays(3)->setTime(15, 0),
                'end_at'          => $now->copy()->subDays(3)->setTime(15, 30),
                'status'          => 'done',
                'notes'           => 'Retorno pós-tratamento.',
            ],
        ];

        foreach ($appointments as $data) {
            $appointment = Appointment::create(array_merge($data, [
                'tenant_id' => $tenant->id,
                'source'    => 'web',
            ]));

            AppointmentLog::create([
                'appointment_id'     => $appointment->id,
                'from_status'        => 'pending',
                'to_status'          => $appointment->status,
                'changed_by_user_id' => $admin->id,
                'note'               => 'Status inicial configurado pelo sistema.',
                'changed_at'         => now(),
            ]);
        }

        $this->command->info('Seeder executado com sucesso!');
    }
}
