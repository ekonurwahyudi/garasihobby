<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\MaterialStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaterialController extends Controller
{
    public function index(): View
    {
        $data = Material::with(['category', 'stock'])->orderBy('name')->get();
        $categories = MaterialCategory::orderBy('name')->get();
        return view('master.materials.index', compact('data', 'categories'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'material_category_id' => 'required|exists:material_categories,id',
            'name' => 'required|string|max:200',
            'cost_price' => 'nullable|numeric|min:0',
        ], [
            'name.required' => 'Nama material wajib diisi.',
        ]);

        $material = Material::create([
            'material_category_id' => $request->material_category_id,
            'name' => $request->name,
            'price' => 0,
            'cost_price' => $request->cost_price,
            'min_stock' => 0,
        ]);

        MaterialStock::create(['material_id' => $material->id, 'qty' => 0, 'updated_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function edit(Material $material): JsonResponse
    {
        return response()->json($material->load('stock'));
    }

    public function update(Request $request, Material $material): JsonResponse
    {
        $request->validate([
            'material_category_id' => 'required|exists:material_categories,id',
            'name' => 'required|string|max:200',
            'cost_price' => 'nullable|numeric|min:0',
        ]);

        $material->update($request->only(
            'material_category_id', 'name', 'cost_price'
        ));

        return response()->json(['success' => true]);
    }

    public function destroy(Material $material): JsonResponse
    {
        $material->delete();
        return response()->json(['success' => true]);
    }
}
