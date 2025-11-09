<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{
    Tenant,
    User,
    Professional,
    ProfessionalSchedule,
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
        // ==================================================
        // 🏢 TENANT PADRÃO
        // ==================================================
        $tenant = Tenant::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Clínica Principal',
                'primary_color' => '#2E7D32',
                'secondary_color' => '#81C784',
                'settings' => ['domains' => ['localhost']],
            ]
        );

        // ==================================================
        // 👑 ADMIN PADRÃO
        // ==================================================
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

        // ==================================================
        // 🧑‍⚕️ PROFISSIONAIS
        // ==================================================
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

        $professional1 = Professional::firstOrCreate([
            'tenant_id' => $tenant->id,
            'user_id' => $prof1->id,
            'specialty' => 'Dermatologia',
            'bio' => 'Especialista em estética facial e cuidados com a pele.',
            'active' => true,
        ]);

        $professional2 = Professional::firstOrCreate([
            'tenant_id' => $tenant->id,
            'user_id' => $prof2->id,
            'specialty' => 'Ortopedia',
            'bio' => 'Atendimento especializado em lesões musculares e articulares.',
            'active' => true,
        ]);

        // ==================================================
        // 💆‍♀️ SERVIÇOS
        // ==================================================
        $service1 = Service::firstOrCreate([
            'tenant_id' => $tenant->id,
            'professional_id' => $professional1->id,
            'name' => 'Consulta de Dermatologia',
            'description' => 'Avaliação de pele, tratamento de acne e manchas.',
            'duration_min' => 30,
            'price' => 250.00,
            'active' => true,
        ]);

        $service2 = Service::firstOrCreate([
            'tenant_id' => $tenant->id,
            'professional_id' => $professional2->id,
            'name' => 'Avaliação Ortopédica',
            'description' => 'Consulta de diagnóstico e acompanhamento ortopédico.',
            'duration_min' => 45,
            'price' => 300.00,
            'active' => true,
        ]);

        // ==================================================
        // 🕒 AGENDA PADRÃO DOS PROFISSIONAIS
        // ==================================================
        foreach (range(1, 5) as $day) {
            ProfessionalSchedule::updateOrCreate(
                ['professional_id' => $professional1->id, 'day_of_week' => $day],
                [
                    'available' => true,
                    'start_hour' => '09:00',
                    'end_hour' => '17:00',
                    'break_start' => '12:00',
                    'break_end' => '13:00',
                    'duration_min' => 30,
                ]
            );

            ProfessionalSchedule::updateOrCreate(
                ['professional_id' => $professional2->id, 'day_of_week' => $day],
                [
                    'available' => true,
                    'start_hour' => '08:00',
                    'end_hour' => '16:00',
                    'break_start' => '12:00',
                    'break_end' => '12:45',
                    'duration_min' => 45,
                ]
            );
        }

        // 🔒 Bloqueia uma data específica
        BlockedDate::updateOrCreate([
            'professional_id' => $professional1->id,
            'date' => '2025-11-12',
            'reason' => 'Congresso Médico'
        ]);

        // ==================================================
        // 👩‍💼 CLIENTES
        // ==================================================
        $clients = collect([
            ['name' => 'Maria Silva',  'email' => 'maria@cliente.com',  'phone' => '11999998888'],
            ['name' => 'João Souza',   'email' => 'joao@cliente.com',   'phone' => '11988887777'],
            ['name' => 'Ana Pereira',  'email' => 'ana@cliente.com',    'phone' => '11977776666'],
        ])->map(fn($c) => Client::firstOrCreate(array_merge($c, [
            'tenant_id' => $tenant->id,
            'birthdate' => '1990-05-12',
            'consent_marketing' => true,
        ])));

        // ==================================================
        // 📅 AGENDAMENTOS
        // ==================================================
        $now = Carbon::now();

        $appointments = [
            [
                'client_id' => $clients[0]->id,
                'professional_id' => $professional1->id,
                'service_id' => $service1->id,
                'start_at' => $now->copy()->addDays(1)->setTime(9, 0),
                'end_at' => $now->copy()->addDays(1)->setTime(9, 30),
                'status' => 'pending',
                'notes' => 'Primeira consulta de avaliação facial.'
            ],
            [
                'client_id' => $clients[1]->id,
                'professional_id' => $professional2->id,
                'service_id' => $service2->id,
                'start_at' => $now->copy()->addDays(2)->setTime(10, 0),
                'end_at' => $now->copy()->addDays(2)->setTime(10, 45),
                'status' => 'confirmed',
                'notes' => 'Avaliação de dor no joelho.'
            ],
            [
                'client_id' => $clients[2]->id,
                'professional_id' => $professional1->id,
                'service_id' => $service1->id,
                'start_at' => $now->copy()->subDays(3)->setTime(15, 0),
                'end_at' => $now->copy()->subDays(3)->setTime(15, 30),
                'status' => 'done',
                'notes' => 'Retorno pós-tratamento.'
            ],
        ];

        foreach ($appointments as $data) {
            $appointment = Appointment::create(array_merge($data, [
                'tenant_id' => $tenant->id,
                'source' => 'web',
            ]));

            // Log automático
            AppointmentLog::create([
                'appointment_id' => $appointment->id,
                'from_status' => 'pending',
                'to_status' => $appointment->status,
                'changed_by_user_id' => $admin->id,
                'note' => 'Status inicial configurado pelo sistema.',
                'changed_at' => now(),
            ]);
        }

        // ==================================================
        // ✅ SAÍDA FINAL
        // ==================================================
        $this->command->info('Seeder executado com sucesso!');
        $this->command->info('Tenant padrão: Clínica Principal');
        $this->command->info('Admin: admin@admin.com | senha: 123123');
        $this->command->info('Profissionais: dra.juliana@clinica.com / dr.marcos@clinica.com');
        $this->command->info('Clientes: maria@cliente.com, joao@cliente.com, ana@cliente.com');
    }
}
