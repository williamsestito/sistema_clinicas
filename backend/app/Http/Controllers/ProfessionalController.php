<?php

namespace App\Http\Controllers;

use App\Models\Professional;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfessionalController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTAGEM
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $authUser = Auth::user();

        $query = Professional::with('user')
            ->where('tenant_id', $authUser->tenant_id)
            ->when($request->name, fn($q) =>
                $q->whereHas('user', fn($u) =>
                    $u->where('name', 'like', "%{$request->name}%")
                )
            )
            ->when($request->specialty, fn($q) =>
                $q->whereJsonContains('specialty', $request->specialty)
            )
            ->when($request->city, fn($q) =>
                $q->where('city', $request->city)
            )
            ->when($request->state, fn($q) =>
                $q->where('state', $request->state)
            )
            ->when($request->active !== null, fn($q) =>
                $q->where('active', $request->active)
            )
            ->orderBy('id', 'desc');

        return response()->json($query->paginate(20));
    }

    /*
    |--------------------------------------------------------------------------
    | CRIAÇÃO
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $authUser = Auth::user();

        if (!in_array($authUser->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name'             => 'required|string|max:120',
            'email'            => 'required|email|unique:users,email',
            'password'         => 'required|string|min:6|confirmed',
            'specialty'        => 'required|array',
            'registration_type'   => 'nullable|string|max:20',
            'registration_number' => 'nullable|string|max:50',
            'bio'              => 'nullable|string|max:2000',
            'about'            => 'nullable|string',
            'education'        => 'nullable|string',
            'specializations'  => 'nullable|array',

            'state'            => 'nullable|string|max:2',
            'city'             => 'nullable|string|max:120',
            'address'          => 'nullable|string',
            'number'           => 'nullable|string|max:20',
            'district'         => 'nullable|string|max:120',
            'complement'       => 'nullable|string',
            'zipcode'          => 'nullable|string|max:20',

            'phone'            => 'nullable|string|max:30',
            'email_public'     => 'nullable|email',

            'linkedin_url'     => 'nullable|url',
            'instagram_url'    => 'nullable|url',
            'website_url'      => 'nullable|url',

            'photo'            => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Criar usuário do profissional
        |--------------------------------------------------------------------------
        */
        $user = User::create([
            'tenant_id' => $authUser->tenant_id,
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'password'  => bcrypt($request->password),
            'role'      => 'professional',
            'active'    => true,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Upload da foto
        |--------------------------------------------------------------------------
        */
        $photoUrl = null;

        if ($request->hasFile('photo')) {
            $file = $request->file('photo')->store('professionals/photos', 'public');
            $photoUrl = Storage::url($file);
        }

        /*
        |--------------------------------------------------------------------------
        | Criação do profissional
        |--------------------------------------------------------------------------
        */
        $professional = Professional::create([
            'tenant_id'              => $authUser->tenant_id,
            'user_id'                => $user->id,

            'specialty'              => $request->specialty,
            'registration_type'      => $request->registration_type,
            'registration_number'    => $request->registration_number,

            'bio'                    => $request->bio,
            'about'                  => $request->about,
            'education'              => $request->education,
            'specializations'        => $request->specializations,

            'state'                  => $request->state,
            'city'                   => $request->city,
            'address'                => $request->address,
            'number'                 => $request->number,
            'district'               => $request->district,
            'complement'             => $request->complement,
            'zipcode'                => $request->zipcode,

            'phone'                  => $request->phone,
            'email_public'           => $request->email_public,

            'linkedin_url'           => $request->linkedin_url,
            'instagram_url'          => $request->instagram_url,
            'website_url'            => $request->website_url,

            'photo_url'              => $photoUrl,

            'default_start_hour'     => $request->default_start_hour ?? '08:00',
            'default_end_hour'       => $request->default_end_hour ?? '17:00',
            'default_consultation_time' => $request->default_consultation_time ?? 30,

            'active' => true,
        ]);

        return response()->json([
            'message' => 'Profissional criado com sucesso.',
            'data'    => $professional->load('user'),
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | DETALHES
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $authUser = Auth::user();

        $professional = Professional::with('user')
            ->where('tenant_id', $authUser->tenant_id)
            ->find($id);

        if (!$professional) {
            return response()->json(['message' => 'Profissional não encontrado.'], 404);
        }

        return response()->json($professional);
    }

    /*
    |--------------------------------------------------------------------------
    | ATUALIZAÇÃO
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $authUser = Auth::user();

        $professional = Professional::where('tenant_id', $authUser->tenant_id)->find($id);

        if (!$professional) {
            return response()->json(['message' => 'Profissional não encontrado.'], 404);
        }

        if (!in_array($authUser->role, ['owner', 'admin']) && $authUser->id !== $professional->user_id) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'specialty'        => 'nullable|array',
            'registration_type'   => 'nullable|string|max:20',
            'registration_number' => 'nullable|string|max:50',
            'bio'              => 'nullable|string|max:2000',
            'about'            => 'nullable|string',
            'education'        => 'nullable|string',
            'specializations'  => 'nullable|array',

            'state'            => 'nullable|string|max:2',
            'city'             => 'nullable|string|max:120',
            'address'          => 'nullable|string',
            'number'           => 'nullable|string|max:20',
            'district'         => 'nullable|string|max:120',
            'complement'       => 'nullable|string',
            'zipcode'          => 'nullable|string|max:20',

            'phone'            => 'nullable|string|max:30',
            'email_public'     => 'nullable|email',

            'linkedin_url'     => 'nullable|url',
            'instagram_url'    => 'nullable|url',
            'website_url'      => 'nullable|url',

            'photo'            => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
            'active'           => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Atualização da foto
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('photo')) {

            if ($professional->photo_url) {
                $old = str_replace('/storage/', '', $professional->photo_url);
                Storage::disk('public')->delete($old);
            }

            $file = $request->file('photo')->store('professionals/photos', 'public');
            $professional->photo_url = Storage::url($file);
        }

        /*
        |--------------------------------------------------------------------------
        | Atualizar tudo
        |--------------------------------------------------------------------------
        */
        $professional->update($request->except(['photo']));

        return response()->json([
            'message' => 'Profissional atualizado com sucesso.',
            'data' => $professional->fresh()->load('user'),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DESATIVAR
    |--------------------------------------------------------------------------
    */
    public function deactivate($id)
    {
        $authUser = Auth::user();

        if (!in_array($authUser->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $professional = Professional::where('tenant_id', $authUser->tenant_id)->find($id);

        if (!$professional) {
            return response()->json(['message' => 'Profissional não encontrado.'], 404);
        }

        $professional->active = false;
        $professional->save();

        return response()->json(['message' => 'Profissional inativado com sucesso.']);
    }

    /*
    |--------------------------------------------------------------------------
    | EXCLUSÃO
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $authUser = Auth::user();

        if (!in_array($authUser->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $professional = Professional::where('tenant_id', $authUser->tenant_id)->find($id);

        if (!$professional) {
            return response()->json(['message' => 'Profissional não encontrado.'], 404);
        }

        if ($professional->photo_url) {
            $old = str_replace('/storage/', '', $professional->photo_url);
            Storage::disk('public')->delete($old);
        }

        $professional->delete();

        return response()->json(['message' => 'Profissional excluído com sucesso.']);
    }
}
