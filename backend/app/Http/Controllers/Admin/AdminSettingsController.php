<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminSettingsController extends Controller
{
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;

        $settings = SiteSetting::firstOrCreate(
            ['tenant_id' => $tenantId],
            [
                'site_title'    => '',
                'tagline'       => '',
                'about_title'   => '',
                'about_text'    => '',
                'contact_phone' => '',
                'contact_email' => '',
                'address'       => '',
                'active'        => true,
            ]
        );

        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $validator = Validator::make($request->all(), [
            'site_title'    => 'nullable|string|max:120',
            'tagline'       => 'nullable|string|max:255',
            'about_title'   => 'nullable|string|max:120',
            'about_text'    => 'nullable|string',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:120',
            'address'       => 'nullable|string|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'facebook_url'  => 'nullable|url|max:255',
            'whatsapp_url'  => 'nullable|url|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $settings = SiteSetting::where('tenant_id', $tenantId)->firstOrFail();

        $settings->update($request->only([
            'site_title', 'tagline', 'about_title', 'about_text',
            'contact_phone', 'contact_email', 'address',
            'instagram_url', 'facebook_url', 'whatsapp_url',
        ]));

        return redirect()->route('admin.settings')
            ->with('success', 'Configuracoes atualizadas com sucesso.');
    }
}
