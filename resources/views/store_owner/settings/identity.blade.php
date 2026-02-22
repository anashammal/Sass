@extends('layouts.app')

@section('content')
<div class="container pb-5">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3 class="mb-0 text-primary fw-bold"><i class="fas fa-palette me-2"></i> الهوية البصرية للعلامة التجارية</h3>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success shadow-sm border-0">
            <i class="fa fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('store.settings.update-identity') }}" enctype="multipart/form-data">
        @csrf

        <div class="row mb-4 justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4 h-100">
                    <div class="card-header bg-white text-primary fw-bold border-bottom">
                        <i class="fa fa-paint-brush me-2"></i> ملفات الهوية البصرية
                    </div>
                    <div class="card-body">
                        
                        {{-- الشعار --}}
                        <div class="mb-5 text-center">
                            <label class="form-label fw-bold d-block">شعار المتجر (Logo)</label>
                            <div class="mb-3 p-3 border rounded bg-light d-inline-block position-relative" style="min-width: 200px; min-height: 120px;">
                                @if($store->logo_path)
                                    <img id="preview_logo" src="{{ $store->logo_url }}?t={{ time() }}" height="100" alt="Logo" style="mix-blend-mode: multiply;">
                                @else
                                    <div class="text-muted p-4 d-flex flex-column justify-content-center align-items-center h-100" id="placeholder_logo">
                                        <i class="fas fa-image fa-2x mb-2 opacity-50"></i>
                                        <span>لا يوجد شعار</span>
                                    </div>
                                    <img id="preview_logo" src="" height="100" style="display:none; mix-blend-mode: multiply;">
                                @endif
                            </div>
                            <div class="localized-file-wrapper mx-auto" style="max-width: 300px;">
                                <button type="button" class="btn btn-sm btn-outline-secondary localized-file-btn w-100 text-center">
                                    <i class="fas fa-upload me-1"></i> تحميل شعار جديد
                                </button>
                                <div class="localized-file-name text-center mt-1 small text-muted">لم يتم اختيار ملف</div>
                                <input type="file" name="logo" class="form-control" accept="image/*" onchange="previewImage(this, 'preview_logo', 'placeholder_logo'); updateFileName(this)">
                            </div>
                        </div>

                        <hr class="text-muted opacity-25">

                        {{-- الختم --}}
                        <div class="mb-5 text-center mt-4">
                            <label class="form-label fw-bold d-block">الختم الإلكتروني (Stamp)</label>
                            <div class="mb-3 p-3 border rounded bg-light d-inline-block position-relative" style="min-width: 200px; min-height: 120px;">
                                @if($store->stamp_path)
                                    <img id="preview_stamp" src="{{ $store->stamp_url }}?t={{ time() }}" height="100" alt="Stamp" style="mix-blend-mode: multiply;">
                                @else
                                    <div class="text-muted p-4 d-flex flex-column justify-content-center align-items-center h-100" id="placeholder_stamp">
                                        <i class="fas fa-stamp fa-2x mb-2 opacity-50"></i>
                                        <span>لا يوجد ختم</span>
                                    </div>
                                    <img id="preview_stamp" src="" height="100" style="display:none; mix-blend-mode: multiply;">
                                @endif
                            </div>
                            <div class="localized-file-wrapper mx-auto" style="max-width: 300px;">
                                <button type="button" class="btn btn-sm btn-outline-secondary localized-file-btn w-100 text-center">
                                    <i class="fas fa-upload me-1"></i> تحميل ختم إلكتروني
                                </button>
                                <div class="localized-file-name text-center mt-1 small text-muted">لم يتم اختيار ملف</div>
                                <input type="file" name="stamp" class="form-control" accept="image/*" onchange="previewImage(this, 'preview_stamp', 'placeholder_stamp'); updateFileName(this)">
                            </div>
                        </div>

                        <hr class="text-muted opacity-25">

                        {{-- التوقيع --}}
                        <div class="mb-4 text-center mt-4">
                            <label class="form-label fw-bold d-block">التوقيع المعتمد (Signature)</label>
                            <div class="mb-3 p-3 border rounded bg-light d-inline-block position-relative" style="min-width: 200px; min-height: 120px;">
                                @if($store->signature_path)
                                    <img id="preview_signature" src="{{ $store->signature_url }}?t={{ time() }}" height="80" alt="Sign" style="mix-blend-mode: multiply;">
                                @else
                                    <div class="text-muted p-4 d-flex flex-column justify-content-center align-items-center h-100" id="placeholder_sign">
                                        <i class="fas fa-signature fa-2x mb-2 opacity-50"></i>
                                        <span>لا يوجد توقيع</span>
                                    </div>
                                    <img id="preview_signature" src="" height="80" style="display:none; mix-blend-mode: multiply;">
                                @endif
                            </div>
                            <div class="localized-file-wrapper mx-auto" style="max-width: 300px;">
                                <button type="button" class="btn btn-sm btn-outline-secondary localized-file-btn w-100 text-center">
                                    <i class="fas fa-upload me-1"></i> تحميل توقيع معتمد
                                </button>
                                <div class="localized-file-name text-center mt-1 small text-muted">لم يتم اختيار ملف</div>
                                <input type="file" name="signature" class="form-control" accept="image/*" onchange="previewImage(this, 'preview_signature', 'placeholder_sign'); updateFileName(this)">
                            </div>
                        </div>

                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary btn-lg px-5 shadow rounded-pill">
                            <i class="fa fa-save me-2"></i> حفظ تحديثات الهوية
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
    function previewImage(input, imgId, placeholderId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                if(document.getElementById(placeholderId)) document.getElementById(placeholderId).style.display = 'none';
                var img = document.getElementById(imgId); 
                img.style.display = 'block'; 
                img.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    function updateFileName(input) {
        let wrapper = input.closest('.localized-file-wrapper');
        if(!wrapper) return;
        let nameDisplay = wrapper.querySelector('.localized-file-name');
        if(!nameDisplay) return;
        
        if (input.files && input.files[0]) {
            nameDisplay.textContent = input.files[0].name;
            nameDisplay.classList.add('text-success');
            nameDisplay.classList.remove('text-muted');
        } else {
            nameDisplay.textContent = 'لم يتم اختيار ملف';
            nameDisplay.classList.add('text-muted');
            nameDisplay.classList.remove('text-success');
        }
    }
</script>
@endsection