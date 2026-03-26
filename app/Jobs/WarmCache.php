<?php

namespace App\Jobs;

use App\Models\Division;
use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WarmCache implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function handle(): void
    {
        Log::info('WarmCache job started...');

        // Force fresh pull from Aiven → store in Redis
        Cache::put('divisions.all', 
            Division::orderBy('name')->get(['id', 'name', 'code']), 
            now()->addHours(2)
        );

        Cache::put('employees.page.1',
            Employee::with('division')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->paginate(20),
            now()->addMinutes(30)
        );

        Log::info('WarmCache job completed.');
    }
}