<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'profile_description')) {
                $table->text('profile_description')->nullable()->after('gender');
            }

            if (! Schema::hasColumn('users', 'city')) {
                $table->string('city', 150)->nullable()->after('profile_description');
            }

            if (! Schema::hasColumn('users', 'region')) {
                $table->string('region', 150)->nullable()->after('city');
            }

            if (! Schema::hasColumn('users', 'country')) {
                $table->string('country', 150)->nullable()->after('region');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'profile_description')) {
                $table->dropColumn('profile_description');
            }

            if (Schema::hasColumn('users', 'city')) {
                $table->dropColumn('city');
            }

            if (Schema::hasColumn('users', 'region')) {
                $table->dropColumn('region');
            }

            if (Schema::hasColumn('users', 'country')) {
                $table->dropColumn('country');
            }
        });
    }
};
