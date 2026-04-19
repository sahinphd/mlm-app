<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\CreditAccount;
use App\Models\EmiSchedule;
use App\Models\Commission;
use App\Services\MLMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class OrderController extends Controller
{
    protected $mlmService;

    public function __construct(MLMService $mlmService)
    {
        $this->mlmService = $mlmService;
    }

    public function index()
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)->with('items')->orderBy('created_at','desc')->get();
        return response()->json(['data'=>$orders]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:main_wallet,credit_wallet'
        ]);

        return DB::transaction(function() use($data,$user,$request){
            $total = 0; $total_bv = 0; $items = [];
            foreach($data['items'] as $it){
                $p = Product::findOrFail($it['product_id']);
                if($p->stock < $it['quantity']){
                    throw new \Exception('Product out of stock: '.$p->id);
                }
                $amount = $p->price * $it['quantity'];
                $bv = $p->bv * $it['quantity'];
                $total += $amount;
                $total_bv += $bv;
                $items[] = ['product'=>$p,'quantity'=>$it['quantity'],'amount'=>$amount,'bv'=>$bv];
            }

            // Payment handling
            if($data['payment_method'] === 'main_wallet'){
                $wallet = Wallet::firstOrCreate(['user_id'=>$user->id],['main_balance'=>0,'earning_balance'=>0,'credit_balance'=>0]);
                if($wallet->main_balance < $total) return response()->json(['message'=>'Insufficient main wallet'],400);
                $wallet->main_balance -= $total; $wallet->save();
                WalletTransaction::create(['wallet_id'=>$wallet->id,'type'=>'debit','source'=>'purchase','amount'=>$total,'description'=>'Order purchase']);
            } else {
                $ca = CreditAccount::firstOrCreate(['user_id'=>$user->id],['credit_limit'=>5000,'used_credit'=>0,'available_credit'=>5000,'approval_status'=>'pending']);
                if($ca->available_credit < $total) return response()->json(['message'=>'Insufficient credit'],400);
                $ca->used_credit += $total;
                $ca->available_credit = max(0, $ca->credit_limit - $ca->used_credit);
                $ca->save();

                \App\Models\CreditTransaction::create([
                    'credit_account_id' => $ca->id,
                    'type' => 'debit',
                    'amount' => $total,
                    'source' => 'purchase',
                    'description' => 'Order purchase'
                ]);

                // log as wallet transaction on credit_balance for record
                $wallet = Wallet::firstOrCreate(['user_id'=>$user->id],['main_balance'=>0,'earning_balance'=>0,'credit_balance'=>0]);
                WalletTransaction::create(['wallet_id'=>$wallet->id,'type'=>'debit','source'=>'purchase','amount'=>$total,'description'=>'Order paid using credit']);
            }

            $order = Order::create(['user_id'=>$user->id,'total_amount'=>$total,'total_bv'=>$total_bv,'payment_method'=>$data['payment_method'],'status'=>'completed']);

            foreach($items as $it){
                OrderItem::create(['order_id'=>$order->id,'product_id'=>$it['product']->id,'quantity'=>$it['quantity'],'price'=>$it['product']->price,'bv'=>$it['bv']]);
                // decrement stock
                $it['product']->stock -= $it['quantity']; $it['product']->save();
            }

            // EMI generation if paid via credit
            if($data['payment_method'] === 'credit_wallet'){
                $installments = (int)config('mlm.emi_installments', 4);
                $interval = (int)config('mlm.emi_interval_days', 7);
                $inst_amount = round($total / $installments,2);
                $remaining = $total - ($inst_amount * ($installments - 1));
                for($i=0;$i<$installments;$i++){
                    $amt = ($i==($installments-1)) ? $remaining : $inst_amount;
                    $due = Carbon::now()->addDays($interval * ($i+1));
                    EmiSchedule::create(['user_id'=>$user->id,'order_id'=>$order->id,'total_amount'=>$total,'installment_amount'=>$amt,'interval_days'=>$interval,'due_date'=>$due,'status'=>'pending']);
                }
            }

            // Commission distribution
            $this->mlmService->distributeOrderCommissions($order);

            return response()->json(['message'=>'Order placed','order'=>$order]);
        });
    }
}
