<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        if (Schema::hasColumn('user_socials', 'provider')) {
            Schema::table('user_socials', function (Blueprint $table) {
                $table->string('provider', 50)->change();
            });
        }

        if (Schema::hasColumn('user_socials', 'provider_user_id')) {
            Schema::table('user_socials', function (Blueprint $table) {
                $table->string('provider_user_id', 191)->change();
            });
        }

        if (! Schema::hasColumn('user_socials', 'username')) {
            Schema::table('user_socials', function (Blueprint $table) {
                $table->string('username', 191)->nullable()->after('provider_user_id');
            });
        }

        if (! Schema::hasColumn('user_socials', 'profile_url')) {
            Schema::table('user_socials', function (Blueprint $table) {
                $table->string('profile_url', 191)->nullable()->after('username');
            });
        }

        if (! Schema::hasColumn('user_socials', 'avatar_url')) {
            Schema::table('user_socials', function (Blueprint $table) {
                $table->string('avatar_url', 191)->nullable()->after('profile_url');
            });
        }

        if (! Schema::hasColumn('user_socials', 'account_created_at')) {
            Schema::table('user_socials', function (Blueprint $table) {
                $table->timestamp('account_created_at')->nullable()->after('avatar_url');
            });
        }

        if (! Schema::hasColumn('user_socials', 'followers_count')) {
            Schema::table('user_socials', function (Blueprint $table) {
                $table->unsignedBigInteger('followers_count')->nullable()->after('account_created_at');
            });
        }

        if (! Schema::hasColumn('user_socials', 'raw_payload')) {
            Schema::table('user_socials', function (Blueprint $table) {
                $table->json('raw_payload')->nullable()->after('followers_count');
            });
        }

        if (! Schema::hasColumn('user_socials', 'last_synced_at')) {
            Schema::table('user_socials', function (Blueprint $table) {
                $table->timestamp('last_synced_at')->nullable()->after('raw_payload');
            });
        }

        if (! Schema::hasColumn('user_socials', 'is_primary')) {
            Schema::table('user_socials', function (Blueprint $table) {
                $table->boolean('is_primary')->default(false)->after('last_synced_at');
            });
        }

        if (! $this->indexExists('user_socials', 'user_socials_provider_provider_user_id_unique')) {
            Schema::table('user_socials', function (Blueprint $table) {
                $table->unique(['provider', 'provider_user_id']);
            });
        }
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

    /**
     * Determine if a given index exists on the table.
     */
    private function indexExists(string $table, string $index): bool
    {
        $result = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$index]);

        return ! empty($result);
    }
};
