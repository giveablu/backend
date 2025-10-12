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
        if (! Schema::hasColumn('users', 'status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('status', 20)->default('active')->after('role');
            });
        }

        $connection = Schema::getConnection()->getDriverName();

        if ($connection !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active'");
        }

        DB::statement(<<<'SQL'
            UPDATE users
            SET status = CASE
                WHEN status IN ('0', 'inactive', 'suspended') THEN 'suspended'
                ELSE 'active'
            END
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = Schema::getConnection()->getDriverName();

        // Revert to a simple active/inactive flag using tinyint for backwards compatibility where supported.
        DB::statement(<<<'SQL'
            UPDATE users
            SET status = CASE
                WHEN status = 'suspended' THEN 0
                ELSE 1
            END
        SQL);

        if ($connection !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY COLUMN status TINYINT(1) NOT NULL DEFAULT 1");
        }
    }
};
