<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\User;

echo "🧪 Testing OTP setup...\n";

try {
    // Test database connection
    $users = DB::table('users')->count();
    echo "✅ Database connected. Users count: $users\n";
    
    // Test User model
    $user = User::first();
    if ($user) {
        echo "✅ User model working. First user: {$user->email}\n";
        
        // Test OTP fields
        if (method_exists($user, 'generateResetOtp')) {
            echo "✅ OTP methods available\n";
        } else {
            echo "❌ OTP methods missing\n";
        }
    } else {
        echo "⚠️ No users found in database\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "🏁 OTP setup test complete!\n";
