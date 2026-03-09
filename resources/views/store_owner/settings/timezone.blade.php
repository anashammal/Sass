@extends('layouts.app')

@section('content')
<div class="container pb-5">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3 class="mb-0 text-primary fw-bold"><i class="fas fa-clock me-2"></i> {{ __('توقيت وساعة النظام') }}</h3>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success shadow-sm border-0">
            <i class="fa fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('store.settings.update-timezone', $store->id) }}">
        @csrf
        @method('PUT')

        <div class="row mb-4 justify-content-center">
            <div class="col-lg-8">
                <style>
                    .preview-clock-container {
                        background: rgba(255, 255, 255, 0.4);
                        backdrop-filter: blur(10px);
                        border-radius: 20px;
                        padding: 30px;
                        border: 1px dashed rgba(102, 126, 234, 0.3);
                        margin: 20px 0;
                        transition: all 0.3s ease;
                        min-height: 180px;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        justify-content: center;
                    }
                    .preview-label {
                        font-size: 0.75rem;
                        font-weight: 800;
                        color: #6366f1;
                        text-transform: uppercase;
                        letter-spacing: 1px;
                        margin-bottom: 15px;
                    }
                    /* Ensure preview clocks look like the header ones but bigger/centered */
                    .preview-clock-container .digital-container { transform: scale(1.5); margin: 20px 0; }
                    .preview-clock-container .analog-face { transform: scale(3); margin: 40px 0; }
                </style>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white text-primary fw-bold border-bottom">
                        <i class="fa fa-globe me-2"></i> {{ __('إعدادات التوقيت (Timezone)') }}
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">{{ __('توقيت النظام الخاص في فواتيرك') }}</label>
                            <select name="timezone" id="timezone-select" class="form-select" dir="ltr">
                                @foreach(timezone_identifiers_list() as $tz)
                                    <option value="{{ $tz }}" {{ $store->timezone == $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-info-circle me-1"></i> {{ __('سيتم تطبيق هذا التوقيت على كافة الفواتير والتقارير في متجرك.') }}
                            </small>
                        </div>
                    </div>
                </div>

                <!-- 🔥 Live Preview Section 🔥 -->
                <div class="preview-clock-container shadow-sm border-0">
                    <div class="preview-label"><i class="fas fa-eye me-1"></i> {{ __('معاينة مباشرة للساعة (Live Preview)') }}</div>
                    
                    <div id="preview_digital_wrap" class="d-none">
                        <div id="preview_digital_el" class="digital-container align-items-center">
                            <div class="time-box">
                                <span class="preview_time">12:45:00</span>
                                <span class="ampm">PM</span>
                            </div>
                            <div class="date-box ms-2 ps-2 border-start">
                                <div class="city">{{ str_replace('_', ' ', explode('/', $store->timezone)[1] ?? $store->timezone) }}</div>
                                <div class="date">01/01/2026</div>
                            </div>
                        </div>
                    </div>

                    <div id="preview_analog_wrap" class="d-none">
                        <div class="analog-face" id="preview_analog_el">
                            @for($i=1; $i<=12; $i++) <div class="marker m-{{$i}}"></div> @endfor
                            <div class="hand hour-hand" style="transform: rotate(30deg)"></div>
                            <div class="hand min-hand" style="transform: rotate(90deg)"></div>
                            <div class="hand sec-hand" style="transform: rotate(180deg)"></div>
                            <div class="center-cap"></div>
                        </div>
                    </div>
                </div>

                <div class="card bg-white border-primary shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-palette me-2"></i> {{ __('شكل الساعة الافتراضية للوحة التحكم') }}</h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="row align-items-center justify-content-center mb-3">
                            <div class="col-md-6 text-center">
                                <div class="btn-group w-100 shadow-sm" role="group">
                                    <input type="radio" class="btn-check" name="clock_type" id="clock_digital" value="digital" {{ $store->clock_type == 'digital' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="clock_digital"><i class="fas fa-font me-1"></i> {{ __('رقمية') }}</label>
        
                                    <input type="radio" class="btn-check" name="clock_type" id="clock_analog" value="analog" {{ $store->clock_type == 'analog' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="clock_analog"><i class="far fa-clock me-1"></i> {{ __('عقارب') }}</label>
                                </div>
                            </div>
                        </div>
        
                        <div id="digital_themes" style="display: {{ $store->clock_type == 'digital' ? 'block' : 'none' }};">
                            <p class="small text-muted mb-2">{{ __('اختر شكل الساعة الرقمية الافتراضي') }}</p>
                            <div class="d-flex justify-content-center flex-wrap gap-2">
                                @php 
                                $d_themes = [
                                    'minimal_light' => __('Minimal (أبيض نقي)'), 
                                    'glass' => __('Glass (زجاجي شفاف)'), 
                                    'midnight' => __('Midnight (أزرق ليلي)'), 
                                    'sunset' => __('Sunset (ضباب برتقالي)'), 
                                    'aurora' => __('Aurora (تدرج نيون)')
                                ]; 
                                @endphp
                                @foreach($d_themes as $k => $label)
                                    <input type="radio" class="btn-check" name="clock_theme" id="theme_{{ $k }}" value="{{ $k }}" {{ ($store->clock_theme == $k && $store->clock_type == 'digital') ? 'checked' : '' }}>
                                    <label class="btn btn-sm btn-outline-dark fw-bold" for="theme_{{ $k }}">{{ $label }}</label>
                                @endforeach
                            </div>
                        </div>
        
                        <div id="analog_themes" style="display: {{ $store->clock_type == 'analog' ? 'block' : 'none' }};">
                            <p class="small text-muted mb-2">{{ __('اختر شكل ساعة العقارب الافتراضي') }}</p>
                            <div class="d-flex justify-content-center flex-wrap gap-2">
                                @php 
                                $a_themes = [
                                    'classic' => __('Classic (كلاسيكي سويسري)'), 
                                    'modern' => __('Modern (أسود عصري)'), 
                                    'station' => __('Station (محطة القطار)'), 
                                    'minimalist' => __('Minimal (مفاهيمي نظيف)'), 
                                    'vintage' => __('Vintage (خشب عتيق)')
                                ]; 
                                @endphp
                                @foreach($a_themes as $k => $label)
                                    <input type="radio" class="btn-check" name="clock_theme" id="theme_{{ $k }}" value="{{ $k }}" {{ ($store->clock_theme == $k && $store->clock_type == 'analog') ? 'checked' : '' }}>
                                    <label class="btn btn-sm btn-outline-dark fw-bold" for="theme_{{ $k }}">{{ $label }}</label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary btn-lg px-5 shadow rounded-pill">
                            <i class="fa fa-save me-2"></i> {{ __('حفظ الإعدادات') }}
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('#timezone-select').select2({ theme: 'bootstrap-5', width: '100%' });
        }

        function triggerPreview() {
            let currentType = $('input[name="clock_type"]:checked').val();
            let currentTheme = '';
            
            if(currentType === 'digital') {
                currentTheme = $('#digital_themes input[name="clock_theme"]:checked').val();
                
                $('#preview_analog_wrap').addClass('d-none');
                $('#preview_digital_wrap').removeClass('d-none');
                $('#preview_digital_el').attr('class', 'digital-container d-flex align-items-center ' + currentTheme);
            } else {
                currentTheme = $('#analog_themes input[name="clock_theme"]:checked').val();
                
                $('#preview_digital_wrap').addClass('d-none');
                $('#preview_analog_wrap').removeClass('d-none');
                $('#preview_analog_el').attr('class', 'analog-face ' + currentTheme);
            }

            // Also update global preview if available
            if(window.updateLiveClockPreview) {
                window.updateLiveClockPreview(currentType, currentTheme);
            }
        }

        // Initialize preview
        triggerPreview();

        $('input[name="clock_type"]').change(function() {
            if ($(this).val() == 'digital') {
                $('#analog_themes').slideUp(); $('#digital_themes').slideDown();
                // Find visible/selected theme or default
                let checks = $('#digital_themes input[name="clock_theme"]:checked');
                if(checks.length === 0) $('#theme_minimal_light').prop('checked', true);
            } else {
                $('#digital_themes').slideUp(); $('#analog_themes').slideDown();
                let checks = $('#analog_themes input[name="clock_theme"]:checked');
                if(checks.length === 0) $('#theme_classic').prop('checked', true);
            }
            triggerPreview();
        });

        $('input[name="clock_theme"]').change(function() {
            triggerPreview();
        });

        // Update preview time every second
        setInterval(function() {
            let now = new Date();
            let h = now.getHours();
            let m = now.getMinutes();
            let s = now.getSeconds();
            let ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12; h = h ? h : 12;
            m = m < 10 ? '0'+m : m;
            s = s < 10 ? '0'+s : s;
            $('.preview_time').text(h + ':' + m + ':' + s);
            $('#preview_digital_el .ampm').text(ampm);
            
            // For analog hands in preview
            let hDeg = (h * 30) + (m / 2);
            let mDeg = m * 6;
            let sDeg = s * 6;
            $('#preview_analog_el .hour-hand').css('transform', 'rotate('+hDeg+'deg)');
            $('#preview_analog_el .min-hand').css('transform', 'rotate('+mDeg+'deg)');
            $('#preview_analog_el .sec-hand').css('transform', 'rotate('+sDeg+'deg)');
        }, 1000);
    });
</script>
@endsection
