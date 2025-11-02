<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SiteSettingController extends Controller
{

    public function index()
    {
        $user = Auth::user();

        $settings = SiteSetting::where('tenant_id', $user->tenant_id)->first();

        if (!$settings) {
            return response()->json(['message' => 'Configurações do site não encontradas.'], 404);
        }

        return response()->json($settings);
    }

    public function storeOrUpdate(Request $request)
    {
        $authUser = Auth::user();

        if (!in_array($authUser->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'site_title' => 'nullable|string|max:120',
            'tagline' => 'nullable|string|max:255',
            'about_title' => 'nullable|string|max:120',
            'about_text' => 'nullable|string',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:120',
            'address' => 'nullable|string|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'whatsapp_url' => 'nullable|url|max:255',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $settings = SiteSetting::updateOrCreate(
            ['tenant_id' => $authUser->tenant_id],
            $request->only([
                'site_title', 'tagline', 'about_title', 'about_text',
                'contact_phone', 'contact_email', 'address',
                'instagram_url', 'facebook_url', 'whatsapp_url', 'active'
            ])
        );

        return response()->json([
            'message' => 'Configurações atualizadas com sucesso.',
            'data' => $settings
        ]);
    }

    public function publicShow($tenantId)
    {
        $settings = SiteSetting::where('tenant_id', $tenantId)
            ->where('active', true)
            ->first();

        if (!$settings) {
            return response()->json(['message' => 'Configurações públicas não encontradas.'], 404);
        }

        return response()->json($settings);
    }

    public function deactivate()
    {
        $authUser = Auth::user();

        if (!in_array($authUser->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $settings = SiteSetting::where('tenant_id', $authUser->tenant_id)->first();

        if (!$settings) {
            return response()->json(['message' => 'Configurações não encontradas.'], 404);
        }

        $settings->update(['active' => false]);

        return response()->json(['message' => 'Site desativado temporariamente.']);
    }

    public function activate()
    {
        $authUser = Auth::user();

        if (!in_array($authUser->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $settings = SiteSetting::where('tenant_id', $authUser->tenant_id)->first();

        if (!$settings) {
            return response()->json(['message' => 'Configurações não encontradas.'], 404);
        }

        $settings->update(['active' => true]);

        return response()->json(['message' => 'Site reativado com sucesso.']);
    }
}
