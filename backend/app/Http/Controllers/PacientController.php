<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PacientController extends Controller
{
    public function index(Request $request)
    {
        $authUser = Auth::user();

        $pacients = User::query()
            ->where('role', 'client')
            ->when($authUser->tenant_id, fn($q) => $q->where('tenant_id', $authUser->tenant_id))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('name', 'like', "%{$request->search}%")
                        ->orWhere('email', 'like', "%{$request->search}%")
                        ->orWhere('phone', 'like', "%{$request->search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15);

        return view('pacients.index', compact('pacients'));
    }

    public function create()
    {
        return view('pacients.create');
    }

    public function store(Request $request)
    {
        $authUser = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'birth_date' => 'nullable|date',
            'document' => 'nullable|string|max:14',
            'rg' => 'nullable|string|max:20',
            'gender' => 'nullable|string|max:20',
            'cep' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:10',
            'district' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'social_name' => 'nullable|boolean',
            'social_name_text' => 'nullable|string|max:120',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        User::create(array_merge(
            $validator->validated(),
            [
                'tenant_id' => $authUser->tenant_id ?? 1,
                'role' => 'client',
                'password' => Hash::make($request->password),
                'active' => true,
            ]
        ));

        return redirect()
            ->route('pacients.index')
            ->with('success', '✅ Paciente cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $authUser = Auth::user();

        $pacient = User::query()
            ->where('role', 'client')
            ->when($authUser->tenant_id, fn($q) => $q->where('tenant_id', $authUser->tenant_id))
            ->findOrFail($id);

        return view('pacients.edit', compact('pacient'));
    }

    public function update(Request $request, $id)
    {
        $authUser = Auth::user();

        $pacient = User::query()
            ->where('role', 'client')
            ->when($authUser->tenant_id, fn($q) => $q->where('tenant_id', $authUser->tenant_id))
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email,' . $pacient->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|confirmed',
            'birth_date' => 'nullable|date',
            'document' => 'nullable|string|max:14',
            'rg' => 'nullable|string|max:20',
            'gender' => 'nullable|string|max:20',
            'cep' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:10',
            'district' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'social_name' => 'nullable|boolean',
            'social_name_text' => 'nullable|string|max:120',
            'active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $pacient->update($data);

        return redirect()
            ->route('pacients.index')
            ->with('success', 'Paciente atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $authUser = Auth::user();

        $pacient = User::query()
            ->where('role', 'client')
            ->when($authUser->tenant_id, fn($q) => $q->where('tenant_id', $authUser->tenant_id))
            ->findOrFail($id);

        $pacient->delete();

        return redirect()
            ->route('pacients.index')
            ->with('success', '🗑️ Paciente excluído com sucesso.');
    }
}
