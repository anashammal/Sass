@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header & Summary -->
    <div class="row mb-4">
        <div class="col-md-6 d-flex align-items-center">
            <h3 class="fw-bold text-dark mb-0">
                <i class="fas fa-wallet me-2 text-danger"></i>{{ __('المصاريف') }}
            </h3>
            <a href="{{ route('store.expense-categories.index') }}" class="btn btn-outline-secondary btn-sm ms-3">
                <i class="fas fa-cog me-1"></i> {{ __('إدارة التصنيفات') }}
            </a>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                <i class="fas fa-plus-circle me-1"></i> {{ __('تسجيل مصروف جديد') }}
            </button>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 h-100 border-start border-4 border-danger">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-bold">{{ __('مصاريف اليوم') }}</small>
                        <h4 class="fw-bold mb-0 mt-1">{{ (float)$totals['today'] == (int)$totals['today'] ? number_format($totals['today'], 0) : number_format($totals['today'], 2) }}</h4>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-3 rounded-circle text-danger">
                        <i class="fas fa-calendar-day fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 h-100 border-start border-4 border-warning">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-bold">{{ __('مصاريف الشهر') }}</small>
                        <h4 class="fw-bold mb-0 mt-1">{{ (float)$totals['month'] == (int)$totals['month'] ? number_format($totals['month'], 0) : number_format($totals['month'], 2) }}</h4>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
                        <i class="fas fa-calendar-alt fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 h-100 border-start border-4 border-secondary">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-bold">{{ __('إجمالي المصاريف (المفلترة)') }}</small>
                        <h4 class="fw-bold mb-0 mt-1">{{ (float)$totals['total'] == (int)$totals['total'] ? number_format($totals['total'], 0) : number_format($totals['total'], 2) }}</h4>
                    </div>
                    <div class="bg-secondary bg-opacity-10 p-3 rounded-circle text-secondary">
                        <i class="fas fa-filter fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters & Data Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <form id="filterForm" action="{{ route('store.expenses.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <label class="small text-muted fw-bold mb-1">{{ __('من تاريخ') }}</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="far fa-calendar-alt text-muted"></i></span>
                        <input type="date" id="filterDateFrom" name="date_from" value="{{ request('date_from') }}" class="form-control enhanced-date-input auto-filter border-start-0 ps-0">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="small text-muted fw-bold mb-1">{{ __('إلى تاريخ') }}</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="far fa-calendar-alt text-muted"></i></span>
                        <input type="date" id="filterDateTo" name="date_to" value="{{ request('date_to') }}" class="form-control enhanced-date-input auto-filter border-start-0 ps-0">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="small text-muted fw-bold mb-1">{{ __('التصنيف') }}</label>
                    <select name="category_id" class="form-select form-select-sm auto-filter">
                        <option value="">{{ __('(الكل)') }}</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <a href="{{ route('store.expenses.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fas fa-undo me-1"></i> {{ __('إعادة تعيين') }}
                    </a>
                </div>
            </form>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-4">{{ __('التاريخ') }}</th>
                            <th class="py-3">{{ __('التصنيف') }}</th>
                            <th class="py-3">{{ __('المبلغ') }}</th>
                            <th class="py-3">{{ __('ملاحظات') }}</th>
                            <th class="py-3">{{ __('بواسطة') }}</th>
                            <th class="py-3 text-end px-4">{{ __('إجراءات') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                            <tr>
                                <td class="px-4 text-nowrap">{{ $expense->expense_date->format('Y-m-d') }}</td>
                                <td>
                                    <span class="badge {{ $expense->category->type == 'fixed' ? 'bg-info bg-opacity-10 text-info' : 'bg-warning bg-opacity-10 text-warning' }} border border-{{ $expense->category->type == 'fixed' ? 'info' : 'warning' }}">
                                        {{ $expense->category->name }}
                                    </span>
                                </td>
                                <td class="fw-bold">
                                    {{ (float)$expense->amount == (int)$expense->amount ? number_format($expense->amount, 0) : number_format($expense->amount, 2) }}
                                </td>
                                <td class="text-muted small text-truncate" style="max-width: 200px;" title="{{ $expense->notes }}">{{ $expense->notes ?? '-' }}</td>
                                <td class="small text-muted">{{ $expense->user->name }}</td>
                                <td class="text-end px-4">
                                    @if($expense->attachment)
                                        <button class="btn btn-sm btn-outline-info" 
                                            onclick="previewAttachment('{{ asset('storage/' . $expense->attachment) }}')"
                                            title="{{ __('عرض المرفق') }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endif
                                    <button class="btn btn-sm btn-outline-primary" 
                                        onclick="editExpense({{ json_encode([
                                            'id' => $expense->id,
                                            'category_id' => $expense->category_id,
                                            'amount' => $expense->amount,
                                            'expense_date' => $expense->expense_date->format('Y-m-d'),
                                            'notes' => $expense->notes
                                        ]) }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('store.expenses.destroy', $expense->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('هل أنت متأكد من الحذف؟') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-receipt fa-3x mb-3 text-secondary opacity-50"></i>
                                    <p>{{ __('لا توجد مصاريف مسجلة') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 py-3">
            {{ $expenses->links() }}
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('store.expenses.store') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title fw-bold">{{ __('تسجيل مصروف جديد') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __('تصنيف المصروف') }}</label>
                    <select name="category_id" class="form-select" required>
                        <option value="" selected disabled>{{ __('اختر التصنيف...') }}</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('المبلغ') }}</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="amount" class="form-control" required>
                            <span class="input-group-text bg-white">{{ __('د.أ') }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('التاريخ') }}</label>
                        <input type="date" name="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __('ملاحظات (اختياري)') }}</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __('المرفق (صورة الفاتورة)') }}</label>
                    <div class="localized-file-wrapper">
                        <button type="button" class="btn btn-sm btn-outline-secondary localized-file-btn">
                            <i class="fas fa-upload me-1"></i> {{ __('اختيار ملف') }}
                        </button>
                        <div class="localized-file-name text-start"> {{ __('لم يتم اختيار ملف') }} </div>
                        <input type="file" name="attachment" accept="image/*" onchange="updateFileName(this)">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('إلغاء') }}</button>
                <button type="submit" class="btn btn-danger px-4">{{ __('حفظ') }}</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Expense Modal -->
