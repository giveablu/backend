<?php

/**
 * Database Fix Script
 * Run this script to fix database issues
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

echo "🔧 Starting database fix script...\n";

try {
    // Check if reset_otp columns exist
    $columns = DB::select("SHOW COLUMNS FROM users LIKE 'reset_otp'");
    
    if (empty($columns)) {
        echo "📝 Adding reset_otp columns to users table...\n";
        
        Schema::table('users', function (Blueprint $table) {
            $table->string('reset_otp', 6)->nullable()->after('remember_token');
            $table->timestamp('reset_otp_expires_at')->nullable()->after('reset_otp');
            $table->string('reset_token')->nullable()->after('reset_otp_expires_at');
            $table->timestamp('reset_token_expires_at')->nullable()->after('reset_token');
        });
        
        echo "✅ Reset OTP columns added successfully\n";
    } else {
        echo "✅ Reset OTP columns already exist\n";
    }
    
    // Check if search_id column exists
    $searchIdColumns = DB::select("SHOW COLUMNS FROM users LIKE 'search_id'");
    
    if (empty($searchIdColumns)) {
        echo "📝 Adding search_id column to users table...\n";
        
        Schema::table('users', function (Blueprint $table) {
            $table->string('search_id')->nullable()->after('role');
        });
        
        echo "✅ Search ID column added successfully\n";
    } else {
        echo "✅ Search ID column already exists\n";
    }
    
    // Check if joined_date column exists
    $joinedDateColumns = DB::select("SHOW COLUMNS FROM users LIKE 'joined_date'");
    
    if (empty($joinedDateColumns)) {
        echo "📝 Adding joined_date column to users table...\n";
        
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('joined_date')->nullable()->after('search_id');
        });
        
        echo "✅ Joined date column added successfully\n";
    } else {
        echo "✅ Joined date column already exists\n";
    }
    
    // Update existing users with missing fields
    echo "📝 Updating existing users with missing fields...\n";
    
    $usersWithoutSearchId = DB::table('users')->whereNull('search_id')->get();
    foreach ($usersWithoutSearchId as $user) {
        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'search_id' => \Illuminate\Support\Str::random(10),
                'joined_date' => $user->created_at ?? now(),
            ]);
    }
    
    echo "✅ Updated " . count($usersWithoutSearchId) . " users with missing fields\n";
    
    echo "🎉 Database fix completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 