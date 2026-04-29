<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PayrollReleasedOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $payroll = $request->route('payroll');
        
        if (!$payroll || !in_array($payroll->status, ['released', 'locked'])) {
            abort(403, 'Payslips are only available for released payroll batches.');
        }
        
        return $next($request);
    }
}
