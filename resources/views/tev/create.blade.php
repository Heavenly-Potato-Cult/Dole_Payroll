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
/* Base styles */
.section-title {
    font-size:0.72rem; font-weight:700; text-transform:uppercase;
    letter-spacing:0.06em; color:var(--text-light); margin-bottom:12px;
    padding-bottom:6px; border-bottom:1px solid var(--border);
}

/* Track radio cards styling */
.track-cards {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.track-card {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    padding: 14px 16px;
    border-radius: 8px;
    border: 2px solid var(--border);
    background: transparent;
    font-weight: 600;
    font-size: 0.88rem;
    transition: all 0.2s ease;
}
.track-card.cash_advance { color: #1B5E20; }
.track-card.reimbursement { color: #1A237E; }
.track-card.selected-cash { border-color: #1B5E20; background: #E8F5E9; }
.track-card.selected-reimb { border-color: #1A237E; background: #E8EAF6; }
.track-card input[type="radio"] {
    accent-color: currentColor;
    margin: 0;
    flex-shrink: 0;
}

/* Totals Panel */
.totals-panel {
    background: var(--navy);
    color: #fff;
    border-radius: 8px;
    padding: 16px 20px;
    font-size: 0.83rem;
}
.totals-panel .totals-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 5px 0;
    border-bottom: 1px solid rgba(255,255,255,0.12);
}
.totals-panel .totals-row:last-child { border-bottom: none; }
.totals-panel .totals-grand {
    font-size: 1rem;
    font-weight: 700;
    color: var(--gold);
}

/* Itinerary Items - Desktop Table View */
.itin-desktop {
    display: block;
}
.itin-desktop table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.82rem;
}
.itin-desktop thead th {
    background: var(--navy);
    color: #fff;
    padding: 8px 10px;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.03em;
    border: 1px solid rgba(255,255,255,0.15);
    text-align: center;
}
.itin-desktop tbody td {
    padding: 6px 6px;
    border: 1px solid var(--border);
    vertical-align: middle;
}
.itin-desktop tfoot td {
    padding: 8px 10px;
    font-weight: 700;
    background: #f8f9ff;
    border: 1px solid var(--border);
    font-size: 0.83rem;
}
.itin-desktop input, .itin-desktop select {
    width: 100%;
    padding: 5px 7px;
    border: 1px solid var(--border);
    border-radius: 4px;
    font-size: 0.80rem;
    background: #fff;
}
.itin-desktop input[type="checkbox"] {
    width: auto;
    cursor: pointer;
}

/* Itinerary Items - Mobile Card View */
.itin-mobile {
    display: none;
}
.itin-card {
    background: #f8f9ff;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 14px;
    margin-bottom: 12px;
}
.itin-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid var(--border);
}
.itin-card-date {
    font-weight: 700;
    color: var(--navy);
    font-size: 0.85rem;
}
.itin-card-remove {
    background: var(--red);
    color: white;
    border: none;
    border-radius: 4px;
    padding: 4px 10px;
    cursor: pointer;
    font-size: 0.75rem;
}
.itin-card-field {
    margin-bottom: 10px;
}
.itin-card-field label {
    display: block;
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--text-mid);
    margin-bottom: 3px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.itin-card-field input,
.itin-card-field select {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: 0.85rem;
}
.itin-card-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 10px;
}
.itin-card-halfday {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 10px 0;
}
.itin-card-halfday input {
    width: auto;
}

/* Responsive */
@media (max-width: 900px) {
    .tev-create-grid {
        grid-template-columns: 1fr !important;
        gap: 20px;
    }
    .tev-right-panel {
        position: static !important;
    }
}

@media (max-width: 768px) {
    .track-cards {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .form-row-grid {
        grid-template-columns: 1fr !important;
        gap: 12px !important;
    }
    
    /* Switch to mobile card view */
    .itin-desktop {
        display: none;
    }
    .itin-mobile {
        display: block;
    }
    
    .card-header {
        flex-wrap: wrap;
    }
    
    #addRowBtn {
        width: 100%;
        text-align: center;
        justify-content: center;
    }
    
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .page-header .btn {
        align-self: flex-start;
    }
    
    .card-body {
        padding: 16px;
    }
}

@media (max-width: 480px) {
    .track-card {
        padding: 12px 14px;
        font-size: 0.82rem;
    }
    .totals-panel {
        padding: 14px 16px;
    }
    .itin-card {
        padding: 12px;
    }
    .itin-card-row {
        grid-template-columns: 1fr;
        gap: 8px;
    }
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

<div class="tev-create-grid" style="display:grid; grid-template-columns:1fr 300px; gap:20px; align-items:start;">
    
    {{-- Left Column: Form Sections --}}
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

                <div class="track-cards">
                    @php $trackChecked = old('track', 'cash_advance'); @endphp
                    <label class="track-card cash_advance {{ $trackChecked === 'cash_advance' ? 'selected-cash' : '' }}">
                        <input type="radio" name="track" value="cash_advance" {{ $trackChecked === 'cash_advance' ? 'checked' : '' }}>
                        💵 Cash Advance
                    </label>
                    <label class="track-card reimbursement {{ $trackChecked === 'reimbursement' ? 'selected-reimb' : '' }}">
                        <input type="radio" name="track" value="reimbursement" {{ $trackChecked === 'reimbursement' ? 'checked' : '' }}>
                        🧾 Reimbursement
                    </label>
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
                <input type="hidden" name="travel_type" id="travel_type" value="{{ old('travel_type', 'local') }}">

                <div class="form-row-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label for="travel_date_start">Travel Date — Start <span style="color:var(--red);">*</span></label>
                        <input type="date" id="travel_date_start" name="travel_date_start"
                               value="{{ old('travel_date_start') }}"
                               class="{{ $errors->has('travel_date_start') ? 'is-invalid' : '' }}" required>
                        @error('travel_date_start')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="travel_date_end">Travel Date — End <span style="color:var(--red);">*</span></label>
                        <input type="date" id="travel_date_end" name="travel_date_end"
                               value="{{ old('travel_date_end') }}"
                               class="{{ $errors->has('travel_date_end') ? 'is-invalid' : '' }}" required>
                        @error('travel_date_end')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="purpose">Purpose <span style="color:var(--red);">*</span></label>
                    <textarea id="purpose" name="purpose" rows="2"
                              class="{{ $errors->has('purpose') ? 'is-invalid' : '' }}" required>{{ old('purpose') }}</textarea>
                    @error('purpose')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="destination">Destination <span style="color:var(--red);">*</span></label>
                    <input type="text" id="destination" name="destination"
                           value="{{ old('destination') }}"
                           class="{{ $errors->has('destination') ? 'is-invalid' : '' }}" required>
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
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;">
                <h3>🗓 Itinerary Lines</h3>
                <button type="button" id="addRowBtn" class="btn btn-sm btn-gold">+ Add Row</button>
            </div>
            <div class="card-body">
                {{-- Desktop Table View --}}
                <div class="itin-desktop">
                    <table>
                        <thead>
                            <tr><th>Date</th><th>From</th><th>To</th><th>Depart</th><th>Arrive</th><th>Mode</th><th>Transport</th><th>Half</th><th>Per Diem</th><th></th></tr>
                        </thead>
                        <tbody id="itinBodyDesktop"></tbody>
                        <tfoot>
                            <tr><td colspan="6" style="text-align:right;">Totals:</td>
                            <td id="foot-transport" style="text-align:right;">₱0.00</td>
                            <td></td><td id="foot-perdiem" style="text-align:right;">₱0.00</td><td></td></tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Mobile Card View --}}
                <div class="itin-mobile" id="itinMobileContainer"></div>
                
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
    <div class="tev-right-panel" style="display:flex; flex-direction:column; gap:16px; position:sticky; top:20px;">
        
        <div class="totals-panel">
            <div style="font-size:0.70rem; font-weight:700; text-transform:uppercase; letter-spacing:0.07em; opacity:0.65; margin-bottom:10px;">
                Running Totals
            </div>
            <div class="totals-row"><span>Transportation</span><span id="tot-transport">₱0.00</span></div>
            <div class="totals-row"><span>Per Diem</span><span id="tot-perdiem">₱0.00</span></div>
            <div class="totals-row totals-grand" style="margin-top:8px; padding-top:8px; border-top:1px solid rgba(255,255,255,0.25);">
                <span>Grand Total</span><span id="tot-grand">₱0.00</span>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div style="font-size:0.78rem; color:var(--text-mid); margin-bottom:14px;">
                    Review all details before saving. Per diem rates are auto-filled based on the selected travel type from the Office Order.
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Save TEV Request</button>
                <a href="{{ route('tev.index') }}" class="btn btn-outline" style="width:100%; margin-top:8px; display:block; text-align:center;">Cancel</a>
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
    var rowsData       = []; // Store row data for sync between desktop and mobile

    // ── Office Order auto-fill ──────────────────────────────────────────
    var ooSelect = document.getElementById('office_order_id');
    ooSelect.addEventListener('change', function () {
        var opt = this.options[this.selectedIndex];
        if (!opt || !opt.value) {
            document.getElementById('oo-preview').style.display = 'none';
            return;
        }
        var dest = opt.getAttribute('data-destination') || '';
        var type = opt.getAttribute('data-travel-type') || 'local';
        var purp = opt.getAttribute('data-purpose') || '';
        var dS = opt.getAttribute('data-date-start') || '';
        var dE = opt.getAttribute('data-date-end') || '';

        document.getElementById('oo-destination').textContent = dest;
        document.getElementById('oo-travel-type').textContent = type.charAt(0).toUpperCase() + type.slice(1);
        document.getElementById('oo-purpose').textContent = purp;
        document.getElementById('oo-preview').style.display = 'block';

        document.getElementById('destination').value = dest;
        document.getElementById('purpose').value = purp;
        document.getElementById('travel_type').value = type;
        if (dS) document.getElementById('travel_date_start').value = dS;
        if (dE) document.getElementById('travel_date_end').value = dE;

        currentType = type;
        refreshAllPerDiem();
    });

    // ── Track radio styling ─────────────────────────────────────────────
    var trackCards = document.querySelectorAll('.track-card');
    function updateTrackCardStyles() {
        trackCards.forEach(function(card) {
            var radio = card.querySelector('input[type="radio"]');
            var value = radio.value;
            if (radio.checked) {
                if (value === 'cash_advance') {
                    card.classList.add('selected-cash');
                    card.classList.remove('selected-reimb');
                } else {
                    card.classList.add('selected-reimb');
                    card.classList.remove('selected-cash');
                }
            } else {
                card.classList.remove('selected-cash', 'selected-reimb');
            }
        });
    }
    trackCards.forEach(function(card) {
        card.querySelector('input[type="radio"]').addEventListener('change', updateTrackCardStyles);
    });
    updateTrackCardStyles();

    // ── Helper functions ────────────────────────────────────────────────
    function getDailyRate(half) {
        var rates = PER_DIEM_RATES[currentType];
        if (!rates) return 0;
        return half ? rates.half_day : rates.daily;
    }

    function refreshAllPerDiem() {
        rowsData.forEach(function(row, idx) {
            var half = row.is_half_day || false;
            var perdiem = getDailyRate(half);
            row.per_diem_amount = perdiem;
            updateRowUI(idx);
        });
        updateTotals();
    }

    function updateTotals() {
        var transport = 0, perdiem = 0;
        rowsData.forEach(function(row) {
            transport += parseFloat(row.transportation_cost) || 0;
            perdiem += parseFloat(row.per_diem_amount) || 0;
        });
        var grand = transport + perdiem;
        var fmt = function (n) { return '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };
        document.getElementById('tot-transport').textContent = fmt(transport);
        document.getElementById('tot-perdiem').textContent = fmt(perdiem);
        document.getElementById('tot-grand').textContent = fmt(grand);
        document.getElementById('foot-transport').textContent = fmt(transport);
        document.getElementById('foot-perdiem').textContent = fmt(perdiem);
        updateEmptyState();
    }

    function updateEmptyState() {
        var hasRows = rowsData.length > 0;
        document.getElementById('itin-empty').style.display = hasRows ? 'none' : 'block';
    }

    function removeRow(idx) {
        rowsData.splice(idx, 1);
        // Re-index remaining rows
        rowsData.forEach(function(row, newIdx) { row.idx = newIdx; });
        renderAllRows();
        updateTotals();
    }

    function updateRowUI(idx) {
        var row = rowsData[idx];
        if (!row) return;
        
        // Update desktop row if exists
        var desktopRow = document.getElementById('desktop-row-' + idx);
        if (desktopRow) {
            desktopRow.querySelector('.itin-transport').value = row.transportation_cost;
            desktopRow.querySelector('.itin-perdiem').value = row.per_diem_amount;
            desktopRow.querySelector('.itin-halfday').checked = row.is_half_day;
        }
        
        // Update mobile card if exists
        var mobileCard = document.getElementById('mobile-card-' + idx);
        if (mobileCard) {
            mobileCard.querySelector('.itin-mobile-transport').value = row.transportation_cost;
            mobileCard.querySelector('.itin-mobile-perdiem').value = row.per_diem_amount;
            mobileCard.querySelector('.itin-mobile-halfday').checked = row.is_half_day;
        }
    }

    function renderAllRows() {
        var desktopTbody = document.getElementById('itinBodyDesktop');
        var mobileContainer = document.getElementById('itinMobileContainer');
        desktopTbody.innerHTML = '';
        mobileContainer.innerHTML = '';
        
        rowsData.forEach(function(row, idx) {
            // Desktop row
            var tr = document.createElement('tr');
            tr.id = 'desktop-row-' + idx;
            var modes = ['bus','jeepney','boat','plane','vehicle','other'];
            var modeOptions = modes.map(function(m) { return '<option value="' + m + '"' + (row.mode_of_transport === m ? ' selected' : '') + '>' + m.charAt(0).toUpperCase() + m.slice(1) + '</option>'; }).join('');
            
            tr.innerHTML = `
                <td><input type="date" name="lines[${idx}][travel_date]" value="${row.travel_date || ''}" required></td>
                <td><input type="text" name="lines[${idx}][origin]" value="${escapeHtml(row.origin || '')}" placeholder="From" required></td>
                <td><input type="text" name="lines[${idx}][destination]" value="${escapeHtml(row.destination || '')}" placeholder="To" required></td>
                <td><input type="time" name="lines[${idx}][departure_time]" value="${row.departure_time || ''}"></td>
                <td><input type="time" name="lines[${idx}][arrival_time]" value="${row.arrival_time || ''}"></td>
                <td><select name="lines[${idx}][mode_of_transport]" required><option value="">—</option>${modeOptions}</select></td>
                <td><input type="number" name="lines[${idx}][transportation_cost]" value="${row.transportation_cost || 0}" step="0.01" min="0" class="itin-transport" required></td>
                <td style="text-align:center;"><input type="checkbox" name="lines[${idx}][is_half_day]" value="1" class="itin-halfday" ${row.is_half_day ? 'checked' : ''}></td>
                <td><input type="number" name="lines[${idx}][per_diem_amount]" value="${row.per_diem_amount || 0}" step="0.01" min="0" class="itin-perdiem" required></td>
                <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRowFunc(${idx})" style="padding:3px 8px;">✕</button></td>
            `;
            
            // Attach event listeners
            tr.querySelector('.itin-transport').addEventListener('input', function(e) { updateRowValue(idx, 'transportation_cost', e.target.value); });
            tr.querySelector('.itin-perdiem').addEventListener('input', function(e) { updateRowValue(idx, 'per_diem_amount', e.target.value); });
            tr.querySelector('.itin-halfday').addEventListener('change', function(e) { 
                updateRowValue(idx, 'is_half_day', e.target.checked);
                var perdiem = getDailyRate(e.target.checked);
                updateRowValue(idx, 'per_diem_amount', perdiem);
            });
            tr.querySelectorAll('input, select').forEach(function(el) {
                if (el.type !== 'checkbox' && el.name) {
                    var field = el.name.match(/\[(.*?)\]/)[1];
                    el.addEventListener('change', function(e) { updateRowValue(idx, field, e.target.value); });
                }
            });
            
            desktopTbody.appendChild(tr);
            
            // Mobile card
            var card = document.createElement('div');
            card.className = 'itin-card';
            card.id = 'mobile-card-' + idx;
            card.innerHTML = `
                <div class="itin-card-header">
                    <span class="itin-card-date">${row.travel_date || 'Select Date'}</span>
                    <button type="button" class="itin-card-remove" onclick="removeRowFunc(${idx})">✕</button>
                </div>
                <div class="itin-card-row">
                    <div class="itin-card-field"><label>From</label><input type="text" name="lines[${idx}][origin]" value="${escapeHtml(row.origin || '')}" placeholder="From" required class="itin-mobile-origin"></div>
                    <div class="itin-card-field"><label>To</label><input type="text" name="lines[${idx}][destination]" value="${escapeHtml(row.destination || '')}" placeholder="To" required class="itin-mobile-dest"></div>
                </div>
                <div class="itin-card-row">
                    <div class="itin-card-field"><label>Date</label><input type="date" name="lines[${idx}][travel_date]" value="${row.travel_date || ''}" required class="itin-mobile-date"></div>
                    <div class="itin-card-field"><label>Mode</label><select name="lines[${idx}][mode_of_transport]" required class="itin-mobile-mode"><option value="">—</option>${modeOptions}</select></div>
                </div>
                <div class="itin-card-row">
                    <div class="itin-card-field"><label>Departure</label><input type="time" name="lines[${idx}][departure_time]" value="${row.departure_time || ''}" class="itin-mobile-depart"></div>
                    <div class="itin-card-field"><label>Arrival</label><input type="time" name="lines[${idx}][arrival_time]" value="${row.arrival_time || ''}" class="itin-mobile-arrive"></div>
                </div>
                <div class="itin-card-row">
                    <div class="itin-card-field"><label>Transport (₱)</label><input type="number" name="lines[${idx}][transportation_cost]" value="${row.transportation_cost || 0}" step="0.01" min="0" class="itin-mobile-transport" required></div>
                    <div class="itin-card-field"><label>Per Diem (₱)</label><input type="number" name="lines[${idx}][per_diem_amount]" value="${row.per_diem_amount || 0}" step="0.01" min="0" class="itin-mobile-perdiem" required></div>
                </div>
                <div class="itin-card-halfday">
                    <input type="checkbox" name="lines[${idx}][is_half_day]" value="1" class="itin-mobile-halfday" ${row.is_half_day ? 'checked' : ''}>
                    <label>Half Day (reduced per diem)</label>
                </div>
            `;
            
            // Attach mobile event listeners
            card.querySelector('.itin-mobile-transport').addEventListener('input', function(e) { updateRowValue(idx, 'transportation_cost', e.target.value); });
            card.querySelector('.itin-mobile-perdiem').addEventListener('input', function(e) { updateRowValue(idx, 'per_diem_amount', e.target.value); });
            card.querySelector('.itin-mobile-halfday').addEventListener('change', function(e) { 
                updateRowValue(idx, 'is_half_day', e.target.checked);
                var perdiem = getDailyRate(e.target.checked);
                updateRowValue(idx, 'per_diem_amount', perdiem);
            });
            card.querySelector('.itin-mobile-date').addEventListener('change', function(e) { updateRowValue(idx, 'travel_date', e.target.value); });
            card.querySelector('.itin-mobile-origin').addEventListener('change', function(e) { updateRowValue(idx, 'origin', e.target.value); });
            card.querySelector('.itin-mobile-dest').addEventListener('change', function(e) { updateRowValue(idx, 'destination', e.target.value); });
            card.querySelector('.itin-mobile-mode').addEventListener('change', function(e) { updateRowValue(idx, 'mode_of_transport', e.target.value); });
            card.querySelector('.itin-mobile-depart').addEventListener('change', function(e) { updateRowValue(idx, 'departure_time', e.target.value); });
            card.querySelector('.itin-mobile-arrive').addEventListener('change', function(e) { updateRowValue(idx, 'arrival_time', e.target.value); });
            
            mobileContainer.appendChild(card);
        });
    }
    
    function updateRowValue(idx, field, value) {
        if (rowsData[idx]) {
            rowsData[idx][field] = value;
            if (field === 'is_half_day') {
                rowsData[idx].per_diem_amount = getDailyRate(value);
            }
            updateRowUI(idx);
            updateTotals();
        }
    }
    
    function addRow(data) {
        data = data || {};
        var idx = rowsData.length;
        var dailyRate = getDailyRate(false);
        rowsData.push({
            idx: idx,
            travel_date: data.travel_date || '',
            origin: data.origin || '',
            destination: data.destination || '',
            departure_time: data.departure_time || '',
            arrival_time: data.arrival_time || '',
            mode_of_transport: data.mode_of_transport || '',
            transportation_cost: data.transportation_cost || 0,
            per_diem_amount: data.per_diem_amount !== undefined ? data.per_diem_amount : dailyRate,
            is_half_day: data.is_half_day || false
        });
        renderAllRows();
        updateTotals();
    }
    
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }
    
    window.removeRowFunc = function(idx) {
        rowsData.splice(idx, 1);
        rowsData.forEach(function(row, newIdx) { row.idx = newIdx; });
        renderAllRows();
        updateTotals();
    };
    
    document.getElementById('addRowBtn').addEventListener('click', function () { addRow({}); });
    
    // Re-populate on validation reload
    @if (old('lines'))
        @foreach (old('lines') as $i => $line)
            addRow({
                travel_date: '{{ $line['travel_date'] ?? '' }}',
                origin: '{!! addslashes($line['origin'] ?? '') !!}',
                destination: '{!! addslashes($line['destination'] ?? '') !!}',
                departure_time: '{{ $line['departure_time'] ?? '' }}',
                arrival_time: '{{ $line['arrival_time'] ?? '' }}',
                mode_of_transport: '{{ $line['mode_of_transport'] ?? '' }}',
                transportation_cost: '{{ $line['transportation_cost'] ?? 0 }}',
                per_diem_amount: '{{ $line['per_diem_amount'] ?? 0 }}',
                is_half_day: {{ !empty($line['is_half_day']) ? 'true' : 'false' }}
            });
        @endforeach
    @endif
    
    if (rowsData.length === 0 && !{!! json_encode(old('lines')) !!}) {
        addRow({});
    }
})();
</script>
@endsection