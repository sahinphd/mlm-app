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
        // Find all users and their commissions to move from earning_balance to main_balance
        // joining and repurchase commissions are currently in earning_balance but should be in main_balance
        
        $wallets = DB::table('wallets')->get();

        foreach ($wallets as $wallet) {
            // 1. Calculate credits to move
            $credits = DB::table('wallet_transactions')
                ->join('commissions', function($join) {
                    $join->on('wallet_transactions.reference_id', '=', DB::raw("CONCAT('commission:', commissions.id)"));
                })
                ->where('wallet_transactions.wallet_id', $wallet->id)
                ->where('wallet_transactions.type', 'credit')
                ->whereIn('commissions.type', ['joining', 'repurchase'])
                ->sum('wallet_transactions.amount');

            // 2. Calculate debits (reversals) to move
            $debits = DB::table('wallet_transactions')
                ->join('commissions', function($join) {
                    $join->on('wallet_transactions.reference_id', '=', DB::raw("CONCAT('reversal:', commissions.id)"));
                })
                ->where('wallet_transactions.wallet_id', $wallet->id)
                ->where('wallet_transactions.type', 'debit')
                ->whereIn('commissions.type', ['joining', 'repurchase'])
                ->sum('wallet_transactions.amount');

            $totalToMove = $credits - $debits;

            if ($totalToMove > 0) {
                DB::table('wallets')
                    ->where('id', $wallet->id)
                    ->update([
                        'main_balance' => DB::raw('main_balance + ' . $totalToMove),
                        'earning_balance' => DB::raw('earning_balance - ' . $totalToMove),
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $wallets = DB::table('wallets')->get();

        foreach ($wallets as $wallet) {
            $credits = DB::table('wallet_transactions')
                ->join('commissions', function($join) {
                    $join->on('wallet_transactions.reference_id', '=', DB::raw("CONCAT('commission:', commissions.id)"));
                })
                ->where('wallet_transactions.wallet_id', $wallet->id)
                ->where('wallet_transactions.type', 'credit')
                ->whereIn('commissions.type', ['joining', 'repurchase'])
                ->sum('wallet_transactions.amount');

            $debits = DB::table('wallet_transactions')
                ->join('commissions', function($join) {
                    $join->on('wallet_transactions.reference_id', '=', DB::raw("CONCAT('reversal:', commissions.id)"));
                })
                ->where('wallet_transactions.wallet_id', $wallet->id)
                ->where('wallet_transactions.type', 'debit')
                ->whereIn('commissions.type', ['joining', 'repurchase'])
                ->sum('wallet_transactions.amount');

            $totalToMove = $credits - $debits;

            if ($totalToMove > 0) {
                DB::table('wallets')
                    ->where('id', $wallet->id)
                    ->update([
                        'main_balance' => DB::raw('main_balance - ' . $totalToMove),
                        'earning_balance' => DB::raw('earning_balance + ' . $totalToMove),
                        'updated_at' => now(),
                    ]);
            }
        }
    }
};
