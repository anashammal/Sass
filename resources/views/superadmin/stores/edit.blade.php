@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0">تعديل المتجر: {{ $store->name }}</h5>
                    <a href="{{ route('superadmin.stores.index') }}" class="btn btn-outline-secondary btn-sm">
                        العودة إلى القائمة
                    </a>
                </div>

                <div class="card-body">

                    {{-- كود عرض الأخطاء العامة --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <h6 class="alert-heading">حدث خطأ!</h6>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('superadmin.stores.update', $store->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <h6 class="text-muted">بيانات صاحب المتجر (المالك)</h6>
                        <hr>

                        {{-- (اسم صاحب المتجر) --}}
                        <div class="row mb-3">
                            <label for="owner_name" class="col-md-4 col-form-label text-md-end">اسم صاحب المتجر</label>
                            <div class="col-md-8">
                                <input id="owner_name" type="text" class="form-control @error('owner_name') is-invalid @enderror" name="owner_name" value="{{ old('owner_name', optional($store->owner)->name) }}" required>
                                @error('owner_name') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                            </div>
                        </div>

                        {{-- (إيميل صاحب المتجر) --}}
                        <div class="row mb-3">
                            <label for="owner_email" class="col-md-4 col-form-label text-md-end">إيميل صاحب المتجر</label>
                            <div class="col-md-8">
                                <input id="owner_email" type="email" class="form-control @error('owner_email') is-invalid @enderror" name="owner_email" value="{{ old('owner_email', optional($store->owner)->email) }}" required>
                                @error('owner_email') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                            </div>
                        </div>

                        <h6 class="text-muted mt-4">بيانات المتجر</h6>
                        <hr>

                        {{-- (اسم المتجر) --}}
                        <div class="row mb-3">
                            <label for="store_name" class="col-md-4 col-form-label text-md-end">اسم المتجر</label>
                            <div class="col-md-8">
                                <input id="store_name" type="text" class="form-control @error('store_name') is-invalid @enderror" name="store_name" value="{{ old('store_name', $store->name) }}" required>
                                @error('store_name') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                            </div>
                        </div>

                        {{-- (الدومين الفرعي) --}}
                        <div class="row mb-3">
                            <label for="subdomain" class="col-md-4 col-form-label text-md-end">الدومين الفرعي</label>
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input id="subdomain" type="text" class="form-control @error('subdomain') is-invalid @enderror" name="subdomain" value="{{ old('subdomain', $store->subdomain) }}" required>
                                    <span class="input-group-text">.tech-sys.online</span>
                                </div>
                                @error('subdomain') <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span> @enderror
                            </div>
                        </div>

                        {{-- ------------------------------------------------------------- --}}
                        {{-- <<<< الكود الجديد: بيانات الإعدادات الأولية (للقراءة فقط) >>>> --}}
                        {{-- ------------------------------------------------------------- --}}
                        <h6 class="text-muted mt-4">بيانات الإعدادات الأولية (Onboarding) - للقراءة فقط</h6>
                        <hr>

                        {{-- الدولة --}}
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-end">الدولة</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" value="{{ $store->country ?? 'غير مكتمل' }}" readonly>
                            </div>
                        </div>

                        {{-- المدينة --}}
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-end">المدينة</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" value="{{ $store->city ?? 'غير مكتمل' }}" readonly>
                            </div>
                        </div>

                        {{-- رقم الهاتف --}}
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-end">رقم الهاتف</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" value="{{ $store->phone_number ?? 'غير مكتمل' }}" readonly>
                            </div>
                        </div>

                        {{-- رقم الحساب البنكي (IBAN) --}}
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-end">IBAN</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" value="{{ $store->iban ?? 'غير مكتمل' }}" readonly>
                            </div>
                        </div>
                        {{-- ------------------------------------------------------------- --}}
                        {{-- <<<< نهاية الكود الجديد >>>> --}}
                        {{-- ------------------------------------------------------------- --}}
                        
                        {{-- (الحالة) --}}
                        <h6 class="text-muted mt-4">إدارة حالة المتجر (للسوبر أدمن)</h6>
                        <hr>
                        <div class="row mb-3">
                            <label for="status" class="col-md-4 col-form-label text-md-end">حالة المتجر</label>
                            <div class="col-md-8">
                                <select id="status" class="form-control @error('status') is-invalid @enderror" name="status" required>
                                    <option value="active" {{ old('status', $store->status) == 'active' ? 'selected' : '' }}>فعال (Active)</option>
                                    <option value="suspended" {{ old('status', $store->status) == 'suspended' ? 'selected' : '' }}>متوقف مؤقتاً (Suspended)</option>
                                </select>
                                @error('status') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                            </div>
                        </div>

                        {{-- (السبب) --}}
                        <div class="row mb-3" id="status-reason-container" style="display: none;">
                            <label for="status_reason" class="col-md-4 col-form-label text-md-end">سبب تغيير الحالة</label>
                            <div class="col-md-8">
                                <textarea id="status_reason" class="form-control @error('status_reason') is-invalid @enderror" name="status_reason" rows="3">{{ old('status_reason', $store->status_reason) }}</textarea>
                                @error('status_reason') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                            </div>
                        </div>

                        <div class="row mb-0 mt-4">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const statusSelect = document.getElementById('status');
        const reasonContainer = document.getElementById('status-reason-container');
        const reasonTextarea = document.getElementById('status_reason');
        const originalStatus = '{{ $store->status }}';

        function toggleReasonField() {
            // نستخدم 'flex' لأننا غيرنا الـ display ليتوافق مع الـ row (Bootstrap)
            if (statusSelect.value !== originalStatus) {
                reasonContainer.style.display = 'flex';
                reasonTextarea.setAttribute('required', 'required');
            } else {
                reasonContainer.style.display = 'none';
                reasonTextarea.removeAttribute('required');
            }
        }

        // تحقق عند تحميل الصفحة (في حال وجود خطأ والعودة)
        toggleReasonField();
        // تحقق عند تغيير القائمة
        statusSelect.addEventListener('change', toggleReasonField);
    });
</script>
@endsection