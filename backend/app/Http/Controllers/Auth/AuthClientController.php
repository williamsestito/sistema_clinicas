<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Client;

class AuthClientController extends Controller
{
    /**
     * LOGIN DO CLIENTE VIA API (TOKEN SANCTUM)
     * - Usado pelo aplicativo ou qualquer cliente externo
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Buscar cliente
        $client = Client::where('email', $request->email)->first();

        if (!$client || !Hash::check($request->password, $client->password)) {
            return response()->json([
                'success' => false,
                'message' => 'E-mail ou senha incorretos.'
            ], 401);
        }

        if (!$client->active) {
            return response()->json([
                'success' => false,
                'message' => 'Conta inativa. Entre em contato com o suporte.'
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | GERAR TOKEN DE API (PARA APP / MOBILE)
        |--------------------------------------------------------------------------
        | Quando o login é realizado pela API, o usuário recebe um token Sanctum.
        |--------------------------------------------------------------------------
        */

        // Remove token antigo (boa prática)
        $client->tokens()->where('name', 'client_api_token')->delete();

        $token = $client->createToken(
            'client_api_token',
            ['client-access']
        )->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login realizado com sucesso.',
            'type'    => 'client',
            'token'   => $token,
            'client'  => $client->only([
                'id', 'tenant_id', 'name', 'email', 'phone', 'city', 'state', 'active'
            ])
        ]);
    }



    /**
     * LOGOUT DO CLIENTE (API)
     * - Invalida apenas o token atual
     */
    public function logout(Request $request)
    {
        $client = $request->user();

        if ($client && $client->currentAccessToken()) {
            $client->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout efetuado com sucesso.'
        ]);
    }



    /**
     * RETORNA OS DADOS DO CLIENTE AUTENTICADO
     * - Funciona para client_api (token)
     * - Pode funcionar também com sessão caso precise
     */
    public function me(Request $request)
    {
        $client = $request->user('client_api') ?? $request->user('client');

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado.'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'client'  => $client
        ]);
    }
}
