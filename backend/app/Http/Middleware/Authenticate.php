<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Auth\AuthenticationException;

class Authenticate extends Middleware
{

    protected function unauthenticated($request, array $guards)
    {
        throw new AuthenticationException(
            'Não autenticado. Token ausente ou inválido.',
            $guards,
            $this->redirectTo($request)
        );
    }

 
    protected function redirectTo($request): ?string
    {

        if ($request->expectsJson()) {
            return null;
        }
        return route('login');
    }
}
