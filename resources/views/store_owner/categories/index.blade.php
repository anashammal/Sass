@extends('layouts.app')

@section('styles')
<style>
    /* ... (كل كود الـ CSS الخاص بالشجرة موجود هنا كما كان) ... */
    .category-tree { list-style-type: none; padding-right: 0; }
    .category-tree-child { list-style-type: none; padding-right: 25px; margin-right: 12px; border-right: 1px dashed #aaa; }
    .category-tree li { position: relative; }
    .category-tree li::before { content: ''; position: absolute; top: 22px; right: -12px; width: 12px; height: 1px; border-top: 1px dashed #aaa; }
    .category-toggle-link { text-decoration: none; color: inherit; display: inline-block; }
    .category-toggle-link:visited { color: inherit; }
    .category-toggle-icon { transition: transform 0.2s ease-in-out; transform: rotate(0deg); }
    .category-toggle-link.collapsed .category-toggle-icon { transform: rotate(-90deg); }
    .category-tree > li::before { display: none; }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0">إدارة التصنيفات (Categories)</h5>
                    <a href="{{ route('store.categories.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> إضافة تصنيف جديد
                    </a>
                </div>

                <div class="card-body">

                    {{-- لعرض رسالة النجاح (الخضراء) --}}
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- !! -- لعرض رسالة الخطأ (الحمراء) -- !! --}}
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center py-2 px-3 bg-light border-bottom">
                        <strong>اسم التصنيف</strong>
                        <strong>إجراءات</strong>
                    </div>

                    <ul class="category-tree">
                        @forelse ($categories as $category)
                            @include('store_owner.categories._category_partial', ['category' => $category])
                        @empty
                            <li class="text-center p-3">
                                لا توجد تصنيفات لعرضها حالياً.
                            </li>
                        @endforelse
                    </ul>

                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Modal خيارات الحذف -->
    <div class="modal fade" id="deleteOptionsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-dark">⚠️ تحذير: التصنيف غير فارغ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        هذا التصنيف يحتوي على <strong id="modal-product-count" class="text-danger">0</strong> منتج.
                        <br>
                        لا يمكنك حذفه مباشرة للحفاظ على سلامة البيانات.
                    </p>

                    <div class="d-grid gap-3">
                        <!-- خيار 1: نقل المنتجات -->
                        <div class="p-3 border rounded bg-light">
                            <h6>1. نقل المنتجات وحذف التصنيف (موصى به)</h6>
                            <div class="mb-2">
                                <label>انقل المنتجات إلى:</label>
                                <select id="targetCategorySelect" class="form-select">
                                    <!-- سيتم تعبئته بالجافاسكربت -->
                                </select>
                            </div>
                            <button onclick="executeMoveDelete()" class="btn btn-primary w-100">نقل المنتجات وحذف التصنيف</button>
                        </div>

                        <!-- خيار 2: الحذف الإجباري -->
                        <div class="p-3 border rounded border-danger bg-white">
                            <h6 class="text-danger">2. حذف الكل (خطر ⛔)</h6>
                            <p class="small text-muted mb-2">سيتم حذف التصنيف وجميع المنتجات المرتبطة به نهائياً. هذا الإجراء لا يمكن التراجع عنه.</p>
                            <button id="forceDeleteBtn" onclick="initForceDelete()" class="btn btn-danger w-100">
                                حذف الكل نهائياً
                            </button>
                            <div id="deleteCountdown" class="text-center text-danger mt-2 fw-bold" style="display:none;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentDeleteId = null;
        let allCategories = @json($allCategories ?? []);

        function checkDelete(e, id) {
            e.preventDefault();
            
            // تحقق مبدئي بسيط
            fetch("{{ url('store-owner/categories/check-status') }}/" + id)
                .then(res => res.json())
                .then(data => {
                    if (data.products_count === 0 && !data.has_children) {
                        // آمن للحذف المباشر
                        if (confirm('هل أنت متأكد أنك تريد حذف هذا التصنيف؟')) {
                            document.getElementById('delete-form-' + id).submit();
                        }
                    } else if (data.has_children) {
                        alert('لا يمكن حذف التصنيف لأنه يحتوي على تصنيفات فرعية. يرجى حذف الفرعية أولاً.');
                    } else {
                        // يوجد منتجات -> فتح المودال
                        currentDeleteId = id;
                        document.getElementById('modal-product-count').innerText = data.products_count;
                        
                        // تعبئة القائمة المسدلة (استثناء التصنيف الحالي)
                        let select = document.getElementById('targetCategorySelect');
                        select.innerHTML = '';
                        allCategories.forEach(cat => {
                            if (cat.id != id) {
                                let option = document.createElement('option');
                                option.value = cat.id;
                                option.text = cat.name;
                                select.appendChild(option);
                            }
                        });

                        // إعادة تعيين زر الحذف الإجباري
                        let btn = document.getElementById('forceDeleteBtn');
                        btn.disabled = false;
                        btn.innerText = 'حذف الكل نهائياً';
                        btn.classList.remove('btn-secondary');
                        btn.classList.add('btn-danger'); // Ensure red color
                        btn.onclick = initForceDelete; // Reset handler
                        document.getElementById('deleteCountdown').style.display = 'none';

                        new bootstrap.Modal(document.getElementById('deleteOptionsModal')).show();
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('حدث خطأ أثناء التحقق من الحالة.');
                });
                
            return false;
        }

        function executeMoveDelete() {
            let targetId = document.getElementById('targetCategorySelect').value;
            if (!targetId) return alert('يرجى اختيار تصنيف لنقل المنتجات إليه');

            if (!confirm('هل أنت متأكد من نقل المنتجات وحذف هذا التصنيف؟')) return;

            fetch("{{ url('store-owner/categories/move-delete') }}/" + currentDeleteId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ target_category_id: targetId })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    location.reload(); 
                } else {
                    alert('خطأ: ' + (data.message || 'Unknown error'));
                }
            });
        }

        function initForceDelete() {
            let btn = document.getElementById('forceDeleteBtn');
            let countdownDiv = document.getElementById('deleteCountdown');
            let count = 5;
            
            btn.disabled = true;
            countdownDiv.style.display = 'block';
            
            let interval = setInterval(() => {
                btn.innerText = `انتظر... (${count})`;
                countdownDiv.innerText = `سيتم التفعيل خلال ${count} ثواني...`;
                count--;
                
                if (count < 0) {
                    clearInterval(interval);
                    btn.innerText = 'تأكيد الحذف النهائي الآن!';
                    btn.disabled = false;
                    countdownDiv.style.display = 'none';
                    // تغيير وظيفة الزر للتنفيذ الفعلي
                    btn.onclick = executeForceDelete;
                }
            }, 1000);
        }

        function executeForceDelete() {
            if (!confirm('تحذير أخير: سيتم مسح المنتجات نهائياً! هل أنت متأكد تماماً؟')) return;

            fetch("{{ url('store-owner/categories/force-delete') }}/" + currentDeleteId, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert('خطأ: ' + data.message);
                }
            });
        }
    </script>
@endsection
