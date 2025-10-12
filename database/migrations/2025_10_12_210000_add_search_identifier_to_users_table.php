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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'search_id')) {
                $table->string('search_id', 32)
                    ->nullable()
                    ->unique()
                    ->after('phone');
            }
        });

        $users = DB::table('users')
            ->select('id', 'search_id')
            ->orderBy('id')
            ->get();

        foreach ($users as $user) {
            if ($user->search_id) {
                continue;
            }

            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'search_id' => $this->formatSearchId((int) $user->id),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'search_id')) {
                $table->dropColumn('search_id');
            }
        });
    }

    private function formatSearchId(int $id): string
    {
        return sprintf('BLU-%06d', $id);
    }
};
