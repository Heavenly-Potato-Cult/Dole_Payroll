{{-- resources/views/payroll/_approval_bar.blade.php --}}
@php
    $steps = [
        ['key' => 'draft',              'label' => 'Draft',       'icon' => '✏'],
        ['key' => 'computed',           'label' => 'Computed',    'icon' => '⚙'],
        ['key' => 'pending_accountant', 'label' => 'Accountant',  'icon' => '💼'],
        ['key' => 'pending_rd',         'label' => 'RD / ARD',    'icon' => '🏛'],
        ['key' => 'released',           'label' => 'Released',    'icon' => '💰'],
        ['key' => 'locked',             'label' => 'Locked',      'icon' => '🔒'],
    ];

    $order = array_column($steps, 'key');
    $currentIndex = array_search($payroll->status, $order);
@endphp

<div style="
    display:flex; align-items:center; gap:0;
    background:white; border:1px solid var(--border);
    border-radius:var(--radius); padding:16px 20px;
    margin-bottom:24px; overflow-x:auto;
">
    @foreach ($steps as $i => $step)
        @php
            $isDone    = $i < $currentIndex;
            $isCurrent = $i === $currentIndex;
        @endphp

        {{-- Step dot --}}
        <div style="
            display:flex; flex-direction:column; align-items:center;
            min-width:80px; gap:6px;
        ">
            <div style="
                width:36px; height:36px; border-radius:50%;
                display:flex; align-items:center; justify-content:center;
                font-size:0.9rem; font-weight:700;
                background: {{ $isDone ? 'var(--success)' : ($isCurrent ? 'var(--navy)' : 'var(--border)') }};
                color: {{ ($isDone || $isCurrent) ? 'white' : 'var(--text-light)' }};
                border: 2px solid {{ $isDone ? 'var(--success)' : ($isCurrent ? 'var(--navy)' : 'var(--border)') }};
            ">
                {{ $isDone ? '✓' : $step['icon'] }}
            </div>
            <div style="
                font-size:0.70rem; font-weight:{{ $isCurrent ? '700' : '500' }};
                color: {{ $isCurrent ? 'var(--navy)' : ($isDone ? 'var(--success)' : 'var(--text-light)') }};
                text-align:center; white-space:nowrap;
            ">
                {{ $step['label'] }}
            </div>
        </div>

        {{-- Connector line (except after last step) --}}
        @if (!$loop->last)
        <div style="
            flex:1; height:2px; min-width:20px;
            background: {{ $i < $currentIndex ? 'var(--success)' : 'var(--border)' }};
            margin-bottom:18px;
        "></div>
        @endif
    @endforeach
</div>
