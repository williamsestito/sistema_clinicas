<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Professional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $authUser = Auth::user();

        $query = Service::with('professional.user')
            ->where('tenant_id', $authUser->tenant_id)
            ->when($request->name, fn($q) => $q->where('name', 'like', "%{$request->name}%"))
            ->when($request->active !== null, fn($q) => $q->where('active', $request->active))
            ->when($request->professional_id, fn($q) => $q->where('professional_id', $request->professional_id))
            ->orderBy('name');

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $authUser = Auth::user();

        if (!in_array($authUser->role, ['owner', 'admin', 'professional'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:160',
            'description' => 'nullable|string|max:2000',
            'duration_min' => 'required|integer|min:5|max:480',
            'price' => 'required|numeric|min:0',
            'professional_id' => 'required|exists:professionals,id',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $professional = Professional::where('tenant_id', $authUser->tenant_id)
            ->find($request->professional_id);

        if (!$professional) {
            return response()->json(['message' => 'Profissional não encontrado ou não pertence a este tenant.'], 404);
        }

        $service = Service::create([
            'tenant_id' => $authUser->tenant_id,
            'professional_id' => $professional->id,
            'name' => $request->name,
            'description' => $request->description,
            'duration_min' => $request->duration_min,
            'price' => $request->price,
            'active' => $request->active ?? true,
        ]);

        return response()->json([
            'message' => 'Serviço cadastrado com sucesso.',
            'data' => $service->load('professional.user')
        ], 201);
    }

    public function show($id)
    {
        $authUser = Auth::user();

        $service = Service::with('professional.user')
            ->where('tenant_id', $authUser->tenant_id)
            ->find($id);

        if (!$service) {
            return response()->json(['message' => 'Serviço não encontrado.'], 404);
        }

        return response()->json($service);
    }

    public function update(Request $request, $id)
    {
        $authUser = Auth::user();

        $service = Service::where('tenant_id', $authUser->tenant_id)->find($id);

        if (!$service) {
            return response()->json(['message' => 'Serviço não encontrado.'], 404);
        }

        if (!in_array($authUser->role, ['owner', 'admin', 'professional'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:160',
            'description' => 'nullable|string|max:2000',
            'duration_min' => 'nullable|integer|min:5|max:480',
            'price' => 'nullable|numeric|min:0',
            'professional_id' => 'nullable|exists:professionals,id',
            'active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->professional_id) {
            $professional = Professional::where('tenant_id', $authUser->tenant_id)
                ->find($request->professional_id);

            if (!$professional) {
                return response()->json(['message' => 'Profissional não pertence a este tenant.'], 404);
            }

            $service->professional_id = $professional->id;
        }

        $service->update($request->only([
            'name', 'description', 'duration_min', 'price', 'active'
        ]));

        return response()->json([
            'message' => 'Serviço atualizado com sucesso.',
            'data' => $service->load('professional.user')
        ]);
    }

    public function deactivate($id)
    {
        $authUser = Auth::user();

        if (!in_array($authUser->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $service = Service::where('tenant_id', $authUser->tenant_id)->find($id);

        if (!$service) {
            return response()->json(['message' => 'Serviço não encontrado.'], 404);
        }

        $service->active = false;
        $service->save();

        return response()->json(['message' => 'Serviço inativado com sucesso.']);
    }

    public function destroy($id)
    {
        $authUser = Auth::user();

        if (!in_array($authUser->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $service = Service::where('tenant_id', $authUser->tenant_id)->find($id);

        if (!$service) {
            return response()->json(['message' => 'Serviço não encontrado.'], 404);
        }

        $service->delete();

        return response()->json(['message' => 'Serviço excluído com sucesso.']);
    }
}
