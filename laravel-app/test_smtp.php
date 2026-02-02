<?php

/**
 * SMTP Connection Test Script
 * 
 * This script tests the Gmail SMTP connection directly to help diagnose authentication issues.
 * Run: php test_smtp.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$logPath = 'c:\Users\User\Desktop\CB015938_Yelani Samarathunga\.cursor\debug.log';

echo "Testing Gmail SMTP Connection...\n\n";

$mailConfig = config('mail.mailers.smtp');

echo "Configuration:\n";
echo "  Host: " . ($mailConfig['host'] ?? 'NOT SET') . "\n";
echo "  Port: " . ($mailConfig['port'] ?? 'NOT SET') . "\n";
echo "  Encryption: " . ($mailConfig['encryption'] ?? 'NOT SET') . "\n";
echo "  Username: " . ($mailConfig['username'] ?? 'NOT SET') . "\n";
echo "  Password Length: " . strlen($mailConfig['password'] ?? '') . " characters\n";
echo "  Password First 2: " . (!empty($mailConfig['password']) ? substr($mailConfig['password'], 0, 2) : 'N/A') . "\n";
echo "  Password Last 2: " . (!empty($mailConfig['password']) ? substr($mailConfig['password'], -2) : 'N/A') . "\n";
echo "  Default Mailer: " . config('mail.default') . "\n\n";

// #region agent log
$logData = [
    'sessionId' => 'debug-session',
    'runId' => 'smtp-test',
    'hypothesisId' => 'E',
    'location' => 'test_smtp.php:35',
    'message' => 'SMTP test script started',
    'data' => [
        'host' => $mailConfig['host'] ?? null,
        'port' => $mailConfig['port'] ?? null,
        'encryption' => $mailConfig['encryption'] ?? null,
        'username' => $mailConfig['username'] ?? null,
        'password_length' => strlen($mailConfig['password'] ?? ''),
        'password_first_2' => !empty($mailConfig['password']) ? substr($mailConfig['password'], 0, 2) : null,
        'password_last_2' => !empty($mailConfig['password']) ? substr($mailConfig['password'], -2) : null,
    ],
    'timestamp' => time() * 1000,
];
file_put_contents($logPath, json_encode($logData) . "\n", FILE_APPEND);
// #endregion

try {
    echo "Attempting to send test email...\n";
    
    $testEmail = config('mail.from.address');
    
    \Illuminate\Support\Facades\Mail::raw('This is a test email from Traxtar SMTP test script.', function ($message) use ($testEmail) {
        $message->to($testEmail)
                ->subject('Traxtar SMTP Test');
    });
    
    echo "✅ SUCCESS: Test email sent successfully!\n";
    echo "Check your inbox at: {$testEmail}\n";
    
    // #region agent log
    $logData = [
        'sessionId' => 'debug-session',
        'runId' => 'smtp-test',
        'hypothesisId' => 'E',
        'location' => 'test_smtp.php:60',
        'message' => 'SMTP test succeeded',
        'data' => ['test_email' => $testEmail],
        'timestamp' => time() * 1000,
    ];
    file_put_contents($logPath, json_encode($logData) . "\n", FILE_APPEND);
    // #endregion
    
} catch (\Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n\n";
    echo "Error Details:\n";
    echo "  Class: " . get_class($e) . "\n";
    echo "  Code: " . $e->getCode() . "\n";
    
    // #region agent log
    $logData = [
        'sessionId' => 'debug-session',
        'runId' => 'smtp-test',
        'hypothesisId' => 'E',
        'location' => 'test_smtp.php:75',
        'message' => 'SMTP test failed',
        'data' => [
            'error_message' => $e->getMessage(),
            'error_code' => $e->getCode(),
            'error_class' => get_class($e),
        ],
        'timestamp' => time() * 1000,
    ];
    file_put_contents($logPath, json_encode($logData) . "\n", FILE_APPEND);
    // #endregion
    
    echo "\n";
    echo "Troubleshooting Steps:\n";
    echo "1. Verify 2FA is enabled: https://myaccount.google.com/security\n";
    echo "2. Generate a new App Password: https://myaccount.google.com/apppasswords\n";
    echo "3. Make sure you copy all 16 characters (no spaces)\n";
    echo "4. Update .env with the new password\n";
    echo "5. Run: php artisan config:clear\n";
}

echo "\nDone.\n";
