<?php

use Illuminate\Support\Facades\Route;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;

Route::get('/', function () {
    return view('welcome');
});

// Test email route
Route::get('/test-mail', function () {
    try {
        // Send test OTP email
        $testOtp = '123456';
        $testEmail = 'lifeofsroy@gmail.com';
        
        Mail::to($testEmail)->send(new ResetPasswordMail($testOtp, $testEmail, 1, true));
        
        return 'Test email sent successfully to ' . $testEmail;
    } catch (\Exception $e) {
        return 'Email failed: ' . $e->getMessage();
    }
});

// 🎯 NEW: Debug route to find conflicting controllers
Route::get('/debug-controllers', function() {
    try {
        $results = [];
        
        // Search in different controller directories
        $directories = [
            'app/Http/Controllers/',
            'app/Http/Controllers/Api/',
            'app/Http/Controllers/Api/Auth/',
            'app/Http/Controllers/Auth/',
        ];
        
        foreach ($directories as $dir) {
            if (is_dir(base_path($dir))) {
                $files = glob(base_path($dir . '*.php'));
                
                foreach ($files as $file) {
                    $filename = basename($file);
                    $content = file_get_contents($file);
                    
                    $methods = [];
                    if (strpos($content, 'function forgotPass') !== false) $methods[] = 'forgotPass';
                    if (strpos($content, 'function verifyResetOtp') !== false) $methods[] = 'verifyResetOtp';
                    if (strpos($content, 'function resetPass') !== false) $methods[] = 'resetPass';
                    
                    if (!empty($methods)) {
                        $results[] = [
                            'file' => $dir . $filename,
                            'methods' => $methods,
                            'should_keep' => strpos($filename, 'ForgotPass') !== false
                        ];
                    }
                }
            }
        }
        
        return response()->json([
            'conflicts_found' => count($results),
            'conflicting_files' => $results,
            'recommendation' => 'Only ForgotPassController should have these methods'
        ], 200, [], JSON_PRETTY_PRINT);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});
