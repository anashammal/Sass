@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-danger"><i class="fas fa-tools me-2"></i> معالجة التنبيهات والمخزون</h3>
                <a href="{{ route('store.dashboard') }}" class="btn btn-secondary btn-sm">العودة للرئيسية</a>
            </div>

            @if (session('success'))
                <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white p-0 border-bottom-0">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        {{-- تبويب الصلاحية --}}
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold text-danger py-3 px-4" id="expired-tab" data-bs-toggle="tab" data-bs-target="#expired" type="button" role="tab">
                                <i class="fas fa-calendar-times me-2"></i> انتهاء الصلاحية
                                <span class="badge bg-danger ms-2">{{ $expiredBatches->count() }}</span>
                            </button>
                        </li>
                        {{-- تبويب المخزون --}}
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold text-warning text-dark py-3 px-4" id="stock-tab" data-bs-toggle="tab" data-bs-target="#stock" type="button" role="tab">
                                <i class="fas fa-boxes me-2"></i> نواقص المخزون
                                <span class="badge bg-warning text-dark ms-2">{{ $lowStockProducts->count() }}</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="tab-content" id="myTabContent">
                        
                        {{-- ================= 1. قسم الصلاحية ================= --}}
                        <div class="tab-pane fade show active" id="expired" role="tabpanel">
                            @if($expiredBatches->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>المنتج</th>
                                                <th>الكمية الحالية</th>
                                                <th>تاريخ الانتهاء</th>
                                                <th>الإجراء المطلوب</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($expiredBatches as $batch)
                                                <tr>
                                                    <td class="fw-bold">{{ $batch->product->name_ar }}</td>
                                                    <td>{{ $batch->quantity }}</td>
                                                    <td class="{{ $batch->expiry_date < now() ? 'text-danger fw-bold' : 'text-warning text-dark' }}">
                                                        {{ $batch->expiry_date }}
                                                        <br><small>{{ \Carbon\Carbon::parse($batch->expiry_date)->diffForHumans() }}</small>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            {{-- زر الإتلاف --}}
                                                            <form action="{{ route('store.products.expired.dispose') }}" method="POST" onsubmit="return confirm('تأكد من إتلاف المنتج فعلياً. هل تريد المتابعة؟')">
                                                                @csrf
                                                                <input type="hidden" name="batch_id" value="{{ $batch->id }}">
                                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                                    <i class="fas fa-trash me-1"></i> إتلاف (شطب)
                                                                </button>
                                                            </form>
                                                            
                                                            {{-- زر التمديد (إذا كان مسموحاً) --}}
                                                            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#renewModal{{$batch->id}}">
                                                                <i class="fas fa-sync me-1"></i> تمديد/تصحيح
                                                            </button>
                                                        </div>

                                                        {{-- مودال التمديد --}}
                                                        <div class="modal fade" id="renewModal{{$batch->id}}" tabindex="-1">
                                                            <div class="modal-dialog">
                                                                <form action="{{ route('store.products.expired.renew') }}" method="POST" class="modal-content">
                                                                    @csrf
                                                                    <input type="hidden" name="batch_id" value="{{ $batch->id }}">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">تصحيح تاريخ: {{ $batch->product->name_ar }}</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <label>التاريخ الجديد:</label>
                                                                        <input type="date" name="new_expiry_date" class="form-control" required min="{{ date('Y-m-d') }}">
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="submit" class="btn btn-primary">حفظ التاريخ الجديد</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                                    <h5>لا توجد منتجات منتهية الصلاحية.</h5>
                                </div>
                            @endif
                        </div>

                        {{-- ================= 2. قسم المخزون (الجديد) ================= --}}
                        <div class="tab-pane fade" id="stock" role="tabpanel">
                            @if($lowStockProducts->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>المنتج</th>
                                                <th>المخزون الحالي</th>
                                                <th>حد التنبيه</th>
                                                <th>إجراء سريع (تعديل المخزون)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($lowStockProducts as $product)
                                                <tr>
                                                    <td class="fw-bold">{{ $product->name_ar }}</td>
                                                    <td>
                                                        @if($product->current_stock <= 0)
                                                            <span class="badge bg-danger">نفذت (0)</span>
                                                        @else
                                                            <span class="badge bg-warning text-dark">{{ (float)$product->current_stock }}</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ (float)$product->alert_quantity }}</td>
                                                    <td>
                                                        <form action="{{ route('store.products.quick_update_stock') }}" method="POST" class="d-flex align-items-center gap-2 quick-stock-form">
                                                            @csrf
                                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                            
                                                            <div class="input-group input-group-sm" style="width: 150px;">
                                                                <input type="number" name="new_stock" class="form-control" 
                                                                       placeholder="الجديد" step="0.01" required>
                                                                <button type="submit" class="btn btn-success" title="حفظ">
                                                                    <i class="fas fa-save"></i>
                                                                </button>
                                                            </div>
                                                            <small class="text-muted status-msg"></small>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                                    <h5>المخزون ممتاز، لا توجد نواقص.</h5>
                                </div>
                            @endif
                        </div>

                    </div> {{-- End Tab Content --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // تفعيل التبويبات (Bootstrap 5)
    var triggerTabList = [].slice.call(document.querySelectorAll('#myTab button'))
    triggerTabList.forEach(function (triggerEl) {
        var tabTrigger = new bootstrap.Tab(triggerEl)
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault()
            tabTrigger.show()
        })
    });

    // تحديث المخزون بدون إعادة تحميل الصفحة (AJAX)
    document.querySelectorAll('.quick-stock-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            let btn = this.querySelector('button');
            let msg = this.querySelector('.status-msg');
            let input = this.querySelector('input[name="new_stock"]');
            
            let originalBtnHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;

            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest' // ليفهم السيرفر أنه طلب AJAX
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    msg.innerHTML = '<span class="text-success fw-bold">تم!</span>';
                    // تحديث الرقم الظاهر في الجدول (اختياري)
                    let row = this.closest('tr');
                    let badge = row.querySelector('.badge');
                    if(badge) {
                        badge.className = 'badge bg-success';
                        badge.innerText = input.value;
                    }
                } else {
                    msg.innerHTML = '<span class="text-danger">خطأ</span>';
                }
            })
            .catch(err => {
                msg.innerHTML = '<span class="text-danger">فشل</span>';
            })
            .finally(() => {
                btn.innerHTML = originalBtnHtml;
                btn.disabled = false;
                setTimeout(() => { msg.innerHTML = ''; }, 3000);
            });
        });
    });
</script>
@endsection