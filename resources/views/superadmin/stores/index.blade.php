@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0">إدارة المتاجر (Stores Management)</h5>

                    {{-- زر إضافة متجر جديد --}}
                    <a href="{{ route('superadmin.stores.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> إضافة متجر جديد
                    </a>
                </div>

                <div class="card-body">

                    {{-- لعرض رسالة النجاح (الخضراء) --}}
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- لعرض رسالة الخطأ (الحمراء) --}}
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">اسم المتجر</th>
                                <th scope="col">الدومين الفرعي</th>
                                <th scope="col">صاحب المتجر</th>
                                <th scope="col">نوع المتجر</th>
                                <th scope="col">تاريخ الإنشاء</th>
                                <th scope="col">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($stores as $store)
                                <tr>
                                    <th scope="row">{{ $store->id }}</th>
                                    <td>{{ $store->name }}</td>
                                    <td>{{ $store->subdomain }}.tech-sys.online</td>
                                    <td>{{ $store->owner->name ?? 'غير محدد' }}</td>
                                    <td>
                                        @if($store->type == 'restaurant')
                                            <span class="badge bg-info text-dark">مطعم</span>
                                        @else
                                            <span class="badge bg-light text-dark">تجارة عامة</span>
                                        @endif
                                    </td>
                                    <td>{{ $store->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        {{-- !! -- هذا هو الرابط الذي قمنا بتفعيله -- !! --}}
                                        <a href="{{ route('superadmin.stores.edit', $store->id) }}" class="btn btn-sm btn-outline-secondary">تعديل</a>

                                        {{-- فورم الحذف (كما هو) --}}
                                        <form action="{{ route('superadmin.stores.destroy', $store->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('هل أنت متأكد أنك تريد حذف هذا المتجر؟ سيتم حذف صاحب المتجر وكل البيانات المرتبطة به بشكل دائم!');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">لا توجد متاجر لعرضها حالياً. قم بإضافة متجر جديد.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
