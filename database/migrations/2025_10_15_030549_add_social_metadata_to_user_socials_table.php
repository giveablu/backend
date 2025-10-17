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
        if (Schema::hasColumn('user_socials', 'social_id')) {
            Schema::table('user_socials', function (Blueprint $table) {
                $table->renameColumn('social_id', 'provider_user_id');
            });
        }

        if (Schema::hasColumn('user_socials', 'service')) {
            Schema::table('user_socials', function (Blueprint $table) {
                $table->renameColumn('service', 'provider');
            });
        }

        Schema::table('user_socials', function (Blueprint $table) {
            $table->string('username')->nullable()->after('provider_user_id');
            $table->string('profile_url')->nullable()->after('username');
            $table->string('avatar_url')->nullable()->after('profile_url');
            $table->timestamp('account_created_at')->nullable()->after('avatar_url');
            $table->unsignedBigInteger('followers_count')->nullable()->after('account_created_at');
            $table->json('raw_payload')->nullable()->after('followers_count');
            $table->timestamp('last_synced_at')->nullable()->after('raw_payload');
            $table->boolean('is_primary')->default(false)->after('last_synced_at');
            $table->unique(['provider', 'provider_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('user_socials', 'provider') && Schema::hasColumn('user_socials', 'provider_user_id')) {
            Schema::table('user_socials', function (Blueprint $table) {
                $table->dropUnique('user_socials_provider_provider_user_id_unique');
            });
        }

        Schema::table('user_socials', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'profile_url',
                'avatar_url',
                'account_created_at',
                'followers_count',
                'raw_payload',
                'last_synced_at',
                'is_primary',
            ]);
        });

        if (Schema::hasColumn('user_socials', 'provider_user_id')) {
            Schema::table('user_socials', function (Blueprint $table) {
                $table->renameColumn('provider_user_id', 'social_id');
            });
        }

        if (Schema::hasColumn('user_socials', 'provider')) {
            Schema::table('user_socials', function (Blueprint $table) {
                $table->renameColumn('provider', 'service');
            });
        }
    }
};
