<?php

namespace App\Http\Controllers;

use App\Models\Professional;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProfessionalController extends Controller
{

    public function index(Request $request)
    {
        $authUser = Auth::user();

        $query = Professional::with('user')
            ->where('tenant_id', $authUser->tenant_id)
            ->when($request->specialty, fn($q) => $q->where('specialty', 'like', "%{$request->specialty}%"))
            ->when($request->active !== null, fn($q) => $q->where('active', $request->active))
            ->orderBy('id', 'desc');

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $authUser = Auth::user();

        if (!in_array($authUser->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Acesso negado. Somente administradores podem criar profissionais.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'name' => 'required_without:user_id|string|max:120',
            'email' => 'required_without:user_id|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required_without:user_id|string|min:6|confirmed',
            'specialty' => 'required|string|max:120',
            'bio' => 'nullable|string|max:2000',
            'photo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userId = $request->user_id;

        if (!$userId) {
            $user = User::create([
                'tenant_id' => $authUser->tenant_id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => bcrypt($request->password),
                'role' => 'professional',
                'active' => true,
            ]);
            $userId = $user->id;
        }

        $photoUrl = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('professionals/photos', 'public');
            $photoUrl = Storage::url($path);
        }

        $professional = Professional::create([
            'tenant_id' => $authUser->tenant_id,
            'user_id' => $userId,
            'specialty' => $request->specialty,
            'bio' => $request->bio,
            'photo_url' => $photoUrl,
            'active' => true,
        ]);

        return response()->json([
            'message' => 'Profissional cadastrado com sucesso.',
            'data' => $professional->load('user')
        ], 201);
    }

    public function show($id)
    {
        $authUser = Auth::user();

        $professional = Professional::with('user')
            ->where('tenant_id', $authUser->tenant_id)
            ->find($id);

        if (!$professional) {
            return response()->json(['message' => 'Profissional não encontrado.'], 404);
        }

        return response()->json($professional);
    }

    public function update(Request $request, $id)
    {
        $authUser = Auth::user();

        $professional = Professional::where('tenant_id', $authUser->tenant_id)->find($id);

        if (!$professional) {
            return response()->json(['message' => 'Profissional não encontrado.'], 404);
        }

        if (!in_array($authUser->role, ['owner', 'admin']) && $authUser->id !== $professional->user_id) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'specialty' => 'nullable|string|max:120',
            'bio' => 'nullable|string|max:2000',
            'photo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->hasFile('photo')) {
            if ($professional->photo_url) {
                $oldPath = str_replace('/storage/', '', $professional->photo_url);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('photo')->store('professionals/photos', 'public');
            $professional->photo_url = Storage::url($path);
        }

        $professional->update($request->only(['specialty', 'bio', 'active']));

        return response()->json([
            'message' => 'Profissional atualizado com sucesso.',
            'data' => $professional->load('user')
        ]);
    }

    public function deactivate($id)
    {
        $authUser = Auth::user();

        if (!in_array($authUser->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $professional = Professional::where('tenant_id', $authUser->tenant_id)->find($id);

        if (!$professional) {
            return response()->json(['message' => 'Profissional não encontrado.'], 404);
        }

        $professional->active = false;
        $professional->save();

        return response()->json(['message' => 'Profissional inativado com sucesso.']);
    }

    public function destroy($id)
    {
        $authUser = Auth::user();

        if (!in_array($authUser->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $professional = Professional::where('tenant_id', $authUser->tenant_id)->find($id);

        if (!$professional) {
            return response()->json(['message' => 'Profissional não encontrado.'], 404);
        }

        $professional->delete();

        return response()->json(['message' => 'Profissional excluído com sucesso.']);
    }
}
