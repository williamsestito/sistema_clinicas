<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminSectionController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $sections = Section::where('tenant_id', $tenantId)
            ->when($request->search, function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('slug', 'like', "%{$request->search}%");
            })
            ->when($request->filled('active'), function ($q) use ($request) {
                $q->where('active', $request->active);
            })
            ->orderBy('position')
            ->paginate(15);

        return view('admin.sections.index', compact('sections'));
    }

    public function create()
    {
        return view('admin.sections.create');
    }

    public function store(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $validator = Validator::make($request->all(), [
            'title'     => 'required|string|max:120',
            'slug'      => 'nullable|string|max:60',
            'content'   => 'required|string',
            'image_url' => 'nullable|url|max:255',
            'position'  => 'required|integer|min:0',
            'active'    => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $slug = $request->slug ?: Str::slug($request->title);

        Section::create([
            'tenant_id' => $tenantId,
            'title'     => $request->title,
            'slug'      => $slug,
            'content'   => $request->content,
            'image_url' => $request->image_url,
            'position'  => $request->position,
            'active'    => $request->active ?? true,
        ]);

        return redirect()->route('admin.sections.index')
            ->with('success', 'Secao cadastrada com sucesso.');
    }

    public function edit($id)
    {
        $tenantId = Auth::user()->tenant_id;
        $section = Section::where('tenant_id', $tenantId)->findOrFail($id);

        return view('admin.sections.edit', compact('section'));
    }

    public function update(Request $request, $id)
    {
        $tenantId = Auth::user()->tenant_id;
        $section = Section::where('tenant_id', $tenantId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title'     => 'required|string|max:120',
            'slug'      => 'nullable|string|max:60',
            'content'   => 'required|string',
            'image_url' => 'nullable|url|max:255',
            'position'  => 'required|integer|min:0',
            'active'    => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $slug = $request->slug ?: Str::slug($request->title);

        $section->update([
            'title'     => $request->title,
            'slug'      => $slug,
            'content'   => $request->content,
            'image_url' => $request->image_url,
            'position'  => $request->position,
            'active'    => $request->active ?? false,
        ]);

        return redirect()->route('admin.sections.index')
            ->with('success', 'Secao atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $tenantId = Auth::user()->tenant_id;
        Section::where('tenant_id', $tenantId)->findOrFail($id)->delete();

        return redirect()->route('admin.sections.index')
            ->with('success', 'Secao excluida com sucesso.');
    }
}
