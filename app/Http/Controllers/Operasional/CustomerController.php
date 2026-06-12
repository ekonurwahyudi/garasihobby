<?php

namespace App\Http\Controllers\Operasional;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        $data = Customer::with('vehicles')->orderBy('name')->get();
        return view('operasional.customers.index', compact('data'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'plate_number' => 'required|string|max:20|unique:vehicles,plate_number',
            'brand' => 'nullable|string|max:100',
            'vehicle_model' => 'nullable|string|max:100',
            'vehicle_size' => 'required|in:small,medium,large',
            'vehicle_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
        ], [
            'name.required' => 'Nama pemilik wajib diisi.',
            'phone.required' => 'No HP wajib diisi.',
            'plate_number.required' => 'Plat mobil wajib diisi.',
            'plate_number.unique' => 'Plat mobil sudah terdaftar.',
            'vehicle_size.required' => 'Ukuran mobil wajib dipilih.',
            'vehicle_size.in' => 'Ukuran mobil tidak valid.',
            'vehicle_photo.image' => 'Foto mobil harus berupa gambar.',
            'vehicle_photo.mimes' => 'Foto mobil harus berformat JPG, PNG, atau WebP.',
            'vehicle_photo.max' => 'Ukuran foto mobil maksimal 4 MB.',
        ]);

        $customer = Customer::create($request->only('name', 'phone', 'email'));

        $customer->vehicles()->create([
            'plate_number' => strtoupper($request->plate_number),
            'brand' => $request->brand,
            'model' => $request->vehicle_model,
            'vehicle_size' => $request->vehicle_size,
            'photo_path' => $request->hasFile('vehicle_photo') ? $this->storeVehiclePhoto($request->file('vehicle_photo')) : null,
            'year' => $request->year,
        ]);

        return response()->json(['success' => true]);
    }

    public function show(Customer $customer): View
    {
        $customer->load('vehicles');
        return view('operasional.customers.show', compact('customer'));
    }

    public function edit(Customer $customer): JsonResponse
    {
        $vehicle = $customer->vehicles()->first();
        return response()->json([
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'plate_number' => $vehicle?->plate_number ?? '',
            'brand' => $vehicle?->brand ?? '',
            'vehicle_model' => $vehicle?->model ?? '',
            'vehicle_size' => $vehicle?->vehicle_size ?? '',
            'vehicle_photo_url' => $vehicle?->photo_path ? Storage::disk('r2')->url($vehicle->photo_path) : '',
            'year' => $vehicle?->year ?? '',
        ]);
    }

    public function update(Request $request, Customer $customer): JsonResponse
    {
        $vehicle = $customer->vehicles()->first();

        $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'plate_number' => 'required|string|max:20|unique:vehicles,plate_number,' . ($vehicle?->id ?? 0),
            'brand' => 'nullable|string|max:100',
            'vehicle_model' => 'nullable|string|max:100',
            'vehicle_size' => 'required|in:small,medium,large',
            'vehicle_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
        ]);

        $customer->update($request->only('name', 'phone', 'email'));

        if ($vehicle) {
            $photoPath = $vehicle->photo_path;
            if ($request->hasFile('vehicle_photo')) {
                $this->deleteVehiclePhoto($photoPath);
                $photoPath = $this->storeVehiclePhoto($request->file('vehicle_photo'));
            }

            $vehicle->update([
                'plate_number' => strtoupper($request->plate_number),
                'brand' => $request->brand,
                'model' => $request->vehicle_model,
                'vehicle_size' => $request->vehicle_size,
                'photo_path' => $photoPath,
                'year' => $request->year,
            ]);
        } else {
            $customer->vehicles()->create([
                'plate_number' => strtoupper($request->plate_number),
                'brand' => $request->brand,
                'model' => $request->vehicle_model,
                'vehicle_size' => $request->vehicle_size,
                'photo_path' => $request->hasFile('vehicle_photo') ? $this->storeVehiclePhoto($request->file('vehicle_photo')) : null,
                'year' => $request->year,
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $customer->load('vehicles');
        foreach ($customer->vehicles as $vehicle) {
            $this->deleteVehiclePhoto($vehicle->photo_path);
        }

        $customer->delete();
        return response()->json(['success' => true]);
    }

    /**
     * API search by plate number (untuk auto-fill di input order).
     */
    public function searchByPlate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:20',
        ]);

        $query = trim((string) ($validated['q'] ?? ''));
        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $vehicles = Vehicle::with('customer')
            ->where('plate_number', 'ilike', "%{$query}%")
            ->limit(10)
            ->get()
            ->map(fn($v) => [
                'plate_number' => $v->plate_number,
                'brand' => $v->brand,
                'model' => $v->model,
                'vehicle_size' => $v->vehicle_size,
                'vehicle_photo_url' => $v->photo_path ? Storage::disk('r2')->url($v->photo_path) : null,
                'year' => $v->year,
                'customer_id' => $v->customer_id,
                'customer_name' => $v->customer->name,
                'customer_phone' => $v->customer->phone,
                'customer_email' => $v->customer->email,
            ]);

        return response()->json($vehicles);
    }

    private function storeVehiclePhoto(UploadedFile $file): string
    {
        $image = imagecreatefromstring(file_get_contents($file->getRealPath()));
        if (!$image) {
            return $file->store('vehicle-photos', 'r2');
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagecopy($canvas, $image, 0, 0, 0, 0, $width, $height);

        ob_start();
        imagewebp($canvas, null, 82);
        $contents = ob_get_clean();

        imagedestroy($image);
        imagedestroy($canvas);

        $path = 'vehicle-photos/' . Str::uuid() . '.webp';
        Storage::disk('r2')->put($path, $contents, [
            'visibility' => 'public',
            'ContentType' => 'image/webp',
        ]);

        return $path;
    }

    private function deleteVehiclePhoto(?string $path): void
    {
        if ($path) {
            Storage::disk('r2')->delete($path);
        }
    }
}
