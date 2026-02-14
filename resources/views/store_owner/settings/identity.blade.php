@extends('layouts.app') {{-- (استبدل هذا باسم ملف الـ layout الرئيسي عندك إذا كان مختلفاً) --}}

@section('content')
<div class="container">
    <h2>إعدادات الهوية البصرية</h2>
    <p>إدارة شعار متجرك، الختم الإلكتروني، والتوقيع المستخدم في الفواتير.</p>

    {{-- لعرض رسائل النجاح أو الأخطاء --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>حدث خطأ:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- فورم الرفع الرئيسي --}}
    {{--!! -- تصحيح المسار: تم تغيير 'tenant.' إلى 'store.' --!! --}}
    <form action="{{ route('store.settings.identity.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- قسم الشعار --}}
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">1. شعار المتجر (يظهر في كل الصفحات)</h5>
                <p class="card-text">سيظهر هذا الشعار في رأس جميع صفحات متجرك وعلى الفواتير. إذا لم يتم رفع شعار، سيظهر شعار "Tech-Sys" الافتراضي.</p>
                
                @if($logoUrl)
                    <div class="mb-2">
                        <strong>الشعار الحالي:</strong><br>
                        <img src="{{ $logoUrl }}" alt="الشعار الحالي" style="max-height: 100px; background: #f0f0f0; padding: 5px; border-radius: 4px;">
                    </div>
                    
                    {{-- فورم حذف الشعار --}}
                    {{--!! -- تصحيح المسار: تم تغيير 'tenant.' إلى 'store.' --!! --}}
                    <form action="{{ route('store.settings.identity.destroy') }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف الشعار؟');">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="type" value="logo">
                        <button type="submit" class="btn btn-sm btn-danger">حذف الشعار الحالي</button>
                    </form>
                @else
                    <div class="alert alert-info">لم يتم رفع شعار. سيتم استخدام شعار Tech-Sys الافتراضي.</div>
                @endif
                
                <div class="mt-3">
                    <label for="logo" class="form-label"> {{ __('رفع شعار جديد (اختياري):') }} </label>
                    <div class="localized-file-wrapper">
                        <button type="button" class="btn btn-sm btn-outline-secondary localized-file-btn">
                            <i class="fas fa-upload me-1"></i> {{ __('اختيار ملف') }}
                        </button>
                        <div class="localized-file-name text-start"> {{ __('لم يتم اختيار ملف') }} </div>
                        <input type="file" name="logo" id="logo" class="@error('logo') is-invalid @enderror" accept="image/*" onchange="updateFileName(this)">
                    </div>
                </div>
            </div>
        </div>

        {{-- قسم الختم --}}
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">2. ختم المتجر (اختياري للفواتير)</h5>
                <p class="card-text">ارفع صورة الختم (يفضل PNG بخلفية شفافة). ستتمكن من إضافته لأي فاتورة.</p>
                @if($sealUrl)
                    <div class="mb-2">
                        <strong>الختم الحالي:</strong><br>
                        <img src="{{ $sealUrl }}" alt="الختم الحالي" style="max-height: 100px; border: 1px solid #ddd; padding: 5px; border-radius: 4px;">
                    </div>
                    
                    {{-- فورم حذف الختم --}}
                    {{--!! -- تصحيح المسار: تم تغيير 'tenant.' إلى 'store.' --!! --}}
                    <form action="{{ route('store.settings.identity.destroy') }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف الختم؟');">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="type" value="seal">
                        <button type="submit" class="btn btn-sm btn-danger">حذف الختم الحالي</button>
                    </form>
                @endif
                <div class="mt-3">
                    <label for="seal" class="form-label"> {{ __('رفع ختم جديد (اختياري - PNG فقط):') }} </label>
                    <div class="localized-file-wrapper">
                        <button type="button" class="btn btn-sm btn-outline-secondary localized-file-btn">
                            <i class="fas fa-upload me-1"></i> {{ __('اختيار ملف') }}
                        </button>
                        <div class="localized-file-name text-start"> {{ __('لم يتم اختيار ملف') }} </div>
                        <input type="file" name="seal" id="seal" class="@error('seal') is-invalid @enderror" accept="image/png" onchange="updateFileName(this)">
                    </div>
                </div>
            </div>
        </div>

        {{-- قسم التوقيع --}}
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">3. توقيع صاحب المتجر (اختياري للفواتير)</h5>
                <p class="card-text">ارفع صورة التوقيع (يفضل PNG بخلفية شفافة).</p>
                @if($signatureUrl)
                    <div class="mb-2">
                        <strong>التوقيع الحالي:</strong><br>
                        <img src="{{ $signatureUrl }}" alt="التوقيع الحالي" style="max-height: 100px; border: 1px solid #ddd; padding: 5px; border-radius: 4px;">
                    </div>

                    {{-- فورم حذف التوقيع --}}
                    {{--!! -- تصحيح المسار: تم تغيير 'tenant.' إلى 'store.' --!! --}}
                    <form action="{{ route('store.settings.identity.destroy') }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف التوقيع؟');">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="type" value="signature">
                        <button type="submit" class="btn btn-sm btn-danger">حذف التوقيع الحالي</button>
                    </form>
                @endif
                <div class="mt-3">
                    <label for="signature" class="form-label"> {{ __('رفع توقيع جديد (اختياري - PNG فقط):') }} </label>
                    <div class="localized-file-wrapper">
                        <button type="button" class="btn btn-sm btn-outline-secondary localized-file-btn">
                            <i class="fas fa-upload me-1"></i> {{ __('اختيار ملف') }}
                        </button>
                        <div class="localized-file-name text-start"> {{ __('لم يتم اختيار ملف') }} </div>
                        <input type="file" name="signature" id="signature" class="@error('signature') is-invalid @enderror" accept="image/png" onchange="updateFileName(this)">
                    </div>
                </div>
            </div>
        </div>
