<?php

namespace App\Http\Controllers;

use App\Models\ScheduleException;
use App\Models\Professional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ScheduleExceptionController extends Controller
{
    public function index(Request $request)
    {
        $authUser = Auth::user();

        $query = ScheduleException::with('professional.user')
            ->where('tenant_id', $authUser->tenant_id)
            ->when($request->professional_id, fn($q) => $q->where('professional_id', $request->professional_id))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->date, fn($q) => $q->where('date', $request->date))
            ->orderBy('date', 'desc');

        return response()->json($query->paginate(20));
    }
    public function store(Request $request)
    {
        $authUser = Auth::user();

        if (!in_array($authUser->role, ['owner', 'admin', 'professional'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'professional_id' => 'required|exists:professionals,id',
            'date' => 'required|date',
            'type' => 'required|in:block,holiday,special',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'reason' => 'nullable|string|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $professional = Professional::where('tenant_id', $authUser->tenant_id)
            ->find($request->professional_id);

        if (!$professional) {
            return response()->json(['message' => 'Profissional não encontrado ou não pertence a este tenant.'], 404);
        }

        $exception = ScheduleException::create([
            'tenant_id' => $authUser->tenant_id,
            'professional_id' => $professional->id,
            'date' => $request->date,
            'type' => $request->type,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'reason' => $request->reason,
        ]);

        return response()->json([
            'message' => 'Exceção de agenda criada com sucesso.',
            'data' => $exception->load('professional.user')
        ], 201);
    }

    public function show($id)
    {
        $authUser = Auth::user();

        $exception = ScheduleException::with('professional.user')
            ->where('tenant_id', $authUser->tenant_id)
            ->find($id);

        if (!$exception) {
            return response()->json(['message' => 'Exceção não encontrada.'], 404);
        }

        return response()->json($exception);
    }

    public function update(Request $request, $id)
    {
        $authUser = Auth::user();

        $exception = ScheduleException::where('tenant_id', $authUser->tenant_id)->find($id);

        if (!$exception) {
            return response()->json(['message' => 'Exceção não encontrada.'], 404);
        }

        if (!in_array($authUser->role, ['owner', 'admin', 'professional'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'date' => 'nullable|date',
            'type' => 'nullable|in:block,holiday,special',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'reason' => 'nullable|string|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $exception->update($request->only(['date', 'type', 'start_time', 'end_time', 'reason']));

        return response()->json([
            'message' => 'Exceção atualizada com sucesso.',
            'data' => $exception->load('professional.user')
        ]);
    }

    public function destroy($id)
    {
        $authUser = Auth::user();

        if (!in_array($authUser->role, ['owner', 'admin', 'professional'])) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $exception = ScheduleException::where('tenant_id', $authUser->tenant_id)->find($id);

        if (!$exception) {
            return response()->json(['message' => 'Exceção não encontrada.'], 404);
        }

        $exception->delete();

        return response()->json(['message' => 'Exceção removida com sucesso.']);
    }
}
