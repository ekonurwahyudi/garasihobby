<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\PromoPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromoPackageController extends Controller
{
    public function index(): View
    {
        $data = PromoPackage::orderBy('name')->get();

        return view('master.promo-packages.index', compact('data'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'price_small' => 'required|numeric|min:0',
            'price_medium' => 'required|numeric|min:0',
            'price_large' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
        ], [
            'name.required' => 'Nama paket wajib diisi.',
            'price_small.required' => 'Harga Small wajib diisi.',
            'price_medium.required' => 'Harga Medium wajib diisi.',
            'price_large.required' => 'Harga Large wajib diisi.',
        ]);

        PromoPackage::create([
            ...$request->only('name', 'price_small', 'price_medium', 'price_large', 'description', 'valid_from', 'valid_until'),
            'price' => $request->price_small,
        ]);

        return response()->json(['success' => true]);
    }

    public function edit(PromoPackage $promo_package): JsonResponse
    {
        return response()->json([
            'id' => $promo_package->id,
            'name' => $promo_package->name,
            'price' => $promo_package->price,
            'price_small' => $promo_package->price_small,
            'price_medium' => $promo_package->price_medium,
            'price_large' => $promo_package->price_large,
            'description' => $promo_package->description,
            'valid_from' => $promo_package->valid_from?->format('Y-m-d'),
            'valid_until' => $promo_package->valid_until?->format('Y-m-d'),
            'is_active' => $promo_package->is_active,
        ]);
    }

    public function update(Request $request, PromoPackage $promo_package): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'price_small' => 'required|numeric|min:0',
            'price_medium' => 'required|numeric|min:0',
            'price_large' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'required|boolean',
        ]);

        $promo_package->update([
            ...$request->only('name', 'price_small', 'price_medium', 'price_large', 'description', 'valid_from', 'valid_until', 'is_active'),
            'price' => $request->price_small,
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(PromoPackage $promo_package): JsonResponse
    {
        $promo_package->delete();
        return response()->json(['success' => true]);
    }
}
