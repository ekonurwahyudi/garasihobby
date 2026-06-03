<?php

namespace App\Http\Controllers\Operasional;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class MaterialInventoryController extends Controller
{
    public function index(): View
    {
        $data = Material::with(['category', 'stock'])
            ->orderBy('name')
            ->get();

        return view('operasional.material-inventory.index', compact('data'));
    }

    public function show(Material $material): JsonResponse
    {
        $material->load(['category', 'stock']);

        return response()->json([
            'id' => $material->id,
            'sku' => $material->sku,
            'name' => $material->name,
            'category' => $material->category?->name,
            'price' => $material->price,
            'cost_price' => $material->cost_price,
            'stock_qty' => $material->stock_qty,
            'min_stock' => $material->min_stock,
            'binrow' => $material->binrow,
            'stock_status' => $material->stock_status,
            'is_active' => $material->is_active,
            'stock_updated_at' => optional($material->stock?->updated_at)->format('d/m/Y H:i'),
        ]);
    }
}
