<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Professional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Listagem JSON (API)
     */
    public function index(Request $request)
    {
        $authUser = Auth::user();

        $query = User::query()
            ->when($authUser->tenant_id, fn($q) => $q->where('tenant_id', $authUser->tenant_id))
            ->when($request->role, fn($q) => $q->where('role', $request->role))
            ->when(!is_null($request->active), fn($q) => $q->where('active', $request->active))
            ->orderBy('name');

        return response()->json($query->paginate(20));
    }

    /**
     * Listagem em view
     */
    public function listView(Request $request)
    {
        $authUser = Auth::user();

        $usuarios = User::query()
            ->when($authUser->tenant_id, fn($q) => $q->where('tenant_id', $authUser->tenant_id))
            ->where('role', '!=', 'client')
            ->when($request->role, fn($q) => $q->where('role', $request->role))
            ->when($request->filled('active'), fn($q) => $q->where('active', $request->active))
            ->when($request->search, fn($q) =>
                $q->where(function ($sub) use ($request) {
                    $sub->where('name', 'like', "%{$request->search}%")
                        ->orWhere('email', 'like', "%{$request->search}%");
                })
            )
            ->orderBy('name')
            ->paginate(10);

        return view('employees.employees', compact('usuarios'));
    }

    public function create()
    {
        return view('employees.create');
    }

    /**
     * Cria novo colaborador
     */
    public function store(Request $request)
    {
        $authUser = Auth::user();

        if (!in_array($authUser->role, ['owner', 'admin'])) {
            return back()->with('error', 'Apenas administradores podem criar colaboradores.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:owner,admin,professional,frontdesk',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'document' => 'nullable|string|max:14',
            'rg' => 'nullable|string|max:20',
            'civil_status' => 'nullable|string|max:20',
            'gender' => 'nullable|string|max:20',
            'cep' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:10',
            'complement' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'social_name' => 'nullable|boolean',
            'social_name_text' => 'nullable|string|max:120',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create(array_merge(
            $validator->validated(),
            [
                'tenant_id' => $authUser->tenant_id ?? 1,
                'active' => true,
            ]
        ));

        // 🔧 Cria o registro do profissional automaticamente
        if ($user->role === 'professional') {
            Professional::create([
                'tenant_id' => $user->tenant_id,
                'user_id'   => $user->id,
                'active'    => true,
            ]);
        }

        return redirect()->route('employees.index')
            ->with('success', '✅ Colaborador cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $authUser = Auth::user();

        $usuario = User::query()
            ->when($authUser->tenant_id, fn($q) => $q->where('tenant_id', $authUser->tenant_id))
            ->findOrFail($id);

        return view('employees.edit', compact('usuario'));
    }

    /**
     * Atualiza colaborador
     */
    public function update(Request $request, $id)
    {
        $authUser = Auth::user();

        $usuario = User::query()
            ->when($authUser->tenant_id, fn($q) => $q->where('tenant_id', $authUser->tenant_id))
            ->findOrFail($id);

        if (!in_array($authUser->role, ['owner', 'admin'])) {
            return back()->with('error', 'Apenas administradores podem editar colaboradores.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email,' . $usuario->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:owner,admin,professional,frontdesk',
            'phone' => 'nullable|string|max:20',
            'active' => 'nullable|boolean',
            'birth_date' => 'nullable|date',
            'document' => 'nullable|string|max:14',
            'rg' => 'nullable|string|max:20',
            'civil_status' => 'nullable|string|max:20',
            'gender' => 'nullable|string|max:20',
            'cep' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:10',
            'complement' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'social_name' => 'nullable|boolean',
            'social_name_text' => 'nullable|string|max:120',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        if ($request->filled('password')) $data['password'] = Hash::make($request->password);

        $usuario->update($data);

        return redirect()->route('employees.index')
            ->with('success', '✅ Colaborador atualizado com sucesso.');
    }

    /**
     * Remove colaborador e registro de profissional (se houver)
     */
    public function destroy($id)
    {
        $authUser = Auth::user();

        $usuario = User::query()
            ->when($authUser->tenant_id, fn($q) => $q->where('tenant_id', $authUser->tenant_id))
            ->findOrFail($id);

        if (!in_array($authUser->role, ['owner', 'admin'])) {
            return back()->with('error', 'Apenas administradores podem excluir colaboradores.');
        }

        if ($usuario->role === 'owner') {
            return back()->with('error', 'Não é permitido excluir o proprietário da clínica.');
        }

        // Remove profissional vinculado (se existir)
        if ($usuario->role === 'professional' && $usuario->professional) {
            $usuario->professional->delete();
        }

        $usuario->delete();

        return redirect()->route('employees.index')
            ->with('success', '🗑️ Colaborador excluído com sucesso.');
    }
}
