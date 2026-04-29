<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class JwtAuth
{
    private string $jwtSecret   = 'dole-hris-payroll-shared-secret-2024';
    private string $jwtIssuer   = 'hris-system';
    private string $jwtAudience = 'payroll-system';

    public function handle(Request $request, Closure $next)
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect('/login')->with('error', 'No token provided. Please login from HRIS.');
        }

        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));

            // Validate issuer and audience
            if ($decoded->iss !== $this->jwtIssuer || $decoded->aud !== $this->jwtAudience) {
                return redirect('/login')->with('error', 'Invalid token issuer or audience.');
            }

            // ── Store HRIS data in session only ──────────────────────────────
            // Do NOT look up the user or call Auth::login() here.
            // AuthController::resolveHrisUser() handles find-or-provision + login.
            session([
                'hris_user' => [
                    'employee_id' => $decoded->employeeId,
                    'name'        => $decoded->name,
                    'email'       => $decoded->email ?? null,
                    'department'  => $decoded->department ?? null,
                    'full_profile'=> $decoded->fullProfile ?? null,
                ],
            ]);

            \Log::info('HRIS JWT validated', [
                'employee_id' => $decoded->employeeId,
                'name'        => $decoded->name,
            ]);

            return $next($request);

        } catch (ExpiredException $e) {
            return redirect('/login')->with('error', 'Token expired. Please login from HRIS again.');
        } catch (\Exception $e) {
            \Log::error('JWT validation failed', ['error' => $e->getMessage()]);
            return redirect('/login')->with('error', 'Invalid token. Please login from HRIS.');
        }
    }
}