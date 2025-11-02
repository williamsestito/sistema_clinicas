<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Nome da Aplicação
    |--------------------------------------------------------------------------
    |
    | Este valor define o nome do seu aplicativo e é usado em notificações,
    | e-mails, logs e outros contextos onde a identificação do sistema é necessária.
    |
    */

    'name' => env('APP_NAME', 'Sistema Clínicas'),

    /*
    |--------------------------------------------------------------------------
    | Ambiente da Aplicação
    |--------------------------------------------------------------------------
    |
    | Define o ambiente atual da aplicação (local, staging, production).
    | Afeta comportamentos como cache, debug e logs.
    |
    */

    'env' => env('APP_ENV', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Modo Debug
    |--------------------------------------------------------------------------
    |
    | Quando habilitado, exibe mensagens de erro detalhadas com stack trace.
    | Em produção, deve ser sempre false.
    |
    */

    'debug' => (bool) env('APP_DEBUG', true),

    /*
    |--------------------------------------------------------------------------
    | URL da Aplicação
    |--------------------------------------------------------------------------
    |
    | Define a URL base usada pelo Artisan e outros serviços internos.
    | Idealmente, deve apontar para o domínio ou endpoint principal.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Fuso Horário
    |--------------------------------------------------------------------------
    |
    | Define o fuso horário padrão para todas as funções de data/hora do PHP.
    | Recomendado: America/Sao_Paulo para o Brasil.
    |
    */

    'timezone' => env('APP_TIMEZONE', 'America/Sao_Paulo'),

    /*
    |--------------------------------------------------------------------------
    | Localização (Idioma)
    |--------------------------------------------------------------------------
    |
    | Define o idioma padrão para traduções, validações e mensagens do sistema.
    | O fallback_locale é usado caso o idioma principal não esteja disponível.
    |
    */

    'locale' => env('APP_LOCALE', 'pt_BR'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Idioma para o Faker
    |--------------------------------------------------------------------------
    |
    | Controla o idioma usado pelo Faker (gerador de dados falsos) nos Seeders
    | e Factories. "pt_BR" gera nomes, endereços e telefones brasileiros.
    |
    */

    'faker_locale' => env('APP_FAKER_LOCALE', 'pt_BR'),

    /*
    |--------------------------------------------------------------------------
    | Chave de Criptografia
    |--------------------------------------------------------------------------
    |
    | A chave usada para criptografar dados sensíveis. Deve ter 32 caracteres.
    | Gere com: php artisan key:generate
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Modo de Manutenção
    |--------------------------------------------------------------------------
    |
    | Controla como o Laravel lida com o modo de manutenção.
    | O driver "file" cria um arquivo local; o "cache" permite controle distribuído.
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
