{{-- resources/views/tev/create.blade.php --}}
{{--
    Expects from TevController@create:
      $approvedOrders — collection of approved OfficeOrder with employee
      $perDiemRates   — collection of PerDiemRate grouped by travel_type
--}}

@extends('layouts.app')

@section('title', 'New TEV Request')
@section('page-title', 'Travel (TEV)')

@section('styles')
<style>
.itin-table { width:100%; border-collapse:collapse; font-size:0.82rem; }
.itin-table thead th {
    background:var(--navy); color:#fff; padding:8px 10px;
    font-size:0.72rem; font-weight:600; letter-spacing:0.03em;
    border:1px solid rgba(255,255,255,0.15); text-align:center;
}
.itin-table tbody td {
    padding:6px 6px; border:1px solid var(--border); vertical-align:middle;
}
.itin-table tfoot td {
    padding:8px 10px; font-weight:700; background:#f8f9ff;
    border:1px solid var(--border); font-size:0.83rem;
}
.itin-table input, .itin-table select {
    width:100%; padding:5px 7px; border:1px solid var(--border);
    border-radius:4px; font-size:0.80rem; background:#fff;
}
.itin-table input[type="checkbox"] {
    width:auto; cursor:pointer;
}
.totals-panel {
    background:var(--navy); color:#fff; border-radius:8px;
    padding:16px 20px; font-size:0.83rem;
}
.totals-panel .totals-row {
    display:flex; justify-content:space-between; align-items:center;
    padding:5px 0; border-bottom:1px solid rgba(255,255,255,0.12);
}
.totals-panel .totals-row:last-child { border-bottom:none; }
.totals-panel .totals-grand {
    font-size:1rem; font-weight:700; color:var(--gold);
}
.section-title {
    font-size:0.72rem; font-weight:700; text-transform:uppercase;
    letter-spacing:0.06em; color:var(--text-light); margin-bottom:12px;
    padding-bottom:6px; border-bottom:1px solid var(--border);
}
</style>
@endsection

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>New TEV Request</h1>
        <p>Travel Expense Voucher — Cash Advance or Reimbursement.</p>
    </div>
    <a href="{{ route('tev.index') }}" class="btn btn-outline btn-sm">← Back to List</a>
</div>

@if ($errors->any())
    <div class="alert alert-error mb-3">
        <ul style="margin:0; padding-left:18px;">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Encode per diem rates as JSON for JS --}}
@php
    $ratesJson = '{}';
    $ratesArr  = [];
    foreach ($perDiemRates as $type => $rates) {
        $first = $rates->first();
        if ($first) {
            $ratesArr[$type] = [
                'daily'    => (float) $first->daily_rate,
                'half_day' => $first->half_day_rate ? (float) $first->half_day_rate : round((float)$first->daily_rate / 2, 2),
            ];
        }
    }
    $ratesJson = json_encode($ratesArr);
@endphp

<form method="POST" action="{{ route('tev.store') }}" id="tevForm">
@csrf

