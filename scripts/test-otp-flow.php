<?php

require_once __DIR__ . '/../vendor/autoload.php';

echo "🧪 Testing OTP flow...\n\n";

$apiUrl = 'http://localhost:8000/api';
$testEmail = 'test@example.com'; // Change this to a real email in your database

// Test 1: Check if controllers are separated
echo "1️⃣ Testing controller separation:\n";
$authTest = file_get_contents("$apiUrl/auth/test-auth");
$forgotTest = file_get_contents("$apiUrl/auth/test-forgot");

echo "AuthController: " . (strpos($authTest, 'Clean AuthController') !== false ? "✅ Clean" : "❌ Has forgot methods") . "\n";
echo "ForgotPassController: " . (strpos($forgotTest, 'ForgotPassController') !== false ? "✅ Correct" : "❌ Wrong controller") . "\n\n";

// Test 2: Try forgot password request
echo "2️⃣ Testing forgot password request:\n";
$postData = json_encode(['email' => $testEmail]);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $postData
    ]
]);

try {
    $response = file_get_contents("$apiUrl/auth/forgot-password", false, $context);
    $data = json_decode($response, true);
    
    if ($data['response'] ?? false) {
        echo "✅ Forgot password request successful\n";
        echo "📧 OTP generated: " . ($data['data']['otp'] ?? 'Not shown') . "\n";
    } else {
        echo "❌ Forgot password failed: " . implode(', ', $data['message'] ?? ['Unknown error']) . "\n";
    }
} catch (Exception $e) {
    echo "❌ Request failed: " . $e->getMessage() . "\n";
}

echo "\n🎯 If you see the OTP above, copy it and test the verification step!\n";
