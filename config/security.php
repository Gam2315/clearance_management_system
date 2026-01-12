<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains security-related configuration options for the
    | clearance management system.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Password Security
    |--------------------------------------------------------------------------
    */
    'password' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_symbols' => true,
        'max_age_days' => 90,
        'history_count' => 5, // Remember last 5 passwords
    ],

    /*
    |--------------------------------------------------------------------------
    | Login Security
    |--------------------------------------------------------------------------
    */
    'login' => [
        'max_attempts' => env('MAX_LOGIN_ATTEMPTS', 5),
        'lockout_minutes' => env('LOGIN_LOCKOUT_MINUTES', 30),
        'rate_limit_per_minute' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    */
    'session' => [
        'regenerate_interval_minutes' => 15,
        'max_lifetime_minutes' => 60,
        'force_logout_on_ip_change' => true,
        'track_user_agent' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    */
    'file_upload' => [
        'max_size_kb' => env('MAX_FILE_SIZE', 5120), // 5MB
        'allowed_types' => explode(',', env('ALLOWED_FILE_TYPES', 'jpeg,png,jpg,gif')),
        'scan_for_malware' => true,
        'quarantine_suspicious' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    */
    'headers' => [
        'force_https' => env('FORCE_HTTPS', true),
        'hsts_max_age' => 31536000, // 1 year
        'content_type_nosniff' => true,
        'frame_options' => 'DENY',
        'xss_protection' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Security
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'enabled' => env('SECURITY_LOG_ENABLED', true),
        'log_failed_logins' => true,
        'log_privilege_escalation' => true,
        'log_file_uploads' => true,
        'log_data_access' => true,
        'retention_days' => 90,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Security
    |--------------------------------------------------------------------------
    */
    'database' => [
        'log_queries' => false, // Only enable for debugging
        'query_timeout_seconds' => 30,
        'max_connections' => 100,
        'encrypt_sensitive_fields' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | API Security
    |--------------------------------------------------------------------------
    */
    'api' => [
        'rate_limit_per_minute' => 60,
        'require_authentication' => true,
        'log_all_requests' => true,
        'allowed_origins' => ['https://your-domain.com'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Trail
    |--------------------------------------------------------------------------
    */
    'audit' => [
        'enabled' => true,
        'log_user_actions' => true,
        'log_admin_actions' => true,
        'log_data_changes' => true,
        'retention_months' => 12,
    ],

];
