<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PacientController extends Controller
{
    public function index(Request $request)
    {
        $authUser = Auth::user();
        $tenantId = $authUser->tenant_id;

        $pacients = Client::query()
            ->ofTenant($tenantId)
            ->search($request->input('search'))
            ->ordered()
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
            'email' => 'nullable|email|unique:clients,email',
            'phone' => 'nullable|string|max:20',
            'birthdate' => 'nullable|date',
            'consent_marketing' => 'boolean',
            'notes' => 'nullable|string|max:500',
            'active' => 'boolean', // ✅ campo incluído
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Client::create(array_merge(
            $validator->validated(),
            ['tenant_id' => $authUser->tenant_id]
        ));

        return redirect()
            ->route('pacients.index')
            ->with('success', '✅ Paciente cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $authUser = Auth::user();
        $pacient = Client::ofTenant($authUser->tenant_id)->findOrFail($id);

        return view('pacients.edit', compact('pacient'));
    }

    public function update(Request $request, $id)
    {
        $authUser = Auth::user();
        $pacient = Client::ofTenant($authUser->tenant_id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'email' => 'nullable|email|unique:clients,email,' . $pacient->id,
            'phone' => 'nullable|string|max:20',
            'birthdate' => 'nullable|date',
            'consent_marketing' => 'boolean',
            'notes' => 'nullable|string|max:500',
            'active' => 'boolean', // ✅ incluído também no update
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $pacient->update($validator->validated());

        return redirect()
            ->route('pacients.index')
            ->with('success', '✅ Paciente atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $authUser = Auth::user();
        $pacient = Client::ofTenant($authUser->tenant_id)->findOrFail($id);
        $pacient->delete();

        return redirect()
            ->route('pacients.index')
            ->with('success', '🗑️ Paciente excluído com sucesso.');
    }
}
