<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Client;

class AuthClientController extends Controller
{
    /**
     * Login do cliente (API via Sanctum)
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Buscar cliente no banco
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

        // Zera tokens anteriores (opcional, porém recomendado)
        $client->tokens()->where('name', 'client_api_token')->delete();

        // Gerar token Sanctum
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
     * Logout (API)
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout efetuado com sucesso.'
        ]);
    }


    /**
     * Dados do cliente autenticado via Sanctum
     */
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'client'  => $request->user()
        ]);
    }
}
