<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Helpers\PhoneHelper;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $users = DB::table('users')->whereNotNull('phone')->get(['id', 'phone']);
        foreach ($users as $user) {
            $normalized = PhoneHelper::normalize($user->phone);
            if ($normalized !== $user->phone) {
                DB::table('users')->where('id', $user->id)->update(['phone' => $normalized]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Irreversible data sanitation
    }
};
