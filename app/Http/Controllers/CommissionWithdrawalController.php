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

        // Calculate currently withdrawable commission
        $withdrawableCommissions = Commission::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('type', '!=', 'bv')
            ->where('withdrawable_at', '<=', now())
            ->get();

        $withdrawableAmount = $withdrawableCommissions->sum('amount');

        return view('commissions.withdrawal', [
            'page' => 'commission_withdrawal',
            'wallet' => $wallet,
            'settings' => $settings,
            'withdrawableAmount' => $withdrawableAmount,
            'pendingCommissions' => Commission::where('user_id', $user->id)
                ->where('status', 'pending')
                ->where('type', '!=', 'bv')
                ->where('withdrawable_at', '>', now())
                ->sum('amount')
        ]);
    }

    public function withdrawCommission(Request $request)
    {
        $user = Auth::user();
        $settings = $this->mlmService->getSettings();
        $minWithdrawal = (float) ($settings['min_commission_withdrawal'] ?? 500);

        $withdrawableCommissions = Commission::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('type', '!=', 'bv')
            ->where('withdrawable_at', '<=', now())
            ->get();

        $totalAmount = $withdrawableCommissions->sum('amount');

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

            // Record transaction for commission wallet (debit)
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'source' => 'commission_withdrawal',
                'amount' => $totalAmount,
                'fee' => $totalFee,
                'description' => "Withdrawal of commissions to main balance. Gross: ₹" . number_format($totalAmount, 2) . ", TDS: ₹" . number_format($tdsAmount, 2) . ", Service Charge: ₹" . number_format($serviceCharge, 2),
            ]);

            // Record transaction for main wallet (credit)
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'credit',
                'source' => 'commission_withdrawal',
                'amount' => $netAmount,
                'description' => "Commission payout (Net after deductions)",
            ]);

            // Update commission records
            foreach ($withdrawableCommissions as $comm) {
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

        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();
        $bvBalance = $wallet->earning_balance;

        if ($bvBalance < $minBv) {
            return back()->with('error', "Minimum BV for conversion is " . number_format($minBv, 2));
        }

        try {
            DB::beginTransaction();

            $mainAmount = $bvBalance * $rate;

            // Debit from BV (earning_balance)
            $wallet->decrement('earning_balance', $bvBalance);
            // Credit to main balance
            $wallet->increment('main_balance', $mainAmount);

            // Record transaction for BV wallet (debit)
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'source' => 'bv_withdrawal',
                'amount' => $bvBalance,
                'description' => "Converted " . number_format($bvBalance, 2) . " BV points to main balance at rate " . $rate,
            ]);

            // Record transaction for main wallet (credit)
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'credit',
                'source' => 'bv_withdrawal',
                'amount' => $mainAmount,
                'description' => "Received from BV conversion",
            ]);

            DB::commit();
            return redirect()->route('commissions.withdrawal')->with('success', 'BV points converted to main balance successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error processing BV conversion: ' . $e->getMessage());
        }
    }
}
