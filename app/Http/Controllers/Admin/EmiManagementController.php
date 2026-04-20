<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmiSchedule;
use App\Models\User;
use App\Notifications\UpcomingEmiNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmiManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = EmiSchedule::with(['user', 'order']);

        // Filters
        $userId = $request->input('user_id');
        $userName = $request->input('user_name');
        $status = $request->input('status');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $perPage = $request->input('per_page', 20);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($startDate) {
            $query->whereDate('due_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('due_date', '<=', $endDate);
        }

        $emis = $query->orderBy('due_date', 'asc')->paginate($perPage);

        return view('admin.emis.index', [
            'page' => 'admin_emis',
            'emis' => $emis,
            'userId' => $userId,
            'userName' => $userName,
            'status' => $status,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'perPage' => $perPage
        ]);
    }

    public function sendReminder($id)
    {
        $emi = EmiSchedule::findOrFail($id);
        $user = User::find($emi->user_id);

        if ($user) {
            $user->notify(new UpcomingEmiNotification($emi));
            return response()->json(['success' => true, 'message' => 'Notification sent successfully to ' . $user->name]);
        }

        return response()->json(['success' => false, 'message' => 'User not found.'], 404);
    }
}
