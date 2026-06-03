---
inclusion: fileMatch
fileMatchPattern: '*.php'
---

# Coding Standards — Garasi Hobby (PHP/Laravel)

## Prinsip Umum
- **Single Responsibility**: 1 class = 1 tujuan. Controller tidak melakukan transaksi DB langsung.
- **Tipis di Controller, gemuk di Service**: business logic di `app/Services/`.
- **Validasi via FormRequest**, bukan di controller.
- **Authorization via Policy + Permission Spatie**, bukan if-else role di controller.
- **Transaction wrapping** untuk operasi multi-tabel: `DB::transaction(fn() => ...)`.

## Controller Pattern
```php
public function store(StoreOrderRequest $request, OrderService $service)
{
    $order = $service->create($request->validated(), auth()->user());
    return redirect()
        ->route('orders.show', $order)
        ->with('success', 'Order berhasil dibuat.');
}
```

## Service Pattern
```php
class OrderService
{
    public function __construct(
        private StockService $stockService,
        private NotificationService $notificationService,
    ) {}

    public function create(array $data, User $user): Order
    {
        return DB::transaction(function () use ($data, $user) {
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'order_date'   => $data['order_date'],
                // ...
                'created_by'   => $user->id,
            ]);

            foreach ($data['items'] as $item) {
                $order->items()->create($item);
            }

            $order->update(['total' => $order->items->sum('subtotal') - ($data['discount'] ?? 0)]);

            $this->notificationService->newOrder($order);

            return $order;
        });
    }
}
```

## FormRequest Pattern
```php
class StoreMaterialPurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('materials.purchase');
    }

    public function rules(): array
    {
        return [
            'purchase_date'        => ['required', 'date'],
            'note'                 => ['nullable', 'string', 'max:1000'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.material_id'  => ['required', 'exists:materials,id'],
            'items.*.qty'          => ['required', 'integer', 'min:1'],
            'items.*.unit_price'   => ['required', 'numeric', 'min:0'],
            'evidences'            => ['nullable', 'array'],
            'evidences.*'          => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'purchase_date.required' => 'Tanggal pembelian wajib diisi.',
            'items.required'         => 'Minimal 1 item material.',
            'items.*.qty.min'        => 'Qty minimal 1.',
            'evidences.*.mimes'      => 'Eviden harus berformat jpg, jpeg, png, atau pdf.',
        ];
    }
}
```

## Model Pattern
```php
class Order extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'order_number', 'order_date', 'customer_id', 'vehicle_id',
        'promo_package_id', 'complaint', 'subtotal', 'discount', 'total',
        'status', 'created_by', 'qc_approved_at', 'qc_approved_by', 'paid_at',
    ];

    protected $casts = [
        'order_date'      => 'date',
        'qc_approved_at'  => 'datetime',
        'paid_at'         => 'datetime',
        'subtotal'        => 'decimal:2',
        'discount'        => 'decimal:2',
        'total'           => 'decimal:2',
    ];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function vehicle()  { return $this->belongsTo(Vehicle::class); }
    public function items()    { return $this->hasMany(OrderItem::class); }
    public function payments() { return $this->hasMany(OrderPayment::class); }
    public function evidences(){ return $this->hasMany(OrderEvidence::class); }

    public function scopeStatus($q, string $status) { return $q->where('status', $status); }
    public function scopeToday($q) { return $q->whereDate('order_date', today()); }

    public function getIsFullyPaidAttribute(): bool
    {
        return $this->payments()->sum('amount') >= $this->total;
    }
}
```

Aturan tambahan:
- Tidak ada query bisnis di Model. Pakai scope sederhana saja.
- Casting selalu eksplisit untuk decimal, date, json.
- Relationship dengan return type implisit (sesuai gaya Laravel).

