<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommissionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('commissions.index', [
            'page' => 'commissions'
        ]);
    }

    public function data(Request $request)
    {
        $user = Auth::user();
        $query = Commission::where('user_id', $user->id)->with('fromUser');

        // Total count
        $totalData = $query->count();
        $totalFiltered = $totalData;

        // Search
        if ($search = $request->input('search.value')) {
            $query->where(function($q) use ($search) {
                $q->where('amount', 'LIKE', "%{$search}%")
                  ->orWhere('type', 'LIKE', "%{$search}%")
                  ->orWhere('level', 'LIKE', "%{$search}%")
                  ->orWhereHas('fromUser', function($sub) use ($search) {
                      $sub->where('name', 'LIKE', "%{$search}%")
                          ->orWhere('email', 'LIKE', "%{$search}%");
                  });
            });
            $totalFiltered = $query->count();
        }

        // Sorting
        $columns = ['created_at', 'from_user_id', 'level', 'amount', 'type', 'created_at'];
        $orderColumnIndex = $request->input('order.0.column', 5);
        $orderDir = $request->input('order.0.dir', 'desc');
        $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
        $query->orderBy($orderColumn, $orderDir);

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $commissions = $query->skip($start)->take($length)->get();

        $data = [];
        foreach ($commissions as $comm) {
            $data[] = [
                'from_user' => ($comm->fromUser->name ?? 'N/A') . '<br><small class="text-gray-500">' . ($comm->fromUser->email ?? '') . '</small>',
                'level' => '<span class="px-2 py-1 rounded-md bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">Level ' . $comm->level . '</span>',
                'amount' => '₹' . number_format($comm->amount, 2),
                'type' => $comm->type === 'joining' 
                    ? '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">Joining</span>'
                    : '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400">' . ucfirst($comm->type) . '</span>',
                'date' => $comm->created_at->format('M d, Y H:i'),
                'raw_date' => $comm->created_at->toDateTimeString()
            ];
        }

        return response()->json([
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        ]);
    }

    public function adminIndex(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $receiverId = $request->query('receiver_id');
        $fromUserId = $request->query('from_user_id');
        $receiverName = $request->query('receiver_name'); // For UI display
        $fromUserName = $request->query('from_user_name'); // For UI display
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $type = $request->query('type');
        $level = $request->query('level');
        $perPage = $request->query('per_page', 50);

        $query = Commission::with(['user', 'fromUser'])->orderBy('created_at', 'desc');

        if ($receiverId) {
            $query->where('user_id', $receiverId);
        }

        if ($fromUserId) {
            $query->where('from_user_id', $fromUserId);
        }

        if ($level) {
            $query->where('level', $level);
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $commissions = $query->paginate($perPage);
            
        return view('admin.commissions.index', compact('commissions', 'receiverId', 'fromUserId', 'receiverName', 'fromUserName', 'startDate', 'endDate', 'type', 'level', 'perPage'));
    }

    public function adminExport(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $receiverId = $request->query('receiver_id');
        $fromUserId = $request->query('from_user_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $type = $request->query('type');
        $level = $request->query('level');

        $query = Commission::with(['user', 'fromUser'])->orderBy('created_at', 'desc');

        if ($receiverId) $query->where('user_id', $receiverId);
        if ($fromUserId) $query->where('from_user_id', $fromUserId);
        if ($level) $query->where('level', $level);
        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);
        if ($type) $query->where('type', $type);

        $commissions = $query->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=commissions_export_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['ID', 'Receiver Name', 'Receiver Email', 'From User', 'From User Email', 'Level', 'Amount', 'Type', 'Date'];

        $callback = function() use($commissions, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($commissions as $comm) {
                fputcsv($file, [
                    $comm->id,
                    $comm->user->name ?? 'N/A',
                    $comm->user->email ?? '',
                    $comm->fromUser->name ?? 'N/A',
                    $comm->fromUser->email ?? '',
                    'Level ' . $comm->level,
                    number_format($comm->amount, 2),
                    ucfirst($comm->type),
                    $comm->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
