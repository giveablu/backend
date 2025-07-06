<?php

echo "🔍 Scanning for controller conflicts and recent changes...\n\n";

function scanDirectory($dir, $pattern = '*.php') {
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file;
        }
    }
    
    return $files;
}

function checkFileForMethods($filePath, $methods) {
    $content = file_get_contents($filePath);
    $foundMethods = [];
    
    foreach ($methods as $method) {
        if (strpos($content, $method) !== false) {
            $foundMethods[] = $method;
        }
    }
    
    return $foundMethods;
}

// Scan for all controller files
$controllerFiles = scanDirectory('app/Http/Controllers/');

echo "🎯 Found Controller Files:\n";
echo str_repeat("-", 80) . "\n";

$suspiciousMethods = ['forgotPass', 'verifyResetOtp', 'resetPass', 'forgot_password'];
$conflicts = [];

foreach ($controllerFiles as $file) {
    $relativePath = str_replace(getcwd() . '/', '', $file->getPathname());
    $modTime = date('Y-m-d H:i:s', $file->getMTime());
    $size = $file->getSize();
    
    // Check for suspicious methods
    $foundMethods = checkFileForMethods($file->getPathname(), $suspiciousMethods);
    
    $status = '';
    if (!empty($foundMethods)) {
        $status = ' ⚠️  HAS: ' . implode(', ', $foundMethods);
        $conflicts[] = [
            'file' => $relativePath,
            'methods' => $foundMethods
        ];
    }
    
    printf("%-50s %s (%d bytes)%s\n", $relativePath, $modTime, $size, $status);
}

if (!empty($conflicts)) {
    echo "\n🚨 CONFLICTS DETECTED:\n";
    echo str_repeat("-", 80) . "\n";
    
    foreach ($conflicts as $conflict) {
        echo "File: {$conflict['file']}\n";
        echo "Methods: " . implode(', ', $conflict['methods']) . "\n";
        echo "Action: ";
        
        if (strpos($conflict['file'], 'ForgotPassController') !== false) {
            echo "✅ KEEP - This should handle password reset\n";
        } else {
            echo "❌ REMOVE these methods from this file\n";
        }
        echo "\n";
    }
}

echo "\n📋 Recommendations:\n";
echo "1. Only ForgotPassController should have password reset methods\n";
echo "2. Remove forgot password methods from other controllers\n";
echo "3. Check routes to ensure they point to the right controller\n";