<div style="display:grid; grid-template-columns:1fr 300px; gap:20px; align-items:start;">

    <div style="display:flex; flex-direction:column; gap:20px;">

        {{-- ── Section 1: Office Order ── --}}
        <div class="card">
            <div class="card-header"><h3>📝 Office Order</h3></div>
            <div class="card-body">
                <div class="section-title">Select the approved travel authority</div>

                <div class="form-group">
                    <label for="office_order_id">
                        Office Order <span style="color:var(--red);">*</span>
                    </label>
                    <select name="office_order_id" id="office_order_id"
                            class="{{ $errors->has('office_order_id') ? 'is-invalid' : '' }}"
                            required>
                        <option value="">— Select Office Order —</option>
                        @foreach ($approvedOrders as $oo)
                            @php
                                $ooEmp = $oo->employee;
                                $empName = optional($ooEmp)->last_name . ', ' . optional($ooEmp)->first_name;
                            @endphp
                            <option value="{{ $oo->id }}"
                                data-destination="{{ $oo->destination }}"
                                data-travel-type="{{ $oo->travel_type }}"
                                data-purpose="{{ $oo->purpose }}"
                                data-date-start="{{ $oo->travel_date_start->toDateString() }}"
                                data-date-end="{{ $oo->travel_date_end->toDateString() }}"
                                {{ old('office_order_id') == $oo->id ? 'selected' : '' }}>
                                {{ $oo->office_order_no }} — {{ $empName }}
                                ({{ $oo->travel_date_start->format('M d') }}–{{ $oo->travel_date_end->format('M d, Y') }})
                            </option>
                        @endforeach
                    </select>
                    @error('office_order_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Auto-filled preview --}}
                <div id="oo-preview" style="display:none; margin-top:10px; padding:10px 14px;
                     background:#f0f2ff; border-radius:6px; font-size:0.82rem;
                     border-left:3px solid var(--navy);">
                    <div><strong>Destination:</strong> <span id="oo-destination"></span></div>
                    <div><strong>Travel Type:</strong> <span id="oo-travel-type"></span></div>
                    <div><strong>Purpose:</strong> <span id="oo-purpose"></span></div>
                </div>
            </div>
        </div>

        {{-- ── Section 2: Track ── --}}
        <div class="card">
            <div class="card-header"><h3>💳 Track</h3></div>
            <div class="card-body">
                <div class="section-title">Cash Advance (before travel) or Reimbursement (after travel)</div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    @foreach (['cash_advance' => ['Cash Advance', '💵', '#E8F5E9', '#1B5E20'], 'reimbursement' => ['Reimbursement', '🧾', '#E8EAF6', '#1A237E']] as $val => $meta)
                        @php $trackChecked = old('track', 'cash_advance') === $val; @endphp
                        <label style="display:flex; align-items:center; gap:10px; cursor:pointer;
                                      padding:14px 16px; border-radius:8px;
                                      border:2px solid {{ $trackChecked ? $meta[3] : 'var(--border)' }};
                                      background:{{ $trackChecked ? $meta[2] : 'transparent' }};
                                      font-weight:600; font-size:0.88rem; color:{{ $meta[3] }};">
                            <input type="radio" name="track" value="{{ $val }}"
                                   {{ $trackChecked ? 'checked' : '' }}
                                   style="accent-color:{{ $meta[3] }};">
                            {{ $meta[1] }} {{ $meta[0] }}
                        </label>
                    @endforeach
                </div>
                @error('track')
                    <div class="invalid-feedback" style="display:block; margin-top:6px;">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- ── Section 3: Travel Details ── --}}
        <div class="card">
            <div class="card-header"><h3>✈ Travel Details</h3></div>
            <div class="card-body">

                {{-- Hidden travel_type field — auto-filled from OO selection --}}
                <input type="hidden" name="travel_type" id="travel_type"
                       value="{{ old('travel_type', 'local') }}">

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label for="travel_date_start">
                            Travel Date — Start <span style="color:var(--red);">*</span>
                        </label>
                        <input type="date" id="travel_date_start" name="travel_date_start"
                               value="{{ old('travel_date_start') }}"
                               class="{{ $errors->has('travel_date_start') ? 'is-invalid' : '' }}"
                               required>
                        @error('travel_date_start')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="travel_date_end">
                            Travel Date — End <span style="color:var(--red);">*</span>
                        </label>
                        <input type="date" id="travel_date_end" name="travel_date_end"
                               value="{{ old('travel_date_end') }}"
                               class="{{ $errors->has('travel_date_end') ? 'is-invalid' : '' }}"
                               required>
                        @error('travel_date_end')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="purpose">
                        Purpose <span style="color:var(--red);">*</span>
                    </label>
                    <textarea id="purpose" name="purpose" rows="2"
                              class="{{ $errors->has('purpose') ? 'is-invalid' : '' }}"
                              required>{{ old('purpose') }}</textarea>
                    @error('purpose')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="destination">
                        Destination <span style="color:var(--red);">*</span>
                    </label>
                    <input type="text" id="destination" name="destination"
                           value="{{ old('destination') }}"
                           class="{{ $errors->has('destination') ? 'is-invalid' : '' }}"
                           required>
                    @error('destination')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="remarks">Remarks (optional)</label>
                    <textarea id="remarks" name="remarks" rows="2">{{ old('remarks') }}</textarea>
                </div>

            </div>
        </div>

        {{-- ── Section 4: Itinerary Builder ── --}}
        <div class="card">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                <h3>🗓 Itinerary Lines</h3>
                <button type="button" id="addRowBtn" class="btn btn-sm btn-gold">+ Add Row</button>
            </div>
            <div class="card-body" style="padding:0; overflow-x:auto;">
                <table class="itin-table">
                    <thead>
                        <tr>
                            <th style="min-width:110px;">Date</th>
                            <th style="min-width:120px;">From</th>
                            <th style="min-width:120px;">To</th>
                            <th style="min-width:80px;">Departure</th>
                            <th style="min-width:80px;">Arrival</th>
                            <th style="min-width:110px;">Mode</th>
                            <th style="min-width:100px;">Transport (₱)</th>
                            <th style="min-width:60px;">Half Day</th>
                            <th style="min-width:100px;">Per Diem (₱)</th>
                            <th style="min-width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="itinBody">
                        {{-- Rows injected by JS --}}
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" style="text-align:right; color:var(--text-light);">Totals:</td>
                            <td id="foot-transport" style="text-align:right; color:var(--navy);">₱0.00</td>
                            <td></td>
                            <td id="foot-perdiem"   style="text-align:right; color:var(--navy);">₱0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                <div id="itin-empty" style="text-align:center; padding:24px; color:var(--text-light); font-size:0.83rem;">
                    Click <strong>+ Add Row</strong> to add itinerary lines.
                </div>
            </div>
            @error('lines')
                <div class="invalid-feedback" style="display:block; padding:8px 16px;">{{ $message }}</div>
            @enderror
        </div>

    </div>

    {{-- ── Right panel: totals + submit ── --}}
    <div style="display:flex; flex-direction:column; gap:16px; position:sticky; top:20px;">

        <div class="totals-panel">
            <div style="font-size:0.70rem; font-weight:700; text-transform:uppercase;
                        letter-spacing:0.07em; opacity:0.65; margin-bottom:10px;">
                Running Totals
            </div>
            <div class="totals-row">
                <span>Transportation</span>
                <span id="tot-transport">₱0.00</span>
            </div>
            <div class="totals-row">
                <span>Per Diem</span>
                <span id="tot-perdiem">₱0.00</span>
            </div>
            <div class="totals-row totals-grand" style="margin-top:8px; padding-top:8px;
                         border-top:1px solid rgba(255,255,255,0.25);">
                <span>Grand Total</span>
                <span id="tot-grand">₱0.00</span>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div style="font-size:0.78rem; color:var(--text-mid); margin-bottom:14px;">
                    Review all details before saving. Per diem rates are auto-filled based
                    on the selected travel type from the Office Order.
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">
                    Save TEV Request
                </button>
                <a href="{{ route('tev.index') }}" class="btn btn-outline"
                   style="width:100%; margin-top:8px; display:block; text-align:center;">
                    Cancel
                </a>
            </div>
        </div>

    </div>

