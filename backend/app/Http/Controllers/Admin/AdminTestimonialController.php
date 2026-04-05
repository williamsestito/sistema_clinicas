<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminTestimonialController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $testimonials = Testimonial::where('tenant_id', $tenantId)
            ->when($request->search, function ($q) use ($request) {
                $q->where('client_name', 'like', "%{$request->search}%");
            })
            ->when($request->filled('visible'), function ($q) use ($request) {
                $q->where('visible', $request->visible);
            })
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function create()
    {
        return view('admin.testimonials.create');
    }

    public function store(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $validator = Validator::make($request->all(), [
            'client_name' => 'required|string|max:120',
            'rating'      => 'required|integer|min:1|max:5',
            'comment'     => 'required|string',
            'photo_url'   => 'nullable|url|max:255',
            'visible'     => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Testimonial::create([
            'tenant_id'   => $tenantId,
            'client_name' => $request->client_name,
            'rating'      => $request->rating,
            'comment'     => $request->comment,
            'photo_url'   => $request->photo_url,
            'visible'     => $request->visible ?? true,
        ]);

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Depoimento cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $tenantId = Auth::user()->tenant_id;
        $testimonial = Testimonial::where('tenant_id', $tenantId)->findOrFail($id);

        return view('admin.testimonials.edit', compact('testimonial'));
    }

    public function update(Request $request, $id)
    {
        $tenantId = Auth::user()->tenant_id;
        $testimonial = Testimonial::where('tenant_id', $tenantId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'client_name' => 'required|string|max:120',
            'rating'      => 'required|integer|min:1|max:5',
            'comment'     => 'required|string',
            'photo_url'   => 'nullable|url|max:255',
            'visible'     => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $testimonial->update([
            'client_name' => $request->client_name,
            'rating'      => $request->rating,
            'comment'     => $request->comment,
            'photo_url'   => $request->photo_url,
            'visible'     => $request->visible ?? false,
        ]);

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Depoimento atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $tenantId = Auth::user()->tenant_id;
        Testimonial::where('tenant_id', $tenantId)->findOrFail($id)->delete();

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Depoimento excluido com sucesso.');
    }
}
