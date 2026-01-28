@extends('layouts.app')
@if(request()->has('iframe'))
    <style>
        /* إخفاء القوائم والهيدر والفوتر */
        nav.navbar, .main-header, .navbar-header, header, 
        aside, .main-sidebar, .app-sidebar, .sidebar, #sidebar, .sidebar-wrapper, 
        footer, .main-footer, .breadcrumb, .content-header, .btn-back {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
            visibility: hidden !important;
        }

        /* ضبط المحتوى ليملأ الشاشة */
        body, .wrapper, .content-wrapper, .main-panel, .main-content {
            margin: 0 !important;
            padding: 0 !important;
            min-height: 100vh !important;
            width: 100% !important;
            max-width: 100% !important;
            background-color: white !important; /* خلفية بيضاء لتبدو نظيفة */
        }
        
        .card { box-shadow: none !important; border: none !important; }
    </style>
@endif
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            {{-- عنوان الصفحة --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="text-primary fw-bold"><i class="fas fa-user-plus me-2"></i> إضافة جهة اتصال جديدة</h4>
                <a href="{{ route('store.contacts.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> العودة للقائمة
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-success">بيانات جهة الاتصال</h5>
                </div>
                <div class="card-body">

                    {{-- عرض الأخطاء --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('store.contacts.store') }}" id="contactForm" novalidate>
                        @csrf

                        <div class="row g-3">
{{-- 1. اسم الشخص المسؤول --}}
<div class="col-md-6">
    <label for="name" class="form-label required">اسم جهة الاتصال (الشخص المسؤول) <span class="text-danger">*</span></label>
    {{-- تم تغيير name="contact_name" إلى name="name" --}}
    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required placeholder="مثال: محمد أحمد">
</div>
                            <div class="col-md-6">
                                <label for="company_name" class="form-label">اسم الشركة / المؤسسة</label>
                                <input type="text" class="form-control" id="company_name" name="company_name" value="{{ old('company_name') }}" placeholder="مثال: شركة الأمانة">
                            </div>

                            {{-- 2. نوع العلاقة (Checkboxes) --}}
                            <div class="col-12">
                                <label class="form-label d-block fw-bold">نوع العلاقة <span class="text-danger">*</span></label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="type[]" value="customer" id="type_customer" 
                                           {{ (is_array(old('type')) && in_array('customer', old('type'))) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="type_customer">زبون (Customer)</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="type[]" value="supplier" id="type_supplier" 
                                           {{ (is_array(old('type')) && in_array('supplier', old('type'))) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="type_supplier">مورد (Supplier)</label>
                                </div>
                            </div>

                            <hr class="my-4">

                            {{-- 3. معلومات الاتصال --}}
                            <div class="col-md-6">
                                <label for="phone" class="form-label">رقم الهاتف</label>
                                <div class="input-group" dir="ltr">
                                    <input type="text" class="form-control text-start" id="phone" name="phone" value="{{ old('phone') }}" placeholder="5xxxxxxxxx">
                                    <select name="dial_code" class="form-select" style="max-width: 100px;">
                                        <option value="+90" selected>+90</option> {{-- تركيا --}}
                                        <option value="+966">+966</option> {{-- السعودية --}}
                                        <option value="+971">+971</option> {{-- الإمارات --}}
                                        {{-- يمكنك إضافة المزيد هنا أو جلبها من مصفوفة --}}
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">البريد الإلكتروني</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="name@example.com">
                            </div>

                            {{-- 4. العنوان والضريبة --}}
                            <div class="col-md-6">
                                <label for="tax_number" class="form-label">الرقم الضريبي</label>
                                <input type="text" class="form-control" id="tax_number" name="tax_number" value="{{ old('tax_number') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="address" class="form-label">العنوان</label>
                                <input type="text" class="form-control" id="address" name="address" value="{{ old('address') }}">
                            </div>

                            <hr class="my-4">

                            {{-- 5. المعلومات المالية --}}
                            <div class="col-md-6">
                                <label for="credit_limit" class="form-label">حد الدين (للزبائن)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" id="credit_limit" name="credit_limit" value="{{ old('credit_limit', 0) }}">
                                    <span class="input-group-text">TL</span> {{-- أو العملة الافتراضية --}}
                                </div>
                                <div class="form-text">اتركه 0 إذا لم يكن هناك حد.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="opening_balance" class="form-label">الرصيد الافتتاحي</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" id="opening_balance" name="opening_balance" value="{{ old('opening_balance', 0) }}">
                                    <span class="input-group-text">TL</span>
                                </div>
                                <div class="form-text text-danger">سالب (-) = عليه دين | موجب (+) = له رصيد</div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-success w-100 fw-bold py-2">
                                    <i class="fas fa-save me-1"></i> حفظ جهة الاتصال
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection