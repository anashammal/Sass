@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-primary text-white">
            <h5 class="m-0 fw-bold"><i class="fas fa-cash-register me-2"></i>تقرير الورديات (الصناديق)</h5>
        </div>
        <div class="card-body">
            
            {{-- بطاقة نظرة عامة (الأرباح والمصاريف) --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm p-3 h-100 border-start border-4 border-primary">
                        <small class="text-muted fw-bold">إجمالي المبيعات</small>
                        <h4 class="fw-bold text-primary mb-0 mt-1">{{ number_format($summary['revenue'], 2) }}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm p-3 h-100 border-start border-4 border-success">
                        <small class="text-muted fw-bold">إجمالي الربح (Gross)</small>
                        <h4 class="fw-bold text-success mb-0 mt-1">{{ number_format($summary['gross_profit'], 2) }}</h4>
                        <small class="text-success opacity-75"><i class="fas fa-chart-line"></i> العائد من المبيعات</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm p-3 h-100 border-start border-4 border-danger">
                        <small class="text-muted fw-bold">المصاريف</small>
                        <h4 class="fw-bold text-danger mb-0 mt-1">{{ number_format($summary['expenses'], 2) }}</h4>
                        <small class="text-danger opacity-75"><i class="fas fa-file-invoice-dollar"></i> تخصم من الربح</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm p-3 h-100 bg-primary text-white">
                        <small class="opacity-75 fw-bold">صافي الربح النهائي (Net)</small>
                        <h3 class="fw-bold mb-0 mt-1">{{ number_format($summary['net_profit'], 2) }}</h3>
                        <small class="opacity-75"><i class="fas fa-check-circle"></i> بعد خصم المصاريف</small>
                    </div>
                </div>
            </div>

            {{-- فلاتر البحث --}}
            <form method="GET" action="{{ route('reports.shifts') }}" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label>من تاريخ</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label>إلى تاريخ</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <label>الموظف</label>
                    <select name="user_id" class="form-select">
                        <option value="">الكل</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> عرض التقرير</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center align-middle" width="100%">
                    <thead class="table-dark">
                        <tr>
                            <th>الموظف</th>
                            <th>التوقيت / المدة</th>
                            <th>العهدة (البداية)</th>
                            <th>تفاصيل المبيعات</th>
                            <th>الإغلاق الفعلي</th>
                            <th>العجز / الزيادة</th>
                            <th>صافي الربح</th>
                            <th>نسبة الربح</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shifts as $shift)
                        <tr>
                            {{-- الموظف --}}
                            <td class="fw-bold">{{ $shift->user->name ?? 'غير معروف' }}</td>
                            
                            {{-- التوقيت والمدة --}}
                            <td class="small">
                                <div class="text-success">فتح: {{ \Carbon\Carbon::parse($shift->opened_at)->format('Y-m-d h:i A') }}</div>
                                @if($shift->status == 'closed')
                                    <div class="text-danger">إغلاق: {{ \Carbon\Carbon::parse($shift->closed_at)->format('Y-m-d h:i A') }}</div>
                                @else
                                    <span class="badge bg-warning text-dark">مستمر...</span>
                                @endif
                                <hr class="my-1">
                                <span class="badge bg-secondary"><i class="fas fa-clock"></i> {{ $shift->duration }}</span>
                            </td>

                            {{-- العهده --}}
                            <td class="fw-bold text-primary">{{ number_format($shift->start_cash, 2) }}</td>

                            {{-- تفاصيل المبيعات --}}
                            <td class="text-start" style="min-width: 180px;">
                                <div class="d-flex justify-content-between"><small>💵 كاش:</small> <b>{{ number_format($shift->total_cash_sales, 2) }}</b></div>
                                <div class="d-flex justify-content-between"><small>💳 شبكة:</small> <b>{{ number_format($shift->total_card_sales, 2) }}</b></div>
                                <div class="d-flex justify-content-between"><small>🏦 تحويل:</small> <b>{{ number_format($shift->total_bank_sales, 2) }}</b></div>
                                <div class="d-flex justify-content-between text-danger"><small>📝 آجل:</small> <b>{{ number_format($shift->total_credit_sales, 2) }}</b></div>
                                <hr class="my-1">
                                <div class="d-flex justify-content-between bg-light fw-bold p-1"><small>الإجمالي:</small> {{ number_format($shift->total_cash_sales + $shift->total_card_sales + $shift->total_bank_sales + $shift->total_credit_sales, 2) }}</div>
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
                                        <span class="badge bg-success p-2">مطابق ✅</span>
                                    @elseif($shift->difference < 0)
                                        <div class="text-danger fw-bold">
                                            <i class="fas fa-arrow-down"></i> عجز
                                            <br> {{ number_format(abs($shift->difference), 2) }}
                                        </div>
                                    @else
                                        <div class="text-success fw-bold">
                                            <i class="fas fa-arrow-up"></i> زيادة
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
                                <small class="text-muted">هامش ربح الوردية</small>
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
                                    <span class="badge bg-success">مفتوح</span>
                                @else
                                    <span class="badge bg-secondary">مغلق</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">لا توجد سجلات ورديات مطابقة</td>
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