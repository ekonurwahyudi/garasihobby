<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\FinanceCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceCategoryController extends Controller
{
    public function index(): View
    {
        $data = FinanceCategory::withCount('items')->orderBy('name')->get();
        return view('master.finance-categories.index', compact('data'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => 'required|string|max:20|unique:finance_categories,code',
            'name' => 'required|string|max:100',
            'type' => 'required|in:income,expense',
            'description' => 'nullable|string',
        ]);

        FinanceCategory::create($data);
        return response()->json(['success' => true]);
    }

    public function edit(FinanceCategory $finance_category): JsonResponse
    {
        return response()->json($finance_category);
    }

    public function update(Request $request, FinanceCategory $finance_category): JsonResponse
    {
        $data = $request->validate([
            'code' => 'required|string|max:20|unique:finance_categories,code,' . $finance_category->id,
            'name' => 'required|string|max:100',
            'type' => 'required|in:income,expense',
            'description' => 'nullable|string',
        ]);

        $finance_category->update($data);
        return response()->json(['success' => true]);
    }

    public function destroy(FinanceCategory $finance_category): JsonResponse
    {
        if ($finance_category->items()->exists()) {
            return response()->json(['success' => false, 'message' => 'Kategori masih memiliki item keuangan.'], 422);
        }

        $finance_category->delete();
        return response()->json(['success' => true]);
    }
}
