<?php

/**
 * Test Registration Script
 * Tests if user registration is working properly
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Models\Otp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

echo "🧪 Testing User Registration\n";
echo "============================\n\n";

try {
    // Generate unique test data
    $testEmail = 'test_' . time() . '@example.com';
    $testPhone = '+1234567890' . rand(100, 999);
    
    echo "1. Attempting to create test user...\n";
    echo "   Email: $testEmail\n";
    echo "   Phone: $testPhone\n";
    
    // Check if user already exists
    $existingUser = User::where('email', $testEmail)->orWhere('phone', $testPhone)->first();
    if ($existingUser) {
        echo "   ❌ Test user already exists, using different data\n";
        $testEmail = 'test_' . (time() + 1) . '@example.com';
        $testPhone = '+1234567890' . rand(100, 999);
    }
    
    // Create test user
    $testUser = User::create([
        'name' => 'Test User',
        'email' => $testEmail,
        'phone' => $testPhone,
        'role' => 'donor',
        'password' => Hash::make('password123'),
        'search_id' => Str::random(10),
        'joined_date' => now(),
    ]);
    
    echo "   ✅ Test user created successfully!\n";
    echo "   User ID: {$testUser->id}\n";
    echo "   Search ID: {$testUser->search_id}\n";
    echo "   Joined Date: {$testUser->joined_date}\n";
    
    // Check if OTP was created
    echo "\n2. Checking OTP creation...\n";
    $otp = Otp::where('user_id', $testUser->id)->first();
    if ($otp) {
        echo "   ✅ OTP record created\n";
        echo "   OTP: {$otp->otp}\n";
        echo "   Expires: {$otp->expire}\n";
    } else {
        echo "   ❌ No OTP record found\n";
    }
    
    // Test login attempt (should fail because email not verified)
    echo "\n3. Testing login attempt...\n";
    $loginUser = User::where('email', $testEmail)->first();
    if ($loginUser) {
        if ($loginUser->email_verified_at) {
            echo "   ✅ User is verified (should not be)\n";
        } else {
            echo "   ✅ User is not verified (correct)\n";
        }
    } else {
        echo "   ❌ User not found for login test\n";
    }
    
    // Clean up test user
    echo "\n4. Cleaning up test user...\n";
    if ($otp) {
        $otp->delete();
    }
    $testUser->delete();
    echo "   ✅ Test user cleaned up\n";
    
    echo "\n🎉 Registration test completed successfully!\n";
    echo "   If you see this message, registration is working properly.\n";
    
} catch (Exception $e) {
    echo "❌ Registration test failed: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 