<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Retorna lista de usuários (API interna em JSON)
     */
    public function index(Request $request)
    {
        $authUser = Auth::user();

        $query = User::when($authUser->tenant_id, fn($q) => $q->where('tenant_id', $authUser->tenant_id))
            ->when($request->role, fn($q) => $q->where('role', $request->role))
            ->when(!is_null($request->active), fn($q) => $q->where('active', $request->active))
            ->orderBy('name');

        return response()->json($query->paginate(20));
    }

    /**
     * Exibe lista de colaboradores (view Blade)
     */
    public function listView(Request $request)
    {
        $authUser = Auth::user();

        $usuarios = User::query()
            ->when($authUser->tenant_id, fn($q) => $q->where('tenant_id', $authUser->tenant_id))
            ->where('role', '!=', 'client')
            ->when($request->role, fn($q) => $q->where('role', $request->role))
            ->when($request->filled('active'), fn($q) => $q->where('active', $request->active))
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('name', 'like', "%{$request->search}%")
                        ->orWhere('email', 'like', "%{$request->search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10);

        return view('employees.employees', compact('usuarios'));
    }

    /**
     * Exibe formulário de criação de colaborador
     */
    public function create()
    {
        return view('employees.create');
    }

    /**
     * Salva um novo colaborador
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
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:owner,admin,professional,frontdesk',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        User::create([
            'tenant_id' => $authUser->tenant_id ?? 1, // fallback seguro
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'active' => true,
        ]);

        return redirect()
            ->route('employees.index')
            ->with('success', '✅ Colaborador cadastrado com sucesso.');
    }

    /**
     * Exibe formulário de edição de colaborador
     */
    public function edit($id)
    {
        $authUser = Auth::user();

        $usuario = User::query()
            ->when($authUser->tenant_id, fn($q) => $q->where('tenant_id', $authUser->tenant_id))
            ->findOrFail($id);

        return view('employees.edit', compact('usuario'));
    }

    /**
     * Atualiza dados de um colaborador
     */
    public function update(Request $request, $id)
    {
        $authUser = Auth::user();

        $usuario = User::query()
            ->when($authUser->tenant_id, fn($q) => $q->where('tenant_id', $authUser->tenant_id))
            ->findOrFail($id);

        if (!in_array($authUser->role, ['owner', 'admin'])) {
            return back()->with('error', 'Acesso negado. Apenas administradores podem editar colaboradores.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email,' . $usuario->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:owner,admin,professional,frontdesk',
            'active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->only(['name', 'email', 'phone', 'role', 'active']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $usuario->update($data);

        return redirect()
            ->route('employees.index')
            ->with('success', '✅ Colaborador atualizado com sucesso.');
    }

    /**
     * Exclui colaborador
     */
    public function destroy($id)
    {
        $authUser = Auth::user();

        $usuario = User::query()
            ->when($authUser->tenant_id, fn($q) => $q->where('tenant_id', $authUser->tenant_id))
            ->findOrFail($id);

        if (!in_array($authUser->role, ['owner', 'admin'])) {
            return back()->with('error', 'Acesso negado. Apenas administradores podem excluir colaboradores.');
        }

        if ($usuario->role === 'owner') {
            return back()->with('error', 'Não é permitido excluir o proprietário da clínica.');
        }

        $usuario->delete();

        return redirect()
            ->route('employees.index')
            ->with('success', '🗑️ Colaborador excluído com sucesso.');
    }
}
