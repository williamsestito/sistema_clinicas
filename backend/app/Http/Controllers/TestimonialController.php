<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $testimonials = Testimonial::where('tenant_id', $user->tenant_id)
            ->when($request->visible !== null, fn($q) => 
                $q->where('visible', filter_var($request->visible, FILTER_VALIDATE_BOOLEAN))
            )
            ->orderByDesc('id')
            ->get();

        return response()->json($testimonials);
    }
    public function store(Request $request)
    {
        $authUser = Auth::user();

        if (!in_array($authUser->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'client_name' => 'required|string|max:120',
            'rating' => 'integer|min:1|max:5',
            'comment' => 'nullable|string',
            'photo' => 'nullable|file|image|max:2048',
            'photo_url' => 'nullable|string|max:255',
            'visible' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $photoPath = $request->hasFile('photo')
            ? $request->file('photo')->store("testimonials/{$authUser->tenant_id}", 'public')
            : $request->photo_url;

        $testimonial = Testimonial::create([
            'tenant_id' => $authUser->tenant_id,
            'client_name' => $request->client_name,
            'rating' => $request->rating ?? 5,
            'comment' => $request->comment,
            'photo_url' => $photoPath,
            'visible' => $request->visible ?? true,
        ]);

        return response()->json([
            'message' => '✅ Depoimento criado com sucesso.',
            'data' => $testimonial
        ], 201);
    }

    public function show($id)
    {
        $user = Auth::user();

        $testimonial = Testimonial::where('tenant_id', $user->tenant_id)->find($id);

        if (!$testimonial) {
            return response()->json(['message' => 'Depoimento não encontrado.'], 404);
        }

        return response()->json($testimonial);
    }

    public function update(Request $request, $id)
    {
        $authUser = Auth::user();

        $testimonial = Testimonial::where('tenant_id', $authUser->tenant_id)->find($id);
        if (!$testimonial) {
            return response()->json(['message' => 'Depoimento não encontrado.'], 404);
        }


        if (!in_array($authUser->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'client_name' => 'nullable|string|max:120',
            'rating' => 'integer|min:1|max:5',
            'comment' => 'nullable|string',
            'photo' => 'nullable|file|image|max:2048',
            'photo_url' => 'nullable|string|max:255',
            'visible' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        if ($request->hasFile('photo')) {
            if ($testimonial->photo_url && Storage::disk('public')->exists($testimonial->photo_url)) {
                Storage::disk('public')->delete($testimonial->photo_url);
            }

            $testimonial->photo_url = $request->file('photo')->store(
                "testimonials/{$authUser->tenant_id}", 
                'public'
            );
        } elseif ($request->photo_url) {
            $testimonial->photo_url = $request->photo_url;
        }

        $testimonial->update($request->except(['photo', 'tenant_id']));

        return response()->json([
            'message' => '✏️ Depoimento atualizado com sucesso.',
            'data' => $testimonial
        ]);
    }

    public function destroy($id)
    {
        $authUser = Auth::user();

        $testimonial = Testimonial::where('tenant_id', $authUser->tenant_id)->find($id);
        if (!$testimonial) {
            return response()->json(['message' => 'Depoimento não encontrado.'], 404);
        }

        if (!in_array($authUser->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        if ($testimonial->photo_url && Storage::disk('public')->exists($testimonial->photo_url)) {
            Storage::disk('public')->delete($testimonial->photo_url);
        }

        $testimonial->delete();

        return response()->json(['message' => '🗑️ Depoimento removido com sucesso.']);
    }

    public function publicTestimonials($tenantId)
    {
        $testimonials = Testimonial::where('tenant_id', $tenantId)
            ->where('visible', true)
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'tenant_id' => $tenantId,
            'count' => $testimonials->count(),
            'testimonials' => $testimonials
        ]);
    }
}
