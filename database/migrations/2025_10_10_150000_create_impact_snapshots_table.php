<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impact_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('country_iso', 2);
            $table->string('category', 50)->default('general');
            $table->decimal('min_usd', 8, 2);
            $table->decimal('max_usd', 8, 2);
            $table->string('headline');
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable();
            $table->string('local_currency', 3)->nullable();
            $table->decimal('local_amount', 12, 2)->nullable();
            $table->string('source')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('observed_at')->nullable();
            $table->timestamps();

            $table->index(['country_iso', 'category']);
            $table->index('observed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impact_snapshots');
    }
};
