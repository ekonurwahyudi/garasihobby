<?php

namespace App\Http\Controllers\Operasional;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\ChecklistCategory;
use App\Models\ChecklistItem;
use App\Models\Customer;
use App\Models\FinanceCategory;
use App\Models\FinanceItem;
use App\Models\FinanceTransaction;
use App\Models\Material;
use App\Models\Order;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Order::with(['customer', 'vehicle', 'creator'])
            ->orderByDesc('created_at');

        if ($request->has('customer_id') && $request->get('format') === 'json') {
            $orders = $query->where('customer_id', $request->customer_id)->get();
            return response()->json($orders->map(fn($o) => [
                'order_number' => $o->order_number,
                'order_date' => $o->order_date->format('d/m/Y'),
                'total' => $o->total,
                'status' => $o->status,
                'show_url' => route('orders.show', $o),
            ]));
        }

        $data = $query->get();
        return view('operasional.orders.index', compact('data'));
    }

    public function create(): View
    {
        $checklistCategories = ChecklistCategory::with(['items' => fn($q) => $q->where('is_active', true)])
            ->orderBy('name')
            ->get();
        $materials = Material::with('stock')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $mechanics = User::role('Mekanik')
            ->where('status', 'aktif')
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);
        $bankAccounts = BankAccount::where('is_active', true)->orderBy('bank_name')->get();

        return view('operasional.orders.create', compact('checklistCategories', 'materials', 'mechanics', 'bankAccounts'));
    }

    public function edit(Order $order): View
    {
        $order->load(['customer', 'vehicle', 'items', 'materials']);
        $checklistCategories = ChecklistCategory::with(['items' => fn($q) => $q->where('is_active', true)])
            ->orderBy('name')
            ->get();
        $materials = Material::with('stock')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $mechanics = User::role('Mekanik')
            ->where('status', 'aktif')
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);
        $bankAccounts = BankAccount::where('is_active', true)->orderBy('bank_name')->get();

        return view('operasional.orders.edit', compact('checklistCategories', 'materials', 'mechanics', 'order', 'bankAccounts'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'new_plate' => [
                'required_without:vehicle_id',
                'string',
                'max:20',
                Rule::unique('vehicles', 'plate_number')->ignore($request->vehicle_id),
            ],
            'new_name' => 'required_without:customer_id|string|max:150',
            'new_phone' => 'required_without:customer_id|string|max:20',
            'new_email' => 'nullable|email|max:255',
            'new_brand' => 'nullable|string|max:100',
            'new_model' => 'nullable|string|max:100',
            'new_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'vehicle_size' => 'required|in:small,medium,large',
            'order_date' => 'required|date',
            'complaint' => 'nullable|string|max:2000',
            'discount' => 'nullable|numeric|min:0',
            'other_service_price' => 'nullable|numeric|min:0',
            'status' => 'required|in:draft,open,belum_bayar,selesai',
            'bank_account_id' => 'required_if:status,selesai|nullable|exists:bank_accounts,id',
            'items' => 'nullable|array',
            'items.*.checklist_item_id' => 'required|exists:checklist_items,id',
            'items.*.name' => 'required|string',
            'items.*.price' => 'required|numeric|min:0',
            'materials_used' => 'nullable|array',
            'materials_used.*.material_id' => 'required|exists:materials,id',
            'materials_used.*.name' => 'required|string',
            'materials_used.*.qty' => 'required|integer|min:1',
            'materials_used.*.price' => 'required|numeric|min:0',
            'evidences_work' => 'nullable|array',
            'evidences_work.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
            'evidences_payment' => 'nullable|array',
            'evidences_payment.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
        ]);

        $order = DB::transaction(function () use ($request) {
            $customerId = $request->customer_id;
            $vehicleId = $request->vehicle_id;

            if (!$vehicleId) {
                $customer = Customer::create([
                    'name' => $request->new_name,
                    'phone' => $request->new_phone,
                    'email' => $request->new_email,
                ]);

                $vehicle = $customer->vehicles()->create([
                    'plate_number' => strtoupper($request->new_plate),
                    'brand' => $request->new_brand,
                    'model' => $request->new_model,
                    'vehicle_size' => $request->vehicle_size,
                    'year' => $request->new_year,
                ]);

                $customerId = $customer->id;
                $vehicleId = $vehicle->id;
            } else {
                Vehicle::whereKey($vehicleId)->update(['vehicle_size' => $request->vehicle_size]);
            }

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'invoice_token' => $this->generateInvoiceToken(),
                'order_date' => $request->order_date,
                'customer_id' => $customerId,
                'vehicle_id' => $vehicleId,
                'complaint' => $request->complaint,
                'mileage' => $request->mileage,
                'km_service' => $request->km_service,
                'km_return' => $request->km_return,
                'head_mechanic' => $request->head_mechanic,
                'mechanic' => $request->mechanic,
                'mechanic_number' => $request->mechanic_number,
                'discount' => $request->discount ?? 0,
                'other_service_price' => $request->other_service_price ?? 0,
                'status' => $request->status,
                'bank_account_id' => $request->bank_account_id,
                'created_by' => auth()->id(),
                'evidence_work_paths' => $this->storeOrderEvidences($request, 'evidences_work', 'orders/work-evidences'),
                'evidence_payment_paths' => $this->storeOrderEvidences($request, 'evidences_payment', 'orders/payment-evidences'),
            ]);

            // Save checklist items
            $checklistTotal = 0;
            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    $order->items()->create([
                        'checklist_item_id' => $item['checklist_item_id'],
                        'name' => $item['name'],
                        'price' => $item['price'],
                    ]);
                    $checklistTotal += (float) $item['price'];
                }
            }

            // Save materials
            $materialTotal = 0;
            if ($request->has('materials_used')) {
                foreach ($request->materials_used as $mat) {
                    $subtotal = $mat['qty'] * $mat['price'];
                    $order->materials()->create([
                        'material_id' => $mat['material_id'],
                        'name' => $mat['name'],
                        'qty' => $mat['qty'],
                        'price' => $mat['price'],
                        'subtotal' => $subtotal,
                    ]);
                    $materialTotal += $subtotal;
                }
            }

            // Calculate total
            $subtotal = $checklistTotal + $materialTotal + (float) ($request->other_service_price ?? 0);
            $order->update([
                'subtotal' => $subtotal,
                'total' => $subtotal - ($request->discount ?? 0),
            ]);
            $this->syncOrderFinanceTransaction($order->refresh());

            return $order;
        });

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'redirect' => $order->status === 'selesai' ? route('orders.show', $order) : route('orders.index'),
        ]);
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'vehicle_size' => 'required|in:small,medium,large',
            'order_date' => 'required|date',
            'complaint' => 'nullable|string|max:2000',
            'discount' => 'nullable|numeric|min:0',
            'other_service_price' => 'nullable|numeric|min:0',
            'status' => 'required|in:draft,open,belum_bayar,selesai',
            'bank_account_id' => 'required_if:status,selesai|nullable|exists:bank_accounts,id',
            'items' => 'nullable|array',
            'items.*.checklist_item_id' => 'required|exists:checklist_items,id',
            'items.*.name' => 'required|string',
            'items.*.price' => 'required|numeric|min:0',
            'materials_used' => 'nullable|array',
            'materials_used.*.material_id' => 'required|exists:materials,id',
            'materials_used.*.name' => 'required|string',
            'materials_used.*.qty' => 'required|integer|min:1',
            'materials_used.*.price' => 'required|numeric|min:0',
            'evidences_work' => 'nullable|array',
            'evidences_work.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
            'evidences_payment' => 'nullable|array',
            'evidences_payment.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
        ]);

        DB::transaction(function () use ($request, $order) {
            Vehicle::whereKey($order->vehicle_id)->update(['vehicle_size' => $request->vehicle_size]);

            $order->update([
                'order_date' => $request->order_date,
                'complaint' => $request->complaint,
                'mileage' => $request->mileage,
                'km_service' => $request->km_service,
                'km_return' => $request->km_return,
                'head_mechanic' => $request->head_mechanic,
                'mechanic' => $request->mechanic,
                'mechanic_number' => $request->mechanic_number,
                'discount' => $request->discount ?? 0,
                'other_service_price' => $request->other_service_price ?? 0,
                'status' => $request->status,
                'bank_account_id' => $request->bank_account_id,
                'evidence_work_paths' => array_values(array_filter(array_merge(
                    $order->evidence_work_paths ?? [],
                    $this->storeOrderEvidences($request, 'evidences_work', 'orders/work-evidences')
                ))),
                'evidence_payment_paths' => array_values(array_filter(array_merge(
                    $order->evidence_payment_paths ?? [],
                    $this->storeOrderEvidences($request, 'evidences_payment', 'orders/payment-evidences')
                ))),
            ]);

            $order->items()->delete();
            $order->materials()->delete();
            $this->syncOrderLines($order, $request);
            $this->syncOrderFinanceTransaction($order->refresh());
        });

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'redirect' => $order->status === 'selesai' ? route('orders.show', $order) : route('orders.index'),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['customer', 'vehicle', 'items.checklistItem.category', 'materials', 'creator']);
        return view('operasional.orders.show', compact('order'));
    }

    public function invoice(Order $order): View
    {
        abort_unless($order->status === 'selesai', 404);

        $order->load(['customer', 'vehicle', 'items.checklistItem.category', 'materials', 'creator']);
        return view('operasional.orders.invoice', [
            'order' => $order,
            'invoiceShareUrl' => route('orders.invoice.share', $this->invoiceShareToken($order)),
            'isPublicInvoice' => false,
        ]);
    }

    public function publicInvoice(string $token): View
    {
        $order = Order::where('invoice_token', strtoupper($token))->firstOrFail();

        abort_unless($order->status === 'selesai', 404);

        $order->load(['customer', 'vehicle', 'items.checklistItem.category', 'materials', 'creator']);
        return view('operasional.orders.invoice', [
            'order' => $order,
            'invoiceShareUrl' => route('orders.invoice.share', $this->invoiceShareToken($order)),
            'isPublicInvoice' => true,
        ]);
    }

    public function destroy(Order $order): JsonResponse
    {
        $order->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Search customer by plate number for auto-fill.
     */
    public function searchPlate(Request $request): JsonResponse
    {
        $q = $request->get('q', '');
        $vehicles = Vehicle::with('customer')
            ->where('plate_number', 'ilike', "%{$q}%")
            ->limit(10)
            ->get()
            ->map(fn($v) => [
                'vehicle_id' => $v->id,
                'plate_number' => $v->plate_number,
                'brand' => $v->brand,
                'model' => $v->model,
                'vehicle_size' => $v->vehicle_size,
                'year' => $v->year,
                'customer_id' => $v->customer_id,
                'customer_name' => $v->customer->name,
                'customer_phone' => $v->customer->phone,
                'customer_email' => $v->customer->email,
            ]);

        return response()->json($vehicles);
    }

    private function generateOrderNumber(): string
    {
        $year = now()->format('y');
        $prefix = 'INV-' . $year . '-';
        $last = Order::where('order_number', 'like', $prefix . '%')
            ->orderByDesc('order_number')
            ->value('order_number');

        $seq = $last ? ((int) substr($last, -5)) + 1 : 1;
        return $prefix . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    private function syncOrderLines(Order $order, Request $request): void
    {
        $checklistTotal = 0;
        if ($request->has('items')) {
            foreach ($request->items as $item) {
                $order->items()->create([
                    'checklist_item_id' => $item['checklist_item_id'],
                    'name' => $item['name'],
                    'price' => $item['price'],
                ]);
                $checklistTotal += (float) $item['price'];
            }
        }

        $materialTotal = 0;
        if ($request->has('materials_used')) {
            foreach ($request->materials_used as $mat) {
                $subtotal = $mat['qty'] * $mat['price'];
                $order->materials()->create([
                    'material_id' => $mat['material_id'],
                    'name' => $mat['name'],
                    'qty' => $mat['qty'],
                    'price' => $mat['price'],
                    'subtotal' => $subtotal,
                ]);
                $materialTotal += $subtotal;
            }
        }

        $subtotal = $checklistTotal + $materialTotal + (float) ($request->other_service_price ?? 0);
        $order->update([
            'subtotal' => $subtotal,
            'total' => $subtotal - ($request->discount ?? 0),
        ]);
    }

    private function syncOrderFinanceTransaction(Order $order): void
    {
        $this->reverseFinanceTransaction($order->finance_transaction_id);
        $order->update(['finance_transaction_id' => null]);

        if ($order->status !== 'selesai') {
            return;
        }

        if (!$order->bank_account_id) {
            throw ValidationException::withMessages(['bank_account_id' => 'Bank pembayaran wajib dipilih saat order selesai.']);
        }

        $amount = (float) $order->total;
        if ($amount <= 0) {
            return;
        }

        $bank = BankAccount::lockForUpdate()->findOrFail($order->bank_account_id);
        $item = $this->financeItem('AUTO-ORDER', 'Pembayaran Order', 'Pembayaran order pelanggan');
        $financeTransaction = FinanceTransaction::create([
            'transaction_number' => $this->financeTransactionNumber(),
            'transaction_type' => 'income',
            'transaction_date' => $order->order_date,
            'finance_item_id' => $item->id,
            'bank_account_id' => $order->bank_account_id,
            'activity' => 'Pembayaran Order ' . $order->order_number,
            'description' => 'Pembayaran Order ' . $order->order_number,
            'amount' => $amount,
            'notes' => $order->customer?->name ? 'Pelanggan: ' . $order->customer->name : null,
            'evidence_paths' => $order->evidence_payment_paths,
            'status' => 'disetujui',
            'created_by' => auth()->id(),
            'submitted_by' => auth()->id(),
            'submitted_at' => now(),
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $bank->increment('balance', $amount);
        $order->update(['finance_transaction_id' => $financeTransaction->id]);
    }

    private function reverseFinanceTransaction(?int $financeTransactionId): void
    {
        if (!$financeTransactionId) {
            return;
        }

        $transaction = FinanceTransaction::find($financeTransactionId);
        if (!$transaction || $transaction->status !== 'disetujui') {
            return;
        }

        $bank = BankAccount::lockForUpdate()->findOrFail($transaction->bank_account_id);
        $transaction->transaction_type === 'income'
            ? $bank->decrement('balance', $transaction->amount)
            : $bank->increment('balance', $transaction->amount);
        $transaction->delete();
    }

    private function financeItem(string $code, string $name, string $description): FinanceItem
    {
        $category = FinanceCategory::firstOrCreate(
            ['code' => 'AUTO-OPS'],
            ['name' => 'Operasional Otomatis', 'type' => 'expense', 'description' => 'Kategori otomatis dari modul operasional.']
        );

        return FinanceItem::firstOrCreate(
            ['code' => $code],
            ['finance_category_id' => $category->id, 'name' => $name, 'description' => $description, 'is_active' => true]
        );
    }

    private function financeTransactionNumber(): string
    {
        $prefix = 'KEU-' . now()->format('y') . '-';
        $lastNumber = FinanceTransaction::where('transaction_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('transaction_number')
            ->value('transaction_number');

        $nextNumber = $lastNumber ? ((int) Str::afterLast($lastNumber, '-') + 1) : 1;

        return $prefix . str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
    }

    private function invoiceShareToken(Order $order): string
    {
        if (!$order->invoice_token) {
            $order->forceFill(['invoice_token' => $this->generateInvoiceToken()])->saveQuietly();
        }

        return $order->invoice_token;
    }

    private function generateInvoiceToken(): string
    {
        do {
            $token = Str::upper(Str::random(10));
        } while (Order::where('invoice_token', $token)->exists());

        return $token;
    }

    private function storeOrderEvidences(Request $request, string $inputName, string $directory): array
    {
        $files = $request->file($inputName, []);
        if ($files instanceof UploadedFile) {
            $files = [$files];
        }

        return collect($files)
            ->filter()
            ->map(fn (UploadedFile $file) => $this->storeOrderEvidence($file, $directory))
            ->values()
            ->all();
    }

    private function storeOrderEvidence(UploadedFile $file, string $directory): string
    {
        if (!Str::startsWith((string) $file->getMimeType(), 'image/')) {
            return $file->store($directory, 'r2');
        }

        $image = imagecreatefromstring(file_get_contents($file->getRealPath()));
        if (!$image) {
            return $file->store($directory, 'r2');
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

        $path = $directory . '/' . Str::uuid() . '.webp';
        Storage::disk('r2')->put($path, $contents, [
            'visibility' => 'public',
            'ContentType' => 'image/webp',
        ]);

        return $path;
    }
}
