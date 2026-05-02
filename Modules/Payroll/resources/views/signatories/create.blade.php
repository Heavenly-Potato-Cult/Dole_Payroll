@extends('layouts.app')

@section('title', 'Add Signatory')
@section('page-title', 'Add Signatory')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Add Signatory</h1>
        <p>Register a signing officer for payslips and official reports</p>
    </div>
    <a href="{{ route('signatories.index') }}" class="btn btn-outline">← Back</a>
</div>

<div style="max-width:520px;">
    <div class="card">
        <div class="card-header"><h3>Officer Details</h3></div>
        <div class="card-body">
            <form method="POST" action="{{ route('signatories.store') }}" id="signatoryForm">
                @csrf

                {{-- ── Role type ─────────────────────────────────────────── --}}
                <div class="form-group">
                    <label for="role_type">Signing Role <span style="color:var(--red)">*</span></label>
                    <select id="role_type" name="role_type"
                            class="{{ $errors->has('role_type') ? 'is-invalid' : '' }}" required>
                        <option value="">— Select a role —</option>
                        <option value="hrmo_designate" {{ old('role_type') === 'hrmo_designate' ? 'selected' : '' }}>HRMO Designate</option>
                        <option value="accountant"     {{ old('role_type') === 'accountant'     ? 'selected' : '' }}>Accountant</option>
                        <option value="ard"            {{ old('role_type') === 'ard'            ? 'selected' : '' }}>ARD / RD</option>
                        <option value="cashier"        {{ old('role_type') === 'cashier'        ? 'selected' : '' }}>Cashier</option>
                    </select>
                    @error('role_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div style="font-size:0.78rem; color:var(--text-light); margin-top:4px;">
                        The document role this person signs on behalf of.
                    </div>
                </div>

                {{-- ── User picker — populated via JS ──────────────────────── --}}
                <div class="form-group" id="userPickerWrap" style="display:none;">
                    <label for="user_id">Officer <span style="color:var(--red)">*</span></label>

                    {{-- Loading state --}}
                    <div id="userLoading" style="display:none; font-size:0.83rem; color:var(--text-light); padding:6px 0;">
                        Loading users…
                    </div>

                    {{-- Single user — shown as read-only text, hidden input carries the value --}}
                    <div id="userSingle" style="display:none;">
                        <div id="userSingleName"
                             style="padding:8px 10px; background:var(--bg); border:1px solid var(--border);
                                    border-radius:var(--radius); font-weight:600; color:var(--navy);"></div>
                        <input type="hidden" name="user_id" id="userSingleInput">
                        <div style="font-size:0.78rem; color:var(--text-light); margin-top:4px;">
                            Only one user holds this role — auto-selected.
                        </div>
                    </div>

                    {{-- Multiple users — dropdown --}}
                    <select id="userSelect" name="user_id"
                            class="{{ $errors->has('user_id') ? 'is-invalid' : '' }}"
                            style="display:none;">
                        <option value="">— Select an officer —</option>
                    </select>

                    {{-- No users found --}}
                    <div id="userNone" style="display:none; font-size:0.83rem; color:var(--red); padding:6px 0;">
                        No users with this role found. Assign the role in User Management first.
                    </div>

                    @error('user_id')
                        <div class="invalid-feedback" style="display:block;">{{ $message }}</div>
                    @enderror
                </div>

                {{-- ── Position title ───────────────────────────────────────── --}}
                <div class="form-group">
                    <label for="position_title">Position Title</label>
                    <input type="text" id="position_title" name="position_title"
                           class="{{ $errors->has('position_title') ? 'is-invalid' : '' }}"
                           value="{{ old('position_title') }}"
                           placeholder="e.g. Labor Employment Officer III">
                    @error('position_title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div style="font-size:0.78rem; color:var(--text-light); margin-top:4px;">
                        Appears below the name on payslips. Optional but recommended.
                    </div>
                </div>

                {{-- ── Display name override ────────────────────────────────── --}}
                <div class="form-group">
                    <label for="full_name">Display Name Override</label>
                    <input type="text" id="full_name" name="full_name"
                           class="{{ $errors->has('full_name') ? 'is-invalid' : '' }}"
                           value="{{ old('full_name') }}"
                           placeholder="Leave blank to use the officer's account name">
                    @error('full_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div style="font-size:0.78rem; color:var(--text-light); margin-top:4px;">
                        Only fill this if the payslip name must differ from the login name (e.g. formal vs. nickname).
                    </div>
                </div>

                {{-- ── Active checkbox ──────────────────────────────────────── --}}
                <div class="form-group" style="margin-bottom:0;">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-weight:600;">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active') ? 'checked' : '' }}
                               style="width:auto; accent-color:var(--navy);">
                        Set as active immediately
                    </label>
                    <div style="font-size:0.78rem; color:var(--text-light); margin-top:4px; padding-left:26px;">
                        Checking this will deactivate any currently active signatory for the same role.
                    </div>
                </div>

                <div style="display:flex; gap:10px; margin-top:24px;">
                    <button type="submit" class="btn btn-primary">✓ Add Signatory</button>
                    <a href="{{ route('signatories.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
(function () {
    const roleSelect    = document.getElementById('role_type');
    const pickerWrap    = document.getElementById('userPickerWrap');
    const loadingEl     = document.getElementById('userLoading');
    const singleWrap    = document.getElementById('userSingle');
    const singleName    = document.getElementById('userSingleName');
    const singleInput   = document.getElementById('userSingleInput');
    const multiSelect   = document.getElementById('userSelect');
    const noneEl        = document.getElementById('userNone');

    // Pre-select old value after a validation error
    const oldUserId = '{{ old('user_id') }}';

    function hideAll() {
        loadingEl.style.display   = 'none';
        singleWrap.style.display  = 'none';
        multiSelect.style.display = 'none';
        noneEl.style.display      = 'none';
        // Remove name attr from multiSelect so it doesn't submit when hidden
        multiSelect.removeAttribute('name');
    }

    async function loadUsers(roleType) {
        pickerWrap.style.display = 'block';
        hideAll();
        loadingEl.style.display = 'block';

        try {
            const res  = await fetch(`/signatories/users-for-role?role_type=${roleType}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            const users = data.users || [];

            hideAll();

            if (users.length === 0) {
                noneEl.style.display = 'block';

            } else if (users.length === 1) {
                // Auto-select the only user
                singleName.textContent  = users[0].name;
                singleInput.value       = users[0].id;
                singleWrap.style.display = 'block';

            } else {
                // Populate and show the dropdown
                multiSelect.innerHTML = '<option value="">— Select an officer —</option>';
                users.forEach(u => {
                    const opt      = document.createElement('option');
                    opt.value      = u.id;
                    opt.textContent = u.name;
                    if (String(u.id) === oldUserId) opt.selected = true;
                    multiSelect.appendChild(opt);
                });
                multiSelect.setAttribute('name', 'user_id');
                multiSelect.style.display = 'block';
            }
        } catch (e) {
            hideAll();
            noneEl.textContent   = 'Error loading users. Please try again.';
            noneEl.style.display = 'block';
        }
    }

    roleSelect.addEventListener('change', function () {
        if (!this.value) {
            pickerWrap.style.display = 'none';
            hideAll();
            return;
        }
        loadUsers(this.value);
    });

    // On page load, if old role_type is set (after validation error), reload users
    if (roleSelect.value) {
        loadUsers(roleSelect.value);
    }
})();
</script>
@endsection
