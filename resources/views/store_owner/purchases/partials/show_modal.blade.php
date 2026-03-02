<div class="row" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="col-md-6">
        <h5>{{ __('invoice_no_value') }}: {{ $purchase->invoice_number }}</h5>
        <p>{{ __('supplier_label') }}: <strong>{{ $purchase->supplier->contact_name ?? __('undefined_supplier') }}</strong></p>
        <p>{{ __('date_label') }}: {{ $purchase->invoice_date }}</p>
    </div>
    <div class="col-md-6 {{ app()->getLocale() == 'ar' ? 'text-start' : 'text-end' }}">
        <span class="badge bg-{{ $purchase->payment_status == 'paid' ? 'success' : 'warning' }}">
            @if($purchase->payment_status == 'paid') {{ __('paid_status') }}
            @elseif($purchase->payment_status == 'partial') {{ __('partial_status') }}
            @else {{ __('unpaid_status') }}
            @endif
        </span>
    </div>
</div>

<hr>

<div class="table-responsive" style="background-color: #fff;">
    <table class="table table-bordered table-sm" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
        <thead class="table-light">
            <tr>
                <th class="{{ app()->getLocale() == 'ar' ? 'text-end' : 'text-start' }}">{{ __('table_product') }}</th>
                <th class="text-center">{{ __('table_unit') }}</th>
                <th class="text-center">{{ __('table_quantity') }}</th>
                <th class="text-center">{{ __('table_price') }}</th>
                <th class="{{ app()->getLocale() == 'ar' ? 'text-start' : 'text-end' }}">{{ __('table_total') }} ({{ $purchase->currency ? $purchase->currency->code : $globalCurrencySymbol }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase->items as $item)
            <tr>
                <td class="{{ app()->getLocale() == 'ar' ? 'text-end' : 'text-start' }}">{{ $item->product->name ?? __('deleted_product') }}</td>
                <td class="text-center">{{ $item->unit->unit_name ?? '-' }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-center">{{ number_format($item->unit_price, 2) }} <small class="text-muted">{{ $purchase->currency ? $purchase->currency->symbol : $globalCurrencySymbol }}</small></td>
                <td class="{{ app()->getLocale() == 'ar' ? 'text-start' : 'text-end' }}">{{ number_format($item->total_cost, 2) }} <small class="text-muted">{{ $purchase->currency ? $purchase->currency->symbol : $globalCurrencySymbol }}</small></td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="{{ app()->getLocale() == 'ar' ? 'text-start' : 'text-end' }}"><strong>{{ __('grand_total_label') }}</strong></td>
                <td class="{{ app()->getLocale() == 'ar' ? 'text-start' : 'text-end' }}">
                    <div class="d-flex flex-column">
                        <strong class="text-primary">{{ number_format($purchase->grand_total, 2) }} {{ $purchase->currency ? $purchase->currency->symbol : $globalCurrencySymbol }}</strong>
                        @if($purchase->exchange_rate && $purchase->exchange_rate != 1)
                            <small class="text-muted" style="font-size: 0.8rem;">
                                ≈ {{ number_format($purchase->grand_total_in_base_currency, 2) }} {{ $globalCurrencySymbol }}
                            </small>
                        @endif
                    </div>
                </td>
            </tr>
        </tfoot>
    </table>
</div>

<hr>

<div class="d-flex justify-content-between no-print" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('close_btn') }}</button>
    
    <div>
        <button type="button" class="btn btn-success" onclick="printInvoiceContent()">
            <i class="fa fa-print"></i> {{ __('print_btn') }}
        </button>

        <a href="{{ route('store.purchases.edit', $purchase->id) }}" class="btn btn-warning">
            <i class="fa fa-edit"></i> {{ __('edit_invoice_btn') }}
        </a>

        <form action="{{ route('store.purchases.destroy', $purchase->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('{{ __('confirm_delete_msg') }}');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="fa fa-trash"></i> {{ __('delete_invoice_btn') }}
            </button>
        </form>
    </div>
</div>