<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "🔍 Debugging OTP storage issue...\n\n";

try {
    // Find the user
    $user = User::where('email', 'lifeofsroy@gmail.com')->first();
    
    if (!$user) {
        echo "❌ User not found!\n";
        exit;
    }
    
    echo "✅ User found: {$user->name} (ID: {$user->id})\n\n";
    
    // Test 1: Check if we can update the user directly
    echo "🧪 Test 1: Direct User model update\n";
    $testOtp = '999888';
    $testExpires = Carbon::now()->addMinutes(15);
    
    $user->reset_otp = $testOtp;
    $user->reset_otp_expires_at = $testExpires;
    $saved = $user->save();
    
    echo "Save result: " . ($saved ? "SUCCESS" : "FAILED") . "\n";
    
    // Check if it was actually saved
    $user->refresh();
    echo "Stored OTP: " . ($user->reset_otp ?? 'NULL') . "\n";
    echo "Stored Expires: " . ($user->reset_otp_expires_at ?? 'NULL') . "\n\n";
    
    // Test 2: Check fillable fields
    echo "🧪 Test 2: Check User model fillable fields\n";
    $fillable = $user->getFillable();
    echo "Fillable fields: " . implode(', ', $fillable) . "\n";
    echo "reset_otp fillable: " . (in_array('reset_otp', $fillable) ? 'YES' : 'NO') . "\n";
    echo "reset_otp_expires_at fillable: " . (in_array('reset_otp_expires_at', $fillable) ? 'YES' : 'NO') . "\n\n";
    
    // Test 3: Try DB::table update
    echo "🧪 Test 3: Direct DB table update\n";
    $testOtp2 = '777666';
    $updated = DB::table('users')
        ->where('id', $user->id)
        ->update([
            'reset_otp' => $testOtp2,
            'reset_otp_expires_at' => Carbon::now()->addMinutes(15),
            'updated_at' => Carbon::now(),
        ]);
    
    echo "DB update result: $updated rows affected\n";
    
    // Check what was actually stored
    $verification = DB::table('users')
        ->where('id', $user->id)
        ->select('reset_otp', 'reset_otp_expires_at')
        ->first();
    
    echo "DB stored OTP: " . ($verification->reset_otp ?? 'NULL') . "\n";
    echo "DB stored expires: " . ($verification->reset_otp_expires_at ?? 'NULL') . "\n\n";
    
    // Test 4: Check for any database constraints or triggers
    echo "🧪 Test 4: Check table structure\n";
    $columns = DB::select("DESCRIBE users");
    foreach ($columns as $column) {
        if (in_array($column->Field, ['reset_otp', 'reset_otp_expires_at', 'reset_token', 'reset_token_expires_at'])) {
            echo "Column: {$column->Field} | Type: {$column->Type} | Null: {$column->Null} | Default: {$column->Default}\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
