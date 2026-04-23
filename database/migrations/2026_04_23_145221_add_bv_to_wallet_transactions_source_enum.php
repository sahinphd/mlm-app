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
        DB::statement("ALTER TABLE wallet_transactions MODIFY COLUMN source ENUM('joining', 'repurchase', 'emi', 'penalty', 'manual', 'purchase', 'commission', 'transfer', 'bv') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE wallet_transactions MODIFY COLUMN source ENUM('joining', 'repurchase', 'emi', 'penalty', 'manual', 'purchase', 'commission', 'transfer') NOT NULL");
    }
};
