@extends('layouts.app') {{-- استخدام نفس التصميم الأساسي --}}

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    لوحة تحكم السوبر أدمن (Super Admin Dashboard)
                </div>

                <div class="card-body">
                    <h1>أهلاً بك، {{ Auth::user()->name }}!</h1>
                    <p>هذه هي لوحة التحكم الرئيسية. من هنا سنقوم بإدارة المتاجر.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
