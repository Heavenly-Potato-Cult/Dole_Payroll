<?php

namespace App\Jobs;
use Spatie\Permission\PermissionRegistrar;
use App\Models\Division;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WarmCache implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    protected $userId;

    public function __construct()
    {
        // Capture the logged in user ID at dispatch time
        $this->userId = Auth::id();
    }

    public function handle(): void
    {
        Log::info('WarmCache job started...');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        app(PermissionRegistrar::class)->getPermissions();
        // Cache the auth user
        if ($this->userId) {
            $user = User::find($this->userId);
            if ($user) {
                Cache::put("auth.user.{$this->userId}", $user, now()->addMinutes(30));
            }
        }

        // Cache divisions
        Cache::put('divisions.all',
            Division::orderBy('name')->get(['id', 'name', 'code']),
            now()->addHours(2)
        );

        // Cache employees page 1
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