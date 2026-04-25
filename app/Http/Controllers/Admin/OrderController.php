<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $mlmService;

    public function __construct(\App\Services\MLMService $mlmService)
    {
        $this->mlmService = $mlmService;
    }

    public function index(Request $request)
    {
        $status = $request->query('status');
        $q = $request->query('q');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $perPage = $request->query('per_page', 15);

        $query = Order::with('user')->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if ($q) {
            $query->where(function($query) use ($q) {
                $query->whereHas('user', function($u) use ($q) {
                    $u->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
                })->orWhere('id', 'like', "%{$q}%");
            });
        }

        $orders = $query->paginate($perPage);

        // Stats for cards
        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'revenue' => Order::where('status', 'completed')->sum('total_amount'),
        ];

        return view('admin.orders.index', compact('orders', 'stats', 'status', 'q', 'startDate', 'endDate', 'perPage'));
    }

    public function show(Order $order)
    {
        $order->load('user', 'items.product');
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,completed,cancelled,returned,failed'
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        $order->update(['status' => $newStatus]);

        // Logic for commission distribution when marked as completed
        if ($newStatus === 'completed' && $oldStatus !== 'completed') {
            // Check if commissions already exist for this order to prevent duplicates
            $commissionsExist = \App\Models\Commission::where('order_id', $order->id)->exists();
            if (!$commissionsExist) {
                $this->mlmService->distributeOrderCommissions($order);
            }
        }

        // Logic for commission reversal
        // If changing TO cancelled/returned FROM completed (commissions were already distributed)
        if (in_array($newStatus, ['cancelled', 'returned']) && $oldStatus === 'completed') {
            $this->mlmService->reverseOrderCommissions($order);
        }

        // Notify User
        $order->user->notify(new \App\Notifications\OrderUpdateNotification($order));

        return back()->with('success', 'Order status updated successfully.');
    }
}
