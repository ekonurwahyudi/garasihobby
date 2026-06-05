<?php

namespace App\Http\Controllers\Operasional;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialPurchase;
use App\Models\MaterialStock;
use App\Models\MaterialStockAdjustment;
use App\Models\OrderMaterial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MaterialInventoryController extends Controller
{
    public function index(): View
    {
        $data = Material::with(['category', 'stock'])
            ->orderBy('name')
            ->get();

        $stats = [
            'total_material' => $data->count(),
            'total_stock' => $data->sum('stock_qty'),
            'low_stock' => $data->filter(fn ($material) => $material->stock_status !== 'Aman')->count(),
            'empty_stock' => $data->filter(fn ($material) => $material->stock_qty === 0)->count(),
            'stock_value' => $data->sum(fn ($material) => $material->stock_qty * (float) ($material->cost_price ?? 0)),
        ];

        return view('operasional.material-inventory.index', compact('data', 'stats'));
    }

    public function data(Material $material): JsonResponse
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
            'photo_url' => $material->photo_url,
            'stock_status' => $material->stock_status,
            'is_active' => $material->is_active,
            'stock_updated_at' => optional($material->stock?->updated_at)->format('d/m/Y H:i'),
        ]);
    }

    public function show(Material $material): View
    {
        $material->load(['category', 'stock']);

        $purchases = MaterialPurchase::query()
            ->where('material_id', $material->id)
            ->where('status', 'disetujui')
            ->orderByDesc('purchase_date')
            ->get()
            ->map(fn ($purchase) => (object) [
                'date' => $purchase->purchase_date,
                'type' => 'Pembelian Material',
                'qty_in' => $purchase->qty,
                'qty_out' => 0,
                'unit_price' => (float) $purchase->unit_price,
                'total_price' => (float) $purchase->total_price,
                'notes' => trim(($purchase->invoice_number ? 'Invoice: ' . $purchase->invoice_number . '. ' : '') . ($purchase->supplier ? 'Supplier: ' . $purchase->supplier . '. ' : '') . ($purchase->notes ?: '')),
                'created_at' => $purchase->created_at,
            ]);

        $orders = OrderMaterial::query()
            ->with('order.customer')
            ->where('material_id', $material->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($orderMaterial) => (object) [
                'date' => $orderMaterial->order?->order_date,
                'type' => 'Pemakaian Order',
                'qty_in' => 0,
                'qty_out' => $orderMaterial->qty,
                'unit_price' => (float) $orderMaterial->price,
                'total_price' => (float) $orderMaterial->subtotal,
                'notes' => trim(($orderMaterial->order?->order_number ? 'Order: ' . $orderMaterial->order->order_number . '. ' : '') . ($orderMaterial->order?->customer?->name ? 'Customer: ' . $orderMaterial->order->customer->name : '')),
                'created_at' => $orderMaterial->created_at,
            ]);

        $adjustments = MaterialStockAdjustment::with('creator')
            ->where('material_id', $material->id)
            ->latest()
            ->get();

        $adjustmentMovements = $adjustments->map(fn ($adjustment) => (object) [
            'date' => $adjustment->created_at,
            'type' => 'Penyesuaian Stok',
            'qty_in' => $adjustment->difference_qty > 0 ? $adjustment->difference_qty : 0,
            'qty_out' => $adjustment->difference_qty < 0 ? abs($adjustment->difference_qty) : 0,
            'unit_price' => (float) ($material->cost_price ?? 0),
            'total_price' => abs($adjustment->difference_qty) * (float) ($material->cost_price ?? 0),
            'notes' => $adjustment->reason,
            'created_at' => $adjustment->created_at,
        ]);

        $history = $purchases
            ->concat($orders)
            ->concat($adjustmentMovements)
            ->sortByDesc(fn ($row) => $row->date?->timestamp ?? $row->created_at?->timestamp ?? 0)
            ->values();

        return view('operasional.material-inventory.show', compact('material', 'history', 'adjustments'));
    }

    public function update(Request $request, Material $material): JsonResponse
    {
        $data = $request->validate([
            'binrow' => 'nullable|string|max:50',
            'min_stock' => 'required|integer|min:0',
        ]);

        $material->update($data);

        return response()->json(['success' => true]);
    }

    public function adjust(Request $request, Material $material)
    {
        $data = $request->validate([
            'actual_qty' => 'required|integer|min:0',
            'reason' => 'required|string|max:1000',
        ], [
            'actual_qty.required' => 'Qty sebenarnya wajib diisi.',
            'reason.required' => 'Alasan perubahan wajib diisi.',
        ]);

        DB::transaction(function () use ($material, $data) {
            $stock = MaterialStock::firstOrCreate(
                ['material_id' => $material->id],
                ['qty' => 0, 'updated_at' => now()]
            );

            $previousQty = (int) $stock->qty;
            $actualQty = (int) $data['actual_qty'];

            MaterialStockAdjustment::create([
                'material_id' => $material->id,
                'previous_qty' => $previousQty,
                'actual_qty' => $actualQty,
                'difference_qty' => $actualQty - $previousQty,
                'reason' => $data['reason'],
                'created_by' => auth()->id(),
            ]);

            $stock->update([
                'qty' => $actualQty,
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('material-inventory.show', $material)->with('success', 'Penyesuaian stok berhasil disimpan.');
    }
}
