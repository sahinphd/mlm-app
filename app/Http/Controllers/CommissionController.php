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
        $totalCommission = Commission::where('user_id', $user->id)
            ->where('type', '!=', 'bv')
            ->sum('amount');

        return view('commissions.index', [
            'page' => 'commissions',
            'totalCommission' => $totalCommission
        ]);
    }

    public function bvIndex()
    {
        $user = Auth::user();
        $settings = $this->getSettings();
        if (($settings['enable_bv_commission'] ?? 'off') !== 'on') {
            abort(404);
        }

        $totalBvCommission = Commission::where('user_id', $user->id)
            ->where('type', 'bv')
            ->sum('amount');

        return view('commissions.bv', [
            'page' => 'bv_commissions',
            'totalBvCommission' => $totalBvCommission
        ]);
    }

    public function data(Request $request)
    {
        $user = Auth::user();
        $type = $request->query('type');
        
        // 1. Get Earnings (Commission Table)
        $commQuery = Commission::where('user_id', $user->id)->with('fromUser');
        if ($type === 'bv') {
            $commQuery->where('type', 'bv');
        } else {
            $commQuery->where('type', '!=', 'bv');
        }

        // Sorting
        $columns = ['from_user_id', 'level', 'type', 'amount', 'created_at'];
        $orderColumnIndex = $request->input('order.0.column', 4);
        $orderDir = $request->input('order.0.dir', 'desc');
        $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';

        // 2. If BV, also get Deductions (WalletTransaction Table)
        $combinedData = [];
        $commissions = $commQuery->get();
        
        foreach ($commissions as $comm) {
            $combinedData[] = [
                'from_user' => ($comm->fromUser->name ?? 'N/A') . '<br><small class="text-gray-500">' . ($comm->fromUser->email ?? '') . '</small>',
                'level' => '<span class="px-2 py-1 rounded-md bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">Level ' . $comm->level . '</span>',
                'amount' => ($type === 'bv' ? '' : '₹') . number_format($comm->amount, 2),
                'amount_raw' => (float)$comm->amount,
                'type' => $comm->type === 'joining' 
                    ? '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">Joining</span>'
                    : ($comm->type === 'bv' 
                        ? '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400">BV Earning</span>'
                        : '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400">' . ucfirst($comm->type) . '</span>'),
                'date' => $comm->created_at->format('M d, Y H:i'),
                'created_at' => $comm->created_at->toDateTimeString()
            ];
        }

        if ($type === 'bv') {
            $wallet = \App\Models\Wallet::where('user_id', $user->id)->first();
            if ($wallet) {
                $deductions = \App\Models\WalletTransaction::where('wallet_id', $wallet->id)
                    ->where('source', 'bv')
                    ->where('type', 'debit')
                    ->get();

                foreach ($deductions as $tx) {
                    $combinedData[] = [
                        'from_user' => '<span class="text-gray-500">System (Conversion)</span>',
                        'level' => '<span class="px-2 py-1 rounded-md bg-gray-100 dark:bg-gray-800 text-gray-400">-</span>',
                        'amount' => '-' . number_format($tx->amount, 2),
                        'amount_raw' => -(float)$tx->amount,
                        'type' => '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">BV Conversion</span>',
                        'date' => $tx->created_at->format('M d, Y H:i'),
                        'created_at' => $tx->created_at->toDateTimeString()
                    ];
                }
            }
        }

        // Apply manual sorting to the combined array
        usort($combinedData, function($a, $b) use ($orderColumn, $orderDir) {
            $valA = $a[$orderColumn];
            $valB = $b[$orderColumn];
            
            if ($orderColumn === 'amount') {
                $valA = $a['amount_raw'];
                $valB = $b['amount_raw'];
            }

            if ($valA == $valB) return 0;
            if ($orderDir === 'asc') {
                return ($valA < $valB) ? -1 : 1;
            } else {
                return ($valA > $valB) ? -1 : 1;
            }
        });

        // Search in the array
        if ($search = $request->input('search.value')) {
            $combinedData = array_filter($combinedData, function($item) use ($search) {
                return stripos(strip_tags($item['from_user']), $search) !== false ||
                       stripos(strip_tags($item['type']), $search) !== false ||
                       stripos($item['amount'], $search) !== false;
            });
        }

        $totalFiltered = count($combinedData);

        // Manual Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $pagedData = array_slice($combinedData, $start, $length);

        return response()->json([
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalFiltered),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $pagedData
        ]);
    }

    protected function getSettings()
    {
        $settingsFile = 'settings.json';
        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($settingsFile)) {
            return [];
        }
        return json_decode(\Illuminate\Support\Facades\Storage::disk('local')->get($settingsFile), true);
    }

    public function adminIndex(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
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

        $query = Commission::with(['user', 'fromUser'])->where('type', '!=', 'bv')->orderBy('created_at', 'desc');

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

    public function bvAdminIndex(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $receiverId = $request->query('receiver_id');
        $fromUserId = $request->query('from_user_id');
        $receiverName = $request->query('receiver_name'); 
        $fromUserName = $request->query('from_user_name'); 
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $level = $request->query('level');
        $perPage = $request->query('per_page', 50);

        $query = Commission::with(['user', 'fromUser'])->where('type', 'bv')->orderBy('created_at', 'desc');

        if ($receiverId) $query->where('user_id', $receiverId);
        if ($fromUserId) $query->where('from_user_id', $fromUserId);
        if ($level) $query->where('level', $level);
        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);

        $commissions = $query->paginate($perPage);
            
        return view('admin.commissions.bv', compact('commissions', 'receiverId', 'fromUserId', 'receiverName', 'fromUserName', 'startDate', 'endDate', 'level', 'perPage'));
    }

    public function adminExport(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $receiverId = $request->query('receiver_id');
        $fromUserId = $request->query('from_user_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $type = $request->query('type');
        $level = $request->query('level');

        $query = Commission::with(['user', 'fromUser'])->where('type', '!=', 'bv')->orderBy('created_at', 'desc');

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

    public function bvAdminExport(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $receiverId = $request->query('receiver_id');
        $fromUserId = $request->query('from_user_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $level = $request->query('level');

        $query = Commission::with(['user', 'fromUser'])->where('type', 'bv')->orderBy('created_at', 'desc');

        if ($receiverId) $query->where('user_id', $receiverId);
        if ($fromUserId) $query->where('from_user_id', $fromUserId);
        if ($level) $query->where('level', $level);
        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);

        $commissions = $query->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=bv_commissions_export_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['ID', 'Receiver Name', 'Receiver Email', 'From User', 'From User Email', 'Level', 'BV Amount', 'Date'];

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
                    $comm->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
