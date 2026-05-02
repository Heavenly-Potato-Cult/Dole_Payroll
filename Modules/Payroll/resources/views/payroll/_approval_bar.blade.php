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
            'sub'      => 'Payroll Officer',
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

    // Create dynamic sub-labels based on status and timestamps
    $dynamicSubs = [];
    foreach ($steps as $i => $step) {
        if ($i < $activeStep) {
            // Completed stage - show when it happened
            if ($i === 0 && $payroll->created_at) {
                $dynamicSubs[] = 'Done · ' . $payroll->created_at->format('M d');
            } elseif ($i === 1) {
                // Accountant step - use audit logs to find certification date
                $certLog = $payroll->auditLogs->where('action', 'certified')->first();
                if ($certLog && $certLog->created_at) {
                    $dynamicSubs[] = 'Certified · ' . $certLog->created_at->format('M d');
                } else {
                    $dynamicSubs[] = 'Certified';
                }
            } elseif ($i === 2 && $payroll->approved_at) {
                $dynamicSubs[] = 'Approved · ' . \Carbon\Carbon::parse($payroll->approved_at)->format('M d');
            } elseif ($i === 3 && $payroll->released_at) {
                $dynamicSubs[] = 'Released · ' . \Carbon\Carbon::parse($payroll->released_at)->format('M d');
            } else {
                $dynamicSubs[] = $step['sub'];
            }
        } elseif ($i === $activeStep) {
            // Active stage - show what's waiting
            if ($payroll->status === 'draft') {
                $dynamicSubs[] = 'Awaiting computation';
            } elseif ($payroll->status === 'computed') {
                $dynamicSubs[] = 'Ready for submission';
            } elseif ($payroll->status === 'pending_accountant') {
                $dynamicSubs[] = 'Awaiting certification';
            } elseif ($payroll->status === 'pending_rd') {
                $dynamicSubs[] = 'Awaiting approval';
            } elseif ($payroll->status === 'released') {
                $dynamicSubs[] = 'Ready for disbursement';
            } elseif ($payroll->status === 'locked') {
                $dynamicSubs[] = 'Disbursement complete';
            } else {
                $dynamicSubs[] = $step['sub'];
            }
        } else {
            // Future stage - just show the role
            $dynamicSubs[] = $step['sub'];
        }
    }
@endphp

<div class="approval-stepper">
            <!-- Progress fill line -->
            <div class="progress-fill" style="width: {{ ($activeStep / (count($steps) - 1)) * 100 }}%;"></div>
            
            @foreach ($steps as $i => $step)
                @php
                    if ($i < $activeStep) {
                        $stepClass = 'done';
                    } elseif ($i === $activeStep) {
                        $stepClass = ($payroll->status === 'locked') ? 'locked' : 'active';
                    } else {
                        $stepClass = 'future';
                    }

                    $dotContent = $i + 1; // Show step number instead of icon
                @endphp

                <div class="approval-step {{ $stepClass }}">
                    <div class="approval-step-dot">{{ $dotContent }}</div>
                    <div class="approval-step-label">
                        {{ $step['label'] }}
                        <span class="approval-step-sub">{{ $dynamicSubs[$i] }}</span>
                    </div>
                </div>
            @endforeach
</div>