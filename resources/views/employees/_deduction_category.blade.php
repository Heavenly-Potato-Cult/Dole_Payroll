{{--
    Partial: renders one category card of deduction checkboxes + amount inputs.
    Variables: $label, $types (Collection), $enrollments (keyed by deduction_type_id), $employee
--}}
<div class="card">
    <div class="card-header">
        <h3>{{ $label }}</h3>
    </div>
    <div class="card-body" style="padding:0;">
        @foreach ($types as $type)
            @php
                $enroll    = $enrollments->get($type->id);
                $isActive  = $enroll && $enroll->is_active;
                $amount    = $enroll ? $enroll->amount : '';
                $effFrom   = $enroll ? $enroll->effective_from?->format('Y-m-d') : now()->startOfMonth()->format('Y-m-d');
                $effTo     = $enroll ? $enroll->effective_to?->format('Y-m-d') : '';
                $notes     = $enroll ? $enroll->notes : '';
            @endphp

            <div class="deduction-row"
                 style="padding:12px 20px;border-bottom:1px solid var(--border);
                        {{ $type->is_computed ? 'background:var(--navy-light);opacity:0.85;' : '' }}">

                {{-- Main row: checkbox + name + computed badge --}}
                <div style="display:flex;align-items:center;gap:10px;">

                    @if ($type->is_computed)
                        {{-- Computed: no checkbox, just a lock icon --}}
                        <span style="width:18px;text-align:center;color:var(--text-light);" title="Auto-computed">🔒</span>
                        <div style="flex:1;">
                            <span style="font-weight:600;font-size:0.875rem;color:var(--navy);">
                                {{ $type->name }}
                            </span>
                            <span class="badge" style="background:var(--navy-light);color:var(--navy);
                                                        margin-left:8px;font-size:0.66rem;">
                                Auto-computed
                            </span>
                            <div style="font-size:0.76rem;color:var(--text-light);margin-top:2px;">
                                {{ $type->notes }}
                            </div>
                        </div>
                    @else
                        {{-- Manual: checkbox + amount input --}}
                        <input type="checkbox"
                               class="deduction-checkbox"
                               id="enroll_{{ $type->id }}"
                               name="deductions[{{ $type->id }}][enrolled]"
                               value="1"
                               {{ $isActive ? 'checked' : '' }}
                               style="width:16px;height:16px;accent-color:var(--navy);flex-shrink:0;">

                        <label for="enroll_{{ $type->id }}"
                               style="flex:1;cursor:pointer;margin:0;
                                      font-weight:600;font-size:0.875rem;
                                      text-transform:none;letter-spacing:0;
                                      color:var(--navy);">
                            {{ $type->name }}
                        </label>
                    @endif

                </div>

                @if (! $type->is_computed)
                {{-- Amount + date range row — shown only when checked --}}
                <div class="deduction-amount-row"
                     style="margin-top:10px;padding-left:28px;
                            display:{{ $isActive ? 'block' : 'none' }};">

                    <div style="display:grid;grid-template-columns:150px 140px 140px;gap:10px;align-items:end;">

                        <div>
                            <label style="font-size:0.72rem;margin-bottom:3px;">Monthly Amount (₱)</label>
                            <input type="number"
                                   class="deduction-amount"
                                   name="deductions[{{ $type->id }}][amount]"
                                   value="{{ $amount }}"
                                   min="0" step="0.01"
                                   placeholder="0.00"
                                   style="margin-bottom:0;">
                        </div>

                        <div>
                            <label style="font-size:0.72rem;margin-bottom:3px;">Effective From</label>
                            <input type="date"
                                   name="deductions[{{ $type->id }}][effective_from]"
                                   value="{{ $effFrom }}"
                                   style="margin-bottom:0;">
                        </div>

                        <div>
                            <label style="font-size:0.72rem;margin-bottom:3px;">Effective To (leave blank = ongoing)</label>
                            <input type="date"
                                   name="deductions[{{ $type->id }}][effective_to]"
                                   value="{{ $effTo }}"
                                   style="margin-bottom:0;">
                        </div>

                    </div>

                    <div style="margin-top:8px;">
                        <label style="font-size:0.72rem;margin-bottom:3px;">Notes (optional)</label>
                        <input type="text"
                               name="deductions[{{ $type->id }}][notes]"
                               value="{{ $notes }}"
                               placeholder="e.g. Loan account no., loan period…"
                               maxlength="200"
                               style="margin-bottom:0;">
                    </div>

                </div>
                @endif

            </div>
        @endforeach
    </div>
</div>