<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();

        $banners = Banner::where('tenant_id', $user->tenant_id)
            ->when($request->active !== null, fn($q) => 
                $q->where('active', filter_var($request->active, FILTER_VALIDATE_BOOLEAN))
            )
            ->orderBy('position')
            ->get();

        return response()->json($banners);
    }

    public function store(Request $request)
    {
        $authUser = Auth::user();

        if (!in_array($authUser->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:120',
            'subtitle' => 'nullable|string|max:255',
            'image' => 'nullable|file|image|max:2048',
            'image_url' => 'nullable|string|max:255',
            'link_url' => 'nullable|string|max:255',
            'position' => 'integer|min:0',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store("banners/{$authUser->tenant_id}", 'public')
            : $request->image_url;

        $banner = Banner::create([
            'tenant_id' => $authUser->tenant_id,
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'image_url' => $imagePath,
            'link_url' => $request->link_url,
            'position' => $request->position ?? 0,
            'active' => $request->active ?? true,
        ]);

        return response()->json([
            'message' => '✅ Banner criado com sucesso.',
            'data' => $banner
        ], 201);
    }

    public function show($id)
    {
        $user = Auth::user();

        $banner = Banner::where('tenant_id', $user->tenant_id)->find($id);

        if (!$banner) {
            return response()->json(['message' => 'Banner não encontrado.'], 404);
        }

        return response()->json($banner);
    }

    public function update(Request $request, $id)
    {
        $authUser = Auth::user();

        $banner = Banner::where('tenant_id', $authUser->tenant_id)->find($id);
        if (!$banner) {
            return response()->json(['message' => 'Banner não encontrado.'], 404);
        }

        // 🔐 Permite apenas owner/admin editar
        if (!in_array($authUser->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:120',
            'subtitle' => 'nullable|string|max:255',
            'image' => 'nullable|file|image|max:2048',
            'image_url' => 'nullable|string|max:255',
            'link_url' => 'nullable|string|max:255',
            'position' => 'integer|min:0',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->hasFile('image')) {
            if ($banner->image_url && Storage::disk('public')->exists($banner->image_url)) {
                Storage::disk('public')->delete($banner->image_url);
            }

            $banner->image_url = $request->file('image')->store(
                "banners/{$authUser->tenant_id}", 
                'public'
            );
        } elseif ($request->image_url) {
            $banner->image_url = $request->image_url;
        }

        $banner->update($request->except(['image', 'tenant_id']));

        return response()->json([
            'message' => '✏️ Banner atualizado com sucesso.',
            'data' => $banner
        ]);
    }

    public function destroy($id)
    {
        $authUser = Auth::user();

        $banner = Banner::where('tenant_id', $authUser->tenant_id)->find($id);
        if (!$banner) {
            return response()->json(['message' => 'Banner não encontrado.'], 404);
        }

        // 🔐 Permite apenas owner/admin excluir
        if (!in_array($authUser->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        if ($banner->image_url && Storage::disk('public')->exists($banner->image_url)) {
            Storage::disk('public')->delete($banner->image_url);
        }

        $banner->delete();

        return response()->json(['message' => '🗑️ Banner removido com sucesso.']);
    }

    public function publicBanners($tenantId)
    {
        $banners = Banner::where('tenant_id', $tenantId)
            ->where('active', true)
            ->orderBy('position')
            ->get();

        return response()->json([
            'tenant_id' => $tenantId,
            'count' => $banners->count(),
            'banners' => $banners
        ]);
    }
}
