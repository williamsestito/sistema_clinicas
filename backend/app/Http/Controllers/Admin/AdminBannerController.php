<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminBannerController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $banners = Banner::where('tenant_id', $tenantId)
            ->when($request->search, function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%");
            })
            ->when($request->filled('active'), function ($q) use ($request) {
                $q->where('active', $request->active);
            })
            ->orderBy('position')
            ->paginate(15);

        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $validator = Validator::make($request->all(), [
            'title'    => 'nullable|string|max:120',
            'subtitle' => 'nullable|string|max:255',
            'images'   => 'required|array|min:1',
            'images.*' => 'image|mimes:jpeg,jpg,png,webp,gif,bmp,svg|max:5120',
            'link_url' => 'nullable|url|max:255',
            'position' => 'required|integer|min:0',
            'active'   => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $files    = $request->file('images');
        $position = (int) $request->position;
        $active   = $request->active ?? true;
        $count    = 0;

        foreach ($files as $i => $file) {
            $path = $file->store("banners/{$tenantId}", 'public');

            $title = $request->title;
            if (!$title || $i > 0) {
                $title = ($request->title ?: 'Banner') . ($i > 0 ? ' (' . ($i + 1) . ')' : '');
            }

            Banner::create([
                'tenant_id' => $tenantId,
                'title'     => $title,
                'subtitle'  => $request->subtitle,
                'image_url' => $path,
                'link_url'  => $request->link_url,
                'position'  => $position + $i,
                'active'    => $active,
            ]);

            $count++;
        }

        $msg = $count === 1
            ? 'Banner cadastrado com sucesso.'
            : "{$count} banners cadastrados com sucesso.";

        return redirect()->route('admin.banners.index')->with('success', $msg);
    }

    public function edit($id)
    {
        $tenantId = Auth::user()->tenant_id;
        $banner = Banner::where('tenant_id', $tenantId)->findOrFail($id);

        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, $id)
    {
        $tenantId = Auth::user()->tenant_id;
        $banner = Banner::where('tenant_id', $tenantId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title'    => 'required|string|max:120',
            'subtitle' => 'nullable|string|max:255',
            'image'    => 'nullable|image|mimes:jpeg,jpg,png,webp,gif,bmp,svg|max:5120',
            'link_url' => 'nullable|url|max:255',
            'position' => 'required|integer|min:0',
            'active'   => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = [
            'title'    => $request->title,
            'subtitle' => $request->subtitle,
            'link_url' => $request->link_url,
            'position' => $request->position,
            'active'   => $request->active ?? false,
        ];

        if ($request->hasFile('image')) {
            // Remove imagem antiga
            if ($banner->image_url && Storage::disk('public')->exists($banner->image_url)) {
                Storage::disk('public')->delete($banner->image_url);
            }
            $data['image_url'] = $request->file('image')->store("banners/{$tenantId}", 'public');
        }

        $banner->update($data);

        return redirect()->route('admin.banners.index')
            ->with('success', 'Banner atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $tenantId = Auth::user()->tenant_id;
        $banner = Banner::where('tenant_id', $tenantId)->findOrFail($id);

        // Remove arquivo do storage
        if ($banner->image_url && Storage::disk('public')->exists($banner->image_url)) {
            Storage::disk('public')->delete($banner->image_url);
        }

        $banner->delete();

        return redirect()->route('admin.banners.index')
            ->with('success', 'Banner excluido com sucesso.');
    }
}
