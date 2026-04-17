<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update Users Table
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->enum('role', ['admin', 'user'])->default('user')->after('password');
            $table->enum('status', ['active', 'blocked', 'pending'])->default('pending')->after('role');
        });

        // 2. Referrals Table (Unilevel Tree)
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('referral_code')->unique();
            $table->integer('level_depth')->default(0);
            $table->timestamps();
            
            $table->index(['user_id', 'parent_id']);
        });

        // 3. Wallets Table
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->decimal('main_balance', 15, 2)->default(0);
            $table->decimal('earning_balance', 15, 2)->default(0);
            $table->decimal('credit_balance', 15, 2)->default(0);
            $table->timestamps();
        });

        // 4. Wallet Transactions Table
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('wallets')->onDelete('cascade');
            $table->enum('type', ['credit', 'debit']);
            $table->enum('source', ['joining', 'repurchase', 'emi', 'penalty', 'manual', 'purchase', 'commission']);
            $table->decimal('amount', 15, 2);
            $table->string('reference_id')->nullable(); // Order ID or Commission ID
            $table->string('description')->nullable();
            $table->timestamps();
            
            $table->index('wallet_id');
        });

        // 5. Credit Accounts Table
        Schema::create('credit_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->decimal('credit_limit', 15, 2)->default(5000); // Configurable limit
            $table->decimal('used_credit', 15, 2)->default(0);
            $table->decimal('available_credit', 15, 2)->default(5000);
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });

        // 6. Products Table
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 15, 2);
            $table->decimal('bv', 15, 2); // Business Volume
            $table->integer('stock')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 7. Orders Table
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('total_amount', 15, 2);
            $table->decimal('total_bv', 15, 2);
            $table->enum('payment_method', ['main_wallet', 'credit_wallet']);
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('completed');
            $table->timestamps();
        });

        // 8. Order Items Table
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('price', 15, 2);
            $table->decimal('bv', 15, 2);
            $table->timestamps();
        });

        // 9. Commissions Table
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->comment('User receiving commission');
            $table->foreignId('from_user_id')->constrained('users')->comment('User generating the commission');
            $table->foreignId('order_id')->nullable()->constrained('orders')->comment('Null for joining fee');
            $table->integer('level');
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['joining', 'repurchase']);
            $table->timestamps();
            
            $table->index(['user_id', 'order_id']);
        });

        // 10. EMI Schedules Table
        Schema::create('emi_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->decimal('total_amount', 15, 2);
            $table->decimal('installment_amount', 15, 2);
            $table->integer('interval_days')->default(7);
            $table->date('due_date');
            $table->enum('status', ['pending', 'paid', 'overdue'])->default('pending');
            $table->timestamps();
            
            $table->index(['user_id', 'status', 'due_date']);
        });

        // 11. Penalties Table
        Schema::create('penalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('emi_schedule_id')->constrained('emi_schedules')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
            $table->timestamps();
        });

        // 12. Welfare Funds Table (Aggregate tracker)
        Schema::create('welfare_funds', function (Blueprint $table) {
            $table->id();
            $table->decimal('total_fund_balance', 15, 2)->default(0);
            $table->timestamps();
        });

        // 13. Settings Table
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('welfare_funds');
        Schema::dropIfExists('penalties');
        Schema::dropIfExists('emi_schedules');
        Schema::dropIfExists('commissions');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('credit_accounts');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');
        Schema::dropIfExists('referrals');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'role', 'status']);
        });
    }
};
