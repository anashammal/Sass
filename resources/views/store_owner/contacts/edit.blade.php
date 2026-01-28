@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary">تعديل جهة الاتصال: {{ $contact->contact_name }}</h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger"><ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul></div>
                    @endif

                    <form method="POST" action="{{ route('store.contacts.update', $contact->id) }}" id="contactForm" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required">الاسم <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="{{ old('name', $contact->contact_name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">الشركة</label>
                                <input type="text" class="form-control" name="company_name" value="{{ old('company_name', $contact->company_name) }}">
                            </div>

                            {{-- 🧠 منطق تحديد النوع (الذكي) --}}
                            <div class="col-12">
                                <label class="form-label d-block fw-bold">النوع <span class="text-danger">*</span></label>
                                
                                {{-- تحديد حالة الزبون --}}
                                @php
                                    $isCustomerChecked = false;
                                    if (old('type')) {
                                        $isCustomerChecked = in_array('customer', old('type'));
                                    } else {
                                        // إذا كان النوع 'customer' أو 'both'
                                        $isCustomerChecked = ($contact->type === 'customer' || $contact->type === 'both');
                                    }
                                @endphp
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="type[]" value="customer" id="type_customer" 
                                           {{ $isCustomerChecked ? 'checked' : '' }}>
                                    <label class="form-check-label" for="type_customer">زبون</label>
                                </div>

                                {{-- تحديد حالة المورد --}}
                                @php
                                    $isSupplierChecked = false;
                                    if (old('type')) {
                                        $isSupplierChecked = in_array('supplier', old('type'));
                                    } else {
                                        // إذا كان النوع 'supplier' أو 'both'
                                        $isSupplierChecked = ($contact->type === 'supplier' || $contact->type === 'both');
                                    }
                                @endphp
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="type[]" value="supplier" id="type_supplier" 
                                           {{ $isSupplierChecked ? 'checked' : '' }}>
                                    <label class="form-check-label" for="type_supplier">مورد</label>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="col-md-6">
                                <label class="form-label">الهاتف</label>
                                <input type="text" class="form-control" name="phone" value="{{ old('phone', $contact->phone) }}" dir="ltr">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">البريد</label>
                                <input type="email" class="form-control" name="email" value="{{ old('email', $contact->email) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">الرقم الضريبي</label>
                                <input type="text" class="form-control" name="tax_number" value="{{ old('tax_number', $contact->tax_number) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">العنوان</label>
                                <input type="text" class="form-control" name="address" value="{{ old('address', $contact->address) }}">
                            </div>

                            <hr class="my-4">

                            <div class="col-md-6">
                                <label class="form-label">حد الدين</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" name="credit_limit" value="{{ old('credit_limit', $contact->credit_limit) }}">
                                    <span class="input-group-text">TL</span>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">الرصيد الحالي</label>
                                <input type="text" class="form-control bg-light" value="{{ number_format($contact->balance, 2) }}" readonly>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary w-100 fw-bold py-2">حفظ التعديلات</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection