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
        Schema::create('mail_otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('old_mail')->nullable();
            $table->string('old_code')->nullable();
            $table->string('new_mail');
            $table->string('new_code');
            $table->string('expire_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_otps');
    }
};
