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
        $columns = Schema::getColumnListing('users');

        Schema::table('users', function (Blueprint $table) use ($columns) {
            if (!in_array('reset_otp', $columns, true)) {
                $table->string('reset_otp', 6)->nullable()->after('remember_token');
            }

            if (!in_array('reset_otp_expires_at', $columns, true)) {
                $table->timestamp('reset_otp_expires_at')->nullable()->after('reset_otp');
            }

            if (!in_array('reset_token', $columns, true)) {
                $table->string('reset_token')->nullable()->after('reset_otp_expires_at');
            }

            if (!in_array('reset_token_expires_at', $columns, true)) {
                $table->timestamp('reset_token_expires_at')->nullable()->after('reset_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = Schema::getColumnListing('users');

        Schema::table('users', function (Blueprint $table) use ($columns) {
            $toDrop = array_intersect(
                ['reset_otp', 'reset_otp_expires_at', 'reset_token', 'reset_token_expires_at'],
                $columns
            );

            if (!empty($toDrop)) {
                $table->dropColumn($toDrop);
            }
        });
    }
};
