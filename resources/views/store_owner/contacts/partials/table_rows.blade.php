@forelse($contacts as $contact)
<tr class="align-middle">
    
{{-- ????? --}}
<td>
    @if(empty($contact->contact_name) || Str::contains($contact->contact_name, '???'))
        <span class="badge bg-danger">{{ __('لا يوجد اسم') }}</span>
    @else
        <span class="fw-bold text-dark">{{ $contact->contact_name }}</span>
    @endif
</td>
    {{-- ?????? --}}
    <td>
        @if($contact->company_name)
            <span class="fw-bold text-muted">{{ $contact->company_name }}</span>
        @else
            <span class="text-muted small">--</span>
        @endif
    </td>

{{-- ????? --}}
    <td>
        @php 
            $type = strtolower(trim($contact->type)); 
        @endphp

        @if(in_array($type, ['client', 'customer', 'زبون']))
            {{-- النوع: زبون --}}
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2">
                <i class="fas fa-user ms-1"></i> {{ __('زبون') }}
            </span>
        @elseif(in_array($type, ['supplier', 'vendor', 'مورد']))
            {{-- النوع: مورد --}}
            <span class="badge bg-warning bg-opacity-10 text-dark border border-warning px-2">
                <i class="fas fa-truck ms-1"></i> {{ __('مورد') }}
            </span>
        @elseif(in_array($type, ['both', 'client_supplier', 'مشترك']))
            {{-- النوع: مشترك --}}
            <span class="badge bg-success bg-opacity-10 text-success border border-success px-2">
                <i class="fas fa-exchange-alt ms-1"></i> {{ __('مشترك') }}
            </span>
        @else
            <span class="badge bg-secondary">{{ $contact->type }}</span>
        @endif
    </td>

    <td dir="ltr" class="font-monospace small">
        {{ $contact->phone ?? '--' }}
        @if($contact->phone_verified_at)
            <i class="fas fa-certificate text-success ms-1" title="{{ __('تم التحقق') }}"></i>
        @endif
    </td>
    
    <td class="small">
        @if($contact->email)
            <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>
            @if($contact->email_verified_at)
                <i class="fas fa-certificate text-success ms-1" title="{{ __('تم التحقق') }}"></i>
            @endif
        @else <span class="text-muted">--</span> @endif
    </td>
    
    <td class="font-monospace small">{{ $contact->tax_number ?? '--' }}</td>
    
    {{-- ? ??????? (?? ???????: ????? truncate ??????? ??????? ???? ????) --}}
    <td style="min-width: 200px; white-space: normal;">
        {{ $contact->address ?? '--' }}
        @if($contact->latitude && $contact->longitude)
            <a href="https://www.google.com/maps?q={{ $contact->latitude }},{{ $contact->longitude }}" target="_blank" class="ms-1 text-primary" title="{{ __('عرض على الخريطة') }}">
                <i class="fas fa-map-marker-alt"></i>
            </a>
        @endif
    </td>

{{-- ?????? --}}
    <td>
        @if($contact->balance > 0)
            {{-- ????: ?? ???? (???? - ????) --}}
            <div class="d-flex flex-column justify-content-center align-items-center">
                <span class="text-success fw-bold" dir="ltr">
                    <i class="fas fa-arrow-up small mb-1"></i> +{{ number_format($contact->balance, 2) }}
                </span>
                {{-- التوع: له رصيد --}}
                <small class="text-success fw-bold mt-1" style="font-size: 0.75rem;">
                    {{ __('له رصيد') }}
                </small>
            </div>

        @elseif($contact->balance < 0)
            {{-- ????: ???? ??? (???? - ????) --}}
            <div class="d-flex flex-column justify-content-center align-items-center">
                <span class="text-danger fw-bold" dir="ltr">
                    <i class="fas fa-arrow-down small mb-1"></i> {{ number_format($contact->balance, 2) }}
                </span>
                {{-- النوع: عليه دين --}}
                <small class="text-danger fw-bold mt-1" style="font-size: 0.75rem;">
                    {{ __('عليه دين') }}
                </small>
            </div>

        @else
            {{-- ????: ??? --}}
            <span class="text-muted fw-bold">0.00</span>
        @endif
    </td>

    <td class="no-print">
        <div class="btn-group btn-group-sm">
            {{-- كشف حساب --}}
            <a href="{{ route('store.payments.ledger', $contact->id) }}" class="btn btn-outline-info" title="{{ __('كشف حساب') }}">
                <i class="fas fa-file-invoice"></i>
            </a>
            {{-- إضافة دفعة --}}
            <button type="button" class="btn btn-outline-success" title="{{ __('إضافة دفعة') }}" 
                    onclick="openPaymentModal({{ $contact->id }}, '{{ $contact->contact_name }}', '{{ $contact->balance }}')">
                <i class="fas fa-money-bill-wave"></i>
            </button>
            
            <a href="{{ route('store.contacts.edit', $contact->id) }}" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a>
            <form action="{{ route('store.contacts.destroy', $contact->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('حذف؟') }}');">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr><td colspan="9" class="text-center py-5 text-muted">{{ __('لا توجد جهات اتصال') }}</td></tr>
@endforelse

<tr><td colspan="9" class="p-0"><div class="d-flex justify-content-center py-2">{{ $contacts->links() }}</div></td></tr>