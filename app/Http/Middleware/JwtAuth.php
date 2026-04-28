<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\Auth;

class JwtAuth
{
    /**
     * JWT configuration - must match HRIS config
     */
    private $jwtSecret = 'dole-hris-payroll-shared-secret-2024';
    private $jwtIssuer = 'hris-system';
    private $jwtAudience = 'payroll-system';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect('/login')->with('error', 'No token provided. Please login from HRIS.');
        }

        try {
            // Decode and validate JWT
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));

            // Validate issuer and audience
            if ($decoded->iss !== $this->jwtIssuer || $decoded->aud !== $this->jwtAudience) {
                return redirect('/login')->with('error', 'Invalid token issuer or audience.');
            }

            // Store user data in session
            session([
                'hris_user' => [
                    'employee_id' => $decoded->employeeId,
                    'name' => $decoded->name,
                    'email' => $decoded->email,
                    'department' => $decoded->department,
                    'full_profile' => $decoded->fullProfile ?? null,
                ]
            ]);

            // Log the successful authentication
            \Log::info('HRIS JWT Authentication successful', [
                'employee_id' => $decoded->employeeId,
                'name' => $decoded->name,
            ]);

            return $next($request);

        } catch (ExpiredException $e) {
            return redirect('/login')->with('error', 'Token expired. Please login from HRIS again.');
        } catch (\Exception $e) {
            \Log::error('JWT Authentication failed', ['error' => $e->getMessage()]);
            return redirect('/login')->with('error', 'Invalid token. Please login from HRIS.');
        }
    }
}
