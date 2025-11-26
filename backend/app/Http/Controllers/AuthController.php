<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * ------------------------------------------------------------------
     * LOGIN WEB (FORM login.blade.php)
     * ------------------------------------------------------------------
     * - Usa o formulário único para TODOS os usuários.
     * - Tenta primeiro logar como usuário interno (guard web).
     * - Se não achar, tenta logar como cliente (guard client).
     * - Redireciona para o painel adequado.
     * ------------------------------------------------------------------
     */
    public function loginWeb(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        /*
        |--------------------------------------------------------------------------
        | 1) Tentativa: USUÁRIO INTERNO (admin/owner/staff/profissional)
        |--------------------------------------------------------------------------
        */
        if (Auth::guard('web')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            /** @var \App\Models\User $user */
            $user = Auth::guard('web')->user();

            // Se tiver campo "role", pode direcionar por tipo
            switch ($user->role) {
                case 'owner':
                case 'admin':
                    // ajuste este nome de rota conforme seu sistema
                    return redirect()->intended(route('admin.dashboard'));
                case 'staff':
                case 'professional':
                    // ajuste se tiver dashboard específico
                    return redirect()->intended(route('staff.dashboard'));
                default:
                    // fallback genérico
                    return redirect()->intended(route('home'));
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 2) Tentativa: CLIENTE / PACIENTE (guard "client")
        |--------------------------------------------------------------------------
        | Aqui usamos o provider "clients" configurado em config/auth.php
        | para autenticação via sessão (não é o guard de API).
        |--------------------------------------------------------------------------
        */
        if (Auth::guard('client')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            /** @var \App\Models\Client $client */
            $client = Auth::guard('client')->user();

            // Se quiser gerar token de API para usar depois, descomente:
            // $token = $client->createToken('client_token')->plainTextToken;
            // session(['client_api_token' => $token]);

            // Rota padrão do painel do cliente
            return redirect()->intended(route('client.schedule'));
        }

        /*
        |--------------------------------------------------------------------------
        | 3) Falha nas duas tentativas
        |--------------------------------------------------------------------------
        */
        return back()
            ->withErrors(['email' => 'Credenciais inválidas.'])
            ->onlyInput('email');
    }

    /**
     * Registro de usuário interno (API)
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:120',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $authUser = Auth::user();

        // Se o registro é via admin ou owner
        if ($authUser) {
            $tenantId = $authUser->tenant_id;
        } else {
            // Registro externo cria tenant automaticamente
            $tenant = Tenant::create([
                'name'   => "{$request->name} - Clínica",
                'active' => true,
            ]);
            $tenantId = $tenant->id;
        }

        $user = User::create([
            'tenant_id' => $tenantId,
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $authUser ? 'staff' : 'owner',
            'active'    => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuário criado com sucesso.',
            'user'    => $user->makeHidden('password')
        ], 201);
    }

    /**
     * Login via API (usuários internos)
     * ⚠️ Mantido como está para não quebrar /api/auth/login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciais inválidas.'
            ], 401);
        }

        $user = Auth::user();

        if (!$user->active) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário inativo.'
            ], 403);
        }

        // Criação do token
        $token = $user->createToken(
            'api_token',
            ['user-access']
        )->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login realizado com sucesso.',
            'token'   => $token,
            'user'    => $user
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'user'    => $request->user()
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso.'
        ]);
    }
}
