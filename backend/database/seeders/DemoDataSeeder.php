<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{
    Tenant,
    User,
    Client,
    Professional,
    Service,
    Appointment,
    AppointmentLog,
    FinancialCategory,
    FinancialEntry,
};
use Carbon\Carbon;

/**
 * Seeder de dados de demonstração para testes.
 * Cria pacientes, agendamentos variados (últimos 30 dias + futuros),
 * categorias financeiras e lançamentos para popular gráficos do dashboard.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        $admin  = User::where('role', 'admin')->where('tenant_id', $tenant->id)->first();
        $professionals = Professional::where('tenant_id', $tenant->id)->where('active', true)->get();
        $services = Service::where('tenant_id', $tenant->id)->where('active', true)->get();

        if ($professionals->isEmpty() || $services->isEmpty()) {
            $this->command->warn('Execute o DatabaseSeeder antes deste seeder.');
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | 1) Pacientes de demonstração
        |--------------------------------------------------------------------------
        */
        $clientsData = [
            ['name' => 'Carlos Eduardo Lima',   'email' => 'carlos.lima@demo.com',    'phone' => '11991001001', 'birthdate' => '1985-03-15', 'gender' => 'male'],
            ['name' => 'Fernanda Oliveira',      'email' => 'fernanda.ol@demo.com',    'phone' => '11991002002', 'birthdate' => '1992-07-22', 'gender' => 'female'],
            ['name' => 'Ricardo Santos',         'email' => 'ricardo.santos@demo.com', 'phone' => '11991003003', 'birthdate' => '1978-11-08', 'gender' => 'male'],
            ['name' => 'Patrícia Mendes',        'email' => 'patricia.m@demo.com',     'phone' => '11991004004', 'birthdate' => '1995-01-30', 'gender' => 'female'],
            ['name' => 'Bruno Ferreira',         'email' => 'bruno.ferreira@demo.com', 'phone' => '11991005005', 'birthdate' => '1988-06-12', 'gender' => 'male'],
            ['name' => 'Camila Rodrigues',       'email' => 'camila.r@demo.com',       'phone' => '11991006006', 'birthdate' => '1990-09-25', 'gender' => 'female'],
            ['name' => 'Diego Almeida',          'email' => 'diego.alm@demo.com',      'phone' => '11991007007', 'birthdate' => '1983-12-05', 'gender' => 'male'],
            ['name' => 'Larissa Costa',          'email' => 'larissa.costa@demo.com',  'phone' => '11991008008', 'birthdate' => '1997-04-18', 'gender' => 'female'],
            ['name' => 'Thiago Barros',          'email' => 'thiago.b@demo.com',       'phone' => '11991009009', 'birthdate' => '1980-08-02', 'gender' => 'male'],
            ['name' => 'Juliana Nascimento',     'email' => 'juliana.n@demo.com',      'phone' => '11991010010', 'birthdate' => '1994-02-14', 'gender' => 'female'],
        ];

        $clients = collect();
        foreach ($clientsData as $c) {
            // Create a user entry for the client (appointments FK references users)
            $user = User::firstOrCreate(
                ['email' => $c['email']],
                [
                    'tenant_id' => $tenant->id,
                    'name'      => $c['name'],
                    'password'  => Hash::make('123123'),
                    'role'      => 'client',
                    'active'    => true,
                ]
            );

            $client = Client::firstOrCreate(
                ['email' => $c['email'], 'tenant_id' => $tenant->id],
                [
                    'tenant_id' => $tenant->id,
                    'user_id'   => $user->id,
                    'name'      => $c['name'],
                    'phone'     => $c['phone'],
                    'birthdate' => $c['birthdate'],
                    'gender'    => $c['gender'] ?? null,
                    'consent_marketing' => true,
                    'password'  => Hash::make('123123'),
                ]
            );

            // Store user_id so we can use it as client_id in appointments
            $client->_user_id = $user->id;
            $clients->push($client);
        }

        // Merge existing clients from DatabaseSeeder (they also need user references)
        $existingClients = Client::where('tenant_id', $tenant->id)
            ->whereNotIn('email', collect($clientsData)->pluck('email'))
            ->get();

        // For existing clients, find or create user entries
        foreach ($existingClients as $ec) {
            $user = User::firstOrCreate(
                ['email' => $ec->email],
                [
                    'tenant_id' => $tenant->id,
                    'name'      => $ec->name,
                    'password'  => Hash::make('123123'),
                    'role'      => 'client',
                    'active'    => true,
                ]
            );
            $ec->_user_id = $user->id;
        }

        $allClients = $existingClients->merge($clients)->unique('id');

        $this->command->info("Pacientes: {$allClients->count()} disponíveis.");

        /*
        |--------------------------------------------------------------------------
        | 2) Agendamentos — últimos 30 dias + próximos 7 dias
        |--------------------------------------------------------------------------
        */
        $statuses = ['pending', 'confirmed', 'done', 'cancelled', 'no_show'];
        $now = Carbon::now();
        $createdAppointments = collect();

        // Past 30 days — mostly completed/no_show/cancelled
        for ($day = 30; $day >= 1; $day--) {
            $date = $now->copy()->subDays($day);
            if ($date->isWeekend()) continue; // skip weekends

            $numAppointments = rand(2, 5);
            for ($i = 0; $i < $numAppointments; $i++) {
                $professional = $professionals->random();
                $service = $services->where('professional_id', $professional->id)->first() ?? $services->random();
                $client = $allClients->random();
                $clientUserId = $client->_user_id ?? $client->user_id ?? $client->id;
                $hour = rand(8, 16);
                $minute = [0, 15, 30, 45][array_rand([0, 15, 30, 45])];

                // Past appointments are mostly done
                $statusWeights = ['done' => 60, 'cancelled' => 15, 'no_show' => 10, 'confirmed' => 5, 'pending' => 10];
                $status = $this->weightedRandom($statusWeights);

                $startAt = $date->copy()->setTime($hour, $minute);
                $endAt = $startAt->copy()->addMinutes($service->duration_min ?? 30);

                $chargedAmount = null;
                if ($status === 'done') {
                    // Vary charged_amount slightly from service price
                    $chargedAmount = $service->price + rand(-20, 50);
                    if ($chargedAmount < 0) $chargedAmount = $service->price;
                }

                $apt = Appointment::create([
                    'tenant_id'       => $tenant->id,
                    'client_id'       => $clientUserId,
                    'professional_id' => $professional->id,
                    'service_id'      => $service->id,
                    'start_at'        => $startAt,
                    'end_at'          => $endAt,
                    'status'          => $status,
                    'source'          => ['web', 'staff', 'whatsapp'][array_rand(['web', 'staff', 'whatsapp'])],
                    'charged_amount'  => $chargedAmount,
                    'notes'           => 'Dados de demonstração.',
                ]);

                $createdAppointments->push($apt);

                AppointmentLog::create([
                    'appointment_id'     => $apt->id,
                    'from_status'        => 'pending',
                    'to_status'          => $status,
                    'changed_by_user_id' => $admin->id,
                    'note'               => 'Seeder de demonstração.',
                    'changed_at'         => $startAt,
                ]);
            }
        }

        // Today — mix of statuses
        for ($i = 0; $i < rand(3, 6); $i++) {
            $professional = $professionals->random();
            $service = $services->where('professional_id', $professional->id)->first() ?? $services->random();
            $client = $allClients->random();
            $clientUserId = $client->_user_id ?? $client->user_id ?? $client->id;
            $hour = rand(8, 17);

            $status = $this->weightedRandom([
                'pending' => 30, 'confirmed' => 40, 'done' => 20, 'cancelled' => 10,
            ]);

            $startAt = $now->copy()->setTime($hour, [0, 30][array_rand([0, 30])]);
            $endAt = $startAt->copy()->addMinutes($service->duration_min ?? 30);

            $apt = Appointment::create([
                'tenant_id'       => $tenant->id,
                'client_id'       => $clientUserId,
                'professional_id' => $professional->id,
                'service_id'      => $service->id,
                'start_at'        => $startAt,
                'end_at'          => $endAt,
                'status'          => $status,
                'source'          => 'web',
                'charged_amount'  => $status === 'done' ? $service->price : null,
                'notes'           => 'Agendamento de hoje (demo).',
            ]);

            $createdAppointments->push($apt);
        }

        // Future 7 days — pending/confirmed
        for ($day = 1; $day <= 7; $day++) {
            $date = $now->copy()->addDays($day);
            if ($date->isWeekend()) continue;

            $numAppointments = rand(2, 4);
            for ($i = 0; $i < $numAppointments; $i++) {
                $professional = $professionals->random();
                $service = $services->where('professional_id', $professional->id)->first() ?? $services->random();
                $client = $allClients->random();
                $clientUserId = $client->_user_id ?? $client->user_id ?? $client->id;
                $hour = rand(8, 16);

                $startAt = $date->copy()->setTime($hour, [0, 30][array_rand([0, 30])]);
                $endAt = $startAt->copy()->addMinutes($service->duration_min ?? 30);

                Appointment::create([
                    'tenant_id'       => $tenant->id,
                    'client_id'       => $clientUserId,
                    'professional_id' => $professional->id,
                    'service_id'      => $service->id,
                    'start_at'        => $startAt,
                    'end_at'          => $endAt,
                    'status'          => ['pending', 'confirmed'][array_rand(['pending', 'confirmed'])],
                    'source'          => 'web',
                    'notes'           => 'Agendamento futuro (demo).',
                ]);
            }
        }

        $this->command->info("Agendamentos criados: {$createdAppointments->count()} passados + hoje + futuros.");

        /*
        |--------------------------------------------------------------------------
        | 3) Categorias Financeiras
        |--------------------------------------------------------------------------
        */
        $categories = [
            ['name' => 'Consultas',       'type' => 'income',  'color' => '#22c55e'],
            ['name' => 'Procedimentos',   'type' => 'income',  'color' => '#3b82f6'],
            ['name' => 'Produtos',        'type' => 'income',  'color' => '#8b5cf6'],
            ['name' => 'Convênios',       'type' => 'income',  'color' => '#14b8a6'],
            ['name' => 'Aluguel',         'type' => 'expense', 'color' => '#ef4444'],
            ['name' => 'Salários',        'type' => 'expense', 'color' => '#f97316'],
            ['name' => 'Material Médico', 'type' => 'expense', 'color' => '#ec4899'],
            ['name' => 'Energia/Água',    'type' => 'expense', 'color' => '#eab308'],
            ['name' => 'Marketing',       'type' => 'expense', 'color' => '#6366f1'],
            ['name' => 'Outros',          'type' => 'expense', 'color' => '#9ca3af'],
        ];

        $catModels = collect();
        foreach ($categories as $cat) {
            $catModels->push(FinancialCategory::firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $cat['name']],
                [
                    'tenant_id' => $tenant->id,
                    'type'   => $cat['type'],
                    'color'  => $cat['color'],
                    'active' => true,
                ]
            ));
        }

        $incomeCategories  = $catModels->where('type', 'income');
        $expenseCategories = $catModels->where('type', 'expense');

        $this->command->info("Categorias financeiras: {$catModels->count()} criadas.");

        /*
        |--------------------------------------------------------------------------
        | 4) Lançamentos Financeiros — últimos 6 meses
        |--------------------------------------------------------------------------
        */
        $paymentMethods = ['pix', 'credit_card', 'debit_card', 'cash', 'transfer'];

        for ($month = 5; $month >= 0; $month--) {
            $mStart = Carbon::now()->subMonths($month)->startOfMonth();
            $mEnd   = Carbon::now()->subMonths($month)->endOfMonth();
            $daysInMonth = $mStart->daysInMonth;

            // Income entries — from completed appointments
            $completedApts = $createdAppointments->filter(function ($apt) use ($mStart, $mEnd) {
                return $apt->status === 'done'
                    && $apt->start_at->between($mStart, $mEnd);
            });

            foreach ($completedApts as $apt) {
                $cat = $incomeCategories->random();
                FinancialEntry::create([
                    'tenant_id'          => $tenant->id,
                    'category_id'        => $cat->id,
                    'appointment_id'     => $apt->id,
                    'type'               => 'income',
                    'description'        => 'Pagamento: ' . ($apt->service?->name ?? 'Consulta'),
                    'amount'             => $apt->charged_amount ?? $apt->service?->price ?? 250,
                    'date'               => $apt->start_at->toDateString(),
                    'payment_method'     => $paymentMethods[array_rand($paymentMethods)],
                    'created_by_user_id' => $admin->id,
                ]);
            }

            // Additional income entries (products, extras)
            $extraIncome = rand(2, 5);
            for ($i = 0; $i < $extraIncome; $i++) {
                $day = rand(1, $daysInMonth);
                $date = $mStart->copy()->addDays($day - 1);
                if ($date->isFuture()) continue;

                FinancialEntry::create([
                    'tenant_id'          => $tenant->id,
                    'category_id'        => $incomeCategories->random()->id,
                    'type'               => 'income',
                    'description'        => ['Venda de produto', 'Sessão extra', 'Retorno pago', 'Exame complementar'][array_rand(['Venda de produto', 'Sessão extra', 'Retorno pago', 'Exame complementar'])],
                    'amount'             => rand(50, 500),
                    'date'               => $date->toDateString(),
                    'payment_method'     => $paymentMethods[array_rand($paymentMethods)],
                    'created_by_user_id' => $admin->id,
                ]);
            }

            // Expense entries
            $expenses = [
                ['desc' => 'Aluguel mensal',       'cat' => 'Aluguel',         'amount' => 3500],
                ['desc' => 'Folha de pagamento',    'cat' => 'Salários',        'amount' => rand(8000, 12000)],
                ['desc' => 'Material descartável',  'cat' => 'Material Médico', 'amount' => rand(500, 1500)],
                ['desc' => 'Conta de energia',      'cat' => 'Energia/Água',    'amount' => rand(300, 700)],
                ['desc' => 'Conta de água',         'cat' => 'Energia/Água',    'amount' => rand(100, 250)],
            ];

            // Add random extra expenses
            if (rand(0, 1)) {
                $expenses[] = ['desc' => 'Campanha Google Ads', 'cat' => 'Marketing', 'amount' => rand(200, 800)];
            }
            if (rand(0, 1)) {
                $expenses[] = ['desc' => 'Manutenção equipamento', 'cat' => 'Outros', 'amount' => rand(150, 600)];
            }

            foreach ($expenses as $exp) {
                $cat = $catModels->where('name', $exp['cat'])->first();
                if (!$cat) continue;

                $day = rand(1, min($daysInMonth, 28));
                $date = $mStart->copy()->addDays($day - 1);
                if ($date->isFuture()) continue;

                FinancialEntry::create([
                    'tenant_id'          => $tenant->id,
                    'category_id'        => $cat->id,
                    'type'               => 'expense',
                    'description'        => $exp['desc'],
                    'amount'             => $exp['amount'],
                    'date'               => $date->toDateString(),
                    'payment_method'     => $paymentMethods[array_rand($paymentMethods)],
                    'created_by_user_id' => $admin->id,
                ]);
            }
        }

        $totalEntries = FinancialEntry::where('tenant_id', $tenant->id)->count();
        $this->command->info("Lançamentos financeiros: {$totalEntries} criados.");

        $this->command->info('✅ DemoDataSeeder concluído com sucesso!');
    }

    /**
     * Retorna uma chave aleatória baseada em peso.
     */
    private function weightedRandom(array $weights): string
    {
        $total = array_sum($weights);
        $rand = rand(1, $total);
        $current = 0;
        foreach ($weights as $key => $weight) {
            $current += $weight;
            if ($rand <= $current) return $key;
        }
        return array_key_first($weights);
    }
}
