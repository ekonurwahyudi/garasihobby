<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\ChecklistCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChecklistCategoryController extends Controller
{
    public function index(): View
    {
        $data = ChecklistCategory::withCount('items')->orderBy('name')->get();
        return view('master.checklist-categories.index', compact('data'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:20|unique:checklist_categories,code',
            'name' => 'required|string|max:100',
        ], [
            'code.required' => 'Kode wajib diisi.',
            'code.unique' => 'Kode sudah digunakan.',
            'name.required' => 'Nama kategori wajib diisi.',
        ]);

        ChecklistCategory::create($request->only('code', 'name'));
        return response()->json(['success' => true]);
    }

    public function edit(ChecklistCategory $checklist_category): JsonResponse
    {
        return response()->json($checklist_category);
    }

    public function update(Request $request, ChecklistCategory $checklist_category): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:20|unique:checklist_categories,code,' . $checklist_category->id,
            'name' => 'required|string|max:100',
        ]);

        $checklist_category->update($request->only('code', 'name'));
        return response()->json(['success' => true]);
    }

    public function destroy(ChecklistCategory $checklist_category): JsonResponse
    {
        $checklist_category->delete();
        return response()->json(['success' => true]);
    }
}
