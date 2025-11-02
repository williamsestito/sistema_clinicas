<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class SectionController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();

        $sections = Section::where('tenant_id', $user->tenant_id)
            ->when(
                $request->active !== null,
                fn($q) => $q->where('active', filter_var($request->active, FILTER_VALIDATE_BOOLEAN))
            )
            ->orderBy('position')
            ->get();

        return response()->json($sections, 200);
    }

    public function store(Request $request)
    {
        $authUser = Auth::user();

        if (!in_array($authUser->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'slug' => 'required|string|max:60|unique:sections,slug,NULL,id,tenant_id,' . $authUser->tenant_id,
            'title' => 'nullable|string|max:120',
            'content' => 'nullable|string',
            'image' => 'nullable|file|image|max:2048',
            'image_url' => 'nullable|string|max:255',
            'position' => 'integer|min:0',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store("sections/{$authUser->tenant_id}", 'public');
        } elseif ($request->image_url) {
            $imagePath = $request->image_url;
        }

        $section = Section::create([
            'tenant_id' => $authUser->tenant_id,
            'slug' => $request->slug,
            'title' => $request->title,
            'content' => $request->content,
            'image_url' => $imagePath,
            'position' => $request->position ?? 0,
            'active' => $request->active ?? true,
        ]);

        return response()->json([
            'message' => 'Seção criada com sucesso.',
            'data' => $section
        ], 201);
    }

    public function show(int $id)
    {
        $user = Auth::user();

        $section = Section::where('tenant_id', $user->tenant_id)->find($id);

        if (!$section) {
            return response()->json(['message' => 'Seção não encontrada.'], 404);
        }

        return response()->json($section, 200);
    }

    public function update(Request $request, int $id)
    {
        $authUser = Auth::user();

        $section = Section::where('tenant_id', $authUser->tenant_id)->find($id);
        if (!$section) {
            return response()->json(['message' => 'Seção não encontrada.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'slug' => 'nullable|string|max:60|unique:sections,slug,' . $id . ',id,tenant_id,' . $authUser->tenant_id,
            'title' => 'nullable|string|max:120',
            'content' => 'nullable|string',
            'image' => 'nullable|file|image|max:2048',
            'image_url' => 'nullable|string|max:255',
            'position' => 'integer|min:0',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 🔹 Atualiza imagem
        if ($request->hasFile('image')) {
            $storage = Storage::disk('public');

            if ($section->image_url && $storage->exists($section->image_url)) {
                $storage->delete($section->image_url);
            }

            $section->image_url = $request->file('image')->store("sections/{$authUser->tenant_id}", 'public');
        } elseif ($request->filled('image_url')) {
            $section->image_url = $request->image_url;
        }

        $section->update($request->except(['image', 'tenant_id']));

        return response()->json([
            'message' => 'Seção atualizada com sucesso.',
            'data' => $section
        ], 200);
    }

    public function destroy(int $id)
    {
        $authUser = Auth::user();

        $section = Section::where('tenant_id', $authUser->tenant_id)->find($id);
        if (!$section) {
            return response()->json(['message' => 'Seção não encontrada.'], 404);
        }

        $storage = Storage::disk('public');
        if ($section->image_url && $storage->exists($section->image_url)) {
            $storage->delete($section->image_url);
        }

        $section->delete();

        return response()->json(['message' => 'Seção excluída com sucesso.'], 200);
    }

    public function publicSections(Request $request, int $tenantId)
    {
        $sections = Section::where('tenant_id', $tenantId)
            ->where('active', true)
            ->when($request->slug, fn($q) => $q->where('slug', $request->slug))
            ->orderBy('position')
            ->get();

        return response()->json($sections, 200);
    }
}
