<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class TenantController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!in_array($user->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        return response()->json(Tenant::with('owner')->paginate(20));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'cnpj' => 'nullable|string|max:18',
            'im' => 'nullable|string|max:30',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'primary_color' => 'nullable|string|max:10',
            'secondary_color' => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $authUser = Auth::user();
        $logoUrl = null;

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('tenants/logos', 'public');
            $logoUrl = $path;
        }

        $tenant = Tenant::create([
            'name' => $request->name,
            'cnpj' => $request->cnpj,
            'im' => $request->im,
            'owner_user_id' => $authUser->id,
            'logo_url' => $logoUrl,
            'primary_color' => $request->primary_color ?? '#004d40',
            'secondary_color' => $request->secondary_color ?? '#009688',
            'settings' => $request->settings ?? [],
        ]);

        return response()->json([
            'message' => 'Tenant criado com sucesso.',
            'data' => $tenant
        ], 201);
    }

    public function show($id)
    {
        $user = Auth::user();
        $tenant = Tenant::with('owner')->find($id);

        if (!$tenant) return response()->json(['message' => 'Tenant não encontrado.'], 404);
        if ($user->tenant_id !== $tenant->id && $user->role !== 'admin') {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        return response()->json($tenant);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $tenant = Tenant::find($id);

        if (!$tenant) return response()->json(['message' => 'Tenant não encontrado.'], 404);
        if ($user->tenant_id !== $tenant->id && $user->role !== 'admin') {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:120',
            'cnpj' => 'nullable|string|max:18',
            'im' => 'nullable|string|max:30',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'primary_color' => 'nullable|string|max:10',
            'secondary_color' => 'nullable|string|max:10',
            'settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->hasFile('logo')) {
            if ($tenant->logo_url) {
                Storage::disk('public')->delete($tenant->logo_url);
            }
            $path = $request->file('logo')->store('tenants/logos', 'public');
            $tenant->logo_url = $path;
        }

        $tenant->fill($request->only([
            'name', 'cnpj', 'im', 'primary_color', 'secondary_color', 'settings'
        ]))->save();

        return response()->json([
            'message' => 'Tenant atualizado com sucesso.',
            'data' => $tenant
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $tenant = Tenant::find($id);

        if (!$tenant) return response()->json(['message' => 'Tenant não encontrado.'], 404);
        if (!in_array($user->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        // 🔧 Remove logo
        if ($tenant->logo_url) {
            Storage::disk('public')->delete($tenant->logo_url);
        }

        $tenant->delete();

        return response()->json(['message' => 'Tenant excluído com sucesso.']);
    }
}
