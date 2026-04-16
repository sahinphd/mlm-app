@extends('admin.layout')

@section('content')
  <div class="container mx-auto p-4">
    <h1 class="text-2xl font-semibold mb-4">Admin Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="p-4 rounded border bg-white">Total Users: 0</div>
      <div class="p-4 rounded border bg-white">Total Orders: 0</div>
      <div class="p-4 rounded border bg-white">Wallet Balance: 0</div>
    </div>
  </div>
@endsection
@extends('layouts.app')

@section('content')
<div class="bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-semibold">Admin Dashboard</h1>
    <div class="mt-4 space-y-3">
        <a href="/admin/payments" class="block p-3 bg-gray-50 rounded">Manage Payment Requests</a>
        <a href="/api/admin/credit-accounts" class="block p-3 bg-gray-50 rounded">Credit Accounts (API)</a>
        <a href="/api/admin/payment-requests" class="block p-3 bg-gray-50 rounded">API: Payment Requests</a>
    </div>
</div>

@endsection