</div>

</form>

@endsection

@section('scripts')
<script>
(function () {
    var PER_DIEM_RATES = {!! $ratesJson !!};
    var rowIndex       = 0;
    var currentType    = document.getElementById('travel_type').value || 'local';

    // ── Office Order auto-fill ──────────────────────────────────────────
    var ooSelect = document.getElementById('office_order_id');
    ooSelect.addEventListener('change', function () {
        var opt = this.options[this.selectedIndex];
        if (!opt || !opt.value) {
            document.getElementById('oo-preview').style.display = 'none';
            return;
        }

        var dest  = opt.getAttribute('data-destination')  || '';
        var type  = opt.getAttribute('data-travel-type')  || 'local';
        var purp  = opt.getAttribute('data-purpose')      || '';
        var dS    = opt.getAttribute('data-date-start')   || '';
        var dE    = opt.getAttribute('data-date-end')     || '';

        document.getElementById('oo-destination').textContent  = dest;
        document.getElementById('oo-travel-type').textContent  = type.charAt(0).toUpperCase() + type.slice(1);
        document.getElementById('oo-purpose').textContent      = purp;
        document.getElementById('oo-preview').style.display    = 'block';

        // Auto-fill form fields
        document.getElementById('destination').value     = dest;
        document.getElementById('purpose').value         = purp;
        document.getElementById('travel_type').value     = type;
        if (dS) document.getElementById('travel_date_start').value = dS;
        if (dE) document.getElementById('travel_date_end').value   = dE;

        currentType = type;
        refreshAllPerDiem();
    });

    // ── Track radio styling ─────────────────────────────────────────────
    var trackColors = {
        cash_advance:  { bg: '#E8F5E9', border: '#1B5E20', text: '#1B5E20' },
        reimbursement: { bg: '#E8EAF6', border: '#1A237E', text: '#1A237E' }
    };
    document.querySelectorAll('input[name="track"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            document.querySelectorAll('input[name="track"]').forEach(function (r) {
                var lbl = r.closest('label');
                var c   = trackColors[r.value];
                if (r.checked) {
                    lbl.style.borderColor = c.border;
                    lbl.style.background  = c.bg;
                    lbl.style.color       = c.text;
                } else {
                    lbl.style.borderColor = 'var(--border)';
                    lbl.style.background  = 'transparent';
                    lbl.style.color       = trackColors[r.value].text;
                }
            });
        });
    });

    // ── Itinerary row builder ───────────────────────────────────────────
    document.getElementById('addRowBtn').addEventListener('click', function () {
        addRow();
    });

    function addRow(data) {
        data = data || {};
        var idx  = rowIndex++;
        var body = document.getElementById('itinBody');
        var tr   = document.createElement('tr');
        tr.setAttribute('data-idx', idx);

        var modes = ['bus','jeepney','boat','plane','vehicle','other'];
        var modeOptions = modes.map(function (m) {
            var sel = (data.mode_of_transport === m) ? ' selected' : '';
            return '<option value="' + m + '"' + sel + '>' + m.charAt(0).toUpperCase() + m.slice(1) + '</option>';
        }).join('');

        var dailyRate = getDailyRate(false);
        var halfRate  = getDailyRate(true);
        var perdiem   = data.per_diem_amount !== undefined ? data.per_diem_amount : dailyRate;

        tr.innerHTML =
            '<td><input type="date" name="lines[' + idx + '][travel_date]"' +
                 ' value="' + (data.travel_date || '') + '" required></td>' +
            '<td><input type="text" name="lines[' + idx + '][origin]"' +
                 ' value="' + (data.origin || '') + '" placeholder="From" required></td>' +
            '<td><input type="text" name="lines[' + idx + '][destination]"' +
                 ' value="' + (data.destination || '') + '" placeholder="To" required></td>' +
            '<td><input type="time" name="lines[' + idx + '][departure_time]"' +
                 ' value="' + (data.departure_time || '') + '"></td>' +
            '<td><input type="time" name="lines[' + idx + '][arrival_time]"' +
                 ' value="' + (data.arrival_time || '') + '"></td>' +
            '<td><select name="lines[' + idx + '][mode_of_transport]" required>' +
                 '<option value="">—</option>' + modeOptions + '</select></td>' +
            '<td><input type="number" name="lines[' + idx + '][transportation_cost]"' +
                 ' value="' + (data.transportation_cost !== undefined ? data.transportation_cost : '0') + '"' +
                 ' step="0.01" min="0" class="itin-transport" required></td>' +
            '<td style="text-align:center;"><input type="checkbox" name="lines[' + idx + '][is_half_day]"' +
                 ' value="1" class="itin-halfday"' + (data.is_half_day ? ' checked' : '') + '></td>' +
            '<td><input type="number" name="lines[' + idx + '][per_diem_amount]"' +
                 ' value="' + perdiem + '" step="0.01" min="0" class="itin-perdiem" required></td>' +
            '<td><button type="button" class="btn btn-sm btn-danger itin-remove"' +
                 ' style="padding:3px 8px;">✕</button></td>';

        body.appendChild(tr);

        // Half-day toggle
        var halfCheck = tr.querySelector('.itin-halfday');
        var perdEl    = tr.querySelector('.itin-perdiem');
        halfCheck.addEventListener('change', function () {
            perdEl.value = this.checked ? halfRate.toFixed(2) : dailyRate.toFixed(2);
            refreshAllPerDiem();
            updateTotals();
        });

        // Remove row
        tr.querySelector('.itin-remove').addEventListener('click', function () {
            body.removeChild(tr);
            updateEmptyState();
            updateTotals();
        });

        // Input changes → totals
        tr.querySelectorAll('input[type="number"]').forEach(function (inp) {
            inp.addEventListener('input', updateTotals);
        });

        updateEmptyState();
        updateTotals();
    }

    function getDailyRate(half) {
        var rates = PER_DIEM_RATES[currentType];
        if (!rates) return 0;
        return half ? rates.half_day : rates.daily;
    }

    function refreshAllPerDiem() {
        var rows = document.querySelectorAll('#itinBody tr');
        rows.forEach(function (tr) {
            var halfCheck = tr.querySelector('.itin-halfday');
            var perdEl    = tr.querySelector('.itin-perdiem');
            if (halfCheck && perdEl) {
                perdEl.value = getDailyRate(halfCheck.checked).toFixed(2);
            }
        });
        updateTotals();
    }

    function updateTotals() {
        var transport = 0, perdiem = 0;
        document.querySelectorAll('#itinBody tr').forEach(function (tr) {
            var t = parseFloat(tr.querySelector('.itin-transport').value) || 0;
            var p = parseFloat(tr.querySelector('.itin-perdiem').value)   || 0;
            transport += t;
            perdiem   += p;
        });
        var grand = transport + perdiem;

        var fmt = function (n) {
            return '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };

        document.getElementById('tot-transport').textContent  = fmt(transport);
        document.getElementById('tot-perdiem').textContent    = fmt(perdiem);
        document.getElementById('tot-grand').textContent      = fmt(grand);
        document.getElementById('foot-transport').textContent = fmt(transport);
        document.getElementById('foot-perdiem').textContent   = fmt(perdiem);
    }

    function updateEmptyState() {
        var hasRows = document.querySelectorAll('#itinBody tr').length > 0;
        document.getElementById('itin-empty').style.display = hasRows ? 'none' : 'block';
    }

    // Re-populate on validation reload
    @if (old('lines'))
        @foreach (old('lines') as $i => $line)
            addRow({
                travel_date:         '{{ $line['travel_date']         ?? '' }}',
                origin:              '{{ addslashes($line['origin']    ?? '') }}',
                destination:         '{{ addslashes($line['destination'] ?? '') }}',
                departure_time:      '{{ $line['departure_time']      ?? '' }}',
                arrival_time:        '{{ $line['arrival_time']        ?? '' }}',
                mode_of_transport:   '{{ $line['mode_of_transport']   ?? '' }}',
                transportation_cost: '{{ $line['transportation_cost'] ?? 0 }}',
                per_diem_amount:     '{{ $line['per_diem_amount']     ?? 0 }}',
                is_half_day:         {{ !empty($line['is_half_day']) ? 'true' : 'false' }}
            });
        @endforeach
    @endif

})();
</script>
@endsection