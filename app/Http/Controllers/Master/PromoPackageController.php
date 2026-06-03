<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\ChecklistItem;
use App\Models\PromoPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromoPackageController extends Controller
{
    public function index(): View
    {
        $data = PromoPackage::withCount('checklistItems')->orderBy('name')->get();
        $checklistItems = ChecklistItem::with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->groupBy(fn($item) => $item->category->name ?? 'Tanpa Kategori');

        return view('master.promo-packages.index', compact('data', 'checklistItems'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'checklist_items' => 'required|array|min:1',
            'checklist_items.*' => 'exists:checklist_items,id',
        ], [
            'name.required' => 'Nama paket wajib diisi.',
            'price.required' => 'Harga promo wajib diisi.',
            'checklist_items.required' => 'Minimal 1 item checklist dipilih.',
        ]);

        $promo = PromoPackage::create($request->only('name', 'price', 'description', 'valid_from', 'valid_until'));
        $promo->checklistItems()->sync($request->checklist_items);

        return response()->json(['success' => true]);
    }

    public function edit(PromoPackage $promo_package): JsonResponse
    {
        return response()->json([
            'id' => $promo_package->id,
            'name' => $promo_package->name,
            'price' => $promo_package->price,
            'description' => $promo_package->description,
            'valid_from' => $promo_package->valid_from?->format('Y-m-d'),
            'valid_until' => $promo_package->valid_until?->format('Y-m-d'),
            'is_active' => $promo_package->is_active,
            'checklist_item_ids' => $promo_package->checklistItems->pluck('id')->toArray(),
        ]);
    }

    public function update(Request $request, PromoPackage $promo_package): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'required|boolean',
            'checklist_items' => 'required|array|min:1',
            'checklist_items.*' => 'exists:checklist_items,id',
        ]);

        $promo_package->update($request->only('name', 'price', 'description', 'valid_from', 'valid_until', 'is_active'));
        $promo_package->checklistItems()->sync($request->checklist_items);

        return response()->json(['success' => true]);
    }

    public function destroy(PromoPackage $promo_package): JsonResponse
    {
        $promo_package->delete();
        return response()->json(['success' => true]);
    }
}
