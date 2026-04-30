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
        // 1. Add commission_balance to wallets
        Schema::table('wallets', function (Blueprint $table) {
            $table->decimal('commission_balance', 15, 2)->default(0)->after('main_balance');
        });

        // 2. Add status and withdrawable_at to commissions
        Schema::table('commissions', function (Blueprint $table) {
            $table->enum('status', ['pending', 'withdrawable', 'withdrawn', 'reversed'])->default('pending')->after('amount');
            $table->timestamp('withdrawable_at')->nullable()->after('status');
        });

        // 3. Add fee to wallet_transactions
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->decimal('fee', 15, 2)->default(0)->after('amount');
        });

        // 4. Update wallet_transactions source enum
        DB::statement("ALTER TABLE wallet_transactions MODIFY COLUMN source ENUM('joining', 'repurchase', 'emi', 'penalty', 'manual', 'purchase', 'commission', 'transfer', 'bv', 'commission_withdrawal', 'bv_withdrawal') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn('commission_balance');
        });

        Schema::table('commissions', function (Blueprint $table) {
            $table->dropColumn(['status', 'withdrawable_at']);
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropColumn('fee');
        });

        DB::statement("ALTER TABLE wallet_transactions MODIFY COLUMN source ENUM('joining', 'repurchase', 'emi', 'penalty', 'manual', 'purchase', 'commission', 'transfer', 'bv') NOT NULL");
    }
};
