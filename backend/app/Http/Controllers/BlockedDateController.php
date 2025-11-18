<?php

namespace App\Http\Controllers;

use App\Models\BlockedDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlockedDateController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $professional = $user->professional;

        $blocked = BlockedDate::where('tenant_id', $user->tenant_id)
            ->where('professional_id', $professional->id)
            ->orderBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $blocked
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $professional = $user->professional;

        $validated = $request->validate([
            'date' => 'required|date',
            'reason' => 'nullable|string|max:255'
        ]);

        $exists = BlockedDate::where('tenant_id', $user->tenant_id)
            ->where('professional_id', $professional->id)
            ->where('date', $validated['date'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Data já bloqueada.'
            ], 409);
        }

        $blocked = BlockedDate::create([
            'tenant_id' => $user->tenant_id,
            'professional_id' => $professional->id,
            'date' => $validated['date'],
            'reason' => $validated['reason'] ?? null
        ]);

        return response()->json([
            'success' => true,
            'data' => $blocked
        ], 201);
    }

    public function destroy($id)
    {
        $user = Auth::user();

        $blocked = BlockedDate::where('tenant_id', $user->tenant_id)
            ->where('professional_id', $user->professional->id)
            ->where('id', $id)
            ->firstOrFail();

        $blocked->delete();

        return response()->json([
            'success' => true
        ]);
    }
}
