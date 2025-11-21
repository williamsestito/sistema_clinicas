<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
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
