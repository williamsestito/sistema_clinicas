<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Professional;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminProfessionalController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $profissionais = Professional::where('tenant_id', $tenantId)
            ->with('user')
            ->when($request->filled('active'), fn($q) => $q->where('active', $request->active))
            ->when($request->search, fn($q) =>
                $q->whereHas('user', function ($sub) use ($request) {
                    $sub->where('name', 'like', "%{$request->search}%")
                        ->orWhere('email', 'like', "%{$request->search}%");
                })
            )
            ->when($request->filled('specialty'), fn($q) => $q->where('specialty', 'like', "%{$request->specialty}%"))
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('admin.professionals.index', compact('profissionais'));
    }

    public function create()
    {
        return view('admin.professionals.create');
    }

    public function store(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $validator = Validator::make($request->all(), [
            // User fields
            'name'                     => 'required|string|max:120',
            'email'                    => 'required|email|unique:users,email',
            'password'                 => 'required|string|min:6|confirmed',
            'phone'                    => 'nullable|string|max:20',
            'birth_date'               => 'nullable|date',
            'document'                 => 'nullable|string|max:14',
            'rg'                       => 'nullable|string|max:20',
            'civil_status'             => 'nullable|string|max:20',
            'gender'                   => 'nullable|string|max:20',
            'social_name'              => 'nullable|boolean',
            'social_name_text'         => 'nullable|string|max:120',
            'cep'                      => 'nullable|string|max:10',
            'address'                  => 'nullable|string|max:255',
            'number'                   => 'nullable|string|max:10',
            'complement'               => 'nullable|string|max:100',
            'district'                 => 'nullable|string|max:100',
            'city'                     => 'nullable|string|max:100',
            'state'                    => 'nullable|string|max:2',
            // Professional fields
            'specialty'                => 'nullable|string|max:120',
            'bio'                      => 'nullable|string|max:1000',
            'show_prices'              => 'nullable|boolean',
            'default_start_hour'       => 'nullable|date_format:H:i',
            'default_end_hour'         => 'nullable|date_format:H:i',
            'default_consultation_time' => 'nullable|integer|min:5|max:480',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'tenant_id'       => $tenantId,
            'name'            => $request->name,
            'email'           => $request->email,
            'password'        => $request->password,
            'phone'           => $request->phone,
            'birth_date'      => $request->birth_date,
            'document'        => $request->document,
            'rg'              => $request->rg,
            'civil_status'    => $request->civil_status,
            'gender'          => $request->gender,
            'social_name'     => $request->social_name ?? false,
            'social_name_text' => $request->social_name_text,
            'cep'             => $request->cep,
            'address'         => $request->address,
            'number'          => $request->number,
            'complement'      => $request->complement,
            'district'        => $request->district,
            'city'            => $request->city,
            'state'           => $request->state,
            'role'            => 'professional',
            'active'          => true,
        ]);

        Professional::create([
            'tenant_id'                => $tenantId,
            'user_id'                  => $user->id,
            'specialty'                => $request->specialty,
            'bio'                      => $request->bio,
            'show_prices'              => $request->show_prices ?? true,
            'default_start_hour'       => $request->default_start_hour,
            'default_end_hour'         => $request->default_end_hour,
            'default_consultation_time' => $request->default_consultation_time ?? 30,
            'active'                   => true,
        ]);

        return redirect()->route('admin.professionals.index')
            ->with('success', 'Profissional cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $tenantId = Auth::user()->tenant_id;
        $profissional = Professional::where('tenant_id', $tenantId)
            ->with('user')
            ->findOrFail($id);

        return view('admin.professionals.edit', compact('profissional'));
    }

    public function update(Request $request, $id)
    {
        $tenantId = Auth::user()->tenant_id;
        $profissional = Professional::where('tenant_id', $tenantId)->findOrFail($id);
        $user = $profissional->user;

        $validator = Validator::make($request->all(), [
            // User fields
            'name'                     => 'required|string|max:120',
            'email'                    => 'required|email|unique:users,email,' . $user->id,
            'password'                 => 'nullable|string|min:6|confirmed',
            'phone'                    => 'nullable|string|max:20',
            'birth_date'               => 'nullable|date',
            'document'                 => 'nullable|string|max:14',
            'rg'                       => 'nullable|string|max:20',
            'civil_status'             => 'nullable|string|max:20',
            'gender'                   => 'nullable|string|max:20',
            'social_name'              => 'nullable|boolean',
            'social_name_text'         => 'nullable|string|max:120',
            'cep'                      => 'nullable|string|max:10',
            'address'                  => 'nullable|string|max:255',
            'number'                   => 'nullable|string|max:10',
            'complement'               => 'nullable|string|max:100',
            'district'                 => 'nullable|string|max:100',
            'city'                     => 'nullable|string|max:100',
            'state'                    => 'nullable|string|max:2',
            'active'                   => 'nullable|boolean',
            // Professional fields
            'specialty'                => 'nullable|string|max:120',
            'bio'                      => 'nullable|string|max:1000',
            'show_prices'              => 'nullable|boolean',
            'default_start_hour'       => 'nullable|date_format:H:i',
            'default_end_hour'         => 'nullable|date_format:H:i',
            'default_consultation_time' => 'nullable|integer|min:5|max:480',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $userData = [
            'name'            => $request->name,
            'email'           => $request->email,
            'phone'           => $request->phone,
            'birth_date'      => $request->birth_date,
            'document'        => $request->document,
            'rg'              => $request->rg,
            'civil_status'    => $request->civil_status,
            'gender'          => $request->gender,
            'social_name'     => $request->social_name ?? false,
            'social_name_text' => $request->social_name_text,
            'cep'             => $request->cep,
            'address'         => $request->address,
            'number'          => $request->number,
            'complement'      => $request->complement,
            'district'        => $request->district,
            'city'            => $request->city,
            'state'           => $request->state,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        $profissional->update([
            'specialty'                => $request->specialty,
            'bio'                      => $request->bio,
            'show_prices'              => $request->show_prices ?? true,
            'default_start_hour'       => $request->default_start_hour,
            'default_end_hour'         => $request->default_end_hour,
            'default_consultation_time' => $request->default_consultation_time ?? 30,
            'active'                   => $request->active ?? true,
        ]);

        return redirect()->route('admin.professionals.index')
            ->with('success', 'Profissional atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $tenantId = Auth::user()->tenant_id;
        $profissional = Professional::where('tenant_id', $tenantId)->findOrFail($id);

        $user = $profissional->user;
        $profissional->delete();
        if ($user) {
            $user->delete();
        }

        return redirect()->route('admin.professionals.index')
            ->with('success', 'Profissional excluído com sucesso.');
    }
}
