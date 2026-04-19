<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // On many databases like MySQL, modifying ENUM directly requires a raw statement.
        // We'll use a more flexible approach if possible, or just raw if it's MySQL.
        DB::statement("ALTER TABLE commissions MODIFY COLUMN type ENUM('joining', 'repurchase', 'bv') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE commissions MODIFY COLUMN type ENUM('joining', 'repurchase') NOT NULL");
    }
};
