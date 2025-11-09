<?php

return [

    // Configuração padrão
    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Guards de Autenticação
    |--------------------------------------------------------------------------
    |
    | web/api → para usuários do sistema (admin, profissionais, recepção)
    | client/client_api → para clientes (pacientes)
    |
    */
    'guards' => [
        // Sessão (painel interno)
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // API para usuários internos (usado com Sanctum)
        'api' => [
            'driver' => 'sanctum',
            'provider' => 'users',
        ],

        // Sessão de clientes (ex: login em frontend público)
        'client' => [
            'driver' => 'session',
            'provider' => 'clients',
        ],

        // API de clientes (login via app, mobile, etc)
        'client_api' => [
            'driver' => 'sanctum',
            'provider' => 'clients',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Providers
    |--------------------------------------------------------------------------
    |
    | Define de onde os usuários são obtidos (models eloquent)
    |
    */
    'providers' => [
        // Usuários do sistema (admin, profissional, recepção)
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        // Clientes (pacientes)
        'clients' => [
            'driver' => 'eloquent',
            'model' => App\Models\Client::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Recuperação de Senha
    |--------------------------------------------------------------------------
    */
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'clients' => [
            'provider' => 'clients',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    // Tempo limite de senha (3 horas)
    'password_timeout' => 10800,

];
