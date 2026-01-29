<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $storeId = Auth::user()->store->id;
        $query = Expense::where('store_id', $storeId)->with(['category', 'user'])->latest('expense_date');

        // Filters
        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Totals
        $totals = [
            'total' => (clone $query)->sum('amount'),
            'today' => Expense::where('store_id', $storeId)->whereDate('expense_date', today())->sum('amount'),
            'month' => Expense::where('store_id', $storeId)->whereMonth('expense_date', now()->month)->sum('amount'),
        ];

        $expenses = $query->paginate(15)->withQueryString();
        $categories = ExpenseCategory::where('store_id', $storeId)->get();

        return view('store_owner.expenses.index', compact('expenses', 'categories', 'totals'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('expenses_attachments', 'public');
        }

        Expense::create([
            'store_id' => Auth::user()->store->id,
            'user_id' => Auth::id(),
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'notes' => $request->notes,
            'attachment' => $attachmentPath,
        ]);

        return back()->with('success', 'تم تسجيل المصروف بنجاح');
    }

    public function update(Request $request, $id)
    {
        $expense = Expense::where('store_id', Auth::user()->store->id)->findOrFail($id);

        $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
        ]);

        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('expenses_attachments', 'public');
            $expense->attachment = $attachmentPath;
        }

        $expense->update([
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'notes' => $request->notes,
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
