@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-11">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold text-dark mb-0"><i class="fas fa-boxes me-2 text-primary"></i> إدارة صلاحية المخزون المتقدمة</h3>
                    <p class="text-muted mb-0">لوحة تحكم لتتبع المنتجات المنتهية، القريبة من الانتهاء، والسليمة.</p>
                </div>
                <a href="{{ route('store.dashboard') }}" class="btn btn-secondary shadow-sm">
                    <i class="fas fa-arrow-right me-1"></i> العودة للرئيسية
                </a>
            </div>

            @if (session('success'))
                <div class="alert alert-success shadow-sm rounded-3 border-0 d-flex align-items-center">
                    <i class="fas fa-check-circle fa-2x me-3"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif
             @if (session('error'))
                <div class="alert alert-danger shadow-sm rounded-3 border-0 d-flex align-items-center">
                    <i class="fas fa-times-circle fa-2x me-3"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            <div class="card shadow border-0 rounded-4 overflow-hidden mb-5">
                <div class="card-header bg-white p-0 border-bottom-0">
                    <ul class="nav nav-tabs nav-justified" id="managerTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active py-3 fw-bold text-danger border-0 border-bottom border-3 border-danger" id="expiration-tab" data-bs-toggle="tab" data-bs-target="#expiration" type="button">
                                <i class="fas fa-history me-2"></i> متابعة الصلاحية
                                @if($productGroups->count() > 0) <span class="badge bg-danger ms-1">{{ $productGroups->count() }} منتج</span> @endif
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-3 fw-bold text-warning text-dark border-0" id="lowstock-tab" data-bs-toggle="tab" data-bs-target="#lowstock" type="button">
                                <i class="fas fa-battery-quarter me-2"></i> نواقص المخزون
                                @if($lowStockProducts->count() > 0) <span class="badge bg-warning text-dark ms-1">{{ $lowStockProducts->count() }}</span> @endif
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body bg-light">
                    <div class="tab-content">
                        
                        {{-- ================= TAB 1: Expiry Management ================= --}}
                        <div class="tab-pane fade show active" id="expiration" role="tabpanel">
                            
                            @if($productGroups->count() > 0)
                                <div class="row g-4">
                                    @foreach($productGroups as $group)
                                        @php 
                                            $product = $group['product'];
                                            $total = $group['total_stock'];
                                            $expiredCount = $group['expired']->sum('quantity');
                                            $nearCount = $group['near_expiry']->sum('quantity');
                                            $validCount = $group['valid']->sum('quantity');

                                            // حساب النسب للشريط
                                            $expPercent = $total > 0 ? ($expiredCount / $total) * 100 : 0;
                                            $nearPercent = $total > 0 ? ($nearCount / $total) * 100 : 0;
                                            $validPercent = $total > 0 ? ($validCount / $total) * 100 : 0;
                                        @endphp

                                        <div class="col-lg-6">
                                            <div class="card h-100 border-0 shadow-sm product-expiry-card">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                                        <div>
                                                            <h5 class="fw-bold text-dark mb-1">{{ $product->name_ar }}</h5>
                                                            <small class="text-muted">الباركود: {{ $product->sku }} | الوحدة: {{ $product->baseUnit->unit_name ?? '-' }}</small>
                                                        </div>
                                                        <h3 class="fw-bold mb-0 text-primary">{{ (float)$total }}</h3>
                                                    </div>

                                                    {{-- شريط الحالة المرئي --}}
                                                    <div class="progress mb-3" style="height: 12px; border-radius: 6px;">
                                                        @if($expPercent > 0)
                                                            <div class="progress-bar bg-danger" style="width: {{ $expPercent }}%" title="منتهي: {{ $expiredCount }}"></div>
                                                        @endif
                                                        @if($nearPercent > 0)
                                                            <div class="progress-bar bg-warning" style="width: {{ $nearPercent }}%" title="قريب الانتهاء: {{ $nearCount }}"></div>
                                                        @endif
                                                        @if($validPercent > 0)
                                                            <div class="progress-bar bg-success" style="width: {{ $validPercent }}%" title="سليم: {{ $validCount }}"></div>
                                                        @endif
                                                    </div>

                                                    {{-- تفاصيل الأرقام --}}
                                                    <div class="d-flex justify-content-between text-center mb-3">
                                                        <div class="px-2">
                                                            <div class="small text-danger fw-bold"><i class="fas fa-times-circle"></i> منتهي</div>
                                                            <div class="fs-5 fw-bold text-danger">{{ (float)$expiredCount }}</div>
                                                        </div>
                                                        <div class="px-2 border-start border-end">
                                                            <div class="small text-warning text-dark fw-bold"><i class="fas fa-exclamation-triangle"></i> قريب</div>
                                                            <div class="fs-5 fw-bold text-dark">{{ (float)$nearCount }}</div>
                                                        </div>
                                                        <div class="px-2">
                                                            <div class="small text-success fw-bold"><i class="fas fa-check-circle"></i> سليم</div>
                                                            <div class="fs-5 fw-bold text-success">{{ (float)$validCount }}</div>
                                                        </div>
                                                    </div>

                                                    {{-- زر الإجراءات --}}
                                                    <button class="btn btn-outline-dark w-100" data-bs-toggle="modal" data-bs-target="#actionModal{{ $product->id }}">
                                                        <i class="fas fa-cog me-1"></i> إدارة الصلاحية (إتلاف / تمديد)
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- ================= MODAL FOR PRODUCT ACTION ================= --}}
                                        <div class="modal fade" id="actionModal{{ $product->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-light">
                                                        <h5 class="modal-title fw-bold">إدارة: {{ $product->name_ar }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-0">
                                                        <ul class="nav nav-pills nav-fill p-3 bg-white border-bottom" role="tablist">
                                                            <li class="nav-item">
                                                                <button class="nav-link active text-danger" data-bs-toggle="pill" data-bs-target="#dispose-{{ $product->id }}">
                                                                    <i class="fas fa-trash-alt me-1"></i> إتلاف مخزون
                                                                </button>
                                                            </li>
                                                            <li class="nav-item">
                                                                <button class="nav-link text-primary" data-bs-toggle="pill" data-bs-target="#extend-{{ $product->id }}">
                                                                    <i class="fas fa-calendar-plus me-1"></i> تمديد/تصحيح تواريخ
                                                                </button>
                                                            </li>
                                                        </ul>

                                                        <div class="tab-content p-4">
                                                            {{-- TAB 1: DISPOSE --}}
                                                            <div class="tab-pane fade show active" id="dispose-{{ $product->id }}">
                                                                <form action="{{ route('store.products.dispose') }}" method="POST" enctype="multipart/form-data">
                                                                    @csrf
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-bold">اختر الدفعة (Batch) المراد إتلافها:</label>
                                                                        <select name="batch_id" class="form-select batch-select" required onchange="updateMaxQty(this)" data-unit-name="{{ $product->baseUnit->unit_name ?? '' }}">
                                                                            <option value="">-- اختر الدفعة --</option>
                                                                            @foreach($group['expired'] as $batch)
                                                                                <option value="{{ $batch->id }}" data-max="{{ $batch->quantity }}" class="text-danger">
                                                                                     منتهية [{{ $batch->expiry_date->format('Y-m-d') }}] - الكمية: {{ (float)$batch->quantity }}
                                                                                </option>
                                                                            @endforeach
                                                                            @foreach($group['near_expiry'] as $batch)
                                                                                <option value="{{ $batch->id }}" data-max="{{ $batch->quantity }}" class="text-warning text-dark">
                                                                                     قريبة [{{ $batch->expiry_date->format('Y-m-d') }}] - الكمية: {{ (float)$batch->quantity }}
                                                                                </option>
                                                                            @endforeach
                                                                            @foreach($group['valid'] as $batch)
                                                                                <option value="{{ $batch->id }}" data-max="{{ $batch->quantity }}" class="text-success">
                                                                                     سليمة [{{ $batch->expiry_date->format('Y-m-d') }}] - الكمية: {{ (float)$batch->quantity }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>

                                                                    <div class="row mb-3">
                                                                        <div class="col-md-6">
                                                                            <label class="form-label">الكمية للإتلاف:</label>
                                                                            <input type="number" name="quantity" class="form-control qty-input" step="0.01" min="0.01" required>
                                                                            <div class="form-text text-muted">الحد الأقصى: <span class="max-qty-display">0</span></div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <label class="form-label">السبب:</label>
                                                                            <input type="text" name="reason" class="form-control" placeholder="مثلاً: انتهاء الصلاحية، تلف، كسر.." required>
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label class="form-label text-danger fw-bold star">صورة إثبات الإتلاف (مطلوب):</label>
                                                                        <input type="file" name="proof_image" class="form-control" accept="image/*" required>
                                                                        <div class="form-text">يرجى تصوير المنتجات أثناء الإتلاف أو في مكان الإتلاف لتوثيق الحالة.</div>
                                                                    </div>

                                                                    <button type="submit" class="btn btn-danger w-100" onclick="return confirm('هل أنت متأكد؟ هذا الإجراء سيخصم الكمية ويسجلها كتالف.')">
                                                                        تأكيد الإتلاف
                                                                    </button>
                                                                </form>
                                                            </div>

                                                            {{-- TAB 2: EXTEND --}}
                                                            <div class="tab-pane fade" id="extend-{{ $product->id }}">
                                                                <form action="{{ route('store.products.extend') }}" method="POST" enctype="multipart/form-data">
                                                                    @csrf
                                                                    <div class="alert alert-info py-2 small">
                                                                        <i class="fas fa-info-circle"></i> يمكنك تمديد صلاحية جزء من الدفعة. سيقوم النظام بإنشاء دفعة جديدة للجزء الممدد تلقائياً.
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-bold">اختر الدفعة:</label>
                                                                        <select name="batch_id" class="form-select batch-select" required onchange="updateMaxQty(this)" data-unit-name="{{ $product->baseUnit->unit_name ?? '' }}">
                                                                            <option value="">-- اختر --</option>
                                                                            {{-- نعرض كل الدفعات هنا أيضاً --}}
                                                                            @foreach($product->batches as $batch)
                                                                                 <option value="{{ $batch->id }}" data-max="{{ $batch->quantity }}">
                                                                                     [{{ $batch->expiry_date->format('Y-m-d') }}] - الكمية: {{ (float)$batch->quantity }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>

                                                                    <div class="row mb-3">
                                                                        <div class="col-md-6">
                                                                            <label class="form-label">الكمية لتصحيح تاريخها:</label>
                                                                            <input type="number" name="quantity" class="form-control qty-input" step="0.01" min="0.01" required>
                                                                            <div class="form-text">الحد الأقصى: <span class="max-qty-display">0</span></div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <label class="form-label">تاريخ الصلاحية الجديد:</label>
                                                                            <input type="date" name="new_date" class="form-control enhanced-date-input" required min="{{ date('Y-m-d') }}">
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-bold">صورة المنتج/التاريخ الجديد (إثبات):</label>
                                                                        <input type="file" name="proof_image" class="form-control" accept="image/*" required>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label class="form-label">سبب التعديل:</label>
                                                                        <input type="text" name="reason" class="form-control" placeholder="مثلاً: خطأ في الإدخال السابق، تمديد من الشركة الأم.." required>
                                                                    </div>

                                                                    <button type="submit" class="btn btn-primary w-100">
                                                                        حفظ التعديلات
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <img src="https://cdn-icons-png.flaticon.com/512/4076/4076478.png" width="120" class="mb-3 opacity-50">
                                    <h4 class="text-muted">ممتاز! لا توجد منتجات منتهية أو قريبة من الانتهاء حالياً.</h4>
                                </div>
                            @endif

                        </div>

                        {{-- ================= TAB 2: Low Stock (Kept similar but styled) ================= --}}
                        <div class="tab-pane fade" id="lowstock" role="tabpanel">
                             @if($lowStockProducts->count() > 0)
                                <div class="table-responsive bg-white rounded-3 shadow-sm p-3">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>المنتج</th>
                                                <th>المخزون الحالي</th>
                                                <th>حد التنبيه</th>
                                                <th width="250">تحديث سريع</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($lowStockProducts as $product)
                                                <tr>
                                                    <td class="fw-bold">{{ $product->name_ar }}</td>
                                                    <td>
                                                        @if($product->current_stock <= 0)
                                                            <span class="badge bg-danger rounded-pill px-3">نفذت (0)</span>
                                                        @else
                                                            <span class="badge bg-warning text-dark rounded-pill px-3 fs-6">{{ (float)$product->current_stock }}</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ (float)$product->alert_quantity }}</td>
                                                    <td>
                                                        <form action="{{ route('store.products.quick_update_stock') }}" method="POST" class="d-flex gap-2 quick-stock-form">
                                                            @csrf
                                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                            <input type="number" name="new_stock" class="form-control form-control-sm" placeholder="الجديد" step="0.01" required>
                                                            <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-save"></i></button>
                                                            <span class="status-msg"></span>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-check-double fa-3x mb-3 text-success"></i>
                                    <h5>المخزون كامل، لا توجد نواقص.</h5>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // سكربت بسيط لتحديث الحد الأقصى عند اختيار الدفعة
    function updateMaxQty(selectElement) {
        const option = selectElement.options[selectElement.selectedIndex];
        const max = option.getAttribute('data-max');
        const form = selectElement.closest('form');
        const display = form.querySelector('.max-qty-display');
        const input = form.querySelector('.qty-input');
        
        // ✅ تحديد الدقة بناء على الوحدة
        const unitName = selectElement.getAttribute('data-unit-name') || '';
        const isKilo = /kilo|kg|كيلو|كغ/i.test(unitName);
        input.step = isKilo ? "0.001" : "1";

        if (max) {
            display.innerText = parseFloat(max);
            input.max = max;
            input.value = Math.min(input.value, max); // Ensure current value doesn't exceed new max
        } else {
            display.innerText = '0';
            input.max = '';
        }
    }

    // AJAX for quick stock update (same as before)
    document.querySelectorAll('.quick-stock-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            let btn = this.querySelector('button');
            let msg = this.querySelector('.status-msg');
            let originalHtml = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            })
            .then(r => r.json())
            .then(data => {
                if(data.status === 'success') {
                    msg.innerHTML = '<i class="fas fa-check text-success"></i>';
                    setTimeout(() => location.reload(), 1000); // Reload to reflect changes
                } else msg.innerHTML = '❌';
            })
            .catch(() => msg.innerHTML = '❌')
            .finally(() => {
                 btn.disabled = false;
                 btn.innerHTML = originalHtml;
            });
        });
    });
</script>

<style>
    .product-expiry-card {
        transition: transform 0.2s;
        border-left: 5px solid #0d705d !important; /* لون جانبي مميز */
    }
    .product-expiry-card:hover {
        transform: translateY(-5px);
    }
    .nav-tabs .nav-link.active {
        background-color: transparent;
        color: #dc3545; 
    }
</style>
@endsection