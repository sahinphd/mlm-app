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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'paid_amount')) {
                $table->decimal('paid_amount', 15, 2)->nullable()->after('total_bv');
            }
            if (!Schema::hasColumn('orders', 'credit_amount')) {
                $table->decimal('credit_amount', 15, 2)->nullable()->after('paid_amount');
            }
        });

        Schema::table('emi_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('emi_schedules', 'commission_distributed')) {
                $table->boolean('commission_distributed')->default(false)->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'credit_amount')) {
                $table->dropColumn('credit_amount');
            }
            if (Schema::hasColumn('orders', 'paid_amount')) {
                $table->dropColumn('paid_amount');
            }
        });

        Schema::table('emi_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('emi_schedules', 'commission_distributed')) {
                $table->dropColumn('commission_distributed');
            }
        });
    }
};
