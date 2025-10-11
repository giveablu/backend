<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impact_examples', function (Blueprint $table) {
            $table->id();
            $table->string('country_iso', 2)->index();
            $table->string('category')->default('general');
            $table->decimal('min_usd', 8, 2);
            $table->decimal('max_usd', 8, 2);
            $table->string('icon')->nullable();
            $table->string('headline');
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impact_examples');
    }
};
