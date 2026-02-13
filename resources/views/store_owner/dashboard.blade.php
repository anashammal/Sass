@extends('layouts.app')

@section('content')
<div class="container">
    {{-- 1. بطاقات الإحصائيات العلوية --}}
    <div class="row mb-4 g-3">
        <div class="col-md-4">
            <div class="card kpi-card kpi-primary h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="kpi-icon-container me-3">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div>
                        <div class="kpi-label">
                            @if(Auth::user()->store->type == 'restaurant')
                                {{ __('إجمالي قائمة الطعام (المنيو)') }}
                            @else
                                {{ __('إجمالي المنتجات') }}
                            @endif
                        </div>
                        <div class="kpi-value english-num">{{ $stats['products_count'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            @php $isLow = ($stats['low_stock_count'] ?? 0) > 0; @endphp
            <div class="card kpi-card {{ $isLow ? 'kpi-warning' : 'kpi-success' }} h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="kpi-icon-container me-3">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <div class="kpi-label">
                            @if(Auth::user()->store->type == 'restaurant')
                                {{ __('خامات ومواد ناقصة') }}
                            @else
                                {{ __('نواقص المخزون') }}
                            @endif
                        </div>
                        <div class="kpi-value english-num">{{ $stats['low_stock_count'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            @php $isExp = ($expiredBatches->count() ?? 0) > 0; @endphp
            <div class="card kpi-card {{ $isExp ? 'kpi-danger' : 'kpi-success' }} h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="kpi-icon-container me-3">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <div>
                        <div class="kpi-label">
                            @if(Auth::user()->store->type == 'restaurant')
                                {{ __('تنبيهات الصلاحية (الخامات)') }}
                            @else
                                {{ __('منتجات منتهية/قريبة') }}
                            @endif
                        </div>
                        <div class="kpi-value english-num">{{ $expiredBatches->count() ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. المحتوى الرئيسي --}}
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-home me-2 text-primary"></i> {{ __('لوحة تحكم المتجر:') }} {{ Auth::user()->store->name }}
                </div>

                <div class="card-body text-center py-5">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <img src="{{ Auth::user()->store->logo_url }}" alt="Logo" class="mb-3" style="max-height: 100px;">
                    <h3>{{ __('أهلاً بك في متجرك!') }}</h3>
                    <p class="text-muted">{{ __('هذه الصفحة خاصة بالدومين الفرعي:') }} <code>{{ request()->route('subdomain') }}</code></p>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <a href="{{ route('store.pos.index') }}" class="btn btn-lg btn-primary">
                            <i class="fas fa-cash-register me-2"></i> 
                            @if(Auth::user()->store->type == 'restaurant')
                                {{ __('نقطة البيع (الكاشير)') }}
                            @else
                                {{ __('نقطة البيع (POS)') }}
                            @endif
                        </a>
                        @if(strtolower(Auth::user()->store->type) == 'restaurant')
                            <a href="{{ route('store.meals.index') }}" class="btn btn-lg btn-outline-dark">
                                <i class="fas fa-utensils me-2"></i> {{ __('إدارة المنيو والوجبات') }}
                            </a>
                        @else
                            <a href="{{ route('store.products.index') }}" class="btn btn-lg btn-outline-dark">
                                <i class="fas fa-boxes me-2"></i> {{ __('إدارة المنتجات') }}
                            </a>
                        @endif
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
                                    <td class="text-muted small">الكمية: {{ (float)$batch->quantity }}</td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-danger py-0" onclick="openExpiryActionModal({{ $batch->id }}, '{{ addslashes($batch->product->name_ar ?? 'منتج') }}', '{{ $batch->product->baseUnit->unit_name ?? 'قطعة' }}', {{ $batch->quantity }}, '{{ $batch->expiry_date }}')">
                                            <i class="fas fa-cog"></i> معالجة
                                        </button>
                                    </td>
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
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-warning text-dark py-0" onclick="openLowStockModal({{ $prod->id }}, '{{ $prod->name_ar }}', {{ $prod->current_stock }}, {{ $prod->alert_quantity }})">
                                            <i class="fas fa-cog"></i> معالجة
                                        </button>
                                    </td>
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
        {{-- ✅ هذا الزر سيأخذك لصفحة المنتجات --}}
        @php 
            $route = (Auth::user()->store->type == 'restaurant') ? route('store.meals.index') : route('store.products.index');
        @endphp
        <a href="{{ $route }}" class="btn btn-outline-primary btn-sm">
            عرض كافة المنتجات
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