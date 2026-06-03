{{-- Welcome banner — Garasi Hobby --}}
<div class="card card-flush mb-5 mb-xl-10 bgi-no-repeat bgi-size-cover bgi-position-y-bottom bg-light-primary">
    <div class="card-body d-flex flex-column flex-md-row align-items-center justify-content-between p-7">
        <div class="d-flex align-items-center">
            <div class="symbol symbol-50px me-5">
                <span class="symbol-label bg-primary">
                    <i class="ki-outline ki-rocket fs-2x text-white"></i>
                </span>
            </div>
            <div>
                <h2 class="fs-2 fw-bold text-gray-900 mb-1">
                    Selamat datang, {{ auth()->user()->name }}!
                </h2>
                <div class="fs-6 text-gray-700 fw-semibold">
                    {{ auth()->user()->jabatan ?? '-' }}
                    @foreach(auth()->user()->getRoleNames() as $role)
                        <span class="badge badge-light-primary fs-8 fw-semibold ms-2">{{ $role }}</span>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4 mt-md-0">
            @can('orders.create')
                <a href="#" class="btn btn-sm btn-primary">
                    <i class="ki-outline ki-plus fs-3"></i> Order Baru
                </a>
            @endcan
            @can('materials.purchase')
                <a href="#" class="btn btn-sm btn-light-primary">
                    <i class="ki-outline ki-purchase fs-3"></i> Pembelian
                </a>
            @endcan
        </div>
    </div>
</div>
