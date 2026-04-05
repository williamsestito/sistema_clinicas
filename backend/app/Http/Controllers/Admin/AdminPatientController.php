<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminPatientController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $pacientes = Client::ofTenant($tenantId)
            ->search($request->search)
            ->when($request->filled('active'), fn($q) => $q->where('active', $request->active))
            ->when($request->filled('city'), fn($q) => $q->where('city', $request->city))
            ->ordered()
            ->paginate(15);

        return view('admin.patients.index', compact('pacientes'));
    }

    public function create()
    {
        return view('admin.patients.create');
    }

    public function store(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $validator = Validator::make($request->all(), [
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|unique:clients,email',
            'phone'             => 'nullable|string|max:20',
            'document'          => 'nullable|string|max:14',
            'rg'                => 'nullable|string|max:20',
            'birthdate'         => 'nullable|date',
            'gender'            => 'nullable|string|max:20',
            'civil_status'      => 'nullable|string|max:20',
            'use_social_name'   => 'nullable|boolean',
            'social_name'       => 'nullable|string|max:255',
            'cep'               => 'nullable|string|max:12',
            'address'           => 'nullable|string|max:255',
            'number'            => 'nullable|string|max:20',
            'complement'        => 'nullable|string|max:255',
            'district'          => 'nullable|string|max:255',
            'city'              => 'nullable|string|max:255',
            'state'             => 'nullable|string|max:2',
            'consent_marketing' => 'nullable|boolean',
            'notes'             => 'nullable|string|max:1000',
            'password'          => 'nullable|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $data['tenant_id'] = $tenantId;
        $data['active'] = true;

        Client::create($data);

        return redirect()->route('admin.patients.index')
            ->with('success', 'Paciente cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $tenantId = Auth::user()->tenant_id;
        $paciente = Client::ofTenant($tenantId)->findOrFail($id);

        return view('admin.patients.edit', compact('paciente'));
    }

    public function update(Request $request, $id)
    {
        $tenantId = Auth::user()->tenant_id;
        $paciente = Client::ofTenant($tenantId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|unique:clients,email,' . $paciente->id,
            'phone'             => 'nullable|string|max:20',
            'document'          => 'nullable|string|max:14',
            'rg'                => 'nullable|string|max:20',
            'birthdate'         => 'nullable|date',
            'gender'            => 'nullable|string|max:20',
            'civil_status'      => 'nullable|string|max:20',
            'use_social_name'   => 'nullable|boolean',
            'social_name'       => 'nullable|string|max:255',
            'cep'               => 'nullable|string|max:12',
            'address'           => 'nullable|string|max:255',
            'number'            => 'nullable|string|max:20',
            'complement'        => 'nullable|string|max:255',
            'district'          => 'nullable|string|max:255',
            'city'              => 'nullable|string|max:255',
            'state'             => 'nullable|string|max:2',
            'consent_marketing' => 'nullable|boolean',
            'notes'             => 'nullable|string|max:1000',
            'active'            => 'nullable|boolean',
            'password'          => 'nullable|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $paciente->update($data);

        return redirect()->route('admin.patients.index')
            ->with('success', 'Paciente atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $tenantId = Auth::user()->tenant_id;
        $paciente = Client::ofTenant($tenantId)->findOrFail($id);

        $paciente->delete();

        return redirect()->route('admin.patients.index')
            ->with('success', 'Paciente excluído com sucesso.');
    }

    /**
     * JSON autocomplete – retorna até 10 pacientes que batem com o termo.
     */
    public function searchJson(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $term     = $request->input('q', '');

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $results = Client::ofTenant($tenantId)
            ->search($term)
            ->select('id', 'name', 'phone', 'email')
            ->ordered()
            ->limit(10)
            ->get();

        return response()->json($results);
    }
}
