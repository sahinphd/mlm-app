<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    protected function ensureAdmin()
    {
        $user = Auth::user();
        if (! $user || ! method_exists($user,'isAdmin') || ! $user->isAdmin()) abort(403);
    }

    public function index(Request $request)
    {
        $this->ensureAdmin();
        $q = User::query();
        if ($s = $request->input('search')) {
            $q->where('name','like','%'.$s.'%')->orWhere('email','like','%'.$s.'%');
        }
        $users = $q->orderBy('id','desc')->paginate(25);
        return response()->json($users);
    }

    public function show($id)
    {
        $this->ensureAdmin();
        $u = User::findOrFail($id);
        return response()->json(['data'=>$u]);
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();
        $data = $request->validate([
            'name'=>'required|string|max:255',
            'email'=>'required|email|unique:users,email',
            'password'=>'required|string|min:6',
            'role'=>'nullable|in:user,admin'
        ]);

        $role = $data['role'] ?? 'user';
        $super = env('SUPER_ADMIN_EMAIL','admin@example.com');
        if ($role==='admin' && Auth::user()->email !== $super) return response()->json(['error'=>'only super-admin may create admin'],403);

        $u = User::create(['name'=>$data['name'],'email'=>$data['email'],'password'=>bcrypt($data['password']),'role'=>$role]);
        return response()->json(['data'=>$u],201);
    }

    public function update(Request $request, $id)
    {
        $this->ensureAdmin();
        $u = User::findOrFail($id);
        $data = $request->validate(['name'=>'sometimes|string|max:255','email'=>'sometimes|email|unique:users,email,'.$u->id,'role'=>'sometimes|in:user,admin']);
        if (isset($data['role']) && $data['role']==='admin' && Auth::user()->email !== env('SUPER_ADMIN_EMAIL','admin@example.com')) {
            return response()->json(['error'=>'only super-admin may assign admin role'],403);
        }
        $u->update($data);
        return response()->json(['data'=>$u]);
    }

    public function destroy($id)
    {
        $this->ensureAdmin();
        $u = User::findOrFail($id);
        if (Auth::id() === $u->id) return response()->json(['error'=>'cannot delete self'],400);
        $u->delete();
        return response()->json(['success'=>true]);
    }
}
