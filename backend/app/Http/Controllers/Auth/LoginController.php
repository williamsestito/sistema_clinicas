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
     * Exibe o formulário de login.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Processa o login de usuários internos (admin/profissional)
     * e clientes (pacientes).
     */
    public function login(Request $request)
    {
        // 🔹 Validação básica
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // 🔹 1. Tenta autenticar como usuário interno (admin/profissional)
        if (Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::guard('web')->user();

            // Redireciona conforme o tipo de usuário
            if ($user->role === 'admin' || $user->role === 'owner') {
                return redirect()->intended('/admin/dashboard');
            }

            if ($user->role === 'professional' || $user->role === 'frontdesk') {
                return redirect()->intended('/professional/dashboard');
            }

            // Fallback genérico
            return redirect()->intended('/admin/agenda');
        }

        // 🔹 2. Tenta autenticar como cliente (paciente)
        $client = Client::where('email', $request->email)->first();

        if ($client && Hash::check($request->password, $client->password)) {
            if (!$client->active) {
                return back()->withErrors([
                    'email' => 'Conta inativa. Entre em contato com o suporte.',
                ])->onlyInput('email');
            }

            Auth::guard('client')->login($client);
            $request->session()->regenerate();

            return redirect()->intended('/client/dashboard');
        }

        // 🔹 3. Falha geral de autenticação
        return back()->withErrors([
            'email' => 'E-mail ou senha incorretos. Por favor, tente novamente.',
        ])->onlyInput('email');
    }

    /**
     * Realiza logout do usuário (independente do tipo).
     */
    public function logout(Request $request)
    {
        // Logout seguro para todos os guards
        foreach (['web', 'client'] as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::guard($guard)->logout();
            }
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('status', '✅ Logout realizado com sucesso!');
    }
}
