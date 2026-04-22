{{-- resources/views/payroll/_approval_bar.blade.php --}}
{{--
    Expects: $payroll (PayrollBatch)
    Status flow: draft | computed → pending_accountant → pending_rd → released → locked
--}}
@php
    $steps = [
        [
            'statuses' => ['draft', 'computed'],
            'label'    => 'HR Prepared',
            'sub'      => 'Payroll Officer ',
            'icon'     => '✏',
        ],
        [
            'statuses' => ['pending_accountant'],
            'label'    => 'Accountant',
            'sub'      => 'Certify Funds',
            'icon'     => '💼',
        ],
        [
            'statuses' => ['pending_rd'],
            'label'    => 'RD / ARD',
            'sub'      => 'Approval',
            'icon'     => '🏛',
        ],
        [
            'statuses' => ['released'],
            'label'    => 'Released',
            'sub'      => 'Cashier',
            'icon'     => '💰',
        ],
        [
            'statuses' => ['locked'],
            'label'    => 'Locked',
            'sub'      => 'Disbursed',
            'icon'     => '🔒',
        ],
    ];

    // Map the current status to a step index (0-based)
    $statusToStep = [
        'draft'               => 0,
        'computed'            => 0,
        'pending_accountant'  => 1,
        'pending_rd'          => 2,
        'released'            => 3,
        'locked'              => 4,
    ];
    $activeStep = $statusToStep[$payroll->status] ?? 0;
@endphp

<div class="approval-bar">
    @foreach ($steps as $i => $step)
        @php
            if ($i < $activeStep) {
                $stepClass = 'done';
            } elseif ($i === $activeStep) {
                $stepClass = ($payroll->status === 'locked') ? 'locked' : 'active';
            } else {
                $stepClass = '';
            }

            $dotContent = $stepClass === 'done' ? '✓' : $step['icon'];
        @endphp

        <div class="approval-step {{ $stepClass }}">
            <div class="approval-step-dot">{{ $dotContent }}</div>
            <div class="approval-step-label">
                {{ $step['label'] }}
                <small>{{ $step['sub'] }}</small>
            </div>
        </div>

        @if (!$loop->last)
            {{-- The CSS for .approval-bar uses flex, so no explicit connector needed;
                 the border-right on each step serves as the visual divider. --}}
        @endif
    @endforeach
</div>