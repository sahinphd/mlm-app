<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QrCodeController extends Controller
{
    protected LedgerService $ledgerService;

    public function __construct(LedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    /**
     * Display the authenticated customer's QR code page for display, download, and printing.
     */
    public function myQr(Request $request)
    {
        $user = Auth::user();
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id], [
            'main_balance' => 0,
            'earning_balance' => 0,
            'credit_balance' => 0,
        ]);

        $customerData = $this->ledgerService->formatCustomerQrData($user);

        return view('wallet.my_qr', [
            'page' => 'my_qr',
            'user' => $user,
            'wallet' => $wallet,
            'customer' => $customerData,
            'qrPayload' => $this->ledgerService->getCustomerQrPayload($user),
        ]);
    }

    /**
     * Display the Scan QR & Pay interface for users with camera viewfinder & manual fallback.
     */
    public function scanPay(Request $request)
    {
        $user = Auth::user();
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id], [
            'main_balance' => 0,
            'earning_balance' => 0,
            'credit_balance' => 0,
        ]);

        $prefillCode = trim((string) $request->query('code', $request->query('recipient_id', '')));
        $initialRecipient = null;

        if (!empty($prefillCode)) {
            $recipientId = $this->ledgerService->parseQrPayload($prefillCode);
            if ($recipientId && $recipientId !== $user->id) {
                $recipientUser = User::find($recipientId);
                if ($recipientUser && ($recipientUser->status ?? 'active') === 'active') {
                    $initialRecipient = $this->ledgerService->formatCustomerQrData($recipientUser);
                }
            }
        }

        return view('wallet.scan_pay', [
            'page' => 'scan_pay',
            'user' => $user,
            'wallet' => $wallet,
            'initialRecipient' => $initialRecipient,
            'prefillCode' => $prefillCode,
        ]);
    }

    /**
     * Resolve scanned QR code or manual input to customer details.
     */
    public function resolveRecipient(Request $request)
    {
        $query = trim((string) $request->input('code', $request->input('query', $request->input('user_id', ''))));

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a valid Customer QR code or Customer ID to scan.',
            ], 422);
        }

        $userId = $this->ledgerService->parseQrPayload($query);

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found. Please verify the QR code or Customer ID.',
            ], 404);
        }

        $currentUser = Auth::user();
        if ($userId === $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Self-transfer disallowed. You scanned your own QR code.',
            ], 422);
        }

        $recipient = User::with(['wallet', 'creditAccount', 'referralRecord'])->find($userId);

        if (!$recipient) {
            return response()->json([
                'success' => false,
                'message' => 'Customer account with ID #' . $userId . ' was not found.',
            ], 404);
        }

        if (($recipient->status ?? 'active') !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This customer account is currently inactive or suspended.',
            ], 422);
        }

        $wallet = Wallet::firstOrCreate(['user_id' => $currentUser->id]);
        $recipientData = $this->ledgerService->formatCustomerQrData($recipient);

        return response()->json([
            'success' => true,
            'recipient' => $recipientData,
            'sender_balance' => (float) ($wallet->main_balance ?? 0),
            'sender_balance_formatted' => '₹ ' . number_format($wallet->main_balance ?? 0, 2),
        ]);
    }

    /**
     * Execute atomic wallet balance transfer with real-time ledger updates.
     */
    public function transfer(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|integer|exists:users,id',
            'amount' => 'required|numeric|min:1|max:10000000',
            'remarks' => 'nullable|string|max:255',
        ]);

        $sender = Auth::user();
        $recipient = User::findOrFail($validated['recipient_id']);

        if ($sender->id === $recipient->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot transfer funds to your own account.',
            ], 422);
        }

        $amount = (float) $validated['amount'];
        $remarks = $validated['remarks'] ?? null;

        try {
            $result = $this->ledgerService->recordQrTransfer($sender, $recipient, $amount, $remarks);

            return response()->json([
                'success' => true,
                'message' => 'Payment of ' . $result['amount_formatted'] . ' to ' . $recipient->name . ' completed successfully.',
                'receipt' => $result,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * View customer QR modal / card (for Admin).
     */
    public function customerQr(Request $request, User $user)
    {
        if (!Auth::user()->isAdmin() && Auth::id() !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $customerData = $this->ledgerService->formatCustomerQrData($user);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'customer' => $customerData,
            ]);
        }

        return view('wallet.my_qr', [
            'page' => 'customer_qr',
            'user' => $user,
            'wallet' => $user->wallet,
            'customer' => $customerData,
            'qrPayload' => $this->ledgerService->getCustomerQrPayload($user),
            'isAdminView' => true,
        ]);
    }
}
