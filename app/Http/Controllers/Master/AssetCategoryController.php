<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\AssetCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetCategoryController extends Controller
{
    public function index(): View
    {
        $data = AssetCategory::withCount('assetPurchases')->orderBy('name')->get();
        return view('master.asset-categories.index', compact('data'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => 'required|string|max:30|unique:asset_categories,code',
            'name' => 'required|string|max:120',
            'description' => 'nullable|string',
        ]);

        AssetCategory::create($data);
        return response()->json(['success' => true]);
    }

    public function edit(AssetCategory $asset_category): JsonResponse
    {
        return response()->json($asset_category);
    }

    public function update(Request $request, AssetCategory $asset_category): JsonResponse
    {
        $data = $request->validate([
            'code' => 'required|string|max:30|unique:asset_categories,code,' . $asset_category->id,
            'name' => 'required|string|max:120',
            'description' => 'nullable|string',
        ]);

        $asset_category->update($data);
        return response()->json(['success' => true]);
    }

    public function destroy(AssetCategory $asset_category): JsonResponse
    {
        if ($asset_category->assetPurchases()->exists()) {
            return response()->json(['success' => false, 'message' => 'Kategori aset masih dipakai pada pembelian aset.'], 422);
        }

        $asset_category->delete();
        return response()->json(['success' => true]);
    }
}