<div class="modal fade" id="editExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editExpenseForm" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title fw-bold">{{ __('تعديل المصروف') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __('تصنيف المصروف') }}</label>
                    <select name="category_id" id="edit_category_id" class="form-select" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('المبلغ') }}</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="amount" id="edit_amount" class="form-control" required>
                            <span class="input-group-text bg-white">{{ __('د.أ') }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('التاريخ') }}</label>
                        <input type="date" name="expense_date" id="edit_expense_date" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __('ملاحظات (اختياري)') }}</label>
                    <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __('تحديث المرفق (اختياري)') }}</label>
                    <div class="localized-file-wrapper">
                        <button type="button" class="btn btn-sm btn-outline-secondary localized-file-btn">
                            <i class="fas fa-upload me-1"></i> {{ __('اختيار ملف') }}
                        </button>
                        <div class="localized-file-name text-start"> {{ __('لم يتم اختيار ملف') }} </div>
                        <input type="file" name="attachment" accept="image/*" onchange="updateFileName(this)">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('إلغاء') }}</button>
                <button type="submit" class="btn btn-primary px-4">{{ __('تحديث') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        // 1. التحديث التلقائي عند تغيير الفلاتر
        $('.auto-filter').on('change', function() {
            $('#filterForm').submit();
        });

        // 2. التحقق الذكي من التواريخ (From <-> To)
        
        // عند تغيير "من تاريخ"
        $('#filterDateFrom').on('change', function() {
            let fromDate = $(this).val();
            $('#filterDateTo').attr('min', fromDate); // منع اختيار تاريخ "إلى" قبل "من"
            
            // تصحيح تلقائي إذا كان التاريخ الحالي خطأ
            let currentTo = $('#filterDateTo').val();
            if(fromDate && currentTo && currentTo < fromDate) {
                $('#filterDateTo').val(fromDate); // تعديل "إلى" ليساوي "من"
            }
        });

        // عند تغيير "إلى تاريخ"
        $('#filterDateTo').on('change', function() {
            let toDate = $(this).val();
            $('#filterDateFrom').attr('max', toDate); // منع اختيار تاريخ "من" بعد "إلى"
            
            // تصحيح تلقائي
            let currentFrom = $('#filterDateFrom').val();
            if(toDate && currentFrom && currentFrom > toDate) {
                $('#filterDateFrom').val(toDate); // تعديل "من" ليساوي "إلى"
            }
        });

        // تشغيل التحقق عند التحميل لضبط القيود الأولية (بدون تحديث تلقائي)
        if($('#filterDateFrom').val()) $('#filterDateTo').attr('min', $('#filterDateFrom').val());
        if($('#filterDateTo').val()) $('#filterDateFrom').attr('max', $('#filterDateTo').val());
    });

    function editExpense(data) {
        document.getElementById('editExpenseForm').action = `{{ url('store-owner/expenses') }}/${data.id}`;
        document.getElementById('edit_category_id').value = data.category_id;
        // تقريب الرقم ليظهر بدون أصفار زائدة إذا كان صحيحاً
        document.getElementById('edit_amount').value = parseFloat(data.amount); 
        document.getElementById('edit_expense_date').value = data.expense_date;
        document.getElementById('edit_notes').value = data.notes ?? '';
        
        new bootstrap.Modal(document.getElementById('editExpenseModal')).show();
    }

    function previewAttachment(url) {
        const body = document.getElementById('previewModalBody');
        const extension = url.split('.').pop().toLowerCase();
        const isLocal = ['localhost', '127.0.0.1'].includes(window.location.hostname);
        
        body.innerHTML = ''; // تنظيف المعاينة السابقة
        
        if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(extension)) {
            body.innerHTML = `<div class="p-3 text-center"><img src="${url}" class="img-fluid rounded shadow-sm max-vh-70"></div>`;
        } 
        else if (['mp4', 'webm', 'ogg'].includes(extension)) {
            body.innerHTML = `
                <div class="p-3">
                    <video controls class="w-100 rounded shadow-sm" style="max-height: 500px;">
                        <source src="${url}" type="video/${extension === 'mp4' ? 'mp4' : (extension === 'ogg' ? 'ogg' : 'webm')}">
                        {{ __('متصفحك لا يدعم تشغيل الفيديو.') }}
                    </video>
                </div>`;
        }
        else if (['mp3', 'wav', 'ogg'].includes(extension)) {
            body.innerHTML = `
                <div class="p-5 text-center">
                    <i class="fas fa-music fa-4x mb-4 text-primary opacity-25"></i>
                    <audio controls class="w-100 mb-3">
                        <source src="${url}" type="audio/${extension === 'mp3' ? 'mpeg' : (extension === 'wav' ? 'wav' : 'ogg')}">
                        {{ __('متصفحك لا يدعم تشغيل الصوت.') }}
                    </audio>
                    <p class="text-muted">{{ __('مشغل ملفات الصوت') }}</p>
                </div>`;
        }
        else if (extension === 'pdf') {
            body.innerHTML = `<iframe src="${url}" width="100%" height="600px" style="border:none;"></iframe>`;
        } 
        else if (['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'].includes(extension)) {
            if (!isLocal) {
                body.innerHTML = `
                    <div class="ratio ratio-16x9">
                        <iframe src="https://docs.google.com/viewer?url=${encodeURIComponent(url)}&embedded=true" frameborder="0"></iframe>
                    </div>`;
            } else {
                body.innerHTML = `
                    <div class="text-center py-5 px-3">
                        <div class="mb-4">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-file fa-stack-1x text-primary opacity-25"></i>
                                <i class="fas fa-laptop-code fa-stack-1x text-dark shadow-sm" style="font-size: 0.5em; margin-top: 25px;"></i>
                            </span>
                        </div>
                        <h4 class="fw-bold">{{ __('معاينة ملفات Office (نظام محلي)') }}</h4>
                        <p class="text-muted mb-4">{!! __('أنت تعمل حالياً على <b>Localhost</b>. خدمات المعاينة السحابية (مثل Google/Microsoft) لا يمكنها الوصول لملفات جهازك الشخصي لأسباب أمنية.') !!}</p>
                        <div class="alert alert-warning d-inline-block shadow-sm">
                            <i class="fas fa-info-circle me-2"></i> {{ __('عند رفع النظام على الإنترنت (Live Server)، ستظهر المعاينة هنا تلقائياً.') }}
                        </div>
                        <div class="mt-4 pt-2">
                            <a href="${url}" download class="btn btn-primary btn-lg px-5 shadow">
                                <i class="fas fa-download me-2"></i> {{ __('تحميل وفتح الملف الآن') }}
                            </a>
                        </div>
                    </div>`;
            }
        } 
        else {
            // لأي امتداد آخر غير معروف
            body.innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-file-alt fa-4x mb-4 text-secondary opacity-25"></i>
                    <h4>{{ __('ملف غير مدعوم للمعاينة المباشرة') }}</h4>
                    <p class="text-muted mb-4">{{ __('الامتداد:') }} ( ${extension.toUpperCase()} )</p>
                    <a href="${url}" download class="btn btn-dark px-5">
                        <i class="fas fa-download me-2"></i> {{ __('تحميل المرفق') }}
                    </a>
                </div>`;
        }
        
        document.getElementById('downloadPreviewLink').href = url;
        new bootstrap.Modal(document.getElementById('attachmentPreviewModal')).show();
    }
</script>

<!-- Attachment Preview Modal -->
<div class="modal fade" id="attachmentPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">{{ __('معاينة المرفق') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="previewModalBody">
                <!-- Preview content injected here -->
            </div>
            <div class="modal-footer border-0">
                <a href="#" id="downloadPreviewLink" download class="btn btn-outline-secondary">
                    <i class="fas fa-download me-1"></i> {{ __('تحميل النسخة الأصلية') }}
                </a>
                <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">{{ __('إغلاق') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection
