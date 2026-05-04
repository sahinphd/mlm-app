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
        // 1. Get all BV records from commissions table
        $bvRecords = DB::table('commissions')->where('type', 'bv')->get();

        foreach ($bvRecords as $record) {
            // 2. Insert into bv_commissions table
            DB::table('bv_commissions')->insert([
                'id' => $record->id, // Preserve ID to maintain reference consistency if any
                'user_id' => $record->user_id,
                'from_user_id' => $record->from_user_id,
                'order_id' => $record->order_id,
                'level' => $record->level,
                'amount' => $record->amount,
                'status' => $record->status,
                'withdrawable_at' => $record->withdrawable_at,
                'note' => $record->note,
                'created_at' => $record->created_at,
                'updated_at' => $record->updated_at,
            ]);
        }

        // 3. Delete from commissions table
        DB::table('commissions')->where('type', 'bv')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $bvRecords = DB::table('bv_commissions')->get();

        foreach ($bvRecords as $record) {
            DB::table('commissions')->insert([
                'id' => $record->id,
                'user_id' => $record->user_id,
                'from_user_id' => $record->from_user_id,
                'order_id' => $record->order_id,
                'level' => $record->level,
                'amount' => $record->amount,
                'type' => 'bv',
                'status' => $record->status,
                'withdrawable_at' => $record->withdrawable_at,
                'note' => $record->note,
                'created_at' => $record->created_at,
                'updated_at' => $record->updated_at,
            ]);
        }

        DB::table('bv_commissions')->truncate();
    }
};
