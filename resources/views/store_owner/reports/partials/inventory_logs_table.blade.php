<div class="table-responsive" id="logsTableContainer">
    <table class="table table-bordered table-hover text-center align-middle" width="100%">
        <thead class="bg-light">
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 15%">اسم المنتج</th>
                <th style="width: 10%">الباركود</th>
                <th style="width: 12%">تاريخ التعديل</th>
                <th style="width: 10%">نوع العملية</th>
                <th style="width: 12%">الحالة قبل</th>
                <th style="width: 12%">الحالة بعد</th>
                <th style="width: 12%">بواسطة</th>
                <th style="width: 12%">السبب</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $index => $log)
            <tr>
                <td>{{ $logs instanceof \Illuminate\Pagination\LengthAwarePaginator ? ($logs->firstItem() + $index) : ($index + 1) }}</td>
                <td class="text-start fw-bold">{{ $log->product->name ?? 'منتج محذوف' }}</td>
                <td><code class="text-dark">{{ $log->product->sku ?? '---' }}</code></td>
                <td class="small">{{ $log->created_at->format('Y-m-d h:i A') }}</td>
                <td>
                    @switch($log->action)
                        @case('manual_adjustment') <span class="badge bg-info text-dark">تعديل يدوي</span> @break
                        @case('dispose') <span class="badge bg-danger">إتلاف مخزون</span> @break
                        @case('extend_expiry') <span class="badge bg-warning text-dark">مدد الصلاحية</span> @break
                        @default <span class="badge bg-secondary">{{ $log->action }}</span>
                    @endswitch
                </td>
                <td class="small text-muted">
                    @if($log->action == 'extend_expiry')
                        📅 {{ $log->old_date ?? '---' }}
                    @else
                        📦 {{ floatval($log->old_quantity) }}
                    @endif
                </td>
                <td class="small fw-bold">
                    @if($log->action == 'extend_expiry')
                        📅 {{ $log->new_date ?? '---' }}
                    @else
                        📦 {{ floatval($log->new_quantity) }}
                    @endif
                </td>
                <td>
                    <span class="badge border text-dark bg-light shadow-sm">
                        <i class="fas fa-user-shield me-1 text-primary"></i> {{ $log->user->name ?? 'System' }}
                    </span>
                </td>
                <td class="small">{{ $log->reason ?? '---' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center py-5 text-muted">لا توجد سجلات تعديل مطابقة للفلاتر المختارة</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($logs instanceof \Illuminate\Pagination\LengthAwarePaginator)
    <div class="d-flex justify-content-center mt-4 no-print pagination-container">
        {{ $logs->links() }}
    </div>
    @endif
</div>
