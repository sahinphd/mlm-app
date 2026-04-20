<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\NotificationService;

class TestPushNotification extends Command
{
    protected $signature = 'emi:test-push {user_id}';
    protected $description = 'Send a test push notification to a specific user';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("User not found.");
            return;
        }

        if (!$user->fcm_token) {
            $this->error("User #{$userId} does not have an FCM token. Make sure the device/browser has registered a token.");
            return;
        }

        $this->info("Sending test notification to User #{$userId} ({$user->name})...");

        $ns = new NotificationService();
        $result = $ns->sendPushNotification(
            $userId, 
            "Test Notification", 
            "This is a test message from " . config('app.name'),
            ['test_key' => 'test_value']
        );

        if ($result) {
            $this->info("Success! The notification was accepted by FCM.");
        } else {
            $this->error("Failed! Check storage/logs/laravel.log for detailed error messages.");
        }
    }
}
