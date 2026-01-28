@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0 text-primary fw-bold"><i class="fas fa-cogs me-2"></i> إعدادات المتجر (إكمال البيانات)</h4>
                </div>

                <div class="card-body p-4">
                    {{-- عرض رسائل النجاح أو الخطأ --}}
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- النموذج يستخدم الآن الرابط الصحيح: store.onboarding.update --}}
                    <form method="POST" action="{{ route('store.onboarding.update') }}">
                        @csrf

                        {{-- 1. الدولة والمدينة --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">الدولة <span class="text-danger">*</span></label>
                                <select name="country" class="form-select @error('country') is-invalid @enderror" required>
                                    <option value="">اختر الدولة...</option>
                                    @foreach($countries as $code => $name)
                                        <option value="{{ $code }}" {{ (old('country', $store->country) == $code) ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">المدينة <span class="text-danger">*</span></label>
                                <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $store->city) }}" required>
                            </div>
                        </div>

                        {{-- 2. العنوان --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">العنوان التفصيلي</label>
                            <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $store->address) }}" placeholder="الشارع، الحي، رقم المبنى...">
                        </div>

                        <hr class="my-4">

                        {{-- 3. الهاتف --}}
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">رمز الدولة</label>
                                <select name="phone_country_code" class="form-select">
                                    <option value="+966" {{ (old('phone_country_code', $store->phone_country_code) == '+966') ? 'selected' : '' }}>+966 (SA)</option>
                                    <option value="+90" {{ (old('phone_country_code', $store->phone_country_code) == '+90') ? 'selected' : '' }}>+90 (TR)</option>
                                    <option value="+971" {{ (old('phone_country_code', $store->phone_country_code) == '+971') ? 'selected' : '' }}>+971 (AE)</option>
                                    <option value="+20" {{ (old('phone_country_code', $store->phone_country_code) == '+20') ? 'selected' : '' }}>+20 (EG)</option>
                                    {{-- يمكنك إضافة المزيد --}}
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-bold">رقم الهاتف <span class="text-danger">*</span></label>
                                <input type="text" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror" value="{{ old('phone_number', $store->phone_number) }}" required>
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- 4. البنك والآيبان --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">رقم الآيبان (IBAN) <span class="text-danger">*</span></label>
                                <input type="text" name="iban" class="form-control @error('iban') is-invalid @enderror" value="{{ old('iban', $store->iban) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">دولة البنك</label>
                                <input type="text" name="bank_country" class="form-control" value="{{ old('bank_country', $store->bank_country) }}">
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i> حفظ وإكمال الإعدادات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection