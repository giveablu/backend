<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('reset_otp', 6)->nullable()->after('remember_token');
            $table->timestamp('reset_otp_expires_at')->nullable()->after('reset_otp');
            $table->string('reset_token')->nullable()->after('reset_otp_expires_at');
            $table->timestamp('reset_token_expires_at')->nullable()->after('reset_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['reset_otp', 'reset_otp_expires_at', 'reset_token', 'reset_token_expires_at']);
        });
    }
};
