@extends('layouts.app')

@section('content')
<div class="container">
    {{-- 1. بطاقات الإحصائيات العلوية --}}
    <div class="row mb-4 text-center">
        <div class="col-md-4">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body">
                    <h3>{{ $stats['products_count'] ?? 0 }}</h3>
                    <p class="mb-0"><i class="fas fa-boxes"></i> إجمالي المنتجات</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card {{ ($stats['low_stock_count'] ?? 0) > 0 ? 'bg-warning text-dark' : 'bg-success text-white' }} shadow-sm">
                <div class="card-body">
                    <h3 class="fw-bold">{{ $stats['low_stock_count'] ?? 0 }}</h3>
                    <p class="mb-0"><i class="fas fa-exclamation-triangle"></i> نواقص المخزون</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card {{ ($expiredBatches->count() ?? 0) > 0 ? 'bg-danger text-white' : 'bg-success text-white' }} shadow-sm">
                <div class="card-body">
                    <h3 class="fw-bold">{{ $expiredBatches->count() ?? 0 }}</h3>
                    <p class="mb-0"><i class="fas fa-calendar-times"></i> منتجات منتهية/قريبة</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. المحتوى الرئيسي --}}
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-home me-2 text-primary"></i> لوحة تحكم المتجر: {{ Auth::user()->store->name }}
                </div>

                <div class="card-body text-center py-5">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <img src="{{ Auth::user()->store->logo_url }}" alt="Logo" class="mb-3" style="max-height: 100px;">
                    <h3>أهلاً بك في متجرك!</h3>
                    <p class="text-muted">هذه الصفحة خاصة بالدومين الفرعي: <code>{{ request()->route('subdomain') }}</code></p>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <a href="{{ route('store.pos.index') }}" class="btn btn-lg btn-primary">
                            <i class="fas fa-cash-register me-2"></i> نقطة البيع (POS)
                        </a>
                        <a href="{{ route('store.products.index') }}" class="btn btn-lg btn-outline-dark">
                            <i class="fas fa-box-open me-2"></i> إدارة المنتجات
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 3. النافذة المنبثقة للتنبيهات (Modal) --}}
<div class="modal fade" id="alertsModal" tabindex="-1" aria-labelledby="alertsModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title fw-bold" id="alertsModalLabel">
            <i class="fas fa-bell me-2"></i> تنبيهات هامة للمخزون والصلاحية
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="markPopupSeen()"></button>
      </div>
      <div class="modal-body">
        
        {{-- أ) قسم المنتجات المنتهية --}}
        @if(isset($expiredBatches) && $expiredBatches->count() > 0)
            <div class="alert alert-danger border-danger">
                <h6 class="fw-bold border-bottom pb-2 border-danger text-danger">
                    <i class="fas fa-calendar-times me-1"></i> منتجات منتهية أو قاربت على الانتهاء:
                </h6>
                <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            @foreach($expiredBatches as $batch)
                                <tr>
                                    <td><strong>{{ $batch->product->name_ar }}</strong></td>
                                    <td><span class="badge bg-danger" dir="ltr">{{ $batch->expiry_date }}</span></td>
                                    <td class="text-muted small">الكمية: {{ $batch->quantity }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- ب) قسم المخزون المنخفض --}}
        @if(isset($lowStockProducts) && $lowStockProducts->count() > 0)
            <div class="alert alert-warning border-warning text-dark mt-3">
                <h6 class="fw-bold border-bottom pb-2 border-warning text-dark">
                    <i class="fas fa-boxes me-1"></i> منتجات مخزونها منخفض أو نفذت:
                </h6>
                <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                    <table class="table table-sm table-borderless mb-0 text-dark">
                        <tbody>
                            @foreach($lowStockProducts as $prod)
                                <tr>
                                    <td><strong>{{ $prod->name_ar }}</strong></td>
                                    <td>
                                        @if($prod->current_stock <= 0)
                                            <span class="badge bg-dark">نفذت الكمية</span>
                                        @else
                                            <span class="badge bg-warning text-dark">باقي: {{ (float)$prod->current_stock }}</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">حد التنبيه: {{ (float)$prod->alert_quantity }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if((!isset($expiredBatches) || $expiredBatches->count() == 0) && (!isset($lowStockProducts) || $lowStockProducts->count() == 0))
            <div class="text-center py-4 text-success">
                <i class="fas fa-check-circle fa-3x mb-3"></i>
                <h5>ممتاز! وضع المخزون سليم.</h5>
            </div>
        @endif

      </div>
     <div class="modal-footer bg-light">
        {{-- ✅ هذا الزر سيأخذك للصفحة المدمجة (صلاحية + مخزون) --}}
        <a href="{{ route('store.products.expired_manager') }}" class="btn btn-outline-primary btn-sm">
            عرض المنتجات ومعالجتها
        </a>
        
        {{-- زر فهمت يغلق النافذة فقط --}}
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" onclick="markPopupSeen()">
            فهمت، إغلاق
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // التحقق من المتغير القادم من الكونترولر
        @if(isset($showPopup) && $showPopup === true)
            var myModal = new bootstrap.Modal(document.getElementById('alertsModal'));
            myModal.show();
        @endif
    });

    function markPopupSeen() {
        // إرسال طلب للسيرفر لعدم إظهار النافذة مرة أخرى في هذه الجلسة
        fetch('{{ route("store.settings.mark_popup_seen") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        });
    }
</script>
@endsection