<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function export(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) abort(403);

        $type = $request->input('type','csv');
        $start = $request->input('start');
        $end = $request->input('end');

        $q = DB::table('users')->select('id','name','email','phone','role','created_at');
        if ($start) $q->where('created_at','>=',$start);
        if ($end) $q->where('created_at','<=',$end);

        $rows = $q->orderBy('created_at','desc')->get();
        if ($type==='csv'){
            $csv = "id,name,email,phone,role,created_at\n";
            foreach($rows as $r){
                $csv .= implode(',',array_map(function($v){ return '"'.str_replace('"','""',(string)$v).'"'; },[(int)$r->id,$r->name,$r->email,$r->phone,$r->role,$r->created_at])) . "\n";
            }
            return response($csv,200,['Content-Type'=>'text/csv','Content-Disposition'=>'attachment; filename="users_export.csv"']);
        }
        return redirect()->back()->with('warning','PDF export not configured; please use CSV');
    }
}
