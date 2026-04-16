@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6">
    <div class="bg-white rounded shadow p-6 text-center">
        <h2 class="text-2xl font-semibold mb-3">Dashboard</h2>
        <p class="mb-4">Welcome, {{ auth()->user()->name }} — please complete your profile and request a wallet top-up to get started.</p>
        <div class="flex items-center justify-center gap-4">
            <a href="/profile" class="px-5 py-3 rounded border">Complete Profile</a>
            <a href="/payments" class="px-5 py-3 rounded bg-blue-600 text-white">Request Wallet Top-up</a>
        </div>
    </div>
</div>

@endsection
