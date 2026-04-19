<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Referral;
use App\Models\CreditAccount;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Settings Seeding (Essential for MLM calculations)
        $this->seedSettings();

        // 2. Create Admin
        $admin = User::updateOrCreate(
            ['email' => 'sahin@mabia.in'],
            [
                'name' => 'Admin User',
                'role' => 'admin',
                'status' => 'active',
                'password' => bcrypt('password'),
            ]
        );

        // Ensure Admin has a referral record (The Root)
        if (!Referral::where('user_id', $admin->id)->exists()) {
            Referral::create([
                'user_id' => $admin->id,
                'parent_id' => null,
                'referral_code' => 'ADMIN',
                'level_depth' => 0,
            ]);
        }

        // 3. Create a Referral Chain (Admin -> User1 -> User2 -> User3)
        $user1 = $this->createUser('Level 1 User', 'user1@mabia.in', $admin->id);
        $user2 = $this->createUser('Level 2 User', 'user2@mabia.in', $user1->id);
        $user3 = $this->createUser('Level 3 User', 'user3@mabia.in', $user2->id);

        // 4. Seed Products
        Product::updateOrCreate(['name' => 'Standard Package'], [
            'price' => 1000,
            'bv' => 50,
            'stock' => 100,
            'status' => 'active'
        ]);

        Product::updateOrCreate(['name' => 'Premium Item'], [
            'price' => 5000,
            'bv' => 200,
            'stock' => 50,
            'status' => 'active'
        ]);

        // 5. Setup Wallets and Credit for testing
        foreach (User::all() as $user) {
            Wallet::firstOrCreate(['user_id' => $user->id], [
                'main_balance' => 10000,
                'earning_balance' => 0,
                'credit_balance' => 0
            ]);

            CreditAccount::firstOrCreate(['user_id' => $user->id], [
                'credit_limit' => 5000,
                'used_credit' => 0,
                'available_credit' => 5000,
                'approval_status' => 'approved'
            ]);
        }
    }

    private function createUser($name, $email, $parentId = null)
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'role' => 'user',
                'status' => 'active',
                'password' => bcrypt('password'),
            ]
        );

        if (!Referral::where('user_id', $user->id)->exists()) {
            Referral::create([
                'user_id' => $user->id,
                'parent_id' => $parentId,
                'referral_code' => Str::upper(Str::random(8)),
                'level_depth' => $parentId ? (Referral::where('user_id', $parentId)->value('level_depth') + 1) : 0,
            ]);
        }

        return $user;
    }

    private function seedSettings()
    {
        $settings = [
            'site_name' => 'MLM App',
            'currency' => '₹',
            'repurchase_commission_level_1' => 10,
            'repurchase_commission_level_2' => 5,
            'repurchase_commission_level_3' => 2,
            'order_commission_level_1' => 2,
            'order_commission_level_2' => 1,
            'order_commission_level_3' => 0.5,
        ];

        // Save to DB table
        foreach ($settings as $key => $value) {
            DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $value]);
        }

        // Save to JSON file as well since MLMService uses it
        $path = storage_path('app/private/settings.json');
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        file_put_contents($path, json_encode($settings, JSON_PRETTY_PRINT));
    }
}
