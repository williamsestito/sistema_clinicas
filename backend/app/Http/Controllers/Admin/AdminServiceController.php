<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Professional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminServiceController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $servicos = Service::where('tenant_id', $tenantId)
            ->with('professional.user')
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
            )
            ->when($request->filled('active'), fn($q) => $q->where('active', $request->active))
            ->when($request->filled('professional_id'), fn($q) => $q->where('professional_id', $request->professional_id))
            ->orderBy('name')
            ->paginate(15);

        $profissionais = Professional::where('tenant_id', $tenantId)
            ->where('active', true)
            ->with('user')
            ->get();

        return view('admin.services.index', compact('servicos', 'profissionais'));
    }

    public function create()
    {
        $tenantId = Auth::user()->tenant_id;

        $profissionais = Professional::where('tenant_id', $tenantId)
            ->where('active', true)
            ->with('user')
            ->get();

        return view('admin.services.create', compact('profissionais'));
    }

    public function store(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $validator = Validator::make($request->all(), [
            'professional_id' => 'required|exists:professionals,id',
            'name'            => 'required|string|max:160',
            'description'     => 'nullable|string|max:1000',
            'duration_min'    => 'required|integer|min:5|max:480',
            'price'           => 'required|numeric|min:0',
            'active'          => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Verify professional belongs to tenant
        $professional = Professional::where('tenant_id', $tenantId)
            ->where('id', $request->professional_id)
            ->firstOrFail();

        Service::create([
            'tenant_id'       => $tenantId,
            'professional_id' => $professional->id,
            'name'            => $request->name,
            'description'     => $request->description,
            'duration_min'    => $request->duration_min,
            'price'           => $request->price,
            'active'          => $request->active ?? true,
        ]);

        return redirect()->route('admin.services.index')
            ->with('success', 'Serviço cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $tenantId = Auth::user()->tenant_id;

        $servico = Service::where('tenant_id', $tenantId)
            ->with('professional.user')
            ->findOrFail($id);

        $profissionais = Professional::where('tenant_id', $tenantId)
            ->where('active', true)
            ->with('user')
            ->get();

        return view('admin.services.edit', compact('servico', 'profissionais'));
    }

    public function update(Request $request, $id)
    {
        $tenantId = Auth::user()->tenant_id;
        $servico = Service::where('tenant_id', $tenantId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'professional_id' => 'required|exists:professionals,id',
            'name'            => 'required|string|max:160',
            'description'     => 'nullable|string|max:1000',
            'duration_min'    => 'required|integer|min:5|max:480',
            'price'           => 'required|numeric|min:0',
            'active'          => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Verify professional belongs to tenant
        Professional::where('tenant_id', $tenantId)
            ->where('id', $request->professional_id)
            ->firstOrFail();

        $servico->update([
            'professional_id' => $request->professional_id,
            'name'            => $request->name,
            'description'     => $request->description,
            'duration_min'    => $request->duration_min,
            'price'           => $request->price,
            'active'          => $request->active ?? true,
        ]);

        return redirect()->route('admin.services.index')
            ->with('success', 'Serviço atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $tenantId = Auth::user()->tenant_id;
        $servico = Service::where('tenant_id', $tenantId)->findOrFail($id);

        $servico->delete();

        return redirect()->route('admin.services.index')
            ->with('success', 'Serviço excluído com sucesso.');
    }
}
