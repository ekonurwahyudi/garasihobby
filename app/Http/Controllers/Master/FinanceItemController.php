<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\FinanceCategory;
use App\Models\FinanceItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceItemController extends Controller
{
    public function index(): View
    {
        $data = FinanceItem::with('category')->orderBy('name')->get();
        $categories = FinanceCategory::orderBy('name')->get();
        return view('master.finance-items.index', compact('data', 'categories'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'finance_category_id' => 'required|exists:finance_categories,id',
            'code' => 'required|string|max:20|unique:finance_items,code',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        FinanceItem::create($data);
        return response()->json(['success' => true]);
    }

    public function edit(FinanceItem $finance_item): JsonResponse
    {
        return response()->json($finance_item);
    }

    public function update(Request $request, FinanceItem $finance_item): JsonResponse
    {
        $data = $request->validate([
            'finance_category_id' => 'required|exists:finance_categories,id',
            'code' => 'required|string|max:20|unique:finance_items,code,' . $finance_item->id,
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $finance_item->update($data);
        return response()->json(['success' => true]);
    }

    public function destroy(FinanceItem $finance_item): JsonResponse
    {
        if ($finance_item->transactions()->exists()) {
            return response()->json(['success' => false, 'message' => 'Item sudah digunakan pada transaksi keuangan.'], 422);
        }

        $finance_item->delete();
        return response()->json(['success' => true]);
    }
}
