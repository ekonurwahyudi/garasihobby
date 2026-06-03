<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\ChecklistCategory;
use App\Models\ChecklistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChecklistItemController extends Controller
{
    public function index(): View
    {
        $data = ChecklistItem::with('category')->orderBy('name')->get();
        $categories = ChecklistCategory::orderBy('name')->get();
        return view('master.checklist-items.index', compact('data', 'categories'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'checklist_category_id' => 'required|exists:checklist_categories,id',
            'name' => 'required|string|max:150',
            'price_small' => 'required|numeric|min:0',
            'price_medium' => 'required|numeric|min:0',
            'price_large' => 'required|numeric|min:0',
        ], [
            'checklist_category_id.required' => 'Kategori wajib dipilih.',
            'name.required' => 'Nama item wajib diisi.',
            'price_small.required' => 'Harga Small wajib diisi.',
            'price_medium.required' => 'Harga Medium wajib diisi.',
            'price_large.required' => 'Harga Large wajib diisi.',
        ]);

        ChecklistItem::create([
            ...$request->only('checklist_category_id', 'name', 'price_small', 'price_medium', 'price_large'),
            'price' => $request->price_small,
        ]);
        return response()->json(['success' => true]);
    }

    public function edit(ChecklistItem $checklist_item): JsonResponse
    {
        return response()->json($checklist_item);
    }

    public function update(Request $request, ChecklistItem $checklist_item): JsonResponse
    {
        $request->validate([
            'checklist_category_id' => 'required|exists:checklist_categories,id',
            'name' => 'required|string|max:150',
            'price_small' => 'required|numeric|min:0',
            'price_medium' => 'required|numeric|min:0',
            'price_large' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
        ]);

        $checklist_item->update([
            ...$request->only('checklist_category_id', 'name', 'price_small', 'price_medium', 'price_large', 'is_active'),
            'price' => $request->price_small,
        ]);
        return response()->json(['success' => true]);
    }

    public function destroy(ChecklistItem $checklist_item): JsonResponse
    {
        $checklist_item->delete();
        return response()->json(['success' => true]);
    }
}
