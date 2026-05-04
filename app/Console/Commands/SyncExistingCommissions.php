<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Commission;
use App\Models\BvCommission;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class SyncExistingCommissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mlm:sync-commissions {--reset : Move main balance back to commission wallet for testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync existing commissions with the new status and withdrawable_at fields';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting commission synchronization...');

        // 1. Mark all existing Joining/Repurchase commissions as 'withdrawn' 
        // because the old system already added them to the Main Balance.
        $count = Commission::where('status', 'pending')
            ->whereIn('type', ['joining', 'repurchase'])
            ->update([
                'status' => 'withdrawn',
                'withdrawable_at' => DB::raw('created_at')
            ]);

        $this->info("Updated {$count} old commissions to 'withdrawn' status.");

        // 2. Populating withdrawable_at for BV commissions if empty
        $bvCount = BvCommission::whereNull('withdrawable_at')
            ->update([
                'withdrawable_at' => DB::raw('created_at')
            ]);
        
        $this->info("Updated {$bvCount} BV records with timestamps.");

        // 3. Optional: If you want to move some balance for testing
        if ($this->option('reset')) {
            $this->warn('Resetting balances for testing purposes...');
            // This is purely for your testing: it finds users with main balance 
            // and moves it to the commission wallet so you can test the withdrawal.
            $wallets = Wallet::where('main_balance', '>', 0)->get();
            foreach($wallets as $wallet) {
                $amount = $wallet->main_balance;
                $wallet->commission_balance = $amount;
                $wallet->main_balance = 0;
                $wallet->save();
                
                // Set their commissions back to pending so they can withdraw
                Commission::where('user_id', $wallet->user_id)
                    ->update(['status' => 'pending']);

                BvCommission::where('user_id', $wallet->user_id)
                    ->update(['status' => 'pending']);
            }
            $this->info('Balances moved to Commission/BV Wallet for testing.');
        }

        $this->info('Sync complete!');
    }
}
