<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('social_accounts')) {
            return;
        }

        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('provider'); // facebook, instagram, twitter, etc.
            $table->string('provider_id');
            $table->string('username')->nullable();
            $table->string('profile_url')->nullable();
            $table->string('profile_photo')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->integer('follower_count')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->json('additional_data')->nullable(); // Store extra platform-specific data
            $table->timestamps();

            $table->unique(['user_id', 'provider']);
            $table->index(['provider', 'provider_id']);
        });

        // Add the foreign key in a second step to avoid failures during creation if the
        // referenced table is stored with an engine that does not currently support FKs.
        Schema::table('social_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('social_accounts', 'user_id')) {
                return;
            }

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
