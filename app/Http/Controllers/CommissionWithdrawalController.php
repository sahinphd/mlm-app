<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\MLMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommissionWithdrawalController extends Controller
{
    protected $mlmService;

    public function __construct(MLMService $mlmService)
    {
        $this->mlmService = $mlmService;
    }

    public function index()
    {
        $user = Auth::user();
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);
        $settings = $this->mlmService->getSettings();
        $bvRate = (float)($settings['bv_conversion_rate'] ?? 1.0);
        $lockDays = (int) ($settings['commission_lock_period_days'] ?? 30);
        $now = now();
        $thresholdDate = $now->copy()->subDays($lockDays);

        // Cash Components
        $cashWithdrawn = Commission::where('user_id', $user->id)
            ->where('status', 'withdrawn')
            ->where('type', '!=', 'bv')
            ->sum('amount');

        $cashWithdrawable = Commission::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('type', '!=', 'bv')
            ->where('created_at', '<=', $thresholdDate)
            ->sum('amount');

        $cashLocked = Commission::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('type', '!=', 'bv')
            ->where('created_at', '>', $thresholdDate)
            ->sum('amount');

        // BV Components
        $withdrawableBvPoints = Commission::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('type', 'bv')
            ->where('created_at', '<=', $thresholdDate)
            ->sum('amount');

        $lockedBvPoints = Commission::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('type', 'bv')
            ->where('created_at', '>', $thresholdDate)
            ->sum('amount');

        $bvCashWithdrawn = WalletTransaction::whereHas('wallet', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('source', 'bv_withdrawal')
          ->where('type', 'credit')
          ->sum('amount');

        // Aggregated Metrics (Unified Financial Value)
        $totalWithdrawn = $cashWithdrawn + $bvCashWithdrawn;
        $totalWithdrawable = $cashWithdrawable + ($withdrawableBvPoints * $bvRate);
        $totalLocked = $cashLocked + ($lockedBvPoints * $bvRate);
        $totalEarned = $totalWithdrawn + $totalWithdrawable + $totalLocked;

        // Total TDS & Service Charge (From Transactions)
        $totalTDS = WalletTransaction::whereHas('wallet', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('source', 'commission_withdrawal')
          ->where('type', 'debit')
          ->where('description', 'LIKE', '%TDS%')
          ->sum('amount');

        $totalServiceCharge = WalletTransaction::whereHas('wallet', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('source', 'commission_withdrawal')
          ->where('type', 'debit')
          ->where('description', 'LIKE', '%Service Charge%')
          ->sum('amount');

        $nextReleaseRecord = Commission::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('created_at', '>', $thresholdDate)
            ->orderBy('created_at', 'asc')
            ->first();
        
        $nextRelease = $nextReleaseRecord ? $nextReleaseRecord->created_at->addDays($lockDays) : null;

        return view('commissions.withdrawal', [
            'page' => 'commission_withdrawal',
            'wallet' => $wallet,
            'settings' => $settings,
            'cashWithdrawable' => $cashWithdrawable, 
            'withdrawableBvPoints' => $withdrawableBvPoints,
            'totalEarned' => $totalEarned,
            'totalWithdrawn' => $totalWithdrawn,
            'totalTDS' => $totalTDS,
            'totalServiceCharge' => $totalServiceCharge,
            'totalWithdrawable' => $totalWithdrawable,
            'totalLocked' => $totalLocked,
            'lockedBvPoints' => $lockedBvPoints,
            'nextRelease' => $nextRelease,
        ]);
    }

    public function withdrawCommission(Request $request)
    {
        $user = Auth::user();
        $settings = $this->mlmService->getSettings();
        $minWithdrawal = (float) ($settings['min_commission_withdrawal'] ?? 500);
        $lockDays = (int) ($settings['commission_lock_period_days'] ?? 30);
        $thresholdDate = now()->subDays($lockDays);

        // Calculate currently withdrawable cash
        $cashWithdrawable = Commission::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('type', '!=', 'bv')
            ->where('created_at', '<=', $thresholdDate)
            ->get();

        $totalAmount = $cashWithdrawable->sum('amount');

        if ($totalAmount < $minWithdrawal) {
            return back()->with('error', "Minimum withdrawal amount is ₹" . number_format($minWithdrawal, 2));
        }

        try {
            DB::beginTransaction();

            $tdsPercent = (float) ($settings['commission_withdrawal_tds_percent'] ?? 5);
            $servicePercent = (float) ($settings['commission_withdrawal_service_charge'] ?? 5);

            $tdsAmount = ($totalAmount * $tdsPercent) / 100;
            $serviceCharge = ($totalAmount * $servicePercent) / 100;
            $totalFee = $tdsAmount + $serviceCharge;
            $netAmount = $totalAmount - $totalFee;

            $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

            // Debit from commission balance
            $wallet->decrement('commission_balance', $totalAmount);
            // Credit net to main balance
            $wallet->increment('main_balance', $netAmount);

            // Record transaction for commission wallet (debit net payout)
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'source' => 'commission_withdrawal',
                'amount' => $netAmount,
                'description' => "Commission Payout Transfer (Net Amount)",
            ]);

            // Record TDS deduction
            if ($tdsAmount > 0) {
                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => 'debit',
                    'source' => 'commission_withdrawal',
                    'amount' => $tdsAmount,
                    'fee' => 0,
                    'description' => "TDS Deduction (" . $tdsPercent . "%)",
                ]);
            }

            // Record Service Charge deduction
            if ($serviceCharge > 0) {
                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => 'debit',
                    'source' => 'commission_withdrawal',
                    'amount' => $serviceCharge,
                    'description' => "Service Charge Deduction (" . $servicePercent . "%)",
                ]);
            }

            // Record transaction for main wallet (credit net)
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'credit',
                'source' => 'commission_withdrawal',
                'amount' => $netAmount,
                'description' => "Received Commission Payout",
            ]);

            // Update commission records
            foreach ($cashWithdrawable as $comm) {
                $comm->update(['status' => 'withdrawn']);
            }

            DB::commit();
            return redirect()->route('commissions.withdrawal')->with('success', 'Commission withdrawn to main balance successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error processing withdrawal: ' . $e->getMessage());
        }
    }

    public function convertBv(Request $request)
    {
        $user = Auth::user();
        $settings = $this->mlmService->getSettings();
        $minBv = (float) ($settings['min_bv_withdrawal'] ?? 100);
        $rate = (float) ($settings['bv_conversion_rate'] ?? 1.0);
        $lockDays = (int) ($settings['commission_lock_period_days'] ?? 30);
        $thresholdDate = now()->subDays($lockDays);

        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();
        
        // Only convert withdrawable BV points
        $withdrawableBvCommissions = Commission::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('type', 'bv')
            ->where('created_at', '<=', $thresholdDate)
            ->get();

        $withdrawableBvPoints = $withdrawableBvCommissions->sum('amount');

        if ($withdrawableBvPoints < $minBv) {
            return back()->with('error', "Minimum withdrawable BV for conversion is " . number_format($minBv, 2));
        }

        try {
            DB::beginTransaction();

            $mainAmount = $withdrawableBvPoints * $rate;

            // Debit from BV (earning_balance)
            $wallet->decrement('earning_balance', $withdrawableBvPoints);
            // Credit to main balance
            $wallet->increment('main_balance', $mainAmount);

            // Record transaction for BV wallet (debit) - Use 'bv' source to keep it in BV ledger/history
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'source' => 'bv',
                'amount' => $withdrawableBvPoints,
                'description' => "Converted " . number_format($withdrawableBvPoints, 2) . " withdrawable BV points to main balance at rate " . $rate,
            ]);

            // Record transaction for main wallet (credit) - Keep 'bv_withdrawal' source for wallet history
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'credit',
                'source' => 'bv_withdrawal',
                'amount' => $mainAmount,
                'description' => "Received from BV conversion",
            ]);

            // Update commission records to withdrawn
            foreach ($withdrawableBvCommissions as $comm) {
                $comm->update(['status' => 'withdrawn']);
            }

            DB::commit();
            return redirect()->route('commissions.withdrawal')->with('success', 'BV points converted to main balance successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error processing BV conversion: ' . $e->getMessage());
        }
    }
}
