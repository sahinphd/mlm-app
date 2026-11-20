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
        // 1. Create wallet_ledgers table for immutable audit ledger entries
        if (!Schema::hasTable('wallet_ledgers')) {
            Schema::create('wallet_ledgers', function (Blueprint $table) {
                $table->id();
                $table->string('transaction_id', 60)->unique();
                $table->foreignId('wallet_id')->constrained('wallets')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('counterparty_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('counterparty_name', 255)->nullable();
                $table->enum('type', ['credit', 'debit']);
                $table->string('source', 50); // qr_transfer, manual, purchase, transfer, etc.
                $table->decimal('amount', 15, 2);
                $table->decimal('resulting_balance', 15, 2);
                $table->text('description')->nullable();
                $table->string('reference_id', 100)->nullable();
                $table->string('status', 20)->default('completed');
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'created_at']);
                $table->index(['wallet_id', 'created_at']);
                $table->index('transaction_id');
            });
        }

        // 2. Add columns to wallet_transactions for consistency & fast querying
        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('wallet_transactions', 'balance')) {
                $table->decimal('balance', 15, 2)->nullable()->after('amount');
            }
            if (!Schema::hasColumn('wallet_transactions', 'counterparty_id')) {
                $table->foreignId('counterparty_id')->nullable()->after('reference_id')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('wallet_transactions', 'status')) {
                $table->string('status', 20)->default('completed')->after('counterparty_id');
            }
        });

        // 3. If MySQL driver, ensure source enum includes 'qr_transfer'
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE wallet_transactions MODIFY COLUMN source ENUM('joining', 'repurchase', 'emi', 'penalty', 'manual', 'purchase', 'commission', 'transfer', 'bv', 'commission_withdrawal', 'bv_withdrawal', 'qr_transfer') NOT NULL");
        }

        // 4. Backfill existing wallet_transactions balance & seed initial wallet_ledgers
        $wallets = DB::table('wallets')->pluck('id');
        foreach ($wallets as $walletId) {
            $txs = DB::table('wallet_transactions')
                ->where('wallet_id', $walletId)
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $running = 0.0;
            $wallet = DB::table('wallets')->where('id', $walletId)->first();
            $userId = $wallet ? $wallet->user_id : null;

            foreach ($txs as $tx) {
                $amt = (float) $tx->amount;
                if ($tx->type === 'credit') {
                    $running += $amt;
                } else {
                    $running -= $amt;
                }

                DB::table('wallet_transactions')
                    ->where('id', $tx->id)
                    ->update([
                        'balance' => $running,
                        'status' => 'completed',
                    ]);

                if ($userId) {
                    $uniqueTxnId = 'T-' . (88000 + (int) $tx->id);
                    if (!empty($tx->reference_id) && !DB::table('wallet_ledgers')->where('transaction_id', $tx->reference_id)->exists()) {
                        $ledgerTxnId = $tx->reference_id;
                    } else {
                        $ledgerTxnId = $uniqueTxnId;
                    }

                    // Avoid duplicate inserts
                    if (!DB::table('wallet_ledgers')->where('transaction_id', $ledgerTxnId)->exists()) {
                        DB::table('wallet_ledgers')->insert([
                            'transaction_id' => $ledgerTxnId,
                            'wallet_id' => $walletId,
                            'user_id' => $userId,
                            'counterparty_id' => null,
                            'counterparty_name' => $tx->source === 'manual' ? 'Admin' : null,
                            'type' => $tx->type,
                            'source' => $tx->source,
                            'amount' => $amt,
                            'resulting_balance' => $running,
                            'description' => $tx->description,
                            'reference_id' => $tx->reference_id,
                            'status' => 'completed',
                            'created_at' => $tx->created_at ?? now(),
                            'updated_at' => $tx->updated_at ?? now(),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_ledgers');

        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('wallet_transactions', 'counterparty_id')) {
                $table->dropForeign(['counterparty_id']);
                $table->dropColumn('counterparty_id');
            }
            if (Schema::hasColumn('wallet_transactions', 'balance')) {
                $table->dropColumn('balance');
            }
            if (Schema::hasColumn('wallet_transactions', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
