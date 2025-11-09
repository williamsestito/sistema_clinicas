<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BlockedDate;
use Illuminate\Support\Facades\Auth;

class ProfessionalBlockedController extends Controller
{
    public function index()
    {
        $professional = Auth::user()->professional;
        $blockedDates = BlockedDate::where('professional_id', $professional->id)
            ->orderBy('date', 'asc')
            ->get();

        return view('professional.blocked', compact('blockedDates'));
    }

    public function store(Request $request)
    {
        $professional = Auth::user()->professional;

        $validated = $request->validate([
            'date' => 'required|date',
            'reason' => 'nullable|string|max:255',
        ]);

        BlockedDate::updateOrCreate(
            ['professional_id' => $professional->id, 'date' => $validated['date']],
            ['reason' => $validated['reason']]
        );

        return back()->with('success', '📅 Dia bloqueado com sucesso!');
    }

    public function destroy($id)
    {
        $blocked = BlockedDate::findOrFail($id);
        $blocked->delete();

        return back()->with('success', '🚫 Bloqueio removido com sucesso!');
    }
}
