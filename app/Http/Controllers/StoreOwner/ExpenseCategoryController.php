<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\Auth;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::where('store_id', Auth::user()->store->id)->get();
        return view('store_owner.expenses.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:fixed,variable',
        ]);

        ExpenseCategory::create([
            'store_id' => Auth::user()->store->id,
            'name' => $request->name,
            'type' => $request->type,
            'description' => $request->description,
        ]);

        return back()->with('success', 'تم إضافة التصنيف بنجاح');
    }

    public function update(Request $request, $id)
    {
        $category = ExpenseCategory::where('store_id', Auth::user()->store->id)->findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:fixed,variable',
        ]);

        $category->update([
            'name' => $request->name,
            'type' => $request->type,
            'description' => $request->description,
        ]);

        return back()->with('success', 'تم تحديث التصنيف بنجاح');
    }

    public function destroy($id)
    {
        $category = ExpenseCategory::where('store_id', Auth::user()->store->id)->findOrFail($id);
        $category->delete();

        return back()->with('success', 'تم حذف التصنيف بنجاح');
    }
}
