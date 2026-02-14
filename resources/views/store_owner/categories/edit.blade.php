@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0">{{ __('تعديل التصنيف:') }} {{ $category->name }}</h5>
                    <a href="{{ route('store.categories.index') }}" class="btn btn-outline-secondary btn-sm">
                        {{ __('العودة إلى القائمة') }}
                    </a>
                </div>

                <div class="card-body">

                    {{-- !! انتبه: قمنا بتغيير "action" و "method" هنا !! --}}
                    <form method="POST" action="{{ route('store.categories.update', $category->id) }}">
                        @csrf
                        @method('PUT') {{-- !! هذا السطر ضروري لإرسال طلب "تحديث" !! --}}

                        {{-- اسم التصنيف --}}
                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('اسم التصنيف') }}</label>
                            <div class="col-md-8">
                                {{-- !! قمنا بإضافة "value" لعرض الاسم القديم !! --}}
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $category->name) }}" required>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        {{-- التصنيف الأب (للتصنيفات الفرعية) --}}
                        <div class="row mb-3">
                            <label for="parent_id" class="col-md-4 col-form-label text-md-end">{{ __('التصنيف الأب (اختياري)') }}</label>
                            <div class="col-md-8">
                                <select id="parent_id" class="form-control @error('parent_id') is-invalid @enderror" name="parent_id">
                                    <option value="">-- {{ __('تصنيف رئيسي') }} --</option>
                                    {{-- قمنا بتغيير اسم المتغير إلى "allCategories" --}}
                                    @foreach ($allCategories as $cat)
                                        {{-- !! قمنا بإضافة "selected" لاختيار الأب الحالي !! --}}
                                        <option value="{{ $cat->id }}" {{ old('parent_id', $category->parent_id) == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">{{ __('اتركه فارغاً لجعله تصنيفاً رئيسياً.') }}</small>
                                @error('parent_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        {{-- زر الحفظ --}}
                        <div class="row mb-0 mt-4">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('حفظ التعديلات') }}
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
