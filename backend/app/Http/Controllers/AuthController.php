<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|string|email|max:120|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $tenantId = 1;

        if (!Tenant::find($tenantId)) {
            $tenant = Tenant::create([
                'id' => 1,
                'name' => 'Clínica Principal',
                'primary_color' => '#004d40',
                'secondary_color' => '#009688',
            ]);
            $tenantId = $tenant->id;
        }

        $user = User::create([
            'tenant_id' => $tenantId,
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'role'      => 'client', 
            'active'    => true,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => '✅ Usuário cadastrado com sucesso!',
            'user'    => $user->makeHidden(['password']),
            'token'   => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => '❌ Credenciais inválidas. Verifique e tente novamente.',
            ], 401);
        }

        if (!$user->active) {
            return response()->json([
                'message' => '⚠️ Usuário inativo. Contate o administrador.',
            ], 403);
        }
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => '✅ Login realizado com sucesso!',
            'token'   => $token,
            'user'    => $user->makeHidden(['password']),
        ], 200);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => '👋 Logout realizado com sucesso. Token revogado.',
        ], 200);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Usuário não autenticado ou token inválido.',
            ], 401);
        }

        return response()->json([
            'user' => $user->makeHidden(['password']),
        ], 200);
    }
}
