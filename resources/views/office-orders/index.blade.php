{{-- resources/views/office-orders/index.blade.php --}}
{{--
    Expects from OfficeOrderController@index:
      $orders      — paginated OfficeOrder (with employee)
      $currentYear — int
--}}

@extends('layouts.app')

@section('title', 'Office Orders')
@section('page-title', 'Travel (TEV)')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Office Orders</h1>
        <p>Manage travel authority documents for DOLE RO9 employees.</p>
    </div>
    @if (auth()->user()->hasAnyRole(['payroll_officer', 'hrmo']))
        <a href="{{ route('office-orders.create') }}" class="btn btn-primary">
            + New Office Order
        </a>
    @endif
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
@endif

{{-- ── Filter bar ── --}}
<div class="card mb-3">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET" action="{{ route('office-orders.index') }}"
              style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">

            <div class="form-group" style="margin:0; min-width:120px;">
                <label for="year" style="margin-bottom:4px;">Year</label>
                <select name="year" id="year">
                    <option value="">All Years</option>
                    @foreach (range($currentYear, $currentYear - 3) as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin:0; min-width:160px;">
                <label for="status" style="margin-bottom:4px;">Status</label>
                <select name="status" id="status">
                    <option value="">All Statuses</option>
                    <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>Draft</option>
                    <option value="approved"  {{ request('status') === 'approved'  ? 'selected' : '' }}>Approved</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div style="display:flex; gap:8px; align-items:flex-end;">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('office-orders.index') }}" class="btn btn-outline btn-sm">Reset</a>
            </div>

        </form>
    </div>
</div>

{{-- ── Table ── --}}
<div class="card">
    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>OO No.</th>
                        <th>Employee</th>
                        <th>Purpose</th>
                        <th>Destination</th>
                        <th>Travel Type</th>
                        <th>Date Range</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        @php
                            $emp = $order->employee;

                            $statusClass = match ($order->status) {
                                'approved'  => 'badge-released',
                                'cancelled' => 'badge-inactive',
                                default     => 'badge-draft',
                            };
                            $statusLabel = match ($order->status) {
                                'draft'     => 'Draft',
                                'approved'  => 'Approved',
                                'cancelled' => 'Cancelled',
                                default     => ucfirst($order->status),
                            };

                            $typeStyle = match ($order->travel_type) {
                                'regional' => 'background:#FFF8E1; color:#F57F17; border:1px solid #F9A825;',
                                'national' => 'background:#E8EAF6; color:#1A237E; border:1px solid #3949AB;',
                                default    => 'background:#E8F5E9; color:#1B5E20; border:1px solid #43A047;',
                            };
                            $typeLabel = ucfirst($order->travel_type);
                        @endphp
                        <tr>
                            <td class="fw-bold" style="color:var(--navy); white-space:nowrap;">
                                {{ $order->office_order_no }}
                            </td>

                            <td class="fw-bold">
                                {{ optional($emp)->last_name }},
                                {{ optional($emp)->first_name }}
                                @if (optional($emp)->middle_name)
                                    {{ substr($emp->middle_name, 0, 1) }}.
                                @endif
                            </td>

                            <td style="max-width:200px; font-size:0.83rem;">
                                {{ Str::limit($order->purpose, 60) }}
                            </td>

                            <td style="font-size:0.83rem;">{{ $order->destination }}</td>

                            <td>
                                <span style="font-size:0.72rem; font-weight:700;
                                             padding:3px 10px; border-radius:12px;
                                             {{ $typeStyle }}">
                                    {{ $typeLabel }}
                                </span>
                            </td>

                            <td class="text-muted" style="font-size:0.82rem; white-space:nowrap;">
                                {{ $order->travel_date_start->format('M d, Y') }}
                                –
                                {{ $order->travel_date_end->format('M d, Y') }}
                            </td>

                            <td>
                                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>

                            <td>
                                <div class="d-flex gap-2" style="justify-content:center;">
                                    <a href="{{ route('office-orders.show', $order->id) }}"
                                       class="btn btn-outline btn-sm">View</a>

                                    @if ($order->status === 'draft' && auth()->user()->hasAnyRole(['ard', 'chief_admin_officer']))
                                        <form method="POST"
                                              action="{{ route('office-orders.approve', $order->id) }}"
                                              onsubmit="return confirm('Approve this Office Order?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                ✓ Approve
                                            </button>
                                        </form>
                                    @endif

                                    @if ($order->status === 'approved' && auth()->user()->hasAnyRole(['hrmo', 'ard', 'chief_admin_officer']))
                                        <form method="POST"
                                              action="{{ route('office-orders.cancel', $order->id) }}"
                                              onsubmit="return confirm('Cancel this Office Order? This cannot be undone.')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                Cancel
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:40px; color:var(--text-light);">
                                No office orders found.
                                @if (auth()->user()->hasAnyRole(['payroll_officer', 'hrmo']))
                                    <a href="{{ route('office-orders.create') }}">Create one now →</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div style="margin-top:12px;">{{ $orders->links() }}</div>

@endsection