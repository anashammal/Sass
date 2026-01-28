@forelse($contacts as $contact)
<tr class="align-middle">
    
{{-- ????? --}}
<td>
    @if(empty($contact->contact_name) || Str::contains($contact->contact_name, '???'))
        <span class="badge bg-danger">?? ???? ???</span>
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

        @if(in_array($type, ['client', 'customer', '????']))
            {{-- ????: ???? --}}
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2">
                <i class="fas fa-user ms-1"></i> &#1586;&#1576;&#1608;&#1606;
            </span>
        @elseif(in_array($type, ['supplier', 'vendor', '????']))
            {{-- ????: ???? --}}
            <span class="badge bg-warning bg-opacity-10 text-dark border border-warning px-2">
                <i class="fas fa-truck ms-1"></i> &#1605;&#1608;&#1585;&#1583;
            </span>
        @elseif(in_array($type, ['both', 'client_supplier', '???? ?????']))
            {{-- ????: ????? --}}
            <span class="badge bg-success bg-opacity-10 text-success border border-success px-2">
                <i class="fas fa-exchange-alt ms-1"></i> &#1605;&#1588;&#1578;&#1585;&#1603;
            </span>
        @else
            <span class="badge bg-secondary">{{ $contact->type }}</span>
        @endif
    </td>

    <td dir="ltr" class="font-monospace small">{{ $contact->phone ?? '--' }}</td>
    
    <td class="small">
        @if($contact->email)
            <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>
        @else <span class="text-muted">--</span> @endif
    </td>
    
    <td class="font-monospace small">{{ $contact->tax_number ?? '--' }}</td>
    
    {{-- ? ??????? (?? ???????: ????? truncate ??????? ??????? ???? ????) --}}
    <td style="min-width: 200px; white-space: normal;">
        {{ $contact->address ?? '--' }}
    </td>

{{-- ?????? --}}
    <td>
        @if($contact->balance > 0)
            {{-- ????: ?? ???? (???? - ????) --}}
            <div class="d-flex flex-column justify-content-center align-items-center">
                <span class="text-success fw-bold" dir="ltr">
                    <i class="fas fa-arrow-up small mb-1"></i> +{{ number_format($contact->balance, 2) }}
                </span>
                {{-- ????: ?? ???? --}}
                <small class="text-success fw-bold mt-1" style="font-size: 0.75rem;">
                    &#1604;&#1607; &#1585;&#1589;&#1610;&#1583;
                </small>
            </div>

        @elseif($contact->balance < 0)
            {{-- ????: ???? ??? (???? - ????) --}}
            <div class="d-flex flex-column justify-content-center align-items-center">
                <span class="text-danger fw-bold" dir="ltr">
                    <i class="fas fa-arrow-down small mb-1"></i> {{ number_format($contact->balance, 2) }}
                </span>
                {{-- ????: ???? ??? --}}
                <small class="text-danger fw-bold mt-1" style="font-size: 0.75rem;">
                    &#1593;&#1604;&#1610;&#1607; &#1583;&#1610;&#1606;
                </small>
            </div>

        @else
            {{-- ????: ??? --}}
            <span class="text-muted fw-bold">0.00</span>
        @endif
    </td>

    <td class="no-print">
        <div class="btn-group btn-group-sm">
            <a href="{{ route('store.contacts.edit', $contact->id) }}" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a>
            <form action="{{ route('store.contacts.destroy', $contact->id) }}" method="POST" class="d-inline" onsubmit="return confirm('????');">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr><td colspan="9" class="text-center py-5 text-muted">?? ???? ??????</td></tr>
@endforelse

<tr><td colspan="9" class="p-0"><div class="d-flex justify-content-center py-2">{{ $contacts->links() }}</div></td></tr>