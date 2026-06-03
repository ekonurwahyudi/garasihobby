<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MaterialCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaterialCategoryController extends Controller
{
    public function index(): View
    {
        $data = MaterialCategory::withCount('materials')->orderBy('name')->get();
        return view('master.material-categories.index', compact('data'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:20|unique:material_categories,code',
            'name' => 'required|string|max:100',
        ], [
            'code.required' => 'Kode wajib diisi.',
            'code.unique' => 'Kode sudah digunakan.',
            'name.required' => 'Nama kategori wajib diisi.',
        ]);

        MaterialCategory::create($request->only('code', 'name'));
        return response()->json(['success' => true]);
    }

    public function edit(MaterialCategory $material_category): JsonResponse
    {
        return response()->json($material_category);
    }

    public function update(Request $request, MaterialCategory $material_category): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:20|unique:material_categories,code,' . $material_category->id,
            'name' => 'required|string|max:100',
        ]);

        $material_category->update($request->only('code', 'name'));
        return response()->json(['success' => true]);
    }

    public function destroy(MaterialCategory $material_category): JsonResponse
    {
        if ($material_category->materials()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Kategori masih memiliki material.'], 422);
        }
        $material_category->delete();
        return response()->json(['success' => true]);
    }
}
