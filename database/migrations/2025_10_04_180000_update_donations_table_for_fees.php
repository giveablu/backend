<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->decimal('gross_amount', 12, 2)->default(0)->after('user_id');
            $table->decimal('processing_fee', 12, 2)->default(0)->after('gross_amount');
            $table->decimal('platform_fee', 12, 2)->default(0)->after('processing_fee');
            $table->decimal('net_amount', 12, 2)->default(0)->after('platform_fee');
            $table->string('currency', 3)->default('USD')->after('net_amount');
            $table->json('processor_payload')->nullable()->after('currency');
        });

        DB::table('donations')->select('id', 'paid_amount')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $gross = is_numeric($row->paid_amount) ? (float) $row->paid_amount : (float) preg_replace('/[^0-9.]/', '', (string) $row->paid_amount);
                DB::table('donations')->where('id', $row->id)->update([
                    'gross_amount' => $gross,
                    'net_amount' => $gross,
                    'processing_fee' => 0,
                    'platform_fee' => 0,
                ]);
            }
        });

        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn('paid_amount');
        });
    }

    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->string('paid_amount')->nullable()->after('user_id');
        });

        DB::table('donations')->select('id', 'gross_amount')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                DB::table('donations')->where('id', $row->id)->update([
                    'paid_amount' => (string) $row->gross_amount,
                ]);
            }
        });

        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn([
                'gross_amount',
                'processing_fee',
                'platform_fee',
                'net_amount',
                'currency',
                'processor_payload',
            ]);
        });
    }
};
