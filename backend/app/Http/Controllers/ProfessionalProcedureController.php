<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProfessionalProcedure;

class ProfessionalProcedureController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $professional = $user->professional;

        if (!$professional) abort(403, 'Acesso negado.');

        $procedures = ProfessionalProcedure::where('tenant_id', $user->tenant_id)
            ->where('professional_id', $professional->id)
            ->orderBy('active','desc')
            ->orderBy('name')
            ->get();

        return view('professional.procedures', compact('procedures'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $professional = $user->professional;
        if (!$professional) abort(403, 'Acesso negado.');

        $data = $request->validate([
            'name'         => 'required|string|max:120',
            'price'        => 'nullable|numeric|min:0|max:999999.99',
            'duration_min' => 'required|integer|min:5|max:600',
            'description'  => 'nullable|string|max:2000',
            'active'       => 'nullable|boolean',
        ]);

        $data['tenant_id']      = $user->tenant_id;
        $data['professional_id']= $professional->id;
        $data['active']         = (bool)($data['active'] ?? true);
        $data['price']          = $data['price'] ?? 0;

        ProfessionalProcedure::updateOrCreate(
            [
                'tenant_id'       => $data['tenant_id'],
                'professional_id' => $data['professional_id'],
                'name'            => $data['name'],
            ],
            [
                'price'        => $data['price'],
                'duration_min' => $data['duration_min'],
                'description'  => $data['description'] ?? null,
                'active'       => $data['active'],
            ]
        );

        return back()->with('success', 'Procedimento salvo.');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $professional = $user->professional;
        if (!$professional) abort(403, 'Acesso negado.');

        $proc = ProfessionalProcedure::where('id', $id)
            ->where('tenant_id', $user->tenant_id)
            ->where('professional_id', $professional->id)
            ->firstOrFail();

        $proc->delete();

        return back()->with('success', 'Procedimento removido.');
    }
}
