<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    protected function ensureAdmin(){
        $u = Auth::user(); if(! $u || ! method_exists($u,'isAdmin') || ! $u->isAdmin()) abort(403);
    }

    public function stats(Request $request)
    {
        $this->ensureAdmin();
        $users = DB::table('users')->count();
        $orders = DB::table('orders')->count();
        $wallet = DB::table('wallets')->selectRaw('COALESCE(SUM(COALESCE(main_balance,0)+COALESCE(earning_balance,0)+COALESCE(credit_balance,0)),0) as total')->value('total');
        return response()->json(['users'=>$users,'orders'=>$orders,'wallet_total'=>$wallet]);
    }

    public function export(Request $request)
    {
        $this->ensureAdmin();
        $type = $request->input('type','csv');
        $start = $request->input('start');
        $end = $request->input('end');

        $q = DB::table('users')->select('id','name','email','phone','role','created_at');
        if ($start) $q->where('created_at','>=',$start);
        if ($end) $q->where('created_at','<=',$end);

        $rows = $q->orderBy('created_at','desc')->get();

        if ($type === 'csv') {
            $csv = "id,name,email,phone,role,created_at\n";
            foreach($rows as $r){
                $csv .= implode(',',array_map(function($v){ return '"'.str_replace('"','""',(string)$v).'"'; },[(int)$r->id,$r->name,$r->email,$r->phone,$r->role,$r->created_at])) . "\n";
            }
            return response($csv,200,['Content-Type'=>'text/csv','Content-Disposition'=>'attachment; filename="users_export.csv"']);
        }

        // PDF support via barryvdh/laravel-dompdf
        if ($type === 'pdf' && class_exists(\Barryvdh\DomPDF\Facade::class)) {
            $html = view('admin.reports.users_pdf', ['users'=>$rows])->render();
            $pdf = \Barryvdh\DomPDF\Facade::loadHTML($html);
            return $pdf->stream('users.pdf');
        }

        return response()->json(['message'=>'PDF export not configured; install barryvdh/laravel-dompdf or use CSV'],400);
    }

    public function signupData(Request $request)
    {
        $this->ensureAdmin();
        $start = $request->input('start') ? \Illuminate\Support\Carbon::parse($request->input('start'))->startOfDay() : now()->subDays(6)->startOfDay();
        $end = $request->input('end') ? \Illuminate\Support\Carbon::parse($request->input('end'))->endOfDay() : now()->endOfDay();

        $days = [];
        $labels = [];
        $data = [];
        $period = \Illuminate\Support\CarbonPeriod::create($start, '1 day', $end);
        foreach($period as $d){
            $labels[] = $d->format('M d');
            $data[] = DB::table('users')->whereBetween('created_at', [$d->copy()->startOfDay(), $d->copy()->endOfDay()])->count();
        }

        return response()->json(['labels'=>$labels,'data'=>$data]);
    }
}
