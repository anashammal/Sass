@extends('layouts.app')

@section('content')
@php
    $arabicService = app(App\Services\ArabicTextService::class);
@endphp
<div class="container-fluid py-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-primary text-white">
            <h5 class="m-0 fw-bold"><i class="fas fa-history me-2"></i>{{ $arabicService->shape('سجل تعديلات المخزون') }}</h5>
            <div class="d-flex gap-2 no-print">
                <button onclick="window.print()" class="btn btn-sm btn-light">
                    <i class="fas fa-print me-1"></i> طباعة
                </button>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-share-alt me-1"></i> مشاركة
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" onclick="shareViaWhatsApp()"><i class="fab fa-whatsapp text-success me-2"></i> واتساب</a></li>
                        <li><a class="dropdown-item" href="#" onclick="shareViaEmail()"><i class="fas fa-envelope text-primary me-2"></i> بريد إلكتروني</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body">
            {{-- فلاتر البحث --}}
            <form id="filterForm" method="GET" action="{{ route('reports.inventory_logs') }}" class="row g-3 mb-4 no-print">
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">البحث (منتج / باركود)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="اسم المنتج أو الباركود..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted">الموظف</label>
                    <select name="user_id" class="form-select auto-filter">
                        <option value="">الكل</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted">من تاريخ</label>
                    <input type="date" name="date_from" class="form-control auto-filter" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted">إلى تاريخ</label>
                    <input type="date" name="date_to" class="form-control auto-filter" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1">
                    <label class="form-label fw-bold small text-muted">الصفوف</label>
                    <select name="per_page" class="form-select auto-filter">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>الكل</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">تطبيق</button>
                    <a href="{{ route('reports.inventory_logs') }}" class="btn btn-outline-secondary"><i class="fas fa-undo"></i></a>
                </div>
            </form>

            <div class="table-responsive" id="printArea">
                <table class="table table-bordered table-hover text-center align-middle" width="100%">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 5%">#</th>
                            <th style="width: 15%">اسم المنتج</th>
                            <th style="width: 10%">الباركود</th>
                            <th style="width: 12%">تاريخ التعديل</th>
                            <th style="width: 10%">نوع العملية</th>
                            <th style="width: 12%">الحالة قبل</th>
                            <th style="width: 12%">الحالة بعد</th>
                            <th style="width: 12%">بواسطة</th>
                            <th style="width: 12%">السبب</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $index => $log)
                        <tr>
                            <td>{{ $logs instanceof \Illuminate\Pagination\LengthAwarePaginator ? ($logs->firstItem() + $index) : ($index + 1) }}</td>
                            <td class="text-start fw-bold">{{ $log->product->name_ar ?? 'منتج محذوف' }}</td>
                            <td><code class="text-dark">{{ $log->product->sku ?? '---' }}</code></td>
                            <td class="small">{{ $log->created_at->format('Y-m-d h:i A') }}</td>
                            <td>
                                @switch($log->action)
                                    @case('manual_adjustment') <span class="badge bg-info text-dark">تعديل يدوي</span> @break
                                    @case('dispose') <span class="badge bg-danger">إتلاف مخزون</span> @break
                                    @case('extend_expiry') <span class="badge bg-warning text-dark">مدد الصلاحية</span> @break
                                    @default <span class="badge bg-secondary">{{ $log->action }}</span>
                                @endswitch
                            </td>
                            <td class="small text-muted">
                                @if($log->action == 'extend_expiry')
                                    📅 {{ $log->old_date ?? '---' }}
                                @else
                                    📦 {{ floatval($log->old_quantity) }}
                                @endif
                            </td>
                            <td class="small fw-bold">
                                @if($log->action == 'extend_expiry')
                                    📅 {{ $log->new_date ?? '---' }}
                                @else
                                    📦 {{ floatval($log->new_quantity) }}
                                @endif
                            </td>
                            <td>
                                <span class="badge border text-dark bg-light shadow-sm">
                                    <i class="fas fa-user-shield me-1 text-primary"></i> {{ $log->user->name ?? 'System' }}
                                </span>
                            </td>
                            <td class="small">{{ $log->reason ?? '---' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">لا توجد سجلات تعديل مطابقة للفلاتر المختارة</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="d-flex justify-content-center mt-4 no-print">
                {{ $logs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
        .card-header { background: #fff !important; color: #000 !important; border-bottom: 2px solid #000 !important; }
        .table thead th { background-color: #f8f9fa !important; color: #000 !important; border: 1px solid #000 !important; }
        .table td { border: 1px solid #000 !important; }
        body { background: white !important; }
    }
</style>
@endsection

@section('scripts')
<script>
    function shareViaWhatsApp() {
        // سيتم تحويل الجدول إلى نص بسيط أو رابط للتقرير مع الفلاتر
        let text = "تقرير سجل تعديلات المخزون\n";
        text += "تم التوليد في: " + new Date().toLocaleString() + "\n";
        text += "رابط التقرير: " + window.location.href;
        
        let url = "https://wa.me/?text=" + encodeURIComponent(text);
        window.open(url, '_blank');
    }

    function shareViaEmail() {
        let subject = "طلب مراجعة سجل تعديلات المخزون";
        let body = "مرحباً،\n\nيرجى مراجعة سجل تعديلات المخزون من خلال الرابط التالي:\n" + window.location.href;
        
        window.location.href = "mailto:?subject=" + encodeURIComponent(subject) + "&body=" + encodeURIComponent(body);
    }
    
    $(document).ready(function() {
        $('.auto-filter').on('change', function() {
            $('#filterForm').submit();
        });
    });
</script>
@endsection