## Notification Pattern
Setiap notifikasi 1 file di `app/Notifications/`.
```php
class MaterialPurchaseSubmitted extends Notification
{
    use Queueable;

    public function __construct(public MaterialPurchase $purchase) {}

    public function via($notifiable): array { return ['database']; }

    public function toDatabase($notifiable): array
    {
        return [
            'title'    => 'Ada pengajuan pembelian baru',
            'message'  => "Pengaju: {$this->purchase->submittedBy->name}, Total: Rp " . number_format($this->purchase->total, 0, ',', '.'),
            'url'      => route('material-purchases.show', $this->purchase),
            'icon'     => 'shopping-cart',
            'category' => 'material_purchase',
        ];
    }
}
```

Trigger:
```php
$superadmins = User::role('Superadmin')->get();
Notification::send($superadmins, new MaterialPurchaseSubmitted($purchase));
```

## Migration Pattern
- 1 migration per tabel, jangan campur multi-tabel kecuali alter.
- Foreign key pakai `foreignId('xxx_id')->constrained()->cascadeOnDelete()` bila perlu cascade.
- Index eksplisit untuk kolom yang sering difilter/sort.
- Pakai `decimal(14,2)` untuk uang. Jangan `float`.
- Enum simpan sebagai `string` (varchar) + dokumentasikan nilainya. Hindari ENUM PostgreSQL (susah diubah).

Contoh:
```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->string('order_number', 30)->unique();
    $table->date('order_date')->index();
    $table->foreignId('customer_id')->constrained();
    $table->foreignId('vehicle_id')->constrained();
    $table->foreignId('promo_package_id')->nullable()->constrained();
    $table->text('complaint')->nullable();
    $table->decimal('subtotal', 14, 2)->default(0);
    $table->decimal('discount', 14, 2)->default(0);
    $table->decimal('total', 14, 2)->default(0);
    $table->string('status', 20)->default('open')->index();
    $table->foreignId('created_by')->constrained('users');
    $table->timestamp('qc_approved_at')->nullable();
    $table->foreignId('qc_approved_by')->nullable()->constrained('users');
    $table->timestamp('paid_at')->nullable();
    $table->timestamps();
    $table->softDeletes();
});
```

## Upload File ke R2
```php
class R2UploadService
{
    public function store(UploadedFile $file, string $folder): array
    {
        $path = $file->store($folder, 'r2');
        return [
            'disk'          => 'r2',
            'path'          => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type'     => $file->getMimeType(),
            'size'          => $file->getSize(),
            'url'           => Storage::disk('r2')->url($path),
        ];
    }

    public function delete(string $path): void
    {
        Storage::disk('r2')->delete($path);
    }
}
```

## Penomoran (Order, Invoice, PO)
Service generator dengan locking untuk avoid race condition:
```php
public function generateOrderNumber(): string
{
    return DB::transaction(function () {
        $prefix = 'ORD/' . now()->format('Ym') . '/';
        $last = Order::where('order_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('order_number')
            ->value('order_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    });
}
```

## Logging & Error Handling
- Pakai `Log::info`, `Log::error` untuk event penting (order created, purchase approved, stock changed).
- Catch exception di service hanya untuk wrap pesan business-friendly:
```php
try {
    DB::transaction(...);
} catch (\Throwable $e) {
    Log::error('Gagal create order', ['error' => $e->getMessage(), 'data' => $data]);
    throw new \DomainException('Gagal menyimpan order. ' . $e->getMessage());
}
```

## Testing
- Setiap service punya 1 test happy path + 1 test edge case minimal.
- Pakai `RefreshDatabase` trait.
- Factory untuk semua model utama.

## Larangan
- ❌ N+1 query. Pakai `with()` eager loading.
- ❌ Raw SQL kecuali untuk reporting kompleks (dokumentasikan kenapa).
- ❌ Inline business logic di Blade.
- ❌ Helper global custom kalau bisa pakai Service/Action.
- ❌ Hardcode role/permission name. Bikin konstan di `app/Constants/Permissions.php` jika perlu.
