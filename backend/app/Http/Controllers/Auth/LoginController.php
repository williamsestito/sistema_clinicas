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
     * Tela única de login (para todos os tipos).
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Login unificado:
     * - admin / owner / professional / frontdesk → guard:web (tabela users)
     * - client (paciente)                       → guard:client (tabela clients)
     */
    public function login(Request $request)
    {
        // ------------------------------------------------------------
        // 1) Validação básica
        // ------------------------------------------------------------
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        // ------------------------------------------------------------
        // 2) TENTA LOGIN COMO USUÁRIO INTERNO (users / guard:web)
        // ------------------------------------------------------------
        $user = User::where('email', $credentials['email'])->first();

        if ($user) {

            // Se usuário interno existe mas está inativo → mensagem específica
            if (!$user->active) {
                return back()->withErrors([
                    'email' => 'Sua conta de acesso interno está inativa. Contate o administrador.',
                ])->onlyInput('email');
            }

            // Confere senha
            if (Hash::check($credentials['password'], $user->password)) {

                Auth::guard('web')->login($user, $remember);
                $request->session()->regenerate();

                // Direcionamento baseado no papel (role)
                return match ($user->role) {
                    'owner', 'admin' => redirect()->route('admin.dashboard'),
                    'professional'   => redirect()->route('professional.dashboard'),
                    'frontdesk'      => redirect()->route('professional.dashboard'),
                    default          => redirect()->route('admin.agenda'),
                };
            }
        }

        // ------------------------------------------------------------
        // 3) TENTA LOGIN COMO CLIENTE / PACIENTE (clients / guard:client)
        // ------------------------------------------------------------
        $client = Client::where('email', $credentials['email'])->first();

        if ($client) {

            if (!$client->active) {
                return back()->withErrors([
                    'email' => 'Sua conta de paciente está inativa. Entre em contato com a clínica.',
                ])->onlyInput('email');
            }

            if (Hash::check($credentials['password'], $client->password)) {

                Auth::guard('client')->login($client, $remember);
                $request->session()->regenerate();

                return redirect()->route('client.dashboard');
            }
        }

        // ------------------------------------------------------------
        // 4) FALHOU PARA TODOS OS TIPOS
        // ------------------------------------------------------------
        return back()->withErrors([
            'email' => 'E-mail ou senha incorretos.',
        ])->onlyInput('email');
    }

    /**
     * Logout unificado para ambos os guards (web e client).
     */
    public function logout(Request $request)
    {
        foreach (['web', 'client'] as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::guard($guard)->logout();
            }
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'Logout realizado com sucesso.');
    }
}
