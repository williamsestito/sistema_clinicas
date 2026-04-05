<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminClinicController extends Controller
{
    public function index()
    {
        $tenant = Auth::user()->tenant;
        return view('admin.clinic', compact('tenant'));
    }

    public function update(Request $request)
    {
        $tenant = Auth::user()->tenant;

        $validator = Validator::make($request->all(), [
            'name'            => 'required|string|max:120',
            'cnpj'            => 'nullable|string|max:18',
            'im'              => 'nullable|string|max:30',
            'email'           => 'nullable|email|max:120',
            'phone'           => 'nullable|string|max:20',
            'logo'            => 'nullable|image|mimes:jpeg,jpg,png,webp,svg|max:2048',
            'primary_color'   => 'nullable|string|max:10',
            'secondary_color' => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->only([
            'name', 'cnpj', 'im', 'email', 'phone',
            'primary_color', 'secondary_color',
        ]);

        if ($request->hasFile('logo')) {
            // Remove logo antiga
            if ($tenant->logo_url && Storage::disk('public')->exists($tenant->logo_url)) {
                Storage::disk('public')->delete($tenant->logo_url);
            }
            $data['logo_url'] = $request->file('logo')->store("tenants/{$tenant->id}", 'public');
        }

        if ($request->has('remove_logo') && $request->remove_logo) {
            if ($tenant->logo_url && Storage::disk('public')->exists($tenant->logo_url)) {
                Storage::disk('public')->delete($tenant->logo_url);
            }
            $data['logo_url'] = null;
        }

        $tenant->update($data);

        return redirect()->route('admin.clinic')
            ->with('success', 'Dados da clínica atualizados com sucesso.');
    }
}
