<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseSecurityService
{
    /**
     * Execute a secure query with logging
     *
     * @param string $query
     * @param array $bindings
     * @param string $operation
     * @return mixed
     */
    public function executeSecureQuery(string $query, array $bindings = [], string $operation = 'SELECT')
    {
        try {
            // Log the query for security auditing (without sensitive data)
            Log::info('Database query executed', [
                'operation' => $operation,
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now(),
            ]);

            // Execute the query with prepared statements
            return DB::select($query, $bindings);
            
        } catch (\Exception $e) {
            // Log security-related database errors
            Log::error('Database security error', [
                'error' => $e->getMessage(),
                'operation' => $operation,
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'timestamp' => now(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Validate and sanitize SQL input
     *
     * @param mixed $input
     * @return mixed
     */
    public function sanitizeSqlInput($input)
    {
        if (is_string($input)) {
            // Remove potential SQL injection patterns
            $input = preg_replace('/[^\w\s\-\.\@]/', '', $input);
            $input = trim($input);
        }
        
        return $input;
    }

    /**
     * Check for suspicious SQL patterns
     *
     * @param string $input
     * @return bool
     */
    public function containsSuspiciousPatterns(string $input): bool
    {
        $suspiciousPatterns = [
            '/union\s+select/i',
            '/drop\s+table/i',
            '/delete\s+from/i',
            '/insert\s+into/i',
            '/update\s+set/i',
            '/exec\s*\(/i',
            '/script\s*>/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload\s*=/i',
            '/onerror\s*=/i',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                // Log suspicious activity
                Log::warning('Suspicious SQL pattern detected', [
                    'pattern' => $pattern,
                    'input' => substr($input, 0, 100), // Log first 100 chars only
                    'user_id' => auth()->id(),
                    'ip_address' => request()->ip(),
                    'timestamp' => now(),
                ]);
                
                return true;
            }
        }

        return false;
    }

    /**
     * Secure user lookup by credentials
     *
     * @param string $login
     * @return \App\Models\User|null
     */
    public function findUserByLogin(string $login)
    {
        // Sanitize input
        $login = $this->sanitizeSqlInput($login);
        
        // Check for suspicious patterns
        if ($this->containsSuspiciousPatterns($login)) {
            Log::warning('Suspicious login attempt blocked', [
                'login' => $login,
                'ip_address' => request()->ip(),
                'timestamp' => now(),
            ]);
            return null;
        }

        // Use parameterized query
        return DB::table('users')
            ->leftJoin('students', 'users.id', '=', 'students.users_id')
            ->where('users.employee_id', $login)
            ->orWhere('students.student_number', $login)
            ->select('users.*')
            ->first();
    }

    /**
     * Log security events
     *
     * @param string $event
     * @param array $data
     */
    public function logSecurityEvent(string $event, array $data = [])
    {
        Log::channel('security')->info($event, array_merge($data, [
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now(),
        ]));
    }
}
