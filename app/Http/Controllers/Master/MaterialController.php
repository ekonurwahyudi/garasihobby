<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\MaterialStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ], [
            'name.required' => 'Nama material wajib diisi.',
        ]);

        $material = Material::create([
            'material_category_id' => $request->material_category_id,
            'name' => $request->name,
            'price' => 0,
            'cost_price' => $request->cost_price,
            'min_stock' => 0,
            'photo_path' => $request->hasFile('photo') ? $this->storeMaterialPhoto($request->file('photo')) : null,
        ]);

        MaterialStock::create(['material_id' => $material->id, 'qty' => 0, 'updated_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function edit(Material $material): JsonResponse
    {
        $material->load('stock');

        return response()->json($material->toArray() + [
            'photo_url' => $material->photo_url,
        ]);
    }

    public function update(Request $request, Material $material): JsonResponse
    {
        $request->validate([
            'material_category_id' => 'required|exists:material_categories,id',
            'name' => 'required|string|max:200',
            'cost_price' => 'nullable|numeric|min:0',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $data = $request->only('material_category_id', 'name', 'cost_price');
        if ($request->hasFile('photo')) {
            $this->deleteMaterialPhoto($material->photo_path);
            $data['photo_path'] = $this->storeMaterialPhoto($request->file('photo'));
        }

        $material->update($data);

        return response()->json(['success' => true]);
    }

    public function destroy(Material $material): JsonResponse
    {
        $this->deleteMaterialPhoto($material->photo_path);
        $material->delete();
        return response()->json(['success' => true]);
    }

    private function storeMaterialPhoto(UploadedFile $file): string
    {
        if (!Str::startsWith((string) $file->getMimeType(), 'image/')) {
            return $file->store('material-photos', 'r2');
        }

        $image = imagecreatefromstring(file_get_contents($file->getRealPath()));
        if (!$image) {
            return $file->store('material-photos', 'r2');
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

        $path = 'material-photos/' . Str::uuid() . '.webp';
        Storage::disk('r2')->put($path, $contents, [
            'visibility' => 'public',
            'ContentType' => 'image/webp',
        ]);

        return $path;
    }

    private function deleteMaterialPhoto(?string $path): void
    {
        if ($path) {
            Storage::disk('r2')->delete($path);
        }
    }
}
