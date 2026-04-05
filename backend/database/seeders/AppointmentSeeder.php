<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{
    Tenant,
    User,
    Professional,
    Service,
    Appointment,
    AppointmentLog,
    ScheduleException,
    BlockedDate
};
use Carbon\Carbon;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        if (!$tenant) {
            $this->command->error('Nenhum tenant encontrado. Execute o DatabaseSeeder primeiro.');
            return;
        }

        $admin = User::where('tenant_id', $tenant->id)->where('role', 'admin')->first();
        if (!$admin) {
            $this->command->error('Nenhum admin encontrado. Execute o DatabaseSeeder primeiro.');
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | 1) Criar Usuarios-Clientes (role=client)
        |--------------------------------------------------------------------------
        */
        $clientsData = [
            ['name' => 'Maria Silva',       'email' => 'maria.silva@paciente.com',    'phone' => '11999998888'],
            ['name' => 'Joao Souza',         'email' => 'joao.souza@paciente.com',     'phone' => '11988887777'],
            ['name' => 'Ana Pereira',        'email' => 'ana.pereira@paciente.com',    'phone' => '11977776666'],
            ['name' => 'Carlos Oliveira',    'email' => 'carlos.oliveira@paciente.com','phone' => '11966665555'],
            ['name' => 'Fernanda Costa',     'email' => 'fernanda.costa@paciente.com', 'phone' => '11955554444'],
            ['name' => 'Roberto Mendes',     'email' => 'roberto.mendes@paciente.com', 'phone' => '11944443333'],
            ['name' => 'Lucia Fernandes',    'email' => 'lucia.fernandes@paciente.com','phone' => '11933332222'],
            ['name' => 'Paulo Ribeiro',      'email' => 'paulo.ribeiro@paciente.com',  'phone' => '11922221111'],
        ];

        $clients = collect($clientsData)->map(function ($c) use ($tenant) {
            return User::firstOrCreate(
                ['email' => $c['email']],
                [
                    'tenant_id'  => $tenant->id,
                    'name'       => $c['name'],
                    'phone'      => $c['phone'],
                    'role'       => 'client',
                    'password'   => Hash::make('123123'),
                    'active'     => true,
                    'birth_date' => '1990-05-12',
                ]
            );
        });

        $this->command->info('8 pacientes de teste criados.');

        /*
        |--------------------------------------------------------------------------
        | 2) Obter profissionais e servicos existentes
        |--------------------------------------------------------------------------
        */
        $professionals = Professional::where('tenant_id', $tenant->id)->where('active', true)->get();
        $services = Service::where('tenant_id', $tenant->id)->where('active', true)->get();

        if ($professionals->isEmpty() || $services->isEmpty()) {
            $this->command->error('Nenhum profissional ou servico encontrado. Execute o DatabaseSeeder primeiro.');
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | 3) Feriados e bloqueios de teste
        |--------------------------------------------------------------------------
        */
        $today = Carbon::today();

        // Feriado: Tiradentes (21 de abril) proximo
        ScheduleException::firstOrCreate(
            ['tenant_id' => $tenant->id, 'date' => $today->copy()->month(4)->day(21)->toDateString(), 'type' => 'holiday'],
            ['reason' => 'Tiradentes', 'professional_id' => $professionals[0]->id]
        );

        // Feriado: Dia do Trabalho (1 de maio) proximo
        ScheduleException::firstOrCreate(
            ['tenant_id' => $tenant->id, 'date' => $today->copy()->month(5)->day(1)->toDateString(), 'type' => 'holiday'],
            ['reason' => 'Dia do Trabalho', 'professional_id' => $professionals[0]->id]
        );

        $this->command->info('Feriados de teste criados.');

        /*
        |--------------------------------------------------------------------------
        | 4) Agendamentos diversificados
        |--------------------------------------------------------------------------
        */
        $colors = ['#E91E63', '#9C27B0', '#2196F3', '#009688', '#FF9800', '#795548', '#607D8B', '#4CAF50'];

        $appointmentsData = [
            // Esta semana - variados
            [
                'client'    => $clients[0],
                'prof'      => $professionals[0],
                'service'   => $services->where('professional_id', $professionals[0]->id)->first(),
                'days'      => 1,
                'hour'      => 9,
                'status'    => 'confirmed',
                'payment'   => 'paid',
                'color'     => $colors[0],
                'notes'     => 'Retorno de avaliacao facial.',
            ],
            [
                'client'    => $clients[1],
                'prof'      => $professionals[0],
                'service'   => $services->where('professional_id', $professionals[0]->id)->first(),
                'days'      => 1,
                'hour'      => 10,
                'status'    => 'pending',
                'payment'   => 'pending',
                'color'     => $colors[1],
                'notes'     => 'Primeira consulta.',
            ],
            [
                'client'    => $clients[2],
                'prof'      => $professionals->count() > 1 ? $professionals[1] : $professionals[0],
                'service'   => $services->where('professional_id', ($professionals->count() > 1 ? $professionals[1] : $professionals[0])->id)->first(),
                'days'      => 1,
                'hour'      => 14,
                'status'    => 'confirmed',
                'payment'   => 'plan',
                'color'     => $colors[2],
                'notes'     => 'Convenio Unimed - avaliacao ortopedica.',
            ],
            [
                'client'    => $clients[3],
                'prof'      => $professionals[0],
                'service'   => $services->where('professional_id', $professionals[0]->id)->first(),
                'days'      => 2,
                'hour'      => 9,
                'status'    => 'pending',
                'payment'   => 'pending',
                'color'     => $colors[3],
                'notes'     => 'Consulta de rotina dermatologica.',
            ],
            [
                'client'    => $clients[4],
                'prof'      => $professionals->count() > 1 ? $professionals[1] : $professionals[0],
                'service'   => $services->where('professional_id', ($professionals->count() > 1 ? $professionals[1] : $professionals[0])->id)->first(),
                'days'      => 2,
                'hour'      => 11,
                'status'    => 'confirmed',
                'payment'   => 'paid',
                'color'     => $colors[4],
                'notes'     => 'Acompanhamento pos-cirurgico.',
            ],
            [
                'client'    => $clients[5],
                'prof'      => $professionals[0],
                'service'   => $services->where('professional_id', $professionals[0]->id)->first(),
                'days'      => 3,
                'hour'      => 10,
                'status'    => 'pending',
                'payment'   => 'plan',
                'color'     => $colors[5],
                'notes'     => 'Plano SulAmerica - limpeza de pele.',
            ],
            [
                'client'    => $clients[6],
                'prof'      => $professionals->count() > 1 ? $professionals[1] : $professionals[0],
                'service'   => $services->where('professional_id', ($professionals->count() > 1 ? $professionals[1] : $professionals[0])->id)->first(),
                'days'      => 3,
                'hour'      => 15,
                'status'    => 'confirmed',
                'payment'   => 'paid',
                'color'     => $colors[6],
                'notes'     => 'Exame de raio-x do ombro.',
            ],
            [
                'client'    => $clients[7],
                'prof'      => $professionals[0],
                'service'   => $services->where('professional_id', $professionals[0]->id)->first(),
                'days'      => 4,
                'hour'      => 9,
                'status'    => 'pending',
                'payment'   => 'pending',
                'color'     => $colors[7],
                'notes'     => 'Consulta inicial.',
            ],

            // Semana passada (concluidos)
            [
                'client'    => $clients[0],
                'prof'      => $professionals[0],
                'service'   => $services->where('professional_id', $professionals[0]->id)->first(),
                'days'      => -2,
                'hour'      => 11,
                'status'    => 'done',
                'payment'   => 'paid',
                'color'     => $colors[0],
                'notes'     => 'Tratamento de acne concluido.',
            ],
            [
                'client'    => $clients[2],
                'prof'      => $professionals->count() > 1 ? $professionals[1] : $professionals[0],
                'service'   => $services->where('professional_id', ($professionals->count() > 1 ? $professionals[1] : $professionals[0])->id)->first(),
                'days'      => -3,
                'hour'      => 14,
                'status'    => 'done',
                'payment'   => 'plan',
                'color'     => $colors[2],
                'notes'     => 'Consulta ortopedica finalizada - plano.',
            ],
            [
                'client'    => $clients[4],
                'prof'      => $professionals[0],
                'service'   => $services->where('professional_id', $professionals[0]->id)->first(),
                'days'      => -1,
                'hour'      => 16,
                'status'    => 'no_show',
                'payment'   => 'pending',
                'color'     => $colors[4],
                'notes'     => 'Paciente nao compareceu.',
            ],

            // Proxima semana
            [
                'client'    => $clients[1],
                'prof'      => $professionals->count() > 1 ? $professionals[1] : $professionals[0],
                'service'   => $services->where('professional_id', ($professionals->count() > 1 ? $professionals[1] : $professionals[0])->id)->first(),
                'days'      => 7,
                'hour'      => 9,
                'status'    => 'pending',
                'payment'   => 'pending',
                'color'     => $colors[1],
                'notes'     => 'Avaliacao inicial de joelho.',
            ],
            [
                'client'    => $clients[3],
                'prof'      => $professionals[0],
                'service'   => $services->where('professional_id', $professionals[0]->id)->first(),
                'days'      => 8,
                'hour'      => 10,
                'status'    => 'confirmed',
                'payment'   => 'paid',
                'color'     => $colors[3],
                'notes'     => 'Retorno tratamento estetico.',
            ],
            [
                'client'    => $clients[6],
                'prof'      => $professionals[0],
                'service'   => $services->where('professional_id', $professionals[0]->id)->first(),
                'days'      => 9,
                'hour'      => 14,
                'status'    => 'pending',
                'payment'   => 'plan',
                'color'     => $colors[6],
                'notes'     => 'Convenio Bradesco - peeling quimico.',
            ],
        ];

        $created = 0;
        foreach ($appointmentsData as $data) {
            $service = $data['service'];
            if (!$service) continue;

            $startAt = $today->copy()->addDays($data['days'])->setTime($data['hour'], 0);
            $endAt = $startAt->copy()->addMinutes($service->duration_min);

            // Evitar duplicatas
            $exists = Appointment::where('professional_id', $data['prof']->id)
                ->where('start_at', $startAt)
                ->exists();
            if ($exists) continue;

            $appointment = Appointment::create([
                'tenant_id'       => $tenant->id,
                'client_id'       => $data['client']->id,
                'professional_id' => $data['prof']->id,
                'service_id'      => $service->id,
                'start_at'        => $startAt,
                'end_at'          => $endAt,
                'status'          => $data['status'],
                'source'          => 'staff',
                'payment_status'  => $data['payment'],
                'color'           => $data['color'],
                'notes'           => $data['notes'],
            ]);

            AppointmentLog::create([
                'appointment_id'     => $appointment->id,
                'changed_by_user_id' => $admin->id ?? null,
                'from_status'        => null,
                'to_status'          => $data['status'],
                'note'               => 'Agendamento de teste criado pelo seeder.',
                'changed_at'         => now(),
            ]);

            $created++;
        }

        $this->command->info("{$created} agendamentos de teste criados.");
        $this->command->info('AppointmentSeeder finalizado com sucesso!');
    }
}
