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
            'payment_method' => 'required|in:main_wallet,credit_wallet'
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
            // Payment handling
            if($request->payment_method === 'main_wallet'){
                $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['main_balance' => 0, 'earning_balance' => 0, 'credit_balance' => 0]);
                if($wallet->main_balance < $total) return back()->with('error', 'Insufficient main wallet balance.');
                $wallet->main_balance -= $total;
                $wallet->save();
                WalletTransaction::create(['wallet_id' => $wallet->id, 'type' => 'debit', 'source' => 'purchase', 'amount' => $total, 'description' => 'Order purchase: ' . $name]);
            } else {
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
                
                $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['main_balance' => 0, 'earning_balance' => 0, 'credit_balance' => 0]);
                WalletTransaction::create(['wallet_id' => $wallet->id, 'type' => 'debit', 'source' => 'purchase', 'amount' => $total, 'description' => 'Order paid using credit: ' . $name]);
            }

            $order = Order::create(['user_id' => $user->id, 'total_amount' => $total, 'total_bv' => $total_bv, 'payment_method' => $request->payment_method, 'status' => 'completed']);
            
            if ($request->type === 'product') {
                OrderItem::create(['order_id' => $order->id, 'product_id' => $item->id, 'quantity' => $quantity, 'price' => $item->price, 'bv' => $total_bv]);
                $item->decrement('stock', $quantity);
            } else {
                OrderItem::create(['order_id' => $order->id, 'package_id' => $item->id, 'quantity' => $quantity, 'price' => $item->price, 'bv' => $total_bv]);
            }
            
            // EMI generation if paid via credit
            if($request->payment_method === 'credit_wallet'){
                $installments = 4; // Default
                $interval = 7; // Default
                $inst_amount = round($total / $installments, 2);
                $remaining = $total - ($inst_amount * ($installments - 1));
                for($i=0; $i<$installments; $i++){
                    $amt = ($i==($installments-1)) ? $remaining : $inst_amount;
                    $due = Carbon::now()->addDays($interval * ($i+1));
                    EmiSchedule::create(['user_id' => $user->id, 'order_id' => $order->id, 'total_amount' => $total, 'installment_amount' => $amt, 'interval_days' => $interval, 'due_date' => $due, 'status' => 'pending']);
                }
            }

            // Commission distribution
            $this->mlmService->distributeOrderCommissions($order);
            
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
