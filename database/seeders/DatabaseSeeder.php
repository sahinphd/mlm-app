<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create(['status' => 'active']);

        // Create admin (uses SUPER_ADMIN_EMAIL / SUPER_ADMIN_PASSWORD)
        $superEmail = env('SUPER_ADMIN_EMAIL', 'admin@example.com');
        $superPass = env('SUPER_ADMIN_PASSWORD', 'password');

        \App\Models\User::updateOrCreate([
            'email' => $superEmail,
        ], [
            'name' => 'Admin User',
            'role' => 'admin',
            'status' => 'active',
            'password' => bcrypt($superPass),
        ]);

        // Create regular user
        \App\Models\User::firstOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'role' => 'user',
            'password' => bcrypt('password'),
            'avatar' => '/images/user/avatar-1.jpg',
        ]);

        // Seed sample projects if none exist
        if (\App\Models\Project::count() === 0) {
            $sample = [
                ['title'=>'Phoenix SaaS','subtitle'=>'Real-time photo sharing app','start_date'=>'2026-03-06','end_date'=>'2026-12-21','progress'=>45,'status'=>'in-progress','participants'=>[2,3]],
                ['title'=>'Radiant Wave','subtitle'=>'Short-term accommodation marketplace','start_date'=>'2026-03-09','end_date'=>'2026-12-23','progress'=>100,'status'=>'completed','participants'=>[4,5]],
                ['title'=>'Dreamweaver','subtitle'=>'Social media photo sharing','start_date'=>'2026-03-05','end_date'=>'2026-12-12','progress'=>85,'status'=>'upcoming','participants'=>[6,7]],
            ];
            foreach($sample as $p) { \App\Models\Project::create($p); }
        }
    }
}
