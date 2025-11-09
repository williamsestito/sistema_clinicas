<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Professional;

class ProfessionalProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $professional = Professional::where('user_id', $user->id)->first();

        if (!$professional) {
            $professional = Professional::create([
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
                'active' => true,
            ]);
        }

        return view('professional.profile', compact('user', 'professional'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $professional = Professional::where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'specialty' => 'nullable|string|max:120',
            'bio' => 'nullable|string',
            'show_prices' => 'nullable|boolean',
            'default_start_hour' => 'nullable|date_format:H:i',
            'default_end_hour' => 'nullable|date_format:H:i',
            'default_consultation_time' => 'nullable|integer|min:10|max:240',
            'photo_url' => 'nullable|string|max:255',
        ]);

        $professional->update($validated);

        return back()->with('success', '✅ Dados profissionais atualizados com sucesso!');
    }
}
