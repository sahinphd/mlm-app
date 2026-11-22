@extends('admin.layout')

@section('content')
  <div class="container mx-auto p-4 space-y-6">
    <!-- Top Header & Filter Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Admin Dashboard</h1>
        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5">
          Comprehensive business metrics, shop credit capital, and operational reports.
        </p>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-wrap items-center gap-2.5">
        <a href="{{ route('admin.dashboard.export', request()->query()) }}" 
           class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white text-xs sm:text-sm font-semibold rounded-lg shadow-xs transition-all">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <span>Export Summary (CSV)</span>
        </a>

        <a href="{{ route('admin.settings') }}" 
           class="inline-flex items-center gap-2 px-3.5 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-medium rounded-lg transition-all">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          <span>Credit Settings</span>
        </a>
      </div>
    </div>

    <!-- Date Range & Preset Filter Bar -->
    <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-xs">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <!-- Preset Filters -->
        <div class="flex flex-wrap items-center gap-1.5">
          <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 mr-1 uppercase tracking-wider">Report Filter:</span>
          
          <a href="{{ route('admin.dashboard', ['filter' => 'all']) }}" 
             class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $filter === 'all' ? 'bg-[#1555a8] text-white shadow-xs' : 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300' }}">
            All Total
          </a>

          <a href="{{ route('admin.dashboard', ['filter' => 'today']) }}" 
             class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $filter === 'today' ? 'bg-[#1555a8] text-white shadow-xs' : 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300' }}">
            Today (Date-wise)
          </a>

          <a href="{{ route('admin.dashboard', ['filter' => 'weekly']) }}" 
             class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $filter === 'weekly' ? 'bg-[#1555a8] text-white shadow-xs' : 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300' }}">
            Weekly
          </a>

          <a href="{{ route('admin.dashboard', ['filter' => 'monthly']) }}" 
             class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $filter === 'monthly' ? 'bg-[#1555a8] text-white shadow-xs' : 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300' }}">
            Monthly
          </a>
        </div>

        <!-- Custom Date Range Form -->
        <form action="{{ route('admin.dashboard') }}" method="GET" class="flex flex-wrap items-center gap-2">
          <input type="hidden" name="filter" value="custom">
          <div class="flex items-center gap-1.5">
            <span class="text-xs text-gray-500 dark:text-gray-400">From:</span>
            <input type="date" name="start_date" value="{{ $startDateFormatted }}" required 
                   class="px-2.5 py-1.5 text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none" />
          </div>
          <div class="flex items-center gap-1.5">
            <span class="text-xs text-gray-500 dark:text-gray-400">To:</span>
            <input type="date" name="end_date" value="{{ $endDateFormatted }}" required 
                   class="px-2.5 py-1.5 text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none" />
          </div>
          <button type="submit" class="px-3 py-1.5 bg-[#1555a8] hover:bg-[#12468d] text-white rounded-lg text-xs font-semibold shadow-xs transition-all">
            Apply Custom Range
          </button>
        </form>
      </div>

      <!-- Active Filter Status Indicator -->
      <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 flex flex-wrap items-center justify-between text-xs text-gray-600 dark:text-gray-300 gap-2">
        <div class="flex items-center gap-2">
          <span class="inline-flex items-center px-2 py-0.5 rounded-full font-medium bg-blue-100 text-[#1555a8] dark:bg-blue-900/50 dark:text-blue-300">
            Active Filter
          </span>
          <span class="font-bold text-gray-900 dark:text-white">{{ $filterLabel }}</span>
        </div>

        @if($filter !== 'all')
          <a href="{{ route('admin.dashboard', ['filter' => 'all']) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-semibold flex items-center gap-1">
            <span>Reset to All-Time Total</span>
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </a>
        @endif
      </div>
    </div>

    <!-- Customer Lookup Quick Access Bar -->
    <div class="p-4 rounded-xl border border-blue-200 bg-gradient-to-r from-blue-50/90 via-indigo-50/40 to-white dark:from-gray-800 dark:via-gray-800/90 dark:to-gray-800/60 dark:border-gray-700 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-4">
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
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Instant customer profile, ledger statement & credit adjustments</p>
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

    <!-- ======================================================== -->
    <!-- NEW FEATURE: Shop Initial Balance & Market Credit Capital Tracker -->
    <!-- ======================================================== -->
    <div class="rounded-2xl border-2 border-indigo-200 dark:border-indigo-900/60 bg-gradient-to-br from-white via-indigo-50/20 to-blue-50/30 dark:from-gray-800 dark:via-gray-800 dark:to-gray-800/90 shadow-md p-5 sm:p-6 transition-all">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-indigo-100 dark:border-gray-700 gap-3">
        <div class="flex items-center gap-3">
          <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-blue-600 text-white flex items-center justify-center shadow-xs">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white">Shop Initial Balance &amp; Market Credit Capital Tracker</h2>
              @if($poolUtilizationPercent < 65)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                  ● Healthy Utilization ({{ $poolUtilizationPercent }}%)
                </span>
              @elseif($poolUtilizationPercent <= 85)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                  ▲ Moderate Exposure ({{ $poolUtilizationPercent }}%)
                </span>
              @else
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300">
                  ⚠ High Risk / Near Limit ({{ $poolUtilizationPercent }}%)
                </span>
              @endif
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
              Live calculation of configured initial credit pool, market credit deployed, and remaining balance.
            </p>
          </div>
        </div>

        <a href="{{ route('admin.settings') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/40 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 rounded-lg text-xs font-semibold transition self-start sm:self-auto">
          <span>Manage Initial Balance</span>
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
      </div>

      <!-- Capital Metric Cards Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-5">
        <!-- 1. Shop Initial Balance -->
        <div class="p-4 rounded-xl border border-indigo-200 dark:border-indigo-900/50 bg-white dark:bg-gray-800 shadow-xs relative overflow-hidden">
          <div class="text-xs font-semibold text-indigo-700 dark:text-indigo-400 uppercase tracking-wider flex items-center justify-between">
            <span>Shop Initial Balance</span>
            <span class="text-[10px] bg-indigo-100 dark:bg-indigo-900/60 px-1.5 py-0.5 rounded font-bold">Configured Ceiling</span>
          </div>
          <div class="text-2xl font-black text-gray-900 dark:text-white mt-1">
            {{ ($symbol ?? '₹') . number_format($shopInitialBalance, 2) }}
          </div>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Maximum allowable credit limit in the system</p>
        </div>

        <!-- 2. Credit in the Market -->
        <div class="p-4 rounded-xl border border-orange-200 dark:border-orange-900/50 bg-white dark:bg-gray-800 shadow-xs relative overflow-hidden">
          <div class="text-xs font-semibold text-orange-700 dark:text-orange-400 uppercase tracking-wider flex items-center justify-between">
            <span>Credit in Market (Used)</span>
            <span class="text-[10px] bg-orange-100 dark:bg-orange-900/60 px-1.5 py-0.5 rounded font-bold">Active Liability</span>
          </div>
          <div class="text-2xl font-black text-orange-600 dark:text-orange-400 mt-1">
            {{ ($symbol ?? '₹') . number_format($creditInMarket, 2) }}
          </div>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Currently used by shoppers across all accounts</p>
        </div>

        <!-- 3. Available Credit Balance -->
        <div class="p-4 rounded-xl border border-emerald-300 dark:border-emerald-800 bg-emerald-50/60 dark:bg-emerald-950/20 shadow-xs relative overflow-hidden">
          <div class="text-xs font-semibold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider flex items-center justify-between">
            <span>Available Credit Balance</span>
            <span class="text-[10px] bg-emerald-200 dark:bg-emerald-900/80 text-emerald-900 dark:text-emerald-200 px-1.5 py-0.5 rounded font-bold">Remaining Pool</span>
          </div>
          <div class="text-2xl font-black text-emerald-700 dark:text-emerald-300 mt-1">
            {{ ($symbol ?? '₹') . number_format($availableCreditBalance, 2) }}
          </div>
          <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-1">Reduces as users shop till initial balance is reached</p>
        </div>

        <!-- 4. Total Approved Credit Limit -->
        <div class="p-4 rounded-xl border border-blue-200 dark:border-blue-900/50 bg-white dark:bg-gray-800 shadow-xs">
          <div class="text-xs font-semibold text-blue-700 dark:text-blue-400 uppercase tracking-wider">
            Total Approved Credit Limit
          </div>
          <div class="text-xl font-bold text-gray-800 dark:text-white mt-1">
            {{ ($symbol ?? '₹') . number_format($totalCreditApproved, 2) }}
          </div>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Combined limits granted to approved customers</p>
        </div>

        <!-- 5. Total Shopping Done -->
        <div class="p-4 rounded-xl border border-purple-200 dark:border-purple-900/50 bg-white dark:bg-gray-800 shadow-xs">
          <div class="text-xs font-semibold text-purple-700 dark:text-purple-400 uppercase tracking-wider flex items-center justify-between">
            <span>Total Shopping Done</span>
            @if($filter !== 'all')
              <span class="text-[10px] text-purple-600 font-bold">In Period</span>
            @else
              <span class="text-[10px] text-purple-600 font-bold">All-Time</span>
            @endif
          </div>
          <div class="text-xl font-bold text-gray-800 dark:text-white mt-1">
            {{ ($symbol ?? '₹') . number_format($totalShoppingDone, 2) }}
          </div>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            @if($filter !== 'all')
              All-Time Shopping: {{ ($symbol ?? '₹') . number_format($allTimeShoppingDone, 2) }}
            @else
              Gross order turnover across all payment channels
            @endif
          </p>
        </div>

        <!-- 6. Shopping Done on Credit -->
        <div class="p-4 rounded-xl border border-teal-200 dark:border-teal-900/50 bg-white dark:bg-gray-800 shadow-xs">
          <div class="text-xs font-semibold text-teal-700 dark:text-teal-400 uppercase tracking-wider flex items-center justify-between">
            <span>Shopping on Credit</span>
            @if($filter !== 'all')
              <span class="text-[10px] text-teal-600 font-bold">In Period</span>
            @else
              <span class="text-[10px] text-teal-600 font-bold">All-Time</span>
            @endif
          </div>
          <div class="text-xl font-bold text-gray-800 dark:text-white mt-1">
            {{ ($symbol ?? '₹') . number_format($totalCreditShopping, 2) }}
          </div>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            @if($filter !== 'all')
              All-Time Credit Shopping: {{ ($symbol ?? '₹') . number_format($allTimeCreditShopping, 2) }}
            @else
              Purchases financed via Credit Wallet or Split Credit
            @endif
          </p>
        </div>
      </div>

      <!-- Credit Pool Capacity Meter -->
      <div class="mt-5 pt-4 border-t border-indigo-100 dark:border-gray-700">
        <div class="flex items-center justify-between text-xs mb-1.5">
          <span class="font-semibold text-gray-700 dark:text-gray-300">
            System Credit Pool Utilization: <span class="font-bold text-indigo-700 dark:text-indigo-400">{{ $poolUtilizationPercent }}%</span>
          </span>
          <span class="text-gray-500 dark:text-gray-400">
            Remaining Capacity: <strong class="text-emerald-700 dark:text-emerald-400">{{ ($symbol ?? '₹') . number_format($availableCreditBalance, 2) }}</strong>
          </span>
        </div>
        <div class="h-3 w-full bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
          <div class="h-full rounded-full transition-all duration-500 {{ $poolUtilizationPercent > 85 ? 'bg-red-500' : ($poolUtilizationPercent > 65 ? 'bg-amber-500' : 'bg-emerald-500') }}" 
               style="width: {{ min(100, $poolUtilizationPercent) }}%"></div>
        </div>

        <!-- Period Activity Sub-strip -->
        <div class="mt-3 flex flex-wrap items-center justify-between text-xs text-gray-600 dark:text-gray-400 bg-white/70 dark:bg-gray-700/50 p-2.5 rounded-lg border border-gray-100 dark:border-gray-600 gap-2">
          <div>
            <span>Credit Disbursed in Period: </span>
            <strong class="text-gray-900 dark:text-white">{{ ($symbol ?? '₹') . number_format($periodCreditDisbursed, 2) }}</strong>
          </div>
          <div>
            <span>Repayments Collected in Period: </span>
            <strong class="text-emerald-700 dark:text-emerald-400">{{ ($symbol ?? '₹') . number_format($periodCreditCollected, 2) }}</strong>
          </div>
          <div>
            <span>Overdue in Period: </span>
            <strong class="text-red-600 dark:text-red-400">{{ ($symbol ?? '₹') . number_format($overdueEmiValue, 2) }}</strong>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Management Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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

    <!-- Core Operational KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-xs">
        <div class="text-sm text-gray-500 dark:text-gray-400 font-medium">
          @if($filter !== 'all') New Signups in Period @else Total Users @endif
        </div>
        <div class="text-2xl font-black text-gray-900 dark:text-white mt-1">
          {{ number_format($usersCount) }}
        </div>
        @if($filter !== 'all')
          <div class="text-xs text-gray-400 mt-1">All-time Users: {{ number_format($allUsersCount) }}</div>
        @endif
      </div>

      <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-xs">
        <div class="text-sm text-gray-500 dark:text-gray-400 font-medium">
          @if($filter !== 'all') Orders Placed in Period @else Total Orders @endif
        </div>
        <div class="text-2xl font-black text-gray-900 dark:text-white mt-1">
          {{ number_format($ordersCount) }}
        </div>
        @if($filter !== 'all')
          <div class="text-xs text-gray-400 mt-1">All-time Orders: {{ number_format($allOrdersCount) }}</div>
        @endif
      </div>

      <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-xs">
        <div class="text-sm text-gray-500 dark:text-gray-400 font-medium">Total Wallet Liability</div>
        <div class="text-2xl font-black text-gray-900 dark:text-white mt-1">
          {{ ($symbol ?? '₹') . number_format($walletTotal ?? 0, 2) }}
        </div>
        <div class="text-xs text-gray-400 mt-1">Sum of main, earning & credit balances</div>
      </div>
    </div>

    <!-- Grouped Operational Metrics -->
    <div class="space-y-6">
      <!-- 1. Revenue & Fulfillment -->
      <div>
        <h2 class="text-base font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
          <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
          <span>Revenue & Fulfillment</span>
          <span class="text-xs font-normal text-gray-500">({{ $filterLabel }})</span>
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div class="p-4 rounded-xl border bg-white dark:bg-gray-800 shadow-xs border-l-4 border-l-emerald-600">
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Completed Sales Value</div>
            <div class="text-xl font-bold text-gray-800 dark:text-white mt-1">{{ ($symbol ?? '₹') . number_format($salesStats->value_completed ?? 0, 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">Orders: {{ number_format($salesStats->count_completed ?? 0) }}</div>
          </div>
          <div class="p-4 rounded-xl border bg-white dark:bg-gray-800 shadow-xs border-l-4 border-l-green-500">
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Delivered Value</div>
            <div class="text-xl font-bold text-gray-800 dark:text-white mt-1">{{ ($symbol ?? '₹') . number_format($salesStats->value_delivered ?? 0, 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">Orders: {{ number_format($salesStats->count_delivered ?? 0) }}</div>
          </div>
          <div class="p-4 rounded-xl border bg-white dark:bg-gray-800 shadow-xs border-l-4 border-l-blue-500">
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">In-Shipping Value</div>
            <div class="text-xl font-bold text-gray-800 dark:text-white mt-1">{{ ($symbol ?? '₹') . number_format($salesStats->value_shipped ?? 0, 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">Orders: {{ number_format($salesStats->count_shipped ?? 0) }}</div>
          </div>
          <div class="p-4 rounded-xl border bg-white dark:bg-gray-800 shadow-xs border-l-4 border-l-indigo-400">
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Processing Value</div>
            <div class="text-xl font-bold text-gray-800 dark:text-white mt-1">{{ ($symbol ?? '₹') . number_format($salesStats->value_processing ?? 0, 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">Orders: {{ number_format($salesStats->count_processing ?? 0) }}</div>
          </div>
          <div class="p-4 rounded-xl border bg-white dark:bg-gray-800 shadow-xs border-l-4 border-l-orange-400">
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pending Order Value</div>
            <div class="text-xl font-bold text-gray-800 dark:text-white mt-1">{{ ($symbol ?? '₹') . number_format($salesStats->value_pending ?? 0, 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">Orders: {{ number_format($salesStats->count_pending ?? 0) }}</div>
          </div>
          <div class="p-4 rounded-xl border bg-white dark:bg-gray-800 shadow-xs border-l-4 border-l-red-500">
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Returned Value</div>
            <div class="text-xl font-bold text-gray-800 dark:text-white mt-1">{{ ($symbol ?? '₹') . number_format($salesStats->value_returned ?? 0, 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">Orders: {{ number_format($salesStats->count_returned ?? 0) }}</div>
          </div>
        </div>
      </div>

      <!-- 2. Credit Risk Management Detailed -->
      <div>
        <h2 class="text-base font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
          <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          <span>Credit Exposure & Repayments</span>
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="p-4 rounded-xl border bg-white dark:bg-gray-800 shadow-xs border-l-4 border-l-indigo-400">
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Credit Exposure</div>
            <div class="text-xl font-bold text-gray-800 dark:text-white mt-1">{{ ($symbol ?? '₹') . number_format($totalCreditApproved ?? 0, 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">Sum of approved customer limits</div>
          </div>
          <div class="p-4 rounded-xl border bg-white dark:bg-gray-800 shadow-xs border-l-4 border-l-orange-400">
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Outstanding Balance</div>
            <div class="text-xl font-bold text-gray-800 dark:text-white mt-1">{{ ($symbol ?? '₹') . number_format($creditInMarket ?? 0, 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">Total credit to be recovered</div>
          </div>
          <div class="p-4 rounded-xl border bg-white dark:bg-gray-800 shadow-xs border-l-4 border-l-emerald-500">
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              @if($filter !== 'all') Period Collections @else Total Realized Collections @endif
            </div>
            <div class="text-xl font-bold text-gray-800 dark:text-white mt-1">
              {{ ($symbol ?? '₹') . number_format($filter !== 'all' ? $periodCreditCollected : $totalCreditCollected, 2) }}
            </div>
            <div class="text-xs text-gray-400 mt-1">Realized repayment collections</div>
          </div>
          <div class="p-4 rounded-xl border bg-white dark:bg-gray-800 shadow-xs border-l-4 border-l-red-600">
            <div class="text-xs font-semibold text-red-600 uppercase tracking-wider">Overdue EMI Value</div>
            <div class="text-xl font-bold text-red-600 mt-1">{{ ($symbol ?? '₹') . number_format($overdueEmiValue ?? 0, 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">Installments past due date</div>
          </div>
        </div>
      </div>

      <!-- 3. User & Compliance Pipeline -->
      <div>
        <h2 class="text-base font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
          <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
          <span>User & Compliance Pipeline</span>
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="p-4 rounded-xl border bg-white dark:bg-gray-800 shadow-xs border-l-4 border-l-emerald-400">
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Active Accounts</div>
            <div class="text-xl font-bold text-gray-800 dark:text-white mt-1">{{ number_format($userAccountStats->active_users ?? 0) }}</div>
          </div>
          <div class="p-4 rounded-xl border bg-white dark:bg-gray-800 shadow-xs border-l-4 border-l-orange-300">
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pending Accounts</div>
            <div class="text-xl font-bold text-gray-800 dark:text-white mt-1">{{ number_format($userAccountStats->pending_users ?? 0) }}</div>
          </div>
          <div class="p-4 rounded-xl border bg-white dark:bg-gray-800 shadow-xs border-l-4 border-l-red-500">
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Blocked Accounts</div>
            <div class="text-xl font-bold text-gray-800 dark:text-white mt-1">{{ number_format($userAccountStats->blocked_users ?? 0) }}</div>
          </div>
          <div class="p-4 rounded-xl border bg-white dark:bg-gray-800 shadow-xs border-l-4 border-l-yellow-500">
            <div class="text-xs font-semibold text-yellow-600 uppercase tracking-wider font-bold">Pending KYC</div>
            <div class="text-xl font-bold text-gray-800 dark:text-white mt-1">{{ number_format($userAccountStats->kyc_pending ?? 0) }}</div>
            <div class="text-xs text-gray-400 mt-1">Awaiting KYC verification</div>
          </div>
        </div>
      </div>

      <!-- 4. Commissions & BV -->
      <div>
        <h2 class="text-base font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
          <svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3-1.343-3-3S10.343 2 12 2s3 1.343 3 3-1.343 3-3 3zM6 20c0-3.866 3.582-7 8-7s8 3.134 8 7"/></svg>
          <span>Commissions & Payouts</span>
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="p-4 rounded-xl border bg-white dark:bg-gray-800 shadow-xs border-l-4 border-l-orange-400">
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pending Payouts</div>
            <div class="text-xl font-bold text-gray-800 dark:text-white mt-1">{{ ($symbol ?? '₹') . number_format($totalPendingWithdrawals ?? 0, 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">Cash flow payout liability</div>
          </div>
          <div class="p-4 rounded-xl border bg-white dark:bg-gray-800 shadow-xs border-l-4 border-l-emerald-500">
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              @if($filter !== 'all') Paid in Period @else Total Paid Commissions @endif
            </div>
            <div class="text-xl font-bold text-gray-800 dark:text-white mt-1">{{ ($symbol ?? '₹') . number_format($totalWithdrawnCommission ?? 0, 2) }}</div>
          </div>
          <div class="p-4 rounded-xl border bg-white dark:bg-gray-800 shadow-xs border-l-4 border-l-teal-500">
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              @if($filter !== 'all') BV Issued in Period @else Total BV Issued @endif
            </div>
            <div class="text-xl font-bold text-gray-800 dark:text-white mt-1">{{ number_format($totalBvIssued ?? 0, 2) }}</div>
          </div>
          <div class="p-4 rounded-xl border bg-white dark:bg-gray-800 shadow-xs border-l-4 border-l-cyan-500">
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              @if($filter !== 'all') Withdrawn BV in Period @else Withdrawn BV @endif
            </div>
            <div class="text-xl font-bold text-gray-800 dark:text-white mt-1">{{ number_format($totalWithdrawnBv ?? 0, 2) }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Chart, Status Breakdown & Recent Transactions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Signups Line Chart -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-xs p-5">
        <div class="flex items-center justify-between mb-3">
          <div>
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">User Signups Trend</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $filterLabel }}</p>
          </div>
          <span class="text-xs bg-blue-100 text-[#1555a8] dark:bg-blue-900/60 dark:text-blue-300 font-semibold px-2 py-0.5 rounded">
            {{ number_format($usersCount) }} Signups
          </span>
        </div>
        <div class="mt-4 h-48">
          <canvas id="signupsChart"></canvas>
        </div>
      </div>

      <!-- Orders by Status -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-xs p-5">
        <div class="flex items-center justify-between mb-3">
          <div>
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Orders by Status</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $filterLabel }}</p>
          </div>
          <span class="text-xs bg-indigo-100 text-indigo-800 dark:bg-indigo-900/60 dark:text-indigo-300 font-semibold px-2 py-0.5 rounded">
            {{ number_format($ordersCount) }} Total
          </span>
        </div>
        <ul class="mt-2 divide-y divide-gray-100 dark:divide-gray-700 text-sm">
          @php 
            $statusLabels = [
              'completed' => ['label' => 'Completed', 'color' => 'text-emerald-600'],
              'delivered' => ['label' => 'Delivered', 'color' => 'text-green-600'],
              'shipped' => ['label' => 'Shipped', 'color' => 'text-blue-600'],
              'processing' => ['label' => 'Processing', 'color' => 'text-indigo-600'],
              'pending' => ['label' => 'Pending', 'color' => 'text-amber-600'],
              'returned' => ['label' => 'Returned', 'color' => 'text-red-600'],
            ]; 
          @endphp
          @foreach($statusLabels as $sKey => $sMeta)
            <li class="flex items-center justify-between py-2.5">
              <span class="font-medium {{ $sMeta['color'] }} flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-current"></span>
                <span>{{ $sMeta['label'] }}</span>
              </span>
              <span class="font-bold text-gray-800 dark:text-white">
                {{ number_format($ordersByStatus[$sKey] ?? 0) }}
              </span>
            </li>
          @endforeach
        </ul>
      </div>

      <!-- Recent Payments -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-xs p-5">
        <div class="flex justify-between items-center mb-3">
          <div>
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Recent Payments</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $filterLabel }}</p>
          </div>
          <a href="{{ route('admin.dashboard.export', request()->query()) }}" class="text-xs text-blue-600 dark:text-blue-400 font-semibold hover:underline">
            Export CSV
          </a>
        </div>
        <div class="mt-2">
          @if(!empty($recentPayments) && count($recentPayments))
            <ul class="divide-y divide-gray-100 dark:divide-gray-700">
              @foreach($recentPayments as $p)
                <li class="py-2.5 flex justify-between items-center text-xs">
                  <div>
                    <div class="font-semibold text-gray-900 dark:text-white">{{ $p->user?->name ?? 'User #' . $p->user_id }}</div>
                    <div class="text-gray-400">{{ $p->created_at?->format('Y-m-d H:i') }}</div>
                  </div>
                  <div class="text-right">
                    <div class="font-bold text-gray-900 dark:text-white">{{ ($symbol ?? '₹') . number_format($p->amount ?? 0, 2) }}</div>
                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] uppercase font-semibold {{ $p->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                      {{ $p->status ?? 'pending' }}
                    </span>
                  </div>
                </li>
              @endforeach
            </ul>
          @else
            <div class="py-6 text-center text-xs text-gray-400">No payment records found for this period.</div>
          @endif
        </div>
      </div>
    </div>

    <!-- Projects Grid -->
    @if(!empty($projects) && count($projects))
      <div class="pt-2">
        <h2 class="text-base font-bold text-gray-900 dark:text-white mb-3">Active Projects</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          @foreach($projects as $proj)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-xs p-5">
              <div class="flex items-start justify-between">
                <div>
                  <div class="text-base font-bold text-gray-900 dark:text-white">{{ $proj['title'] }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ $proj['subtitle'] }}</div>
                </div>
                @if(isset($proj['status']))
                  @php
                    $badge = 'bg-gray-100 text-gray-700';
                    if($proj['status']==='completed') $badge = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300';
                    if($proj['status']==='in-progress') $badge = 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300';
                    if($proj['status']==='upcoming') $badge = 'bg-gray-100 text-gray-600';
                  @endphp
                  <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $badge }}">
                    {{ ucfirst(str_replace('-',' ',$proj['status'])) }}
                  </span>
                @endif
              </div>

              <div class="mt-3 text-xs text-gray-600 dark:text-gray-400">
                Timeline: <strong class="text-gray-800 dark:text-white">{{ $proj['start'] }}</strong> to <strong class="text-gray-800 dark:text-white">{{ $proj['end'] }}</strong>
              </div>

              <div class="mt-3">
                <div class="h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                  <div class="h-2 rounded-full bg-blue-600" style="width: {{ $proj['progress'] }}%"></div>
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 flex justify-between">
                  <span>Progress</span>
                  <span class="font-bold">{{ $proj['progress'] }}%</span>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @endif
  </div>

  <!-- Chart.js Setup -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function(){
      const ctx = document.getElementById('signupsChart');
      if (!ctx) return;
      
      const labels = {!! json_encode($labels ?? []) !!};
      const data = {!! json_encode($data ?? []) !!};

      new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{
            label: 'Signups',
            data: data,
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37, 99, 235, 0.1)',
            fill: true,
            tension: 0.35,
            borderWidth: 2,
            pointRadius: labels.length > 30 ? 0 : 3,
            pointHoverRadius: 5
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { 
            legend: { display: false },
            tooltip: {
              mode: 'index',
              intersect: false
            }
          },
          scales: { 
            y: { 
              beginAtZero: true, 
              precision: 0,
              ticks: { font: { size: 10 } }
            },
            x: {
              ticks: { 
                font: { size: 10 },
                maxRotation: 45,
                minRotation: 0,
                autoSkip: true,
                maxTicksLimit: 12
              }
            }
          }
        }
      });
    });
  </script>
@endsection
