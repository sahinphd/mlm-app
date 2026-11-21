<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Package;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\CreditAccount;
use App\Models\EmiSchedule;
use App\Services\MLMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ShopController extends Controller
{
    protected $mlmService;

    public function __construct(MLMService $mlmService)
    {
        $this->mlmService = $mlmService;
    }

    public function index()
    {
        $products = Product::where('status', 'active')->orderBy('name')->get();
        $packages = Package::where('status', 'active')->orderBy('name')->get();
        return view('usershop.index', [
            'products' => $products,
            'packages' => $packages,
            'page' => 'shop'
        ]);
    }

    public function checkout(Request $request)
    {
        $productId = $request->query('product_id');
        $packageId = $request->query('package_id');
        $quantity = $request->query('quantity', 1);
        
        $item = null;
        $type = '';

        if ($productId) {
            $item = Product::findOrFail($productId);
            $type = 'product';
        } elseif ($packageId) {
            $item = Package::findOrFail($packageId);
            $type = 'package';
        } else {
            return redirect()->route('shop.index')->with('error', 'No item selected.');
        }
        
        $user = Auth::user();
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['main_balance' => 0, 'earning_balance' => 0, 'credit_balance' => 0]);
        $creditAccount = CreditAccount::where('user_id', $user->id)->first();

        return view('usershop.checkout', [
            'item' => $item,
            'type' => $type,
            'quantity' => $quantity,
            'wallet' => $wallet,
            'creditAccount' => $creditAccount,
            'page' => 'shop'
        ]);
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'item_id' => 'required',
            'type' => 'required|in:product,package',
            'quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:main_wallet,credit_wallet,split'
        ]);

        $user = Auth::user();
        $quantity = $request->quantity;
        $item = null;
        $name = '';
        
        if ($request->type === 'product') {
            $item = Product::findOrFail($request->item_id);
            if ($item->stock < $quantity) return back()->with('error', 'Product out of stock.');
            $name = $item->name;
        } else {
            $item = Package::findOrFail($request->item_id);
            $name = $item->name;
        }

        $total = $item->price * $quantity;
        $total_bv = $item->bv * $quantity;

        return DB::transaction(function() use($user, $item, $quantity, $total, $total_bv, $request, $name){
            $creditPortion = 0;

            // Payment handling
            if($request->payment_method === 'main_wallet'){
                $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['main_balance' => 0, 'earning_balance' => 0, 'credit_balance' => 0]);
                if($wallet->main_balance < $total) return back()->with('error', 'Insufficient main wallet balance.');
                $wallet->main_balance -= $total;
                $wallet->save();
                WalletTransaction::create(['wallet_id' => $wallet->id, 'type' => 'debit', 'source' => 'purchase', 'amount' => $total, 'description' => 'Order purchase: ' . $name]);
            } elseif ($request->payment_method === 'credit_wallet') {
                $ca = CreditAccount::where('user_id', $user->id)->first();
                if(!$ca || $ca->approval_status !== 'approved') return back()->with('error', 'Your credit account is not approved.');
                if($ca->available_credit < $total) return back()->with('error', 'Insufficient credit limit.');
                
                $ca->used_credit += $total;
                $ca->available_credit = max(0, $ca->credit_limit - $ca->used_credit);
                $ca->save();

                \App\Models\CreditTransaction::create([
                    'credit_account_id' => $ca->id,
                    'type' => 'debit',
                    'amount' => $total,
                    'source' => 'purchase',
                    'description' => 'Purchase of ' . $name
                ]);

                $creditPortion = $total;
            } elseif ($request->payment_method === 'split') {
                $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['main_balance' => 0, 'earning_balance' => 0, 'credit_balance' => 0]);
                $ca = CreditAccount::where('user_id', $user->id)->first();
                if(!$ca || $ca->approval_status !== 'approved') return back()->with('error', 'Your credit account is not approved.');

                $mainBal = (float) $wallet->main_balance;
                $mainDeduct = min($total, max(0.0, $mainBal));
                $creditDeduct = round($total - $mainDeduct, 2);

                if ($ca->available_credit < $creditDeduct) {
                    return back()->with('error', 'Insufficient combined balance and credit limit. Need Rs.' . number_format($creditDeduct, 2) . ' from credit, but only Rs.' . number_format($ca->available_credit, 2) . ' is available.');
                }

                if ($mainDeduct > 0) {
                    $wallet->main_balance -= $mainDeduct;
                    $wallet->save();
                    WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'type' => 'debit',
                        'source' => 'purchase',
                        'amount' => $mainDeduct,
                        'description' => 'Order purchase (Wallet portion): ' . $name
                    ]);
                }

                if ($creditDeduct > 0) {
                    $ca->used_credit += $creditDeduct;
                    $ca->available_credit = max(0, $ca->credit_limit - $ca->used_credit);
                    $ca->save();

                    \App\Models\CreditTransaction::create([
                        'credit_account_id' => $ca->id,
                        'type' => 'debit',
                        'amount' => $creditDeduct,
                        'source' => 'purchase',
                        'description' => 'Purchase of ' . $name . ' (Credit portion)'
                    ]);

                    $creditPortion = $creditDeduct;
                }
            }

            $settings = $this->mlmService->getSettings();
            $defaultStatus = $settings['default_order_status'] ?? 'processing';

            $paidPortion = max(0, (float)$total - (float)$creditPortion);
            $order = Order::create([
                'user_id' => $user->id, 
                'total_amount' => $total, 
                'total_bv' => $total_bv, 
                'paid_amount' => $paidPortion,
                'credit_amount' => $creditPortion,
                'payment_method' => $request->payment_method, 
                'status' => $defaultStatus
            ]);
            
            // Notify User
            $user->notify(new \App\Notifications\OrderPlacedNotification($order));
            
            if ($request->type === 'product') {
                OrderItem::create(['order_id' => $order->id, 'product_id' => $item->id, 'quantity' => $quantity, 'price' => $item->price, 'bv' => $total_bv]);
                $item->decrement('stock', $quantity);
            } else {
                OrderItem::create(['order_id' => $order->id, 'package_id' => $item->id, 'quantity' => $quantity, 'price' => $item->price, 'bv' => $total_bv]);
            }
            
            // EMI generation if paid via credit or split
            if($creditPortion > 0){
                $this->mlmService->generateEmiSchedules($user, $order, $creditPortion);
            }

            // Commission distribution ONLY if status is completed
            if ($order->status === 'completed') {
                $this->mlmService->distributeOrderCommissions($order);
            }
            
            return redirect()->route('dashboard')->with('success', 'Order placed successfully!');
        });
    }

    public function orderHistory()
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)
            ->with(['items.product', 'items.package'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('orders.index', [
            'orders' => $orders,
            'page' => 'orders'
        ]);
    }
}
