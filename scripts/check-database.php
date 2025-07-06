<?php

/**
 * Database Status Check Script
 * Run this to verify database status and user data
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "🔍 Database Status Check\n";
echo "========================\n\n";

try {
    // 1. Check if users table exists
    echo "1. Checking users table...\n";
    $tableExists = Schema::hasTable('users');
    echo "   Users table exists: " . ($tableExists ? "✅ YES" : "❌ NO") . "\n";
    
    if (!$tableExists) {
        echo "   ❌ Users table missing! This is a critical error.\n";
        exit(1);
    }
    
    // 2. Check required columns
    echo "\n2. Checking required columns...\n";
    $requiredColumns = [
        'id', 'name', 'email', 'phone', 'role', 'password',
        'search_id', 'joined_date', 'email_verified_at',
        'reset_otp', 'reset_otp_expires_at', 'reset_token', 'reset_token_expires_at'
    ];
    
    $missingColumns = [];
    foreach ($requiredColumns as $column) {
        $exists = Schema::hasColumn('users', $column);
        echo "   $column: " . ($exists ? "✅" : "❌") . "\n";
        if (!$exists) {
            $missingColumns[] = $column;
        }
    }
    
    if (!empty($missingColumns)) {
        echo "   ❌ Missing columns: " . implode(', ', $missingColumns) . "\n";
    } else {
        echo "   ✅ All required columns exist\n";
    }
    
    // 3. Check user count
    echo "\n3. Checking user data...\n";
    $userCount = DB::table('users')->count();
    echo "   Total users in database: $userCount\n";
    
    // 4. Show recent users
    echo "\n4. Recent users (last 5):\n";
    $recentUsers = DB::table('users')
        ->select('id', 'name', 'email', 'role', 'email_verified_at', 'created_at')
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
    
    if ($recentUsers->isEmpty()) {
        echo "   ❌ No users found in database\n";
    } else {
        foreach ($recentUsers as $user) {
            $verified = $user->email_verified_at ? "✅" : "❌";
            echo "   ID: {$user->id} | Name: {$user->name} | Email: {$user->email} | Role: {$user->role} | Verified: $verified | Created: {$user->created_at}\n";
        }
    }
    
    // 5. Check for users with missing required fields
    echo "\n5. Checking for users with missing fields...\n";
    $usersWithoutSearchId = DB::table('users')->whereNull('search_id')->count();
    $usersWithoutJoinedDate = DB::table('users')->whereNull('joined_date')->count();
    $usersWithoutEmailVerified = DB::table('users')->whereNull('email_verified_at')->count();
    
    echo "   Users without search_id: $usersWithoutSearchId\n";
    echo "   Users without joined_date: $usersWithoutJoinedDate\n";
    echo "   Users without email_verified_at: $usersWithoutEmailVerified\n";
    
    // 6. Check migration status
    echo "\n6. Checking migration status...\n";
    $migrations = DB::table('migrations')->get();
    echo "   Total migrations run: " . $migrations->count() . "\n";
    
    // Show recent migrations
    $recentMigrations = $migrations->sortByDesc('id')->take(5);
    echo "   Recent migrations:\n";
    foreach ($recentMigrations as $migration) {
        echo "   - {$migration->migration} (Batch: {$migration->batch})\n";
    }
    
    // 7. Check for duplicate migrations
    echo "\n7. Checking for duplicate migrations...\n";
    $duplicateMigrations = DB::table('migrations')
        ->select('migration', DB::raw('count(*) as count'))
        ->groupBy('migration')
        ->having('count', '>', 1)
        ->get();
    
    if ($duplicateMigrations->isEmpty()) {
        echo "   ✅ No duplicate migrations found\n";
    } else {
        echo "   ❌ Duplicate migrations found:\n";
        foreach ($duplicateMigrations as $duplicate) {
            echo "   - {$duplicate->migration} (Count: {$duplicate->count})\n";
        }
    }
    
    echo "\n🎉 Database check completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 