<div class="table-responsive" id="logsTableContainer">
    <table class="table table-bordered table-hover text-center align-middle" width="100%">
        <thead class="bg-light">
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 15%">{{ __('product_name') }}</th>
                <th style="width: 10%">{{ __('barcode') }}</th>
                <th style="width: 12%">{{ __('adjustment_date') }}</th>
                <th style="width: 10%">{{ __('action_type') }}</th>
                <th style="width: 12%">{{ __('status_before') }}</th>
                <th style="width: 12%">{{ __('status_after') }}</th>
                <th style="width: 12%">{{ __('by_user') }}</th>
                <th style="width: 12%">{{ __('action_reason') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $index => $log)
            <tr>
                <td>{{ $logs instanceof \Illuminate\Pagination\LengthAwarePaginator ? ($logs->firstItem() + $index) : ($index + 1) }}</td>
                <td class="text-start fw-bold">{{ $log->product->name ?? __('deleted_product') }}</td>
                <td><code class="text-dark">{{ $log->product->sku ?? '---' }}</code></td>
                <td class="small">{{ $log->created_at->format('Y-m-d h:i A') }}</td>
                <td>
                    @switch($log->action)
                        @case('manual_adjustment') <span class="badge bg-info text-dark">{{ __('manual_adjustment') }}</span> @break
                        @case('dispose') <span class="badge bg-danger">{{ __('dispose_inventory') }}</span> @break
                        @case('extend_expiry') <span class="badge bg-warning text-dark">{{ __('extend_expiry') }}</span> @break
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
                <td class="small">
                    @if($log->reason == 'تعديل مخزون سريع')
                        {{ __('reason_quick_adjustment') }}
                    @elseif($log->reason == 'تعديل مخزون سريع من نقطة البيع')
                        {{ __('reason_quick_adjustment_pos') }}
                    @else
                        {{ $log->reason ?? '---' }}
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center py-5 text-muted">{{ __('no_matching_adjustment_records') }}</td>
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
