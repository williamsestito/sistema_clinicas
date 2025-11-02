<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $authUser = Auth::user();

        $query = User::where('tenant_id', $authUser->tenant_id)
            ->when($request->role, fn($q) => $q->where('role', $request->role))
            ->when(!is_null($request->active), fn($q) => $q->where('active', $request->active))
            ->orderBy('name');

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $authUser = Auth::user();

        if (!in_array($authUser->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Apenas administradores podem criar usuários.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:owner,admin,professional,frontdesk,client',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'tenant_id' => $authUser->tenant_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'active' => true,
        ]);

        return response()->json([
            'message' => '✅ Usuário criado com sucesso.',
            'data' => $user->makeHidden('password'),
        ], 201);
    }

    public function show($id)
    {
        $authUser = Auth::user();

        $user = User::where('tenant_id', $authUser->tenant_id)->find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }

        return response()->json($user->makeHidden('password'));
    }

    public function update(Request $request, $id)
    {
        $authUser = Auth::user();
        $user = User::where('tenant_id', $authUser->tenant_id)->find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }

        if (!in_array($authUser->role, ['owner', 'admin']) && $authUser->id !== $user->id) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:120',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'nullable|in:owner,admin,professional,frontdesk,client',
            'active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only(['name', 'email', 'phone', 'role', 'active']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'message' => '✅ Usuário atualizado com sucesso.',
            'data' => $user->makeHidden('password'),
        ]);
    }

    public function deactivate($id)
    {
        $authUser = Auth::user();
        $user = User::where('tenant_id', $authUser->tenant_id)->find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }

        if (!in_array($authUser->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $user->update(['active' => false]);

        return response()->json(['message' => 'Usuário inativado com sucesso.']);
    }

    public function reactivate($id)
    {
        $authUser = Auth::user();
        $user = User::where('tenant_id', $authUser->tenant_id)->find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }

        if (!in_array($authUser->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $user->update(['active' => true]);

        return response()->json(['message' => 'Usuário reativado com sucesso.']);
    }

    public function destroy($id)
    {
        $authUser = Auth::user();
        $user = User::where('tenant_id', $authUser->tenant_id)->find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }

        if (!in_array($authUser->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        if ($user->role === 'owner') {
            return response()->json(['message' => 'Não é permitido excluir o proprietário da clínica.'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'Usuário excluído com sucesso.']);
    }
}
