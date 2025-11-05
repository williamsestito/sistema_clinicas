<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Tenant;

class RegisterController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $v = Validator::make($request->all(), [
            'name'                  => 'required|string|max:120',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|string|min:6|confirmed',
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        $authUser = Auth::user();
        if ($authUser) {
            $tenantId = $authUser->tenant_id;
            $role = 'client';
        } else {
            $tenant = Tenant::create([
                'name'   => $request->name . ' - Clínica',
                'active' => true,
            ]);
            $tenantId = $tenant->id;
            $role = 'owner';
        }

        $user = User::create([
            'tenant_id' => $tenantId,
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $role,
            'active'    => true,
        ]);

        if (!$authUser) {
            return redirect()
                ->route('login')
                ->with('success', '✅ Cadastro realizado com sucesso! Faça login para continuar.');
        }

        return redirect()->route('dashboard')->with('success', 'Usuário criado com sucesso.');
    }
}
