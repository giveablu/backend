<?php

echo "🔍 Checking for controller conflicts...\n\n";

// Check if multiple controllers exist
$controllers = [
    'AuthController' => 'app/Http/Controllers/Api/Auth/AuthController.php',
    'ForgotPassController' => 'app/Http/Controllers/Api/Auth/ForgotPassController.php',
    'Old AuthController' => 'app/Http/Controllers/Auth/AuthController.php',
    'Old AuthForgotController' => 'app/Http/Controllers/Auth/AuthForgotController.php',
];

foreach ($controllers as $name => $path) {
    if (file_exists($path)) {
        echo "✅ Found: $name at $path\n";
        
        // Check if it has forgot password methods
        $content = file_get_contents($path);
        if (strpos($content, 'forgotPass') !== false) {
            echo "   ⚠️  WARNING: Contains forgotPass method!\n";
        }
        if (strpos($content, 'verifyResetOtp') !== false) {
            echo "   ⚠️  WARNING: Contains verifyResetOtp method!\n";
        }
    } else {
        echo "❌ Missing: $name\n";
    }
}

echo "\n🧹 Recommended cleanup:\n";
echo "1. Remove any old controllers in app/Http/Controllers/Auth/\n";
echo "2. Make sure only ForgotPassController has password reset methods\n";
echo "3. Clear all caches\n";
