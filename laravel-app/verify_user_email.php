<?php
/**
 * Quick script to manually verify a user's email address
 * 
 * Usage: php verify_user_email.php <user_id>
 * Example: php verify_user_email.php 2
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

// Get user ID from command line argument
$userId = $argv[1] ?? null;

if (!$userId) {
    echo "Usage: php verify_user_email.php <user_id>\n";
    echo "Example: php verify_user_email.php 2\n";
    exit(1);
}

try {
    $user = User::find($userId);
    
    if (!$user) {
        echo "User with ID {$userId} not found.\n";
        exit(1);
    }
    
    if ($user->hasVerifiedEmail()) {
        echo "User {$user->email} is already verified.\n";
        exit(0);
    }
    
    // Mark email as verified
    $user->email_verified_at = now();
    $user->save();
    
    echo "✅ Successfully verified email for user: {$user->email}\n";
    echo "User ID: {$user->id}\n";
    echo "Name: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "\nYou can now access protected routes!\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