<div class="form-group mb-3">
    <label class="form-label fw-bold">توقيت النظام (Timezone)</label>
    <select name="timezone" class="form-select" dir="ltr"> {{-- dir="ltr" لعرض الوقت بشكل صحيح --}}
        @php
            $currentTz = auth()->user()->store->timezone ?? 'Asia/Riyadh';
            
            // قائمة شاملة بالتوقيت العالمي
            $timezones = [
                'Pacific/Midway'       => '(GMT-11:00) ميدواي، ساموا',
                'America/Adak'         => '(GMT-10:00) هاواي-ألوتيان',
                'Pacific/Honolulu'     => '(GMT-10:00) هاواي',
                'America/Anchorage'    => '(GMT-09:00) ألاسكا',
                'America/Los_Angeles'  => '(GMT-08:00) توقيت المحيط الهادئ (الولايات المتحدة وكندا)',
                'America/Denver'       => '(GMT-07:00) التوقيت الجبلي (الولايات المتحدة وكندا)',
                'America/Chicago'      => '(GMT-06:00) التوقيت المركزي (الولايات المتحدة وكندا)',
                'America/New_York'     => '(GMT-05:00) التوقيت الشرقي (الولايات المتحدة وكندا)',
                'America/Caracas'      => '(GMT-04:00) كاراكاس، لاباز',
                'America/Santiago'     => '(GMT-04:00) سانتياغو',
                'America/St_Johns'     => '(GMT-03:30) نيوفاوندلاند',
                'America/Sao_Paulo'    => '(GMT-03:00) برازيليا',
                'America/Argentina/Buenos_Aires' => '(GMT-03:00) بوينس آيرس، جورج تاون',
                'Atlantic/Azores'      => '(GMT-01:00) جزر الأزور',
                'Europe/London'        => '(GMT+00:00) لندن، دبلن، لشبونة (توقيت جرينتش)',
                'Africa/Casablanca'    => '(GMT+00:00) الدار البيضاء، مونروفيا',
                'Europe/Paris'         => '(GMT+01:00) أمستردام، برلين، روما، باريس، مدريد',
                'Africa/Lagos'         => '(GMT+01:00) غرب وسط أفريقيا',
                'Africa/Cairo'         => '(GMT+02:00) القاهرة',
                'Europe/Kiev'          => '(GMT+02:00) هلسنكي، كييف، ريغا، صوفيا',
                'Asia/Amman'           => '(GMT+02:00) عمّان',
                'Asia/Beirut'          => '(GMT+02:00) بيروت',
                'Asia/Jerusalem'       => '(GMT+02:00) القدس',
                'Africa/Johannesburg'  => '(GMT+02:00) هراري، بريتوريا',
                'Asia/Baghdad'         => '(GMT+03:00) بغداد',
                'Asia/Riyadh'          => '(GMT+03:00) الكويت، الرياض، مكة المكرمة',
                'Europe/Istanbul'      => '(GMT+03:00) إسطنبول',
                'Europe/Moscow'        => '(GMT+03:00) موسكو، سان بطرسبرج',
                'Asia/Tehran'          => '(GMT+03:30) طهران',
                'Asia/Dubai'           => '(GMT+04:00) أبو ظبي، مسقط',
                'Asia/Baku'            => '(GMT+04:00) باكو',
                'Asia/Kabul'           => '(GMT+04:30) كابول',
                'Asia/Karachi'         => '(GMT+05:00) إسلام آباد، كراتشي',
                'Asia/Kolkata'         => '(GMT+05:30) تشيناي، كلكتا، مومباي، نيودلهي',
                'Asia/Kathmandu'       => '(GMT+05:45) كاتماندو',
                'Asia/Dhaka'           => '(GMT+06:00) دكا',
                'Asia/Bangkok'         => '(GMT+07:00) بانكوك، هانوي، جاكرتا',
                'Asia/Hong_Kong'       => '(GMT+08:00) بكين، هونغ كونغ، سنغافورة، كوالالمبور',
                'Asia/Tokyo'           => '(GMT+09:00) أوساكا، سابورو، طوكيو',
                'Asia/Seoul'           => '(GMT+09:00) سيول',
                'Australia/Darwin'     => '(GMT+09:30) داروين',
                'Australia/Sydney'     => '(GMT+10:00) كانبيرا، ملبورن، سيدني',
                'Pacific/Guadalcanal'  => '(GMT+11:00) جزر سليمان، كاليدونيا الجديدة',
                'Pacific/Auckland'     => '(GMT+12:00) أوكلاند، ويلينغتون',
            ];
        @endphp

        @foreach($timezones as $tz => $label)
            <option value="{{ $tz }}" {{ $currentTz == $tz ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>
    <small class="text-muted">سيتم تطبيق هذا التوقيت على كافة الفواتير والتقارير في متجرك.</small>
</div>
        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
    </form>
</div>
@endsection