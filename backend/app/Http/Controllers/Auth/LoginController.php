<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Client;

class LoginController extends Controller
{
    /**
     * Exibe a tela de login unificada.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * LOGIN UNIFICADO PARA:
     * - Admin / Owner / Profissional / Atendente → guard:web
     * - Cliente / Paciente → guard:client
     */
    public function login(Request $request)
    {
        // 1) Validação
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');


        /*
        |--------------------------------------------------------------------------
        | 2) LOGIN PARA USUÁRIOS INTERNOS (TABELA USERS)
        |--------------------------------------------------------------------------
        */
        $user = User::where('email', $credentials['email'])->first();

        if ($user) {

            if (!$user->active) {
                return back()
                    ->withErrors(['email' => 'Sua conta está inativa.'])
                    ->onlyInput('email');
            }

            if (Hash::check($credentials['password'], $user->password)) {

                Auth::guard('web')->login($user, $remember);
                $request->session()->regenerate();

                return match ($user->role) {
                    'owner', 'admin' => redirect()->route('admin.dashboard'),
                    'professional'   => redirect()->route('professional.dashboard'),
                    'frontdesk'      => redirect()->route('professional.dashboard'),
                    default          => redirect()->route('admin.dashboard'),
                };
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 3) LOGIN PARA CLIENTE / PACIENTE (TABELA CLIENTS)
        |--------------------------------------------------------------------------
        */
        $client = Client::where('email', $credentials['email'])->first();

        if ($client) {

            if (!$client->active) {
                return back()
                    ->withErrors(['email' => 'Sua conta de paciente está inativa.'])
                    ->onlyInput('email');
            }

            if (Hash::check($credentials['password'], $client->password)) {

                Auth::guard('client')->login($client, $remember);
                $request->session()->regenerate();

                return redirect()->route('client.dashboard');
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 4) ERRO PADRÃO
        |--------------------------------------------------------------------------
        */
        return back()
            ->withErrors(['email' => 'E-mail ou senha incorretos.'])
            ->onlyInput('email');
    }


    /**
     * Logout unificado (WEB + CLIENT)
     */
    public function logout(Request $request)
    {
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        if (Auth::guard('client')->check()) {
            Auth::guard('client')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'Logout realizado com sucesso.');
    }
}
