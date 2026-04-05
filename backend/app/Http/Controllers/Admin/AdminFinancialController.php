<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialCategory;
use App\Models\FinancialEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminFinancialController extends Controller
{
    /**
     * Painel financeiro — fluxo de caixa
     */
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate   = $request->end_date   ? Carbon::parse($request->end_date)->endOfDay()     : Carbon::now()->endOfMonth();

        $query = FinancialEntry::where('tenant_id', $tenantId)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->with(['category', 'appointment.client', 'appointment.service', 'createdBy']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $entries = $query->orderBy('date', 'desc')->orderBy('created_at', 'desc')->paginate(20)->appends($request->query());

        // Resumo
        $summary = FinancialEntry::where('tenant_id', $tenantId)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw("
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense,
                COUNT(*) as total_entries
            ")
            ->first();

        // Receita por categoria
        $byCategory = FinancialEntry::where('financial_entries.tenant_id', $tenantId)
            ->whereBetween('financial_entries.date', [$startDate->toDateString(), $endDate->toDateString()])
            ->leftJoin('financial_categories', 'financial_entries.category_id', '=', 'financial_categories.id')
            ->selectRaw("COALESCE(financial_categories.name, 'Sem Categoria') as cat_name, financial_categories.color as cat_color, financial_entries.type, SUM(financial_entries.amount) as total, COUNT(*) as qty")
            ->groupBy('cat_name', 'cat_color', 'financial_entries.type')
            ->orderByDesc('total')
            ->get();

        // Fluxo por dia (gráfico)
        $byDay = FinancialEntry::where('tenant_id', $tenantId)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw("date, type, SUM(amount) as total")
            ->groupBy('date', 'type')
            ->orderBy('date')
            ->get();

        $categories = FinancialCategory::where('tenant_id', $tenantId)->where('active', true)->get();

        return view('admin.financial.index', compact(
            'entries', 'summary', 'byCategory', 'byDay', 'categories',
            'startDate', 'endDate'
        ));
    }

    /**
     * Form de novo lançamento
     */
    public function create()
    {
        $tenantId = Auth::user()->tenant_id;
        $categories = FinancialCategory::where('tenant_id', $tenantId)->where('active', true)->get();

        return view('admin.financial.create', compact('categories'));
    }

    /**
     * Salva lançamento
     */
    public function store(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $validator = Validator::make($request->all(), [
            'type'           => 'required|in:income,expense',
            'description'    => 'required|string|max:255',
            'amount'         => 'required|numeric|min:0.01',
            'date'           => 'required|date',
            'category_id'    => 'nullable|exists:financial_categories,id',
            'payment_method' => 'required|in:cash,credit_card,debit_card,pix,transfer,plan,other',
            'notes'          => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        FinancialEntry::create([
            'tenant_id'          => $tenantId,
            'type'               => $request->type,
            'description'        => $request->description,
            'amount'             => $request->amount,
            'date'               => $request->date,
            'category_id'        => $request->category_id,
            'payment_method'     => $request->payment_method,
            'notes'              => $request->notes,
            'created_by_user_id' => Auth::id(),
        ]);

        return redirect()->route('admin.financial.index')
            ->with('success', 'Lançamento registrado com sucesso.');
    }

    /**
     * Edit form
     */
    public function edit($id)
    {
        $tenantId = Auth::user()->tenant_id;
        $entry = FinancialEntry::where('tenant_id', $tenantId)->findOrFail($id);
        $categories = FinancialCategory::where('tenant_id', $tenantId)->where('active', true)->get();

        return view('admin.financial.edit', compact('entry', 'categories'));
    }

    /**
     * Update
     */
    public function update(Request $request, $id)
    {
        $tenantId = Auth::user()->tenant_id;
        $entry = FinancialEntry::where('tenant_id', $tenantId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'type'           => 'required|in:income,expense',
            'description'    => 'required|string|max:255',
            'amount'         => 'required|numeric|min:0.01',
            'date'           => 'required|date',
            'category_id'    => 'nullable|exists:financial_categories,id',
            'payment_method' => 'required|in:cash,credit_card,debit_card,pix,transfer,plan,other',
            'notes'          => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $entry->update($request->only([
            'type', 'description', 'amount', 'date',
            'category_id', 'payment_method', 'notes',
        ]));

        return redirect()->route('admin.financial.index')
            ->with('success', 'Lançamento atualizado com sucesso.');
    }

    /**
     * Destroy
     */
    public function destroy($id)
    {
        $tenantId = Auth::user()->tenant_id;
        $entry = FinancialEntry::where('tenant_id', $tenantId)->findOrFail($id);
        $entry->delete();

        return redirect()->route('admin.financial.index')
            ->with('success', 'Lançamento excluído com sucesso.');
    }

    // =========================================================
    // CATEGORIAS
    // =========================================================

    public function categories()
    {
        $tenantId = Auth::user()->tenant_id;
        $categories = FinancialCategory::where('tenant_id', $tenantId)
            ->withCount('entries')
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('admin.financial.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:80',
            'type'  => 'required|in:income,expense',
            'color' => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        FinancialCategory::create([
            'tenant_id' => $tenantId,
            'name'      => $request->name,
            'type'      => $request->type,
            'color'     => $request->color ?? '#6B7280',
        ]);

        return redirect()->route('admin.financial.categories')
            ->with('success', 'Categoria criada com sucesso.');
    }

    public function destroyCategory($id)
    {
        $tenantId = Auth::user()->tenant_id;
        $category = FinancialCategory::where('tenant_id', $tenantId)->findOrFail($id);
        $category->delete();

        return redirect()->route('admin.financial.categories')
            ->with('success', 'Categoria excluída com sucesso.');
    }
}
