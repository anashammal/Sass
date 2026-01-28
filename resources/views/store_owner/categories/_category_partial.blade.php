<li>
    <div class="category-item d-flex justify-content-between align-items-center p-2 border-bottom">

        <a href="#category-children-{{ $category->id }}" 
           data-bs-toggle="collapse" 
           role="button" 
           aria-expanded="true" 
           aria-controls="category-children-{{ $category->id }}"
           class="category-toggle-link {{ $category->children->isEmpty() ? 'disabled' : '' }}">

            @if($category->children->isNotEmpty())
                <i class="fa fa-caret-down category-toggle-icon"></i>
            @else
                <i class="fa fa-circle" style="font-size: 6px; vertical-align: middle; opacity: 0.3;"></i>
            @endif

            <strong class="me-1">{{ $category->name }}</strong>
        </a>

        <div>
            {{-- !! -- هذا هو الرابط الذي قمنا بتفعيله -- !! --}}
            <a href="{{ route('store.categories.edit', $category->id) }}" class="btn btn-sm btn-outline-secondary">تعديل</a>

            {{-- فورم الحذف (كما هو) --}}
            <form action="{{ route('store.categories.destroy', $category->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('هل أنت متأكد أنك تريد حذف هذا التصنيف؟');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
            </form>
        </div>
    </div>

    {{-- استدعاء ذاتي (Recursion) --}}
    @if ($category->children->isNotEmpty())
        <ul class="category-tree-child collapse show" id="category-children-{{ $category->id }}">
            @foreach ($category->children as $child)
                @include('store_owner.categories._category_partial', ['category' => $child])
            @endforeach
        </ul>
    @endif
</li>
