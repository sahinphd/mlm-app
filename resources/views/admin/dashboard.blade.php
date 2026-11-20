@extends('admin.layout')

@section('content')
  <div class="container mx-auto p-4">
    <!-- Top Header & One-Click Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
      <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Admin Dashboard</h1>

      </div>
      <!-- <div class="flex items-center gap-2">
        <a href="{{ route('admin.customer-lookup.index') }}" 
           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-[#1555a8] hover:bg-[#12468d] active:scale-[0.99] text-white text-sm font-semibold rounded-xl shadow-xs hover:shadow transition-all group">
          <svg class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
          </svg>
          <span>Customer Lookup</span>
        </a>
      </div> -->
    </div>

    <!-- Customer Lookup Quick Access Bar -->
    <div class="mb-6 p-4 rounded-xl border border-blue-200 bg-gradient-to-r from-blue-50/90 via-indigo-50/40 to-white dark:from-gray-800 dark:via-gray-800/90 dark:to-gray-800/60 dark:border-gray-700 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div class="flex items-center gap-3.5">
        <div class="h-11 w-11 rounded-xl bg-[#1555a8] text-white flex items-center justify-center shadow-xs shrink-0">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
          </svg>
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h2 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white">Customer Lookup</h2>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-100 text-[#1555a8] dark:bg-blue-900/60 dark:text-blue-300">Single Screen Khata</span>
          </div>
          
        </div>
      </div>
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 shrink-0">
        <form action="{{ route('admin.customer-lookup.index') }}" method="GET" class="flex items-center gap-2">
          <input type="text" name="q" placeholder="Search customer ID, phone, email..." class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs sm:text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#1555a8]/20 focus:border-[#1555a8] w-full sm:w-60" />
          <button type="submit" class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-xs sm:text-sm font-semibold transition shrink-0">
            Find
          </button>
        </form>
        <a href="{{ route('admin.customer-lookup.index') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-[#1555a8] hover:bg-[#12468d] text-white text-xs sm:text-sm font-semibold rounded-lg shadow-xs hover:shadow transition-all whitespace-nowrap">
          <span>Open Lookup Screen</span>
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </a>
      </div>
    </div>

    @php
      use Illuminate\Support\Facades\Storage;
      $settingsFile = 'settings.json';
      $settings = Storage::disk('local')->exists($settingsFile) ? json_decode(Storage::disk('local')->get($settingsFile), true) : [];
      $currencyCode = $settings['currency'] ?? 'INR';
      $currencySymbols = [
        'INR' => '₹', 'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'JPY' => '¥', 'AUD' => 'A$', 'CAD' => 'C$'
      ];
      $symbol = $currencySymbols[$currencyCode] ?? $currencyCode;
    @endphp
    <!-- Quick Management Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <!-- Manage Products -->
      <a href="{{ route('admin.products') }}" 
         class="group p-4 rounded-xl border border-blue-200 bg-blue-50/80 hover:bg-blue-100/90 dark:bg-blue-950/25 dark:border-blue-900/50 dark:hover:bg-blue-900/35 shadow-xs hover:shadow-md transition-all duration-200 flex items-center justify-between">
        <div class="flex items-center gap-3.5">
          <div class="h-11 w-11 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-xs shrink-0 transition-transform duration-200 group-hover:scale-105">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
          </div>
          <div>
            <div class="font-bold text-sm sm:text-base text-blue-900 dark:text-blue-200">Manage Products</div>
            <div class="text-xs text-blue-600 dark:text-blue-400 mt-0.5">Add, edit, delete products</div>
          </div>
        </div>
        <svg class="w-4 h-4 text-blue-400 dark:text-blue-500 opacity-60 group-hover:opacity-100 group-hover:translate-x-1 transition-all shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </a>

      <!-- Manage Packages -->
      <a href="{{ route('admin.packages') }}" 
         class="group p-4 rounded-xl border border-purple-200 bg-purple-50/80 hover:bg-purple-100/90 dark:bg-purple-950/25 dark:border-purple-900/50 dark:hover:bg-purple-900/35 shadow-xs hover:shadow-md transition-all duration-200 flex items-center justify-between">
        <div class="flex items-center gap-3.5">
          <div class="h-11 w-11 rounded-xl bg-purple-600 text-white flex items-center justify-center shadow-xs shrink-0 transition-transform duration-200 group-hover:scale-105">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
          </div>
          <div>
            <div class="font-bold text-sm sm:text-base text-purple-900 dark:text-purple-200">Manage Packages</div>
            <div class="text-xs text-purple-600 dark:text-purple-400 mt-0.5">Create product bundles</div>
          </div>
        </div>
        <svg class="w-4 h-4 text-purple-400 dark:text-purple-500 opacity-60 group-hover:opacity-100 group-hover:translate-x-1 transition-all shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </a>

      <!-- Admin Shop -->
      <a href="{{ route('admin.shop.index') }}" 
         class="group p-4 rounded-xl border border-emerald-200 bg-emerald-50/80 hover:bg-emerald-100/90 dark:bg-emerald-950/25 dark:border-emerald-900/50 dark:hover:bg-emerald-900/35 shadow-xs hover:shadow-md transition-all duration-200 flex items-center justify-between">
        <div class="flex items-center gap-3.5">
          <div class="h-11 w-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-xs shrink-0 transition-transform duration-200 group-hover:scale-105">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
          </div>
          <div>
            <div class="font-bold text-sm sm:text-base text-emerald-900 dark:text-emerald-200">Admin Shop</div>
            <div class="text-xs text-emerald-600 dark:text-emerald-400 mt-0.5">Place orders for users</div>
          </div>
        </div>
        <svg class="w-4 h-4 text-emerald-400 dark:text-emerald-500 opacity-60 group-hover:opacity-100 group-hover:translate-x-1 transition-all shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <div class="p-4 rounded border bg-white shadow">
        <div class="text-sm text-gray-500">Total Users</div>
        <div class="text-2xl font-bold text-primary">{{ number_format($usersCount ?? 0) }}</div>
      </div>
      <div class="p-4 rounded border bg-white shadow">
        <div class="text-sm text-gray-500">Total Orders</div>
        <div class="text-2xl font-bold text-primary">{{ number_format($ordersCount ?? 0) }}</div>
      </div>
      <div class="p-4 rounded border bg-white shadow">
        <div class="text-sm text-gray-500">Total Wallet Balance</div>
        <div class="text-2xl font-bold text-primary">{{ ($symbol ?? '') . number_format($walletTotal ?? 0, 2) }}</div>
      </div>
    </div>

    <!-- Grouped Operational Metrics -->
    <div class="space-y-8 mb-8">
      <!-- 1. Revenue & Fulfillment -->
      <div>
        <h2 class="text-lg font-semibold mb-3 flex items-center gap-2">
          <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
          Revenue & Fulfillment
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div class="p-4 rounded border bg-white shadow border-l-4 border-l-emerald-600">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Completed Sales Value</div>
            <div class="text-xl font-bold text-gray-800">{{ ($symbol ?? '') . number_format($salesStats->value_completed ?? 0, 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">Orders: {{ number_format($salesStats->count_completed ?? 0) }}</div>
          </div>
          <div class="p-4 rounded border bg-white shadow border-l-4 border-l-green-500">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Delivered Value</div>
            <div class="text-xl font-bold text-gray-800">{{ ($symbol ?? '') . number_format($salesStats->value_delivered ?? 0, 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">Orders: {{ number_format($salesStats->count_delivered ?? 0) }}</div>
          </div>
          <div class="p-4 rounded border bg-white shadow border-l-4 border-l-blue-500">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">In-Shipping Value</div>
            <div class="text-xl font-bold text-gray-800">{{ ($symbol ?? '') . number_format($salesStats->value_shipped ?? 0, 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">Orders: {{ number_format($salesStats->count_shipped ?? 0) }}</div>
          </div>
          <div class="p-4 rounded border bg-white shadow border-l-4 border-l-indigo-400">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Processing Value</div>
            <div class="text-xl font-bold text-gray-800">{{ ($symbol ?? '') . number_format($salesStats->value_processing ?? 0, 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">Orders: {{ number_format($salesStats->count_processing ?? 0) }}</div>
          </div>
          <div class="p-4 rounded border bg-white shadow border-l-4 border-l-orange-300">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Pending Order Value</div>
            <div class="text-xl font-bold text-gray-800">{{ ($symbol ?? '') . number_format($salesStats->value_pending ?? 0, 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">Orders: {{ number_format($salesStats->count_pending ?? 0) }}</div>
          </div>
          <div class="p-4 rounded border bg-white shadow border-l-4 border-l-red-400">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Returned Value</div>
            <div class="text-xl font-bold text-gray-800">{{ ($symbol ?? '') . number_format($salesStats->value_returned ?? 0, 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">Orders: {{ number_format($salesStats->count_returned ?? 0) }}</div>
          </div>
        </div>
      </div>

      <!-- 2. Credit Risk Management -->
      <div>
        <h2 class="text-lg font-semibold mb-3 flex items-center gap-2">
          <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          Credit & Risk Management
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="p-4 rounded border bg-white shadow border-l-4 border-l-indigo-400">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Credit Exposure</div>
            <div class="text-xl font-bold text-gray-800">{{ ($symbol ?? '') . number_format($creditAccountStats->total_issued ?? 0, 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">Max potential risk</div>
          </div>
          <div class="p-4 rounded border bg-white shadow border-l-4 border-l-orange-400">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Outstanding Balance</div>
            <div class="text-xl font-bold text-gray-800">{{ ($symbol ?? '') . number_format($creditAccountStats->total_used ?? 0, 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">Credit to be collected</div>
          </div>
          <div class="p-4 rounded border bg-white shadow border-l-4 border-l-green-400">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Collected</div>
            <div class="text-xl font-bold text-gray-800">{{ ($symbol ?? '') . number_format($totalCreditCollected ?? 0, 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">Realized repayment</div>
          </div>
          <div class="p-4 rounded border bg-white shadow border-l-4 border-l-red-600">
            <div class="text-xs font-medium text-red-600 uppercase tracking-wider">Overdue EMI Value</div>
            <div class="text-xl font-bold text-red-600">{{ ($symbol ?? '') . number_format($overdueEmiValue ?? 0, 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">Immediate action required</div>
          </div>
        </div>
      </div>

      <!-- 3. User & Compliance -->
      <div>
        <h2 class="text-lg font-semibold mb-3 flex items-center gap-2">
          <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
          User & Compliance
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="p-4 rounded border bg-white shadow border-l-4 border-l-emerald-400">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Active Accounts</div>
            <div class="text-xl font-bold text-gray-800">{{ number_format($userAccountStats->active_users ?? 0) }}</div>
          </div>
          <div class="p-4 rounded border bg-white shadow border-l-4 border-l-orange-300">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Pending Accounts</div>
            <div class="text-xl font-bold text-gray-800">{{ number_format($userAccountStats->pending_users ?? 0) }}</div>
          </div>
          <div class="p-4 rounded border bg-white shadow border-l-4 border-l-red-500">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Blocked Accounts</div>
            <div class="text-xl font-bold text-gray-800">{{ number_format($userAccountStats->blocked_users ?? 0) }}</div>
          </div>
          <div class="p-4 rounded border bg-white shadow border-l-4 border-l-yellow-500">
            <div class="text-xs font-medium text-yellow-600 uppercase tracking-wider font-bold">Pending KYC</div>
            <div class="text-xl font-bold text-gray-800">{{ number_format($userAccountStats->kyc_pending ?? 0) }}</div>
            <div class="text-xs text-gray-400 mt-1">Awaiting review</div>
          </div>
        </div>
      </div>

      <!-- 4. Commissions & BV -->
      <div>
        <h2 class="text-lg font-semibold mb-3 flex items-center gap-2">
          <svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3-1.343-3-3S10.343 2 12 2s3 1.343 3 3-1.343 3-3 3zM6 20c0-3.866 3.582-7 8-7s8 3.134 8 7"/></svg>
          Commissions & Payouts
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="p-4 rounded border bg-white shadow border-l-4 border-l-orange-400">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Pending Payouts</div>
            <div class="text-xl font-bold text-gray-800">{{ ($symbol ?? '') . number_format($totalPendingWithdrawals ?? 0, 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">Cash flow liability</div>
          </div>
          <div class="p-4 rounded border bg-white shadow border-l-4 border-l-emerald-500">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Paid Commissions</div>
            <div class="text-xl font-bold text-gray-800">{{ ($symbol ?? '') . number_format($totalWithdrawnCommission ?? 0, 2) }}</div>
          </div>
          <div class="p-4 rounded border bg-white shadow border-l-4 border-l-teal-500">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total BV Issued</div>
            <div class="text-xl font-bold text-gray-800">{{ number_format($totalBvIssued ?? 0, 2) }}</div>
          </div>
          <div class="p-4 rounded border bg-white shadow border-l-4 border-l-cyan-500">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Withdrawn BV</div>
            <div class="text-xl font-bold text-gray-800">{{ number_format($totalWithdrawnBv ?? 0, 2) }}</div>
          </div>
        </div>
      </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
      <div class="bg-white rounded shadow p-4">
        <div class="flex items-center justify-between">
          <h3 class="text-sm text-gray-500">Signups</h3>
          <div class="flex-inline items-center gap-2">
            <input id="startDate" type="date" class="border rounded px-1 py-1 text-xs" />
            <input id="endDate" type="date" class="border rounded px-1 py-1 text-xs" />
            <button id="applyRange" class="bg-blue-600 text-white px-2 py-1 rounded text-xs">Apply</button>
          </div>
        </div>
        <div class="mt-3">
          <canvas id="signupsChart" width="400" height="120"></canvas>
        </div>
      </div>

      <div class="bg-white rounded shadow p-4">
        <h3 class="text-sm text-gray-500">Orders by Status</h3>
        <ul class="mt-2">
          @php $statuses = ['pending','paid','shipped','cancelled','completed']; @endphp
          @foreach($statuses as $s)
            <li class="flex justify-between py-1 border-b last:border-b-0">
              <span class="capitalize">{{ $s }}</span>
              <span>{{ number_format($ordersByStatus[$s] ?? 0) }}</span>
            </li>
          @endforeach
        </ul>
      </div>

      <div class="bg-white rounded shadow p-4">
        <div class="flex justify-between items-center">
          <h3 class="text-sm text-gray-500">Recent Payments</h3>
          <div>
            <a href="/admin/reports/export?type=pdf" class="text-sm text-blue-600 mr-2">Export PDF</a>
            <a href="/admin/reports/export?type=csv" class="text-sm text-gray-600">CSV</a>
          </div>
        </div>
        <div class="mt-2">
          @if(!empty($recentPayments) && count($recentPayments))
            <ul>
              @foreach($recentPayments as $p)
                <li class="py-1 border-b last:border-b-0 flex justify-between">
                  <div>
                    <div class="text-sm">{{ $p->user?->name ?? '—' }}</div>
                    <div class="text-xs text-gray-500">{{ $p->created_at->format('Y-m-d H:i') }}</div>
                  </div>
                  <div class="text-right">
                    <div class="font-medium">{{ ($symbol ?? '') . number_format($p->amount ?? 0,2) }}</div>
                    <div class="text-xs text-gray-500">{{ $p->type ?? 'payment' }}</div>
                  </div>
                </li>
              @endforeach
            </ul>
          @else
            <div class="text-sm text-gray-500">No payments data available.</div>
          @endif
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    (function(){
      const ctx = document.getElementById('signupsChart').getContext('2d');
      let labels = {!! json_encode($labels ?? []) !!};
      let data = {!! json_encode($data ?? []) !!};
      new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{
            label: 'Signups',
            data: data,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,0.1)',
            fill: true,
            tension: 0.3,
          }]
        },
        options: {
          responsive: true,
          plugins: { legend: { display: false } },
          scales: { y: { beginAtZero: true, precision:0 } }
        }
      });

      // date-range filter
      const startInput = document.getElementById('startDate');
      const endInput = document.getElementById('endDate');
      const applyBtn = document.getElementById('applyRange');

      // set defaults (last 7 days)
      const today = new Date();
      const prior = new Date(); prior.setDate(today.getDate() - 6);
      const fmt = d=>d.toISOString().slice(0,10);
      startInput.value = fmt(prior);
      endInput.value = fmt(today);

      applyBtn.addEventListener('click', ()=>{
        const s = startInput.value;
        const e = endInput.value;
        fetch(`/admin/reports/signup-data?start=${s}&end=${e}`)
          .then(r=>r.json())
          .then(json=>{
            labels = json.labels || [];
            data = json.data || [];
            // update chart
            const ch = Chart.getChart(ctx.canvas);
            ch.data.labels = labels;
            ch.data.datasets[0].data = data;
            ch.update();
            // update export links
            document.querySelectorAll('a[href*="/admin/reports/export"]').forEach(a=>{
              const url = new URL(a.href, window.location.origin);
              url.searchParams.set('start', s);
              url.searchParams.set('end', e);
              a.href = url.toString();
            });
          });
      });
    })();
  </script>
  
  <!-- Projects grid (Metronic-like cards) -->
  <div class="container mx-auto p-4">
    <h2 class="text-xl font-semibold mb-4">Projects</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      @foreach($projects ?? [] as $proj)
        <div class="bg-white rounded-lg border shadow-sm p-5">
          <div class="flex items-start justify-between">
            <div class="flex items-center gap-3">
              <div class="h-12 w-12 rounded-md bg-gray-100 flex items-center justify-center">
                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3-1.343-3-3S10.343 2 12 2s3 1.343 3 3-1.343 3-3 3zM6 20c0-3.866 3.582-7 8-7s8 3.134 8 7"/></svg>
              </div>
              <div>
                <div class="text-lg font-medium">{{ $proj['title'] }}</div>
                <div class="text-sm text-gray-500">{{ $proj['subtitle'] }}</div>
              </div>
            </div>
            <div>
              @if(isset($proj['status']))
                @php
                  $badge = 'bg-gray-100 text-gray-700';
                  if($proj['status']==='completed') $badge = 'bg-green-100 text-green-700';
                  if($proj['status']==='in-progress') $badge = 'bg-blue-100 text-blue-700';
                  if($proj['status']==='upcoming') $badge = 'bg-gray-50 text-gray-600';
                @endphp
                <span class="px-3 py-1 text-xs rounded-full {{ $badge }}">{{ ucfirst(str_replace('-',' ',$proj['status'])) }}</span>
              @endif
            </div>
          </div>

          <div class="mt-4 text-sm text-gray-600">Start: <strong class="text-gray-800">{{ $proj['start'] }}</strong> &nbsp; End: <strong class="text-gray-800">{{ $proj['end'] }}</strong></div>

          <div class="mt-4">
            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
              <div class="h-2 rounded-full bg-blue-500" style="width: {{ $proj['progress'] }}%"></div>
            </div>
            <div class="text-sm text-gray-500 mt-2">Progress: {{ $proj['progress'] }}%</div>
          </div>

          <div class="mt-4 flex items-center">
            <div class="flex -space-x-2">
              @if(!empty($proj['avatars']))
                @foreach($proj['avatars'] as $a)
                  <img src="{{ $a }}" class="h-8 w-8 rounded-full border-2 border-white" alt="avatar">
                @endforeach
              @endif
              @if(!empty($proj['extra']))
                <div class="h-8 w-8 rounded-full bg-green-500 text-white flex items-center justify-center text-xs font-semibold border-2 border-white">{{ $proj['extra'] }}</div>
              @endif
            </div>
            <div class="ml-auto text-sm text-gray-500">&nbsp;</div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
@endsection
