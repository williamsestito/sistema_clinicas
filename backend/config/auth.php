<?php

return [

    'defaults' => [
        'guard'     => 'web',
        'passwords' => 'users',
    ],

    'guards' => [

        // Sessão interna (painel administrativo/profissional)
        'web' => [
            'driver'   => 'session',
            'provider' => 'users',
        ],

        // API interna (admin, owner, profissionais)
        'api' => [
            'driver'   => 'sanctum',
            'provider' => 'users',
        ],

        // Sessão do cliente (site/painel do paciente)
        'client' => [
            'driver'   => 'session',
            'provider' => 'clients',
        ],

        // API externa para cliente (app, frontend externo)
        'client_api' => [
            'driver'   => 'sanctum',
            'provider' => 'clients',
        ],
    ],

    'providers' => [

        // Usuários internos
        'users' => [
            'driver' => 'eloquent',
            'model'  => App\Models\User::class,
        ],

        // Clientes/pacientes
        'clients' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Client::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 60,
        ],

        'clients' => [
            'provider' => 'clients',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
