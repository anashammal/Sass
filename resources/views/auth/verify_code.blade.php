@extends('layouts.app', ['title' => 'التحقق من الرمز'])

@section('content')
<div class="row align-items-center justify-content-center h-100">
    <div class="col-xl-5 col-lg-6 col-md-8 col-sm-10">
        <div class="card o-hidden rounded-3 border-0 shadow-lg my-5" style="background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(20px); border-radius: 24px;">
            <div class="card-body p-0">
                <div class="p-5">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 shadow-sm" style="width: 80px; height: 80px; background: var(--gradient-primary);">
                            <i class="fas fa-lock fa-2x text-white"></i>
                        </div>
                        <h1 class="h3 text-gray-900 mb-2 font-weight-bold" style="font-family: 'Tajawal', sans-serif;">أدخل رمز التحقق</h1>
                        <p class="mb-4 text-muted" style="font-family: 'Tajawal', sans-serif;">
                            تم إرسال رمز التحقق إلى بريدك الإلكتروني و WhatsApp
                        </p>
                    </div>

                    <form class="user" method="POST" action="{{ route('password.verify.submit') }}">
                        @csrf
                        <input type="hidden" name="email" value="{{ request('email') ?? old('email') }}">

                        <div class="form-group mb-4">
                            <label class="form-label font-weight-bold text-gray-700 small px-2">رمز التحقق (6 أرقام)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-0 ps-3 rounded-pill-start">
                                    <i class="fas fa-key text-primary"></i>
                                </span>
                                <input type="text" class="form-control form-control-user border-0 shadow-sm bg-white @error('code') is-invalid @enderror"
                                    name="code" value="{{ old('code') }}" required autofocus
                                    placeholder="XXXXXX" 
                                    style="border-radius: 0 50px 50px 0; height: 50px; font-size: 1.2rem; letter-spacing: 5px; text-align: center;">
                            </div>
                            @error('code')
                                <span class="text-danger small mt-2 d-block px-2">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            @error('email')
                                <span class="text-danger small mt-2 d-block px-2">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary btn-user btn-block shadow-lg font-weight-bold" 
                            style="background: var(--gradient-primary); border: none; height: 50px; border-radius: 50px; font-family: 'Tajawal', sans-serif;">
                            تحقق من الرمز
                        </button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a class="small font-weight-bold" href="{{ route('login') }}" style="color: #4e73df; font-family: 'Tajawal', sans-serif;">
                            <i class="fas fa-arrow-right fa-sm me-1"></i> العودة لتسجيل الدخول
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
