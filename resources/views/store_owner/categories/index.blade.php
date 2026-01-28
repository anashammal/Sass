@extends('layouts.app')

@section('styles')
<style>
    /* ... (كل كود الـ CSS الخاص بالشجرة موجود هنا كما كان) ... */
    .category-tree { list-style-type: none; padding-right: 0; }
    .category-tree-child { list-style-type: none; padding-right: 25px; margin-right: 12px; border-right: 1px dashed #aaa; }
    .category-tree li { position: relative; }
    .category-tree li::before { content: ''; position: absolute; top: 22px; right: -12px; width: 12px; height: 1px; border-top: 1px dashed #aaa; }
    .category-toggle-link { text-decoration: none; color: inherit; display: inline-block; }
    .category-toggle-link:visited { color: inherit; }
    .category-toggle-icon { transition: transform 0.2s ease-in-out; transform: rotate(0deg); }
    .category-toggle-link.collapsed .category-toggle-icon { transform: rotate(-90deg); }
    .category-tree > li::before { display: none; }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0">إدارة التصنيفات (Categories)</h5>
                    <a href="{{ route('store.categories.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> إضافة تصنيف جديد
                    </a>
                </div>

                <div class="card-body">

                    {{-- لعرض رسالة النجاح (الخضراء) --}}
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- !! -- لعرض رسالة الخطأ (الحمراء) -- !! --}}
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center py-2 px-3 bg-light border-bottom">
                        <strong>اسم التصنيف</strong>
                        <strong>إجراءات</strong>
                    </div>

                    <ul class="category-tree">
                        @forelse ($categories as $category)
                            @include('store_owner.categories._category_partial', ['category' => $category])
                        @empty
                            <li class="text-center p-3">
                                لا توجد تصنيفات لعرضها حالياً.
                            </li>
                        @endforelse
                    </ul>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
