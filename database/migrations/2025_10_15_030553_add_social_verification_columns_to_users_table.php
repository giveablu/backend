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
            $table->string('social_verification_status')->default('pending')->after('phone_verified_at');
            $table->timestamp('social_verified_at')->nullable()->after('social_verification_status');
            $table->text('social_verification_notes')->nullable()->after('social_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'social_verification_status',
                'social_verified_at',
                'social_verification_notes',
            ]);
        });
    }
};
