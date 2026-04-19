<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderAdminController extends Controller
{
    protected function ensureAdmin()
    {
        $user = Auth::user();
        if (! $user || ! method_exists($user,'isAdmin') || ! $user->isAdmin()) abort(403);
    }

    public function index(Request $request)
    {
        $this->ensureAdmin();
        $q = Order::with('user')->orderBy('created_at','desc');
        if ($s = $request->input('search')) {
            $q->whereHas('user', function($q2) use($s){ $q2->where('name','like','%'.$s.'%')->orWhere('email','like','%'.$s.'%'); });
        }
        return response()->json($q->paginate(25));
    }

    public function updateStatus(Request $request, $id)
    {
        $this->ensureAdmin();
        $o = Order::findOrFail($id);
        $data = $request->validate(['status'=>'required|string']);
        $o->status = $data['status'];
        $o->save();
        return response()->json(['data'=>$o]);
    }
}
