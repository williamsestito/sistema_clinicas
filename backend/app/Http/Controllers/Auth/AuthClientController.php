<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Client;

class AuthClientController extends Controller
{
    /**
     * Login do cliente via Sanctum
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Busca o cliente pelo e-mail
        $client = Client::where('email', $request->email)->first();

        // Verifica senha e status
        if (!$client || !Hash::check($request->password, $client->password)) {
            return response()->json(['message' => 'E-mail ou senha incorretos.'], 401);
        }

        if (!$client->active) {
            return response()->json(['message' => 'Conta inativa.'], 403);
        }

        // Gera token Sanctum
        $token = $client->createToken('client_api_token')->plainTextToken;

        return response()->json([
            'message' => 'Login efetuado com sucesso.',
            'type' => 'client',
            'token' => $token,
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'tenant_id' => $client->tenant_id,
                'phone' => $client->phone,
                'city' => $client->city,
                'state' => $client->state,
                'active' => $client->active,
            ]
        ]);
    }

    /**
     * Logout do cliente
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->tokens()->delete();
        }

        return response()->json(['message' => 'Logout efetuado com sucesso.']);
    }

    /**
     * Retorna o cliente autenticado
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
