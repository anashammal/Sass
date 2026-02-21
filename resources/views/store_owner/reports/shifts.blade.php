@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-primary text-white">
            <h5 class="m-0 fw-bold"><i class="fas fa-cash-register me-2"></i>{{ __('shifts_report') }}</h5>
        </div>
        <div class="card-body">
            
            {{-- بطاقة نظرة عامة (الأرباح والمصاريف) --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm p-3 h-100 border-start border-4 border-primary">
                        <small class="text-muted fw-bold">{{ __('total_revenue_sales') }}</small>
                        <h4 class="fw-bold text-primary mb-0 mt-1">{{ number_format($summary['revenue'], 2) }}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm p-3 h-100 border-start border-4 border-success">
                        <small class="text-muted fw-bold">{{ __('total_gross_profit') }}</small>
                        <h4 class="fw-bold text-success mb-0 mt-1">{{ number_format($summary['gross_profit'], 2) }}</h4>
                        <small class="text-success opacity-75"><i class="fas fa-chart-line"></i> {{ __('sales_revenue') }}</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm p-3 h-100 border-start border-4 border-danger">
                        <small class="text-muted fw-bold">{{ __('expenses') }}</small>
                        <h4 class="fw-bold text-danger mb-0 mt-1">{{ number_format($summary['expenses'], 2) }}</h4>
                        <small class="text-danger opacity-75"><i class="fas fa-file-invoice-dollar"></i> {{ __('deducted_from_profit') }}</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm p-3 h-100 bg-primary text-white">
                        <small class="opacity-75 fw-bold">{{ __('net_profit_final') }}</small>
                        <h3 class="fw-bold mb-0 mt-1">{{ number_format($summary['net_profit'], 2) }}</h3>
                        <small class="opacity-75"><i class="fas fa-check-circle"></i> {{ __('after_deductions') }}</small>
                    </div>
                </div>
            </div>

            {{-- فلاتر البحث --}}
            <form id="filterForm" method="GET" action="{{ route('reports.shifts') }}" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">{{ __('from_date') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="far fa-calendar-alt text-muted"></i></span>
                        <input type="date" id="filterDateFrom" name="date_from" class="form-control enhanced-date-input auto-filter" value="{{ request('date_from') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">{{ __('to_date') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="far fa-calendar-alt text-muted"></i></span>
                        <input type="date" id="filterDateTo" name="date_to" class="form-control enhanced-date-input auto-filter" value="{{ request('date_to') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">{{ __('employee') }}</label>
                    <select name="user_id" class="form-select auto-filter">
                        <option value="">{{ __('all_filter') }}</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <a href="{{ route('reports.shifts') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-undo me-1"></i> {{ __('reset_filter') }}
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center align-middle" width="100%">
                    <thead class="table-dark">
                        <tr>
                            <th>{{ __('employee') }}</th>
                            <th>{{ __('time_duration') }}</th>
                            <th>{{ __('opening_cash_start') }}</th>
                            <th>{{ __('sales_details') }}</th>
                            <th>{{ __('actual_closing') }}</th>
                            <th>{{ __('shortage_surplus') }}</th>
                            <th>{{ __('net_profit_margin') }}</th>
                            <th>{{ __('profit_percentage') }}</th>
                            <th>{{ __('status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shifts as $shift)
                        <tr>
                            {{-- الموظف --}}
                            <td class="fw-bold">{{ $shift->user->name ?? __('unknown_emp') }}</td>
                            
                            {{-- التوقيت والمدة --}}
                            <td class="small">
                                <div class="text-success">{{ __('opened_at') }} {{ \Carbon\Carbon::parse($shift->opened_at)->format('Y-m-d h:i A') }}</div>
                                @if($shift->status == 'closed')
                                    <div class="text-danger">{{ __('closed_at') }} {{ \Carbon\Carbon::parse($shift->closed_at)->format('Y-m-d h:i A') }}</div>
                                @else
                                    <span class="badge bg-warning text-dark">{{ __('ongoing_shift') }}</span>
                                @endif
                                <hr class="my-1">
                                <span class="badge bg-secondary"><i class="fas fa-clock"></i> {{ $shift->duration }}</span>
                            </td>

                            {{-- العهده --}}
                            <td class="fw-bold text-primary">{{ number_format($shift->start_cash, 2) }}</td>

                            {{-- تفاصيل المبيعات --}}
                            <td class="text-start" style="min-width: 180px;">
                                <div class="d-flex justify-content-between"><small>{{ __('cash_emoji') }}</small> <b>{{ number_format($shift->total_cash_sales, 2) }}</b></div>
                                <div class="d-flex justify-content-between"><small>{{ __('card_emoji') }}</small> <b>{{ number_format($shift->total_card_sales, 2) }}</b></div>
                                <div class="d-flex justify-content-between"><small>{{ __('bank_emoji') }}</small> <b>{{ number_format($shift->total_bank_sales, 2) }}</b></div>
                                <div class="d-flex justify-content-between text-danger"><small>{{ __('credit_emoji') }}</small> <b>{{ number_format($shift->total_credit_sales, 2) }}</b></div>
                                <hr class="my-1">
                                <div class="d-flex justify-content-between bg-light fw-bold p-1"><small>{{ __('total_label') }}</small> {{ number_format($shift->total_cash_sales + $shift->total_card_sales + $shift->total_bank_sales + $shift->total_credit_sales, 2) }}</div>
                            </td>

                            {{-- الإغلاق الفعلي (ما تم عده) --}}
                            <td>
                                @if($shift->status == 'closed')
                                    <span class="fw-bold fs-5">{{ number_format($shift->end_cash, 2) }}</span>
                                @else
                                    -
                                @endif
                            </td>

                            {{-- العجز والزيادة --}}
                            <td>
                                @if($shift->status == 'closed')
                                    @if($shift->difference == 0)
                                        <span class="badge bg-success p-2">{{ __('matched_status') }}</span>
                                    @elseif($shift->difference < 0)
                                        <div class="text-danger fw-bold">
                                            <i class="fas fa-arrow-down"></i> {{ __('shortage_label') }}
                                            <br> {{ number_format(abs($shift->difference), 2) }}
                                        </div>
                                    @else
                                        <div class="text-success fw-bold">
                                            <i class="fas fa-arrow-up"></i> {{ __('surplus_label') }}
                                            <br> {{ number_format($shift->difference, 2) }}
                                        </div>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>

                            {{-- الأرباح --}}
                            <td>
                                <span class="fw-bold text-success fs-5">
                                    {{ number_format($shift->net_profit, 2) }}
                                </span>
                                <br>
                                <small class="text-muted">{{ __('shift_profit_margin') }}</small>
                            </td>
{{-- 🟢 النسبة المئوية للربح --}}
<td>
    @if($shift->profit_percentage > 0)
        <span class="badge bg-success text-white fs-6">
            %{{ number_format($shift->profit_percentage, 1) }} <i class="fas fa-arrow-up"></i>
        </span>
    @elseif($shift->profit_percentage < 0)
        <span class="badge bg-danger text-white fs-6">
            %{{ number_format($shift->profit_percentage, 1) }} <i class="fas fa-arrow-down"></i>
        </span>
    @else
        <span class="badge bg-secondary">0%</span>
    @endif
</td>
                            {{-- الحالة --}}
                            <td>
                                @if($shift->status == 'open')
                                    <span class="badge bg-success">{{ __('open_status') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('closed_status_badge') }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">{{ __('no_matching_shifts') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $shifts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // 1. التحديث التلقائي
        $('.auto-filter').on('change', function() {
            $('#filterForm').submit();
        });

        // 2. التحقق الذكي من التواريخ
        $('#filterDateFrom').on('change', function() {
            let fromDate = $(this).val();
            $('#filterDateTo').attr('min', fromDate);
            let currentTo = $('#filterDateTo').val();
            if(fromDate && currentTo && currentTo < fromDate) {
                $('#filterDateTo').val(fromDate);
            }
        });

        $('#filterDateTo').on('change', function() {
            let toDate = $(this).val();
            $('#filterDateFrom').attr('max', toDate);
            let currentFrom = $('#filterDateFrom').val();
            if(toDate && currentFrom && currentFrom > toDate) {
                $('#filterDateFrom').val(toDate);
            }
        });

        // تشغيل التحقق البدئي (بدون تحديث تلقائي)
        if($('#filterDateFrom').val()) $('#filterDateTo').attr('min', $('#filterDateFrom').val());
        if($('#filterDateTo').val()) $('#filterDateFrom').attr('max', $('#filterDateTo').val());
    });
</script>
@endsection