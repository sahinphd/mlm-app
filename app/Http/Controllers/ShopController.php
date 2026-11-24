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

    private function normalizeCart($cart): array
    {
        if (is_string($cart)) {
            $decoded = json_decode($cart, true);
            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }
            $cart = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
        }

        if (!is_array($cart)) {
            return [];
        }

        $normalized = [];
        foreach ($cart as $item) {
            if (is_array($item)) {
                $normalized[] = $item;
            } elseif (is_object($item)) {
                $normalized[] = (array)$item;
            }
        }

        return $normalized;
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

    public function syncCart(Request $request)
    {
        $cart = $this->normalizeCart($request->input('cart', []));
        session(['shop_cart' => $cart]);
        return response()->json(['success' => true, 'count' => count($cart)]);
    }

    public function checkout(Request $request)
    {
        if (!Auth::check()) {
            session()->put('url.intended', route('shop.checkout'));
            if ($request->isMethod('post') && $request->has('cart')) {
                session(['shop_cart' => $this->normalizeCart($request->input('cart'))]);
            }
            return redirect()->route('login')->with('info', 'Please log in or register to complete your order. Your cart items are safely saved!');
        }

        $user = Auth::user();
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['main_balance' => 0, 'earning_balance' => 0, 'credit_balance' => 0]);
        $creditAccount = CreditAccount::where('user_id', $user->id)->first();

        // If POST with cart payload, store in session
        if ($request->isMethod('post') && $request->has('cart')) {
            session(['shop_cart' => $this->normalizeCart($request->input('cart'))]);
        }

        $productId = $request->query('product_id') ?? $request->input('product_id');
        $packageId = $request->query('package_id') ?? $request->input('package_id');
        $quantity = (int) ($request->query('quantity') ?? $request->input('quantity', 1));
        if ($quantity < 1) $quantity = 1;

        $items = [];
        $total = 0;
        $totalBv = 0;

        if ($productId) {
            $product = Product::findOrFail($productId);
            $items[] = [
                'id' => $product->id,
                'name' => $product->name,
                'type' => 'product',
                'price' => (float)$product->price,
                'bv' => (float)$product->bv,
                'quantity' => $quantity,
                'subtotal' => (float)$product->price * $quantity,
                'subtotal_bv' => (float)$product->bv * $quantity,
                'image' => $product->image,
                'stock' => $product->stock
            ];
            $total += (float)$product->price * $quantity;
            $totalBv += (float)$product->bv * $quantity;
        } elseif ($packageId) {
            $package = Package::findOrFail($packageId);
            $items[] = [
                'id' => $package->id,
                'name' => $package->name,
                'type' => 'package',
                'price' => (float)$package->price,
                'bv' => (float)$package->bv,
                'quantity' => $quantity,
                'subtotal' => (float)$package->price * $quantity,
                'subtotal_bv' => (float)$package->bv * $quantity,
                'image' => $package->image,
                'stock' => 999
            ];
            $total += (float)$package->price * $quantity;
            $totalBv += (float)$package->bv * $quantity;
        } else {
            // Read items from session cart
            $cart = $this->normalizeCart(session('shop_cart', []));
            if (empty($cart)) {
                return redirect()->route('shop.index')->with('error', 'Your shopping cart is empty. Please select products to continue.');
            }

            foreach ($cart as $cartItem) {
                $type = $cartItem['type'] ?? 'product';
                $id = $cartItem['id'] ?? null;
                $qty = max(1, (int)($cartItem['quantity'] ?? 1));

                if ($type === 'package') {
                    $package = Package::find($id);
                    if ($package && $package->status === 'active') {
                        $items[] = [
                            'id' => $package->id,
                            'name' => $package->name,
                            'type' => 'package',
                            'price' => (float)$package->price,
                            'bv' => (float)$package->bv,
                            'quantity' => $qty,
                            'subtotal' => (float)$package->price * $qty,
                            'subtotal_bv' => (float)$package->bv * $qty,
                            'image' => $package->image,
                            'stock' => 999
                        ];
                        $total += (float)$package->price * $qty;
                        $totalBv += (float)$package->bv * $qty;
                    }
                } else {
                    $product = Product::find($id);
                    if ($product && $product->status === 'active') {
                        $items[] = [
                            'id' => $product->id,
                            'name' => $product->name,
                            'type' => 'product',
                            'price' => (float)$product->price,
                            'bv' => (float)$product->bv,
                            'quantity' => $qty,
                            'subtotal' => (float)$product->price * $qty,
                            'subtotal_bv' => (float)$product->bv * $qty,
                            'image' => $product->image,
                            'stock' => $product->stock
                        ];
                        $total += (float)$product->price * $qty;
                        $totalBv += (float)$product->bv * $qty;
                    }
                }
            }

            if (empty($items)) {
                return redirect()->route('shop.index')->with('error', 'The selected items are no longer available.');
            }
        }

        $firstItem = $items[0] ?? null;

        return view('usershop.checkout', [
            'items' => $items,
            'total' => $total,
            'total_bv' => $totalBv,
            'item' => (object)[
                'id' => $firstItem['id'] ?? 0,
                'name' => count($items) === 1 ? $firstItem['name'] : count($items) . ' items in cart',
                'price' => count($items) === 1 ? $firstItem['price'] : $total,
                'bv' => count($items) === 1 ? $firstItem['bv'] : $totalBv,
            ],
            'type' => count($items) === 1 ? $firstItem['type'] : 'cart',
            'quantity' => count($items) === 1 ? $firstItem['quantity'] : 1,
            'wallet' => $wallet,
            'creditAccount' => $creditAccount,
            'systemSettings' => \App\Http\Controllers\Admin\SettingController::getSystemSettings(),
            'page' => 'shop'
        ]);
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:main_wallet,credit_wallet,split'
        ]);

        $user = Auth::user();

        // 1. Gather all items to order
        $orderItemsData = [];
        if ($request->filled('item_id') && $request->filled('type')) {
            $qty = max(1, (int)$request->input('quantity', 1));
            $orderItemsData[] = [
                'id' => $request->item_id,
                'type' => $request->type,
                'quantity' => $qty
            ];
        } elseif ($request->has('items')) {
            $reqItems = $this->normalizeCart($request->input('items'));
            foreach ($reqItems as $it) {
                $qty = max(1, (int)($it['quantity'] ?? 1));
                $orderItemsData[] = [
                    'id' => $it['id'] ?? $it['product_id'] ?? $it['package_id'] ?? null,
                    'type' => $it['type'] ?? 'product',
                    'quantity' => $qty
                ];
            }
        }

        if (empty($orderItemsData) && session()->has('shop_cart')) {
            $sessionCart = $this->normalizeCart(session('shop_cart'));
            foreach ($sessionCart as $it) {
                $qty = max(1, (int)($it['quantity'] ?? 1));
                $orderItemsData[] = [
                    'id' => $it['id'] ?? $it['product_id'] ?? $it['package_id'] ?? null,
                    'type' => $it['type'] ?? 'product',
                    'quantity' => $qty
                ];
            }
        }

        if (empty($orderItemsData)) {
            return back()->with('error', 'No items selected for order.');
        }

        // 2. Validate items and verify stocks
        $resolvedItems = [];
        $total = 0;
        $total_bv = 0;
        $itemNames = [];

        foreach ($orderItemsData as $entry) {
            $id = $entry['id'];
            $type = $entry['type'];
            $qty = $entry['quantity'];

            if ($type === 'product') {
                $prod = Product::findOrFail($id);
                if ($prod->stock < $qty) {
                    return back()->with('error', "Product '{$prod->name}' is out of stock (available: {$prod->stock}).");
                }
                $itemSubtotal = $prod->price * $qty;
                $itemBv = $prod->bv * $qty;
                $total += $itemSubtotal;
                $total_bv += $itemBv;
                $itemNames[] = $prod->name . ($qty > 1 ? " (x{$qty})" : "");
                $resolvedItems[] = [
                    'model' => $prod,
                    'type' => 'product',
                    'quantity' => $qty,
                    'price' => $prod->price,
                    'bv' => $itemBv
                ];
            } else {
                $pkg = Package::findOrFail($id);
                $itemSubtotal = $pkg->price * $qty;
                $itemBv = $pkg->bv * $qty;
                $total += $itemSubtotal;
                $total_bv += $itemBv;
                $itemNames[] = $pkg->name . ($qty > 1 ? " (x{$qty})" : "");
                $resolvedItems[] = [
                    'model' => $pkg,
                    'type' => 'package',
                    'quantity' => $qty,
                    'price' => $pkg->price,
                    'bv' => $itemBv
                ];
            }
        }

        $orderSummaryName = implode(', ', $itemNames);

        return DB::transaction(function() use($user, $resolvedItems, $total, $total_bv, $request, $orderSummaryName) {
            $creditPortion = 0;

            // Payment handling
            if ($request->payment_method === 'main_wallet') {
                $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['main_balance' => 0, 'earning_balance' => 0, 'credit_balance' => 0]);
                if ($wallet->main_balance < $total) return back()->with('error', 'Insufficient main wallet balance.');
                $wallet->main_balance -= $total;
                $wallet->save();
                WalletTransaction::create([
                    'wallet_id' => $wallet->id, 
                    'type' => 'debit', 
                    'source' => 'purchase', 
                    'amount' => $total, 
                    'description' => 'Order purchase: ' . $orderSummaryName
                ]);
            } elseif ($request->payment_method === 'credit_wallet') {
                $ca = CreditAccount::where('user_id', $user->id)->first();
                if (!$ca || $ca->approval_status !== 'approved') return back()->with('error', 'Your credit account is not approved.');
                if ($ca->available_credit < $total) return back()->with('error', 'Insufficient credit limit.');

                $settings = \App\Http\Controllers\Admin\SettingController::getSystemSettings();
                $poolLimit = (float) ($settings['shop_initial_balance'] ?? 0);
                if (($settings['enforce_shop_credit_pool_limit'] ?? 'off') === 'on' && $poolLimit > 0) {
                    $activeCredit = (float) CreditAccount::where('approval_status', 'approved')->sum('used_credit');
                    if (($activeCredit + $total) > $poolLimit) {
                        $remaining = max(0.0, $poolLimit - $activeCredit);
                        return back()->with('error', 'Shop maximum allowable credit limit pool reached. Remaining system credit capacity is Rs.' . number_format($remaining, 2) . '.');
                    }
                }
                
                $ca->used_credit += $total;
                $ca->available_credit = max(0, $ca->credit_limit - $ca->used_credit);
                $ca->save();

                \App\Models\CreditTransaction::create([
                    'credit_account_id' => $ca->id,
                    'type' => 'debit',
                    'amount' => $total,
                    'source' => 'purchase',
                    'description' => 'Purchase of ' . $orderSummaryName
                ]);

                $creditPortion = $total;
            } elseif ($request->payment_method === 'split') {
                $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['main_balance' => 0, 'earning_balance' => 0, 'credit_balance' => 0]);
                $ca = CreditAccount::where('user_id', $user->id)->first();
                if (!$ca || $ca->approval_status !== 'approved') return back()->with('error', 'Your credit account is not approved.');

                $mainBal = (float) $wallet->main_balance;
                $mainDeduct = min($total, max(0.0, $mainBal));
                $creditDeduct = round($total - $mainDeduct, 2);

                if ($ca->available_credit < $creditDeduct) {
                    return back()->with('error', 'Insufficient combined balance and credit limit. Need Rs.' . number_format($creditDeduct, 2) . ' from credit, but only Rs.' . number_format($ca->available_credit, 2) . ' is available.');
                }

                $settings = \App\Http\Controllers\Admin\SettingController::getSystemSettings();
                $poolLimit = (float) ($settings['shop_initial_balance'] ?? 0);
                if (($settings['enforce_shop_credit_pool_limit'] ?? 'off') === 'on' && $poolLimit > 0) {
                    $activeCredit = (float) CreditAccount::where('approval_status', 'approved')->sum('used_credit');
                    if (($activeCredit + $creditDeduct) > $poolLimit) {
                        $remaining = max(0.0, $poolLimit - $activeCredit);
                        return back()->with('error', 'Shop maximum allowable credit limit pool reached. Remaining system credit capacity is Rs.' . number_format($remaining, 2) . '.');
                    }
                }

                if ($mainDeduct > 0) {
                    $wallet->main_balance -= $mainDeduct;
                    $wallet->save();
                    WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'type' => 'debit',
                        'source' => 'purchase',
                        'amount' => $mainDeduct,
                        'description' => 'Order purchase (Wallet portion): ' . $orderSummaryName
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
                        'description' => 'Purchase of ' . $orderSummaryName . ' (Credit portion)'
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
            
            // Create OrderItems and decrement product stocks
            foreach ($resolvedItems as $resItem) {
                if ($resItem['type'] === 'product') {
                    OrderItem::create([
                        'order_id' => $order->id, 
                        'product_id' => $resItem['model']->id, 
                        'quantity' => $resItem['quantity'], 
                        'price' => $resItem['price'], 
                        'bv' => $resItem['bv']
                    ]);
                    $resItem['model']->decrement('stock', $resItem['quantity']);
                } else {
                    OrderItem::create([
                        'order_id' => $order->id, 
                        'package_id' => $resItem['model']->id, 
                        'quantity' => $resItem['quantity'], 
                        'price' => $resItem['price'], 
                        'bv' => $resItem['bv']
                    ]);
                }
            }
            
            // EMI generation if paid via credit or split
            if ($creditPortion > 0) {
                $this->mlmService->generateEmiSchedules($user, $order, $creditPortion);
            }

            // Commission distribution ONLY if status is completed
            if ($order->status === 'completed') {
                $this->mlmService->distributeOrderCommissions($order);
            }
            
            // Clear cart from session
            session()->forget('shop_cart');

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
