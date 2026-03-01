<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Currency;
use App\Services\ExchangeRateService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    protected $exchangeService;

    public function __construct(ExchangeRateService $exchangeService)
    {
        $this->exchangeService = $exchangeService;
    }

    public function index(Request $request)
    {
        $store   = Auth::user()->store;
        $storeId = $store->id;

        // العملة الأساسية للمتجر
        $baseCurrency = Currency::find($store->base_currency_id);

        // العملات المتاحة: الأساسية + المقبولة
        $acceptedCurrencies = $store->acceptedCurrencies ?? collect();
        $currencies = collect([$baseCurrency])->merge($acceptedCurrencies)->unique('id')->filter();

        $query = Expense::where('store_id', $storeId)
                        ->with(['category', 'user', 'currency'])
                        ->latest('expense_date');

        // Filters
        if ($request->filled('date_from'))   $query->whereDate('expense_date', '>=', $request->date_from);
        if ($request->filled('date_to'))     $query->whereDate('expense_date', '<=', $request->date_to);
        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);
        if ($request->filled('currency_id')) $query->where('currency_id', $request->currency_id);

        // إجماليات محوّلة للعملة الأساسية
        $allFiltered   = (clone $query)->get();
        $todayExpenses = Expense::where('store_id', $storeId)->whereDate('expense_date', today())->with('currency')->get();
        $monthExpenses = Expense::where('store_id', $storeId)->whereMonth('expense_date', now()->month)->with('currency')->get();

        $totals = [
            'total' => $this->sumInBaseCurrency($allFiltered),
            'today' => $this->sumInBaseCurrency($todayExpenses),
            'month' => $this->sumInBaseCurrency($monthExpenses),
        ];

        // تفصيل بالعملات (للمصاريف المفلترة)
        $currencyBreakdown = $this->getCurrencyBreakdown($allFiltered, $baseCurrency);

        $expenses   = $query->paginate(15)->withQueryString();
        $categories = ExpenseCategory::where('store_id', $storeId)->get();

        return view('store_owner.expenses.index', compact(
            'expenses', 'categories', 'totals',
            'currencies', 'baseCurrency', 'currencyBreakdown'
        ));
    }

    private function sumInBaseCurrency($expenses)
    {
        return $expenses->sum(fn($e) => $e->amount_in_base_currency);
    }

    private function getCurrencyBreakdown($expenses, $baseCurrency)
    {
        return $expenses->groupBy(fn($e) => $e->currency_id ?? 'base')
            ->map(function ($group) use ($baseCurrency) {
                $currency = $group->first()->currency ?? $baseCurrency;
                return [
                    'currency'      => $currency,
                    'total_amount'  => $group->sum('amount'),
                    'exchange_rate' => $group->first()->exchange_rate ?? 1,
                    'in_base'       => $group->sum('amount_in_base_currency'),
                    'base_currency' => $baseCurrency,
                ];
            })->values();
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id'  => 'required|exists:expense_categories,id',
            'amount'       => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'currency_id'  => 'nullable|exists:currencies,id',
        ]);

        $store        = Auth::user()->store;
        $baseCurrency = Currency::find($store->base_currency_id);
        $currencyId   = $request->currency_id ?? $store->base_currency_id;
        $currency     = Currency::find($currencyId);

        $exchangeRate = 1;
        if ($currency && $baseCurrency && $currency->code !== $baseCurrency->code) {
            $rate = $this->exchangeService->convert(1, $currency->code, $baseCurrency->code);
            $exchangeRate = $rate ?? 1;
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('expenses_attachments', 'public');
        }

        Expense::create([
            'store_id'      => $store->id,
            'user_id'       => Auth::id(),
            'category_id'   => $request->category_id,
            'amount'        => $request->amount,
            'currency_id'   => $currencyId,
            'exchange_rate' => $exchangeRate,
            'expense_date'  => $request->expense_date,
            'notes'         => $request->notes,
            'attachment'    => $attachmentPath,
        ]);

        return back()->with('success', 'تم تسجيل المصروف بنجاح');
    }

    public function update(Request $request, $id)
    {
        $store   = Auth::user()->store;
        $expense = Expense::where('store_id', $store->id)->findOrFail($id);

        $request->validate([
            'category_id'  => 'required|exists:expense_categories,id',
            'amount'       => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'currency_id'  => 'nullable|exists:currencies,id',
        ]);

        $baseCurrency = Currency::find($store->base_currency_id);
        $currencyId   = $request->currency_id ?? $store->base_currency_id;
        $currency     = Currency::find($currencyId);

        $exchangeRate = 1;
        if ($currency && $baseCurrency && $currency->code !== $baseCurrency->code) {
            $rate = $this->exchangeService->convert(1, $currency->code, $baseCurrency->code);
            $exchangeRate = $rate ?? 1;
        }

        if ($request->hasFile('attachment')) {
            $expense->attachment = $request->file('attachment')->store('expenses_attachments', 'public');
        }

        $expense->update([
            'category_id'   => $request->category_id,
            'amount'        => $request->amount,
            'currency_id'   => $currencyId,
            'exchange_rate' => $exchangeRate,
            'expense_date'  => $request->expense_date,
            'notes'         => $request->notes,
        ]);

        return back()->with('success', 'تم تحديث المصروف بنجاح');
    }

    public function destroy($id)
    {
        $expense = Expense::where('store_id', Auth::user()->store->id)->findOrFail($id);
        $expense->delete();
        return back()->with('success', 'تم حذف المصروف بنجاح');
    }
}
