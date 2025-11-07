<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 🔹 Verifica se existe tenant padrão
        $tenant = Tenant::firstOrCreate(
            ['id' => 1],
            ['name' => 'Clínica Principal', 'active' => true]
        );

        // 🔹 Cria usuário admin padrão, se não existir
        User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'User Admin',
                'password' => Hash::make('123123'),
                'role' => 'admin',
                'active' => true,
            ]
        );

        $this->command->info('Seeder executado com sucesso: Tenant e usuário admin configurados.');
    }
}
