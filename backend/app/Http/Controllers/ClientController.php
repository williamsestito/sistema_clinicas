<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Client::where('tenant_id', $user->tenant_id)
            ->when($request->name, fn($q) => $q->where('name', 'like', "%{$request->name}%"))
            ->when($request->email, fn($q) => $q->where('email', 'like', "%{$request->email}%"))
            ->when($request->phone, fn($q) => $q->where('phone', 'like', "%{$request->phone}%"))
            ->orderBy('name');

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $authUser = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'email' => 'nullable|email|max:120',
            'phone' => 'nullable|string|max:20',
            'birthdate' => 'nullable|date',
            'consent_marketing' => 'boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $client = Client::create([
            'tenant_id' => $authUser->tenant_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'birthdate' => $request->birthdate,
            'consent_marketing' => $request->consent_marketing ?? false,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'message' => 'Cliente cadastrado com sucesso.',
            'data' => $client
        ], 201);
    }

    public function show($id)
    {
        $authUser = Auth::user();

        $client = Client::where('tenant_id', $authUser->tenant_id)->find($id);

        if (!$client) {
            return response()->json(['message' => 'Cliente não encontrado.'], 404);
        }

        return response()->json($client);
    }

    public function update(Request $request, $id)
    {
        $authUser = Auth::user();

        $client = Client::where('tenant_id', $authUser->tenant_id)->find($id);

        if (!$client) {
            return response()->json(['message' => 'Cliente não encontrado.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:120',
            'email' => 'nullable|email|max:120',
            'phone' => 'nullable|string|max:20',
            'birthdate' => 'nullable|date',
            'consent_marketing' => 'boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $client->update($request->only([
            'name', 'email', 'phone', 'birthdate', 'consent_marketing', 'notes'
        ]));

        return response()->json([
            'message' => 'Cliente atualizado com sucesso.',
            'data' => $client
        ]);
    }

    public function destroy($id)
    {
        $authUser = Auth::user();

        $client = Client::where('tenant_id', $authUser->tenant_id)->find($id);

        if (!$client) {
            return response()->json(['message' => 'Cliente não encontrado.'], 404);
        }

        $client->delete();

        return response()->json(['message' => 'Cliente excluído com sucesso.']);
    }

    public function search(Request $request)
    {
        $authUser = Auth::user();
        $term = $request->query('q', '');

        $results = Client::where('tenant_id', $authUser->tenant_id)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%$term%")
                  ->orWhere('email', 'like', "%$term%")
                  ->orWhere('phone', 'like', "%$term%");
            })
            ->limit(10)
            ->get(['id', 'name', 'email', 'phone']);

        return response()->json($results);
    }
}
