<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletLedger;
use App\Models\WalletTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LedgerService
{
    /**
     * Parse raw QR code payload and resolve matching customer User ID.
     * Supports formats:
     * - CUST:15 or CUST:10015
     * - CUST-10015 (Khatabook format)
     * - Plain numeric user ID: 15
     * - Referral code, phone, or email fallback
     */
    public function parseQrPayload(string $raw): ?int
    {
        $payload = trim($raw);
        if (empty($payload)) {
            return null;
        }

        // 1. Format: "CUST:<id>"
        if (preg_match('/^CUST:([0-9]+)$/i', $payload, $matches)) {
            $num = (int) $matches[1];
            // Check direct user ID
            if (User::where('id', $num)->exists()) {
                return $num;
            }
            // Check if offset Khatabook ID (10000 + ID)
            if ($num > 10000 && User::where('id', $num - 10000)->exists()) {
                return $num - 10000;
            }
        }

        // 2. Format: "CUST-<id>"
        if (preg_match('/^CUST-([0-9]+)$/i', $payload, $matches)) {
            $num = (int) $matches[1];
            if ($num > 10000 && User::where('id', $num - 10000)->exists()) {
                return $num - 10000;
            }
            if (User::where('id', $num)->exists()) {
                return $num;
            }
        }

        // 3. Plain numeric ID
        if (ctype_digit($payload) || (is_numeric($payload) && (int)$payload > 0)) {
            $num = (int) $payload;
            if ($num > 10000 && User::where('id', $num - 10000)->exists()) {
                return $num - 10000;
            }
            if (User::where('id', $num)->exists()) {
                return $num;
            }
        }

        // 4. Try parsing JSON if encoded as json object
        if (str_starts_with($payload, '{') && str_ends_with($payload, '}')) {
            $json = json_decode($payload, true);
            if (is_array($json) && !empty($json['id']) && is_numeric($json['id'])) {
                $num = (int) $json['id'];
                if (User::where('id', $num)->exists()) {
                    return $num;
                }
            }
        }

        // 5. Try query by email or phone
        if (filter_var($payload, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', strtolower($payload))->first();
            if ($user) return $user->id;
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $payload);
        if (!empty($cleanPhone) && strlen($cleanPhone) >= 7) {
            $user = User::where('phone', $payload)->orWhere('phone', $cleanPhone)->first();
            if ($user) return $user->id;
        }

        // 6. Referral code match
        $referral = DB::table('referrals')->where('referral_code', strtoupper($payload))->first();
        if ($referral) {
            return (int) $referral->user_id;
        }

        return null;
    }

    /**
     * Get unique QR payload string for a customer.
     */
    public function getCustomerQrPayload(User $user): string
    {
        return 'CUST:' . $user->id;
    }

    /**
     * Format verified customer details for QR display and recipient resolution.
     */
    public function formatCustomerQrData(User $user): array
    {
        $wallet = $user->wallet ?? Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['main_balance' => 0, 'earning_balance' => 0, 'credit_balance' => 0]
        );

        $creditAccount = $user->creditAccount;

        // Initials (e.g. "VC")
        $words = preg_split('/\s+/', trim((string) $user->name));
        $initials = '';
        foreach ($words as $w) {
            if (!empty($w)) $initials .= strtoupper($w[0]);
            if (strlen($initials) >= 2) break;
        }
        $initials = $initials ?: 'CU';

        // Mask phone: +91 ******1234
        $phone = $user->phone ?: '';
        $maskedPhone = 'Not provided';
        if (!empty($phone)) {
            $len = strlen($phone);
            if ($len >= 7) {
                $maskedPhone = substr($phone, 0, 2) . '******' . substr($phone, -3);
            } else {
                $maskedPhone = $phone;
            }
        }

        // Mask email: s***@example.com
        $email = $user->email;
        $maskedEmail = $email;
        if (str_contains($email, '@')) {
            [$namePart, $domainPart] = explode('@', $email, 2);
            $nameLen = strlen($namePart);
            if ($nameLen <= 2) {
                $maskedEmail = substr($namePart, 0, 1) . '*@' . $domainPart;
            } else {
                $maskedEmail = substr($namePart, 0, 2) . str_repeat('*', min(4, $nameLen - 2)) . '@' . $domainPart;
            }
        }

        $custId = 'CUST-' . (10000 + (int) $user->id);

        return [
            'id' => $user->id,
            'cust_id' => $custId,
            'qr_payload' => $this->getCustomerQrPayload($user),
            'name' => $user->name,
            'initials' => $initials,
            'email' => $user->email,
            'masked_email' => $maskedEmail,
            'phone' => $phone,
            'masked_phone' => $maskedPhone,
            'avatar_url' => $user->avatar_url,
            'status' => $user->status ?? 'active',
            'is_active' => ($user->status ?? 'active') === 'active',
            'referral_code' => $user->referralRecord?->referral_code ?: 'N/A',
            'main_balance' => (float) ($wallet->main_balance ?? 0),
            'main_balance_formatted' => '₹ ' . number_format($wallet->main_balance ?? 0, 2),
            'available_credit' => (float) ($creditAccount?->available_credit ?? 0),
            'available_credit_formatted' => '₹ ' . number_format($creditAccount?->available_credit ?? 0, 2),
            'created_at' => $user->created_at ? $user->created_at->format('d M Y') : 'N/A',
        ];
    }

    /**
     * Atomically execute a User-to-User QR Transfer with pessimistic locks and immutable ledger entries.
     *
     * @throws \Exception
     */
    public function recordQrTransfer(User $sender, User $recipient, float $amount, ?string $remarks = null): array
    {
        if ($sender->id === $recipient->id) {
            throw new \InvalidArgumentException('You cannot transfer balance to your own account.');
        }

        if ($amount <= 0) {
            throw new \InvalidArgumentException('Transfer amount must be greater than zero.');
        }

        if (($recipient->status ?? 'active') !== 'active') {
            throw new \InvalidArgumentException('Recipient account is not active.');
        }

        return DB::transaction(function () use ($sender, $recipient, $amount, $remarks) {
            // Guarantee deterministic lock ordering to prevent deadlocks (lowest user_id first)
            $firstUserId = min($sender->id, $recipient->id);
            $secondUserId = max($sender->id, $recipient->id);

            $wallet1 = Wallet::where('user_id', $firstUserId)->lockForUpdate()->first();
            $wallet2 = Wallet::where('user_id', $secondUserId)->lockForUpdate()->first();

            if (!$wallet1) {
                $wallet1 = Wallet::create(['user_id' => $firstUserId, 'main_balance' => 0, 'earning_balance' => 0, 'credit_balance' => 0]);
                $wallet1 = Wallet::where('id', $wallet1->id)->lockForUpdate()->first();
            }
            if (!$wallet2) {
                $wallet2 = Wallet::create(['user_id' => $secondUserId, 'main_balance' => 0, 'earning_balance' => 0, 'credit_balance' => 0]);
                $wallet2 = Wallet::where('id', $wallet2->id)->lockForUpdate()->first();
            }

            $senderWallet = ($sender->id === $firstUserId) ? $wallet1 : $wallet2;
            $recipientWallet = ($recipient->id === $firstUserId) ? $wallet1 : $wallet2;

            if ((float) $senderWallet->main_balance < $amount) {
                throw new \RuntimeException('Insufficient balance. Your current available balance is ₹' . number_format($senderWallet->main_balance, 2) . '.');
            }

            // 1. Update balances atomically
            $senderWallet->main_balance = (float) $senderWallet->main_balance - $amount;
            $senderWallet->save();

            $recipientWallet->main_balance = (float) $recipientWallet->main_balance + $amount;
            $recipientWallet->save();

            // 2. Generate canonical unique Transaction ID
            $txnDate = date('Ymd');
            $randomStr = strtoupper(Str::random(8));
            $txnId = "TXN-{$txnDate}-{$randomStr}";

            $senderCustId = 'CUST-' . (10000 + (int) $sender->id);
            $recipientCustId = 'CUST-' . (10000 + (int) $recipient->id);

            $cleanRemarks = !empty($remarks) ? trim($remarks) : null;
            $senderDesc = "Sent to {$recipient->name} ({$recipientCustId})" . ($cleanRemarks ? " - {$cleanRemarks}" : "");
            $recipientDesc = "Received from {$sender->name} ({$senderCustId})" . ($cleanRemarks ? " - {$cleanRemarks}" : "");

            // 3. Sender WalletTransaction
            $senderTx = WalletTransaction::create([
                'wallet_id' => $senderWallet->id,
                'type' => 'debit',
                'source' => 'transfer',
                'amount' => $amount,
                'balance' => $senderWallet->main_balance,
                'counterparty_id' => $recipient->id,
                'reference_id' => $txnId,
                'status' => 'completed',
                'description' => $senderDesc,
            ]);

            // 4. Sender Immutable WalletLedger
            $senderLedger = WalletLedger::create([
                'transaction_id' => "{$txnId}-DR",
                'wallet_id' => $senderWallet->id,
                'user_id' => $sender->id,
                'counterparty_id' => $recipient->id,
                'counterparty_name' => $recipient->name,
                'type' => 'debit',
                'source' => 'qr_transfer',
                'amount' => $amount,
                'resulting_balance' => $senderWallet->main_balance,
                'reference_id' => $txnId,
                'status' => 'completed',
                'description' => $senderDesc,
                'metadata' => [
                    'transfer_type' => 'qr_transfer',
                    'sender_cust_id' => $senderCustId,
                    'recipient_cust_id' => $recipientCustId,
                    'remarks' => $cleanRemarks,
                ],
            ]);

            // 5. Recipient WalletTransaction
            $recipientTx = WalletTransaction::create([
                'wallet_id' => $recipientWallet->id,
                'type' => 'credit',
                'source' => 'transfer',
                'amount' => $amount,
                'balance' => $recipientWallet->main_balance,
                'counterparty_id' => $sender->id,
                'reference_id' => $txnId,
                'status' => 'completed',
                'description' => $recipientDesc,
            ]);

            // 6. Recipient Immutable WalletLedger
            $recipientLedger = WalletLedger::create([
                'transaction_id' => "{$txnId}-CR",
                'wallet_id' => $recipientWallet->id,
                'user_id' => $recipient->id,
                'counterparty_id' => $sender->id,
                'counterparty_name' => $sender->name,
                'type' => 'credit',
                'source' => 'qr_transfer',
                'amount' => $amount,
                'resulting_balance' => $recipientWallet->main_balance,
                'reference_id' => $txnId,
                'status' => 'completed',
                'description' => $recipientDesc,
                'metadata' => [
                    'transfer_type' => 'qr_transfer',
                    'sender_cust_id' => $senderCustId,
                    'recipient_cust_id' => $recipientCustId,
                    'remarks' => $cleanRemarks,
                ],
            ]);

            $now = Carbon::now();

            return [
                'transaction_id' => $txnId,
                'amount' => $amount,
                'amount_formatted' => '₹ ' . number_format($amount, 2),
                'sender_id' => $sender->id,
                'sender_name' => $sender->name,
                'sender_new_balance' => (float) $senderWallet->main_balance,
                'sender_new_balance_formatted' => '₹ ' . number_format($senderWallet->main_balance, 2),
                'recipient_id' => $recipient->id,
                'recipient_name' => $recipient->name,
                'recipient_cust_id' => $recipientCustId,
                'recipient_new_balance' => (float) $recipientWallet->main_balance,
                'date' => $now->format('d M Y, h:i A'),
                'timestamp' => $now->toIso8601String(),
                'sender_tx_id' => $senderTx->id,
                'recipient_tx_id' => $recipientTx->id,
            ];
        });
    }

    /**
     * Record Admin "Add Wallet Money" atomically with resulting balance and immutable ledger entry.
     */
    public function recordAdminAddMoney(User $user, float $amount, ?string $note = null, ?User $admin = null): array
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than zero.');
        }

        $adminName = $admin ? $admin->name : 'Admin';

        return DB::transaction(function () use ($user, $amount, $note, $adminName, $admin) {
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
            if (!$wallet) {
                $wallet = Wallet::create(['user_id' => $user->id, 'main_balance' => 0, 'earning_balance' => 0, 'credit_balance' => 0]);
                $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();
            }

            // Update balance
            $wallet->main_balance = (float) $wallet->main_balance + $amount;
            $wallet->save();

            $cleanNote = !empty($note)
                ? trim($note)
                : "Wallet recharge added by Admin ({$adminName})";

            $refId = 'ADMIN-ADD-' . strtoupper(Str::random(6)) . '-' . $user->id;

            // 1. WalletTransaction with balance
            $tx = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'credit',
                'source' => 'manual',
                'amount' => $amount,
                'balance' => $wallet->main_balance,
                'counterparty_id' => $admin?->id,
                'reference_id' => $refId,
                'status' => 'completed',
                'description' => $cleanNote,
            ]);

            // 2. Immutable WalletLedger
            $ledger = WalletLedger::create([
                'transaction_id' => $refId,
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'counterparty_id' => $admin?->id,
                'counterparty_name' => "Admin ({$adminName})",
                'type' => 'credit',
                'source' => 'manual',
                'amount' => $amount,
                'resulting_balance' => $wallet->main_balance,
                'reference_id' => $refId,
                'status' => 'completed',
                'description' => $cleanNote,
                'metadata' => [
                    'admin_id' => $admin?->id,
                    'admin_name' => $adminName,
                ],
            ]);

            return [
                'ref_id' => $refId,
                'wallet' => $wallet,
                'transaction' => $tx,
                'ledger' => $ledger,
            ];
        });
    }

    /**
     * Record Admin "Shop Amount" deduction atomically with resulting balance and immutable ledger entry.
     */
    public function recordAdminShopDeduct(User $user, float $amount, Order $order, ?string $note = null, ?User $admin = null): array
    {
        $adminName = $admin ? $admin->name : 'Admin';

        return DB::transaction(function () use ($user, $amount, $order, $note, $adminName, $admin) {
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
            if (!$wallet || (float) $wallet->main_balance < $amount) {
                throw new \RuntimeException('Insufficient wallet balance.');
            }

            // Deduct
            $wallet->main_balance = (float) $wallet->main_balance - $amount;
            $wallet->save();

            $cleanNote = !empty($note)
                ? trim($note)
                : "Shop purchase deducted by Admin ({$adminName})";

            $refId = 'ORDER-' . $order->id;

            // 1. WalletTransaction with balance
            $tx = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'source' => 'purchase',
                'amount' => $amount,
                'balance' => $wallet->main_balance,
                'counterparty_id' => $admin?->id,
                'reference_id' => $refId,
                'status' => 'completed',
                'description' => $cleanNote,
            ]);

            // 2. Immutable WalletLedger
            $ledger = WalletLedger::create([
                'transaction_id' => "ORDER-{$order->id}-DR",
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'counterparty_id' => $admin?->id,
                'counterparty_name' => "Admin ({$adminName})",
                'type' => 'debit',
                'source' => 'purchase',
                'amount' => $amount,
                'resulting_balance' => $wallet->main_balance,
                'reference_id' => $refId,
                'status' => 'completed',
                'description' => $cleanNote,
                'metadata' => [
                    'order_id' => $order->id,
                    'admin_id' => $admin?->id,
                    'admin_name' => $adminName,
                ],
            ]);

            return [
                'ref_id' => $refId,
                'wallet' => $wallet,
                'transaction' => $tx,
                'ledger' => $ledger,
            ];
        });
    }
}
