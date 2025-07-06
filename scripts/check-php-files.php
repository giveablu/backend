<?php

/**
 * PHP Files Check Script
 * Checks for syntax errors and missing dependencies without touching database
 */

echo "🔍 Checking PHP Files\n";
echo "====================\n\n";

// 1. Check if Laravel is properly loaded
echo "1. Checking Laravel setup...\n";
try {
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "   ✅ Autoloader loaded\n";
    
    // Check if we can access Laravel
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    echo "   ✅ Laravel app loaded\n";
} catch (Exception $e) {
    echo "   ❌ Laravel setup error: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Check controller files for syntax errors
echo "\n2. Checking controller syntax...\n";
$controllers = [
    '../app/Http/Controllers/Api/Auth/RegisterController.php',
    '../app/Http/Controllers/Api/Auth/LoginController.php',
    '../app/Http/Controllers/Api/Auth/ForgotPassController.php',
    '../app/Http/Controllers/Api/Auth/OtpVerifyController.php',
    '../app/Http/Controllers/Api/Auth/LogoutController.php',
];

foreach ($controllers as $controller) {
    $fullPath = __DIR__ . '/' . $controller;
    if (file_exists($fullPath)) {
        $output = shell_exec("php -l " . escapeshellarg($fullPath) . " 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "   ✅ " . basename($controller) . "\n";
        } else {
            echo "   ❌ " . basename($controller) . " - " . trim($output) . "\n";
        }
    } else {
        echo "   ❌ " . basename($controller) . " - File not found\n";
    }
}

// 3. Check model files
echo "\n3. Checking model syntax...\n";
$models = [
    '../app/Models/User.php',
    '../app/Models/Otp.php',
];

foreach ($models as $model) {
    $fullPath = __DIR__ . '/' . $model;
    if (file_exists($fullPath)) {
        $output = shell_exec("php -l " . escapeshellarg($fullPath) . " 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "   ✅ " . basename($model) . "\n";
        } else {
            echo "   ❌ " . basename($model) . " - " . trim($output) . "\n";
        }
    } else {
        echo "   ❌ " . basename($model) . " - File not found\n";
    }
}

// 4. Check routes file
echo "\n4. Checking routes syntax...\n";
$routesFile = __DIR__ . '/../routes/api.php';
if (file_exists($routesFile)) {
    $output = shell_exec("php -l " . escapeshellarg($routesFile) . " 2>&1");
    if (strpos($output, 'No syntax errors') !== false) {
        echo "   ✅ api.php\n";
    } else {
        echo "   ❌ api.php - " . trim($output) . "\n";
    }
} else {
    echo "   ❌ api.php - File not found\n";
}

// 5. Check if classes can be instantiated (without database)
echo "\n5. Checking class instantiation...\n";
try {
    // Test User model
    $user = new \App\Models\User();
    echo "   ✅ User model can be instantiated\n";
} catch (Exception $e) {
    echo "   ❌ User model error: " . $e->getMessage() . "\n";
}

try {
    // Test RegisterController
    $registerController = new \App\Http\Controllers\Api\Auth\RegisterController();
    echo "   ✅ RegisterController can be instantiated\n";
} catch (Exception $e) {
    echo "   ❌ RegisterController error: " . $e->getMessage() . "\n";
}

try {
    // Test LoginController
    $loginController = new \App\Http\Controllers\Api\Auth\LoginController();
    echo "   ✅ LoginController can be instantiated\n";
} catch (Exception $e) {
    echo "   ❌ LoginController error: " . $e->getMessage() . "\n";
}

// 6. Check environment configuration
echo "\n6. Checking environment...\n";
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    echo "   ✅ .env file exists\n";
    
    // Check key environment variables
    $envContent = file_get_contents($envFile);
    $requiredVars = ['DB_CONNECTION', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'];
    
    foreach ($requiredVars as $var) {
        if (strpos($envContent, $var . '=') !== false) {
            echo "   ✅ $var is set\n";
        } else {
            echo "   ❌ $var is missing\n";
        }
    }
} else {
    echo "   ❌ .env file not found\n";
}

echo "\n🎉 PHP files check completed!\n";
echo "   If all checks pass, the issue is likely in the database or frontend.\n"; 