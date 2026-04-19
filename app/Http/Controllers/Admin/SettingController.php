<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class SettingController extends Controller
{
    protected $settingsFile = 'settings.json';

    public function index()
    {
        $settings = $this->getSettings();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        // Handle Logo Uploads
        $logos = ['logo', 'logo_dark', 'logo_icon', 'auth_logo'];
        foreach ($logos as $logoKey) {
            if ($request->hasFile($logoKey)) {
                $file = $request->file($logoKey);
                $filename = str_replace('_', '-', $logoKey) . '.' . $file->getClientOriginalExtension();
                
                // Ensure directory exists
                $path = public_path('images/logo');
                if (!File::isDirectory($path)) {
                    File::makeDirectory($path, 0777, true, true);
                }

                // Move file to public/images/logo/
                $file->move($path, $filename);
            }
        }

        $data = $request->except(['_token', 'logo', 'logo_dark', 'logo_icon', 'auth_logo']);
        
        $settings = $this->getSettings();
        $newSettings = array_merge($settings, $data);
        
        Storage::disk('local')->put($this->settingsFile, json_encode($newSettings, JSON_PRETTY_PRINT));

        return back()->with('success', 'Settings and logos updated successfully.');
    }

    protected function getSettings()
    {
        if (!Storage::disk('local')->exists($this->settingsFile)) {
            return [
                'site_name' => config('app.name', 'MLM App'),
                'contact_email' => 'admin@example.com',
                'currency' => 'INR',
                'min_withdrawal' => 500,
                'maintenance_mode' => 'off',
                'registration_enabled' => 'on',
                'joining_commission_level_1' => 100,
                'joining_commission_level_2' => 50,
                'joining_commission_level_3' => 30,
                'joining_commission_level_4' => 20,
                'joining_commission_level_5' => 10,
                'repurchase_commission_level_1' => 20,
                'repurchase_commission_level_2' => 10,
                'repurchase_commission_level_3' => 5,
                'repurchase_commission_level_4' => 3,
                'repurchase_commission_level_5' => 2,
                'order_commission_level_1' => 2.0,
                'order_commission_level_2' => 1.0,
                'order_commission_level_3' => 0.3,
                'order_commission_level_4' => 0.2,
                'order_commission_level_5' => 0.1,
            ];
        }

        return json_decode(Storage::disk('local')->get($this->settingsFile), true);
    }
}
