<?php

echo "🔍 Laravel Controller Audit\n";
echo str_repeat("=", 50) . "\n";

$controllerDirs = [
    'app/Http/Controllers/',
    'app/Http/Controllers/Api/',
    'app/Http/Controllers/Api/Auth/',
    'app/Http/Controllers/Auth/',
];

foreach ($controllerDirs as $dir) {
    if (!is_dir($dir)) continue;
    
    echo "\n📁 Directory: $dir\n";
    echo str_repeat("-", 40) . "\n";
    
    $files = glob($dir . "*.php");
    
    if (empty($files)) {
        echo "   (empty)\n";
        continue;
    }
    
    foreach ($files as $file) {
        $filename = basename($file);
        $content = file_get_contents($file);
        $lines = substr_count($content, "\n");
        $modified = date('M j, H:i', filemtime($file));
        
        echo sprintf("   %-30s %4d lines, %s", $filename, $lines, $modified);
        
        // Check for specific methods
        $methods = [];
        if (strpos($content, 'function forgotPass') !== false) $methods[] = 'forgotPass';
        if (strpos($content, 'function verifyResetOtp') !== false) $methods[] = 'verifyResetOtp';
        if (strpos($content, 'function resetPass') !== false) $methods[] = 'resetPass';
        if (strpos($content, 'function login') !== false) $methods[] = 'login';
        if (strpos($content, 'function register') !== false) $methods[] = 'register';
        
        if (!empty($methods)) {
            echo " [" . implode(', ', $methods) . "]";
        }
        
        echo "\n";
    }
}

echo "\n🎯 Summary:\n";
echo "Run this script anytime to see which controllers exist and what methods they contain.\n";
