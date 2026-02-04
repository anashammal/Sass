<div class="unit-row card border-secondary mb-2 shadow-sm position-relative">
    <button type="button" class="btn-close position-absolute top-0 end-0 m-2 bg-danger" onclick="this.closest('.unit-row').remove()"></button>
    <div class="card-body py-2">
        <div class="row g-2 align-items-center">
            @if(isset($unit))
                <input type="hidden" name="units[{{ $index }}][id]" value="{{ $unit->id }}">
            @endif
            <div class="col-md-2 text-center">
                <img id="preview_{{ $index }}" src="{{ isset($unit) && $unit->image ? $unit->image : asset('images/default-product.png') }}" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: contain;">
                <input type="file" name="units[{{ $index }}][image]" class="form-control form-control-sm mt-1" accept="image/*" onchange="previewImage(this, 'preview_{{ $index }}')">
            </div>
            
            <div class="col-md-10">
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="small fw-bold">اسم الوحدة</label>
                        @php 
                            $standardUnits = ['كرتون', 'درزن', 'شريط'];
                            $isCustom = isset($unit) && !in_array($unit->unit_name, $standardUnits);
                        @endphp
                        <select name="units[{{ $index }}][name_select]" class="form-select form-select-sm unit-select fw-bold" onchange="handleUnitChange(this)">
                            @foreach($standardUnits as $u)
                                <option value="{{ $u }}" {{ isset($unit) && $unit->unit_name == $u ? 'selected' : '' }}>{{ $u }}</option>
                            @endforeach
                            <option value="custom" {{ $isCustom ? 'selected' : '' }}>مخصص..</option>
                        </select>
                        <input type="text" name="units[{{ $index }}][name]" class="form-control form-control-sm {{ $isCustom ? '' : 'd-none' }} mt-1 unit-custom-input fw-bold" placeholder="اكتب الاسم" value="{{ isset($unit) ? $unit->unit_name : '' }}">
                    </div>

                    <div class="col-md-2">
                        <label class="small fw-bold">التحويل</label>
                        <input type="number" step="any" name="units[{{ $index }}][factor]" class="form-control form-control-sm unit-factor fw-bold text-center" value="{{ isset($unit) ? (float)$unit->conversion_factor : 1 }}" oninput="calculateUnitCost(this)">
                    </div>

                    <div class="col-md-3">
                        <label class="small fw-bold">الباركود</label>
                        <input type="text" name="units[{{ $index }}][barcode]" class="form-control form-control-sm fw-bold" value="{{ isset($unit) ? $unit->barcode : '' }}">
                    </div>

                    <div class="col-md-4 d-flex align-items-end justify-content-start gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="units[{{ $index }}][is_purchase]" {{ !isset($unit) || $unit->is_purchase ? 'checked' : '' }}>
                            <label class="form-check-label small fw-bold">شراء</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input row-is-sale" type="checkbox" name="units[{{ $index }}][is_sale]" {{ !isset($unit) || $unit->is_sale ? 'checked' : '' }} onchange="toggleRowSaleFields(this)">
                            <label class="form-check-label small fw-bold">بيع</label>
                        </div>

                    </div>

                    <div class="col-md-4">
                        <label class="small text-muted fw-bold">التكلفة (آلي)</label>
                        <input type="number" step="any" name="units[{{ $index }}][cost_price]" class="form-control form-control-sm bg-light unit-cost fw-bold text-danger text-center" readonly value="{{ isset($unit) ? (float)$unit->cost_price : '' }}">
                    </div>

                    <div class="col-md-4">
                        <label class="small fw-bold text-primary">الربح %</label>
                        <input type="number" step="any" name="units[{{ $index }}][profit_percent]" class="form-control form-control-sm unit-profit fw-bold text-primary text-center" oninput="calcExtraUnitSell(this)" value="{{ isset($unit) ? (float)$unit->profit_percent : '' }}">
                    </div>

                    <div class="col-md-4">
                        <label class="small text-success fw-bold">سعر البيع</label>
                        <input type="number" step="any" name="units[{{ $index }}][selling_price]" class="form-control form-control-sm unit-sell fw-bold text-success text-center" oninput="calcExtraUnitProfit(this)" value="{{ isset($unit) ? (float)$unit->selling_price : '' }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
