@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark mb-0">
            <i class="fas fa-tags me-2 text-primary"></i>{{ __('تصنيفات المصاريف') }}
        </h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="fas fa-plus me-1"></i> {{ __('إضافة تصنيف جديد') }}
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-4">{{ __('اسم التصنيف') }}</th>
                            <th class="py-3">{{ __('النوع') }}</th>
                            <th class="py-3">{{ __('الوصف') }}</th>
                            <th class="py-3 text-end px-4">{{ __('إجراءات') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td class="px-4 fw-bold">{{ $category->name }}</td>
                                <td>
                                    @if($category->type == 'fixed')
                                        <span class="badge bg-info text-dark">{{ __('ثابت (Fixed)') }}</span>
                                    @else
                                        <span class="badge bg-warning text-dark">{{ __('متغير (Variable)') }}</span>
                                    @endif
                                </td>
                                <td class="text-muted">{{ $category->description ?? '-' }}</td>
                                <td class="text-end px-4">
                                    <button class="btn btn-sm btn-outline-primary me-1" 
                                            onclick="editCategory({{ $category->id }}, '{{ $category->name }}', '{{ $category->type }}', '{{ $category->description }}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('store.expense-categories.destroy', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('هل أنت متأكد من الحذف؟') }}')">
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
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
                                    <p>{{ __('لا توجد تصنيفات مضافة بعد') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('store.expense-categories.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title fw-bold">{{ __('إضافة تصنيف جديد') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __('اسم التصنيف') }}</label>
                    <input type="text" name="name" class="form-control" required placeholder="{{ __('مثال: إيجار، رواتب...') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __('نوع المصروف') }}</label>
                    <select name="type" class="form-select" required>
                        <option value="variable">{{ __('متغير (Variable)') }}</option>
                        <option value="fixed">{{ __('ثابت (Fixed)') }}</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __('وصف (اختياري)') }}</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('إلغاء') }}</button>
                <button type="submit" class="btn btn-primary px-4">{{ __('حفظ') }}</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editForm" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title fw-bold">{{ __('تعديل التصنيف') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __('اسم التصنيف') }}</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __('نوع المصروف') }}</label>
                    <select name="type" id="edit_type" class="form-select" required>
                        <option value="variable">{{ __('متغير (Variable)') }}</option>
                        <option value="fixed">{{ __('ثابت (Fixed)') }}</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __('وصف (اختياري)') }}</label>
                    <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
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
    function editCategory(id, name, type, description) {
        document.getElementById('editForm').action = `{{ url('store-owner/expense-categories') }}/${id}`;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_type').value = type;
        document.getElementById('edit_description').value = description;
        
        new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
    }
</script>
@endsection
