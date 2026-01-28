@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0">إنشاء تصنيف جديد</h5>
                    <a href="{{ route('store.categories.index') }}" class="btn btn-outline-secondary btn-sm">
                        العودة إلى القائمة
                    </a>
                </div>

                <div class="card-body">

                    <form method="POST" action="{{ route('store.categories.store') }}"> 
                        @csrf

                        {{-- اسم التصنيف --}}
                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">اسم التصنيف</label>
                            <div class="col-md-8">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        {{-- التصنيف الأب (للتصنيفات الفرعية) --}}
                        <div class="row mb-3">
                            <label for="parent_id" class="col-md-4 col-form-label text-md-end">التصنيف الأب (اختياري)</label>
                            <div class="col-md-8">
                                {{-- !! تم تعديل هذه القائمة !! --}}
                                <select id="parent_id" class="form-control @error('parent_id') is-invalid @enderror" name="parent_id">
                                    <option value="">-- تصنيف رئيسي --</option>
                                    {{-- قمنا بتغيير اسم المتغير إلى "allCategories" --}}
                                    @foreach ($allCategories as $category)
                                        <option value="{{ $category->id }}" {{ old('parent_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">اتركه فارغاً لجعله تصنيفاً رئيسياً.</small>
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
                                    حفظ التصنيف
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
