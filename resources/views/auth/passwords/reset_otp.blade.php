@extends('layouts.app', ['title' => 'تعيين كلمة المرور الجديدة'])

@section('content')
<div class="row align-items-center justify-content-center h-100">
    <div class="col-xl-5 col-lg-6 col-md-8 col-sm-10">
        <div class="card o-hidden rounded-3 border-0 shadow-lg my-5" style="background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(20px); border-radius: 24px;">
            <div class="card-body p-0">
                <div class="p-5">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 shadow-sm" style="width: 80px; height: 80px; background: var(--gradient-gold);">
                            <i class="fas fa-check-double fa-2x text-white"></i>
                        </div>
                        <h1 class="h3 text-gray-900 mb-2 font-weight-bold" style="font-family: 'Tajawal', sans-serif;">كلمة المرور الجديدة</h1>
                        <p class="mb-4 text-muted small" style="font-family: 'Tajawal', sans-serif;">
                            يجب أن تحتوي كلمة المرور على:<br>
                            - 8 أحرف على الأقل<br>
                            - حرف كبير (A-Z) وحرف صغير (a-z)<br>
                            - رمز خاص (@$!%*#?&)
                        </p>
                    </div>

                    <form class="user" method="POST" action="{{ route('password.update.custom') }}">
                        @csrf
                        <input type="hidden" name="email" value="{{ $email }}">
                        <input type="hidden" name="code" value="{{ $code }}">

                        <div class="form-group mb-3">
                            <label class="form-label font-weight-bold text-gray-700 small px-2">كلمة المرور الجديدة</label>
                            <input type="password" class="form-control form-control-user shadow-sm @error('password') is-invalid @enderror"
                                name="password" required autocomplete="new-password"
                                style="border-radius: 50px; height: 50px;">
                            @error('password')
                                <span class="text-danger small mt-2 d-block px-2">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label font-weight-bold text-gray-700 small px-2">تأكيد كلمة المرور</label>
                            <input type="password" class="form-control form-control-user shadow-sm"
                                name="password_confirmation" required autocomplete="new-password"
                                style="border-radius: 50px; height: 50px;">
                        </div>

                        <button type="submit" class="btn btn-primary btn-user btn-block shadow-lg font-weight-bold" 
                            style="background: var(--gradient-primary); border: none; height: 50px; border-radius: 50px; font-family: 'Tajawal', sans-serif;">
                            حفظ كلمة المرور
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
