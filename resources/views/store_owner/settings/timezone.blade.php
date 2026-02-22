@extends('layouts.app')

@section('content')
<div class="container pb-5">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3 class="mb-0 text-primary fw-bold"><i class="fas fa-clock me-2"></i> توقيت وساعة النظام</h3>
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
                <div class="card shadow-sm border-0 mb-4 h-100">
                    <div class="card-header bg-white text-primary fw-bold border-bottom">
                        <i class="fa fa-globe me-2"></i> إعدادات التوقيت (Timezone)
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">توقيت النظام الخاص في فواتيرك</label>
                            <select name="timezone" id="timezone-select" class="form-select" dir="ltr">
                                @foreach(timezone_identifiers_list() as $tz)
                                    <option value="{{ $tz }}" {{ $store->timezone == $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-info-circle me-1"></i> سيتم تطبيق هذا التوقيت على كافة الفواتير والتقارير في متجرك.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="card bg-white border-primary shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-palette me-2"></i> شكل الساعة الافتراضية للوحة التحكم</h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="row align-items-center justify-content-center mb-3">
                            <div class="col-md-6 text-center">
                                <div class="btn-group w-100 shadow-sm" role="group">
                                    <input type="radio" class="btn-check" name="clock_type" id="clock_digital" value="digital" {{ $store->clock_type == 'digital' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="clock_digital"><i class="fas fa-font me-1"></i> رقمية</label>
        
                                    <input type="radio" class="btn-check" name="clock_type" id="clock_analog" value="analog" {{ $store->clock_type == 'analog' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="clock_analog"><i class="far fa-clock me-1"></i> عقارب</label>
                                </div>
                            </div>
                        </div>
        
                        <div id="digital_themes" style="display: {{ $store->clock_type == 'digital' ? 'block' : 'none' }};">
                            <p class="small text-muted mb-2">اختر شكل الساعة الرقمية الافتراضي</p>
                            <div class="d-flex justify-content-center flex-wrap gap-2">
                                @php 
                                $d_themes = [
                                    'minimal_light' => 'Minimal (أبيض نقي)', 
                                    'glass' => 'Glass (زجاجي شفاف)', 
                                    'midnight' => 'Midnight (أزرق ليلي)', 
                                    'sunset' => 'Sunset (ضباب برتقالي)', 
                                    'aurora' => 'Aurora (تدرج نيون)'
                                ]; 
                                @endphp
                                @foreach($d_themes as $k => $label)
                                    <input type="radio" class="btn-check" name="clock_theme" id="theme_{{ $k }}" value="{{ $k }}" {{ ($store->clock_theme == $k && $store->clock_type == 'digital') ? 'checked' : '' }}>
                                    <label class="btn btn-sm btn-outline-dark fw-bold" for="theme_{{ $k }}">{{ $label }}</label>
                                @endforeach
                            </div>
                        </div>
        
                        <div id="analog_themes" style="display: {{ $store->clock_type == 'analog' ? 'block' : 'none' }};">
                            <p class="small text-muted mb-2">اختر شكل ساعة العقارب الافتراضي</p>
                            <div class="d-flex justify-content-center flex-wrap gap-2">
                                @php 
                                $a_themes = [
                                    'classic' => 'Classic (كلاسيكي سويسري)', 
                                    'modern' => 'Modern (أسود عصري)', 
                                    'station' => 'Station (محطة القطار)', 
                                    'minimalist' => 'Minimal (مفاهيمي نظيف)', 
                                    'vintage' => 'Vintage (خشب عتيق)'
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
                            <i class="fa fa-save me-2"></i> حفظ الإعدادات
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
            if(window.updateLiveClockPreview) {
                let currentType = $('input[name="clock_type"]:checked').val();
                let currentTheme = '';
                if(currentType === 'digital') {
                    currentTheme = $('#digital_themes input[name="clock_theme"]:checked').val();
                } else {
                    currentTheme = $('#analog_themes input[name="clock_theme"]:checked').val();
                }
                if(currentTheme) {
                    window.updateLiveClockPreview(currentType, currentTheme);
                }
            }
        }

        $('input[name="clock_type"]').change(function() {
            if ($(this).val() == 'digital') {
                $('#analog_themes').slideUp(); $('#digital_themes').slideDown();
                let checks = $('#digital_themes input:checked');
                if(checks.length === 0) $('#theme_minimal_light').prop('checked', true);
            } else {
                $('#digital_themes').slideUp(); $('#analog_themes').slideDown();
                let checks = $('#analog_themes input:checked');
                if(checks.length === 0) $('#theme_classic').prop('checked', true);
            }
            triggerPreview();
        });

        $('input[name="clock_theme"]').change(function() {
            triggerPreview();
        });
    });
</script>
@endsection
