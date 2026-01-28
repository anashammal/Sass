@forelse($withdrawals as $sale)
    <tr>
        <td class="fw-bold">{{ $sale['number'] }}</td>
        <td>{{ $sale['items_count'] }}</td>
        <td class="text-danger fw-bold">{{ number_format($sale['total_cost'], 2) }}</td>
        <td class="text-success fw-bold">{{ number_format($sale['total_sale'], 2) }}</td>
        <td class="text-muted">{{ $sale['date'] }}</td>
        <td>
            <button class="btn btn-sm btn-outline-primary" onclick="viewInvoice({{ $sale['id'] }})">
                <i class="fas fa-eye"></i> التفاصيل
            </button>
            <button class="btn btn-sm btn-outline-warning" onclick="returnItems({{ $sale['id'] }})">
                <i class="fas fa-undo"></i> إرجاع
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center py-5 text-muted">
            <i class="fas fa-box-open fa-3x mb-3 opacity-50"></i>
            <p>لا توجد مسحوبات مسجلة</p>
        </td>
    </tr>
@endforelse
<tr class="d-none">
    <td colspan="6" id="paginationLinks">
        {{ $withdrawals->links() }}
    </td>
</tr>
