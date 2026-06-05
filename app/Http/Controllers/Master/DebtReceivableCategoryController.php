<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\DebtReceivableCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DebtReceivableCategoryController extends Controller
{
    public function index(): View
    {
        $data = DebtReceivableCategory::withCount('debtReceivables')->orderBy('name')->get();
        return view('master.debt-receivable-categories.index', compact('data'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => 'required|string|max:30|unique:debt_receivable_categories,code',
            'name' => 'required|string|max:120',
            'type' => 'required|in:debt,receivable,both',
            'description' => 'nullable|string',
        ]);

        DebtReceivableCategory::create($data);
        return response()->json(['success' => true]);
    }

    public function edit(DebtReceivableCategory $debt_receivable_category): JsonResponse
    {
        return response()->json($debt_receivable_category);
    }

    public function update(Request $request, DebtReceivableCategory $debt_receivable_category): JsonResponse
    {
        $data = $request->validate([
            'code' => 'required|string|max:30|unique:debt_receivable_categories,code,' . $debt_receivable_category->id,
            'name' => 'required|string|max:120',
            'type' => 'required|in:debt,receivable,both',
            'description' => 'nullable|string',
        ]);

        $debt_receivable_category->update($data);
        return response()->json(['success' => true]);
    }

    public function destroy(DebtReceivableCategory $debt_receivable_category): JsonResponse
    {
        if ($debt_receivable_category->debtReceivables()->exists()) {
            return response()->json(['success' => false, 'message' => 'Kategori masih dipakai pada hutang/piutang.'], 422);
        }

        $debt_receivable_category->delete();
        return response()->json(['success' => true]);
    }
}
