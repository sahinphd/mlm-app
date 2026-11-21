@extends('admin.layout')

@section('content')
<div class="mx-auto max-w-3xl font-sans transition-all duration-150" 
     :class="customer ? 'pb-28' : 'pb-8'"
     x-data="singleScreenKhataApp({
        initialCustomer: {{ $initialCustomer ? json_encode($initialCustomer) : 'null' }},
        initialTransactions: {{ $initialTransactions ? json_encode($initialTransactions) : '[]' }},
        initialPagination: {{ $initialPagination ? json_encode($initialPagination) : 'null' }},
        routes: {
            search: '{{ route('admin.customer-lookup.search') }}',
            addMoney: '{{ url('/admin/customer-lookup') }}/:id/add-money',
            shopDeduct: '{{ url('/admin/customer-lookup') }}/:id/shop-deduct',
            updateCreditLimit: '{{ url('/admin/customer-lookup') }}/:id/update-credit-limit',
            transactions: '{{ url('/admin/customer-lookup') }}/:id/transactions'
        },
        csrfToken: '{{ csrf_token() }}'
     })" 
     x-init="init()"
     x-cloak>

    <!-- Global Floating Toast Notification -->
    <div x-show="toast.show" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="fixed top-6 right-6 z-99999 max-w-sm rounded-lg p-4 shadow-xl flex items-center justify-between text-sm font-semibold"
         :class="toast.type === 'success' ? 'bg-[#15803d] text-white' : 'bg-[#b91c1c] text-white'">
        <div class="flex items-center gap-2">
            <span x-text="toast.message"></span>
        </div>
        <button @click="toast.show = false" class="text-white/80 hover:text-white font-bold ml-4">✕</button>
    </div>

    <!-- ============================================================== -->
    <!-- TOP UNIFIED SEARCH & QUICK SWITCH BAR (ALWAYS VISIBLE)        -->
    <!-- ============================================================== -->
    <div class="mb-4 rounded-xl border border-stroke bg-white p-3 shadow-xs dark:border-strokedark dark:bg-boxdark">
        <!-- Live on-key-up Search Input -->
        <div class="relative" @click.outside="dropdownOpen = false">
            <div class="relative flex items-center">
                <!-- Search Icon / Spinner -->
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                    <template x-if="!keyUpLoading">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </template>
                    <template x-if="keyUpLoading">
                        <svg class="w-4 h-4 animate-spin text-[#1555a8]" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </template>
                </div>

                <!-- Input Field -->
                <input type="text"
                       x-model="searchQuery"
                       x-ref="searchInput"
                       @input.debounce.200ms="onKeyUpSearch()"
                       @keydown.down.prevent="navigateDropdown('down')"
                       @keydown.up.prevent="navigateDropdown('up')"
                       @keydown.enter.prevent="selectHighlightedOrSearch()"
                       @keydown.escape.prevent="dropdownOpen = false"
                       @focus="if(searchQuery.trim().length >= 1) dropdownOpen = true"
                       placeholder="Search customer by ID, Mobile, or Email (live on key up)..."
                       autocomplete="off"
                       class="w-full rounded-lg border border-gray-200 bg-gray-50/70 py-2.5 pl-10 pr-20 text-xs sm:text-sm font-medium text-black outline-none focus:border-[#1555a8] focus:bg-white dark:border-strokedark dark:bg-meta-4/30 dark:text-white dark:focus:border-[#1555a8] transition" />

                <!-- Action Buttons (Scan QR + Find) -->
                <div class="absolute inset-y-1 right-1 flex items-center gap-1.5">
                    <template x-if="searchQuery">
                        <button type="button" 
                                @click="searchQuery = ''; matches = []; dropdownOpen = false"
                                class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            ✕
                        </button>
                    </template>
                    <button type="button"
                            @click="openQrScannerModal()"
                            class="rounded-md bg-emerald-600 hover:bg-emerald-700 px-2.5 py-1.5 text-xs font-bold text-white transition flex items-center gap-1 shadow-xs"
                            title="Scan Customer QR Code">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        <span class="hidden sm:inline">Scan QR</span>
                    </button>
                    <button type="button"
                            @click="selectHighlightedOrSearch()"
                            class="rounded-md bg-[#1555a8] px-3 py-1.5 text-xs font-bold text-white hover:bg-[#12468d] transition">
                        Find
                    </button>
                </div>
            </div>

            <!-- Live On-Key-Up Suggestions Dropdown -->
            <div x-show="dropdownOpen && (matches.length > 0 || (searchQuery.trim().length >= 1 && !keyUpLoading && noMatchesFound))"
                 x-transition
                 class="absolute left-0 right-0 top-full mt-1.5 z-50 rounded-xl border border-stroke bg-white shadow-2xl dark:border-strokedark dark:bg-boxdark max-h-72 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800">
                
                <div class="px-3.5 py-1.5 bg-gray-50 dark:bg-meta-4/40 flex items-center justify-between text-[11px] font-bold uppercase text-gray-500">
                    <span>Customers</span>
                    <span class="text-[#1555a8] font-bold" x-text="matches.length + ' found'"></span>
                </div>

                <template x-for="(item, index) in matches" :key="item.id">
                    <div @click="selectCustomer(item.id)"
                         @mouseenter="highlightedIndex = index"
                         class="p-3 cursor-pointer flex items-center justify-between gap-3 transition"
                         :class="highlightedIndex === index ? 'bg-blue-50/80 dark:bg-meta-4 text-black dark:text-white' : 'hover:bg-gray-50 dark:hover:bg-meta-4/20 text-black dark:text-white'">
                        
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#1555a8] text-white text-xs font-bold flex-shrink-0"
                                 x-text="item.initials || 'CU'"></div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-xs text-black dark:text-white truncate" x-text="item.name"></span>
                                    <span class="text-[10px] text-gray-500 font-mono">#<span x-text="item.id"></span></span>
                                </div>
                                <div class="text-[11px] text-gray-500 dark:text-gray-400 truncate" x-text="item.phone || item.email"></div>
                            </div>
                        </div>

                        <div class="text-right flex-shrink-0">
                            <div class="text-xs font-bold text-green-700 dark:text-green-400" x-text="item.main_balance_formatted"></div>
                        </div>
                    </div>
                </template>

                <div x-show="searchQuery.trim().length >= 1 && !keyUpLoading && matches.length === 0" class="p-4 text-center text-xs text-gray-500">
                    No customers found matching "<span class="font-semibold" x-text="searchQuery"></span>"
                </div>
            </div>
        </div>
    </div>

    <!-- Empty State (Fast & Clean: Shown when no customer is selected yet) -->
    <div x-show="!customer && !dataLoading" class="rounded-2xl border border-stroke bg-white dark:border-strokedark dark:bg-boxdark p-10 sm:p-14 text-center shadow-xs">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 text-primary">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <h3 class="mt-4 text-base font-bold text-black dark:text-white">Customer Lookup</h3>
        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
            Search for a customer by ID, Mobile Number, or Email address above to view their Khatabook ledger, profile details, and manage transactions.
        </p>
    </div>

    <!-- ============================================================== -->
    <!-- MAIN KHATABOOK APP CONTAINER (MATCHES SCREENSHOT EXACTLY)      -->
    <!-- ============================================================== -->
    <div x-show="customer" class="rounded-2xl border border-stroke bg-white shadow-md dark:border-strokedark dark:bg-boxdark overflow-hidden">
        
        <!-- 1. Royal Blue Header Bar (#1555a8) -->
        <div class="bg-[#1555a8] px-4 py-3 text-white flex items-center justify-between">
            <div class="flex items-center gap-3 min-w-0">
                <!-- Back / Clear Customer Button -->
                <button @click="clearCustomer()" 
                        title="Search another customer"
                        class="p-1 text-white hover:text-white/80 active:scale-95 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </button>

                <!-- Avatar Circle with Initials (e.g. VC) -->
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-white font-bold text-sm flex-shrink-0 border border-white/20"
                     x-text="customer ? customer.initials : 'VC'">
                </div>

                <!-- Customer Name & Subtitle -->
                <div class="min-w-0">
                    <h3 class="text-base font-bold text-white leading-tight truncate" 
                        x-text="customer ? customer.name : 'Select Customer'"></h3>
                    <p class="text-[11px] text-white/80 leading-tight">Admin Panel</p>
                </div>
            </div>

            <!-- Customer Actions: Login As User & Quick Refresh -->
            <div class="flex items-center gap-2 flex-shrink-0">
                <form :action="'{{ url('/admin/impersonate') }}/' + (customer ? customer.id : '') + '?redirect=profile'" method="POST" class="inline m-0">
                    @csrf
                    <button type="submit" 
                            :disabled="!customer"
                            title="Login to customer account & view profile"
                            class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold shadow-xs transition disabled:opacity-50">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        <span class="hidden sm:inline">LOGIN AS USER</span>
                    </button>
                </form>

                <button type="button" 
                        @click="refreshCustomerData()"
                        :disabled="dataLoading"
                        title="Refresh ledger"
                        class="p-1.5 rounded-full hover:bg-white/10 text-white transition">
                    <svg class="w-4 h-4" :class="dataLoading ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="p-3.5 sm:p-4 space-y-3.5">

            <!-- 2. Customer Profile Card (Clean Khatabook Table from Screenshot) -->
            <div class="rounded-xl border border-stroke bg-white dark:border-strokedark dark:bg-boxdark shadow-xs overflow-hidden">
                <div class="px-4 py-2 border-b border-stroke dark:border-strokedark bg-gray-50/70 dark:bg-meta-4/20 flex items-center justify-between">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-black dark:text-white">Customer Profile</h4>
                    
                    <!-- Login as User (Profile) Button -->
                    <form :action="'{{ url('/admin/impersonate') }}/' + (customer ? customer.id : '') + '?redirect=profile'" method="POST" class="inline m-0">
                        @csrf
                        <button type="submit" 
                                :disabled="!customer"
                                title="Login as user and view their profile"
                                class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 active:scale-95 rounded-lg shadow-xs transition disabled:opacity-50">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            <span>Login as User</span>
                        </button>
                    </form>
                </div>

                <div class="divide-y divide-stroke dark:divide-strokedark text-xs">
                    <!-- ID Row -->
                    <div class="px-4 py-2.5 flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400 font-medium">ID</span>
                        <div class="flex items-center gap-2 font-medium text-black dark:text-white">
                            <span class="font-mono text-xs font-bold" x-text="customer ? customer.cust_id : 'CUST-10000'"></span>
                            <button type="button" 
                                    @click="copyText(customer ? customer.cust_id : '')" 
                                    title="Copy Customer ID"
                                    class="text-gray-400 hover:text-black dark:hover:text-white p-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </button>
                            <!-- View Customer QR Button -->
                            <button type="button" 
                                    @click="openCustomerQrModal()"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-blue-50 dark:bg-meta-4/40 text-[11px] font-bold text-[#1555a8] dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-meta-4 transition"
                                    title="View Customer QR Code">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                                <span>QR Code</span>
                            </button>
                        </div>
                    </div>

                    <!-- Email Row -->
                    <div class="px-4 py-2.5 flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400 font-medium">Email</span>
                        <span class="font-medium text-black dark:text-white truncate max-w-[200px]" x-text="customer ? customer.email : 'customer@email.com'"></span>
                    </div>

                    <!-- Phone Row -->
                    <div class="px-4 py-2.5 flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400 font-medium">Phone</span>
                        <span class="font-medium text-black dark:text-white" x-text="customer && customer.phone ? customer.phone : '+91 9876543210'"></span>
                    </div>

                    <!-- Current Wallet Balance Row -->
                    <div class="px-4 py-2.5 flex items-center justify-between bg-gray-50/50 dark:bg-meta-4/10">
                        <span class="text-gray-700 dark:text-gray-200 font-semibold">Current Wallet Balance</span>
                        <span class="text-sm font-extrabold text-black dark:text-white" x-text="customer ? customer.main_balance_formatted : '₹ 0'"></span>
                    </div>

                    <!-- Credit Wallet Balance & Limit Row -->
                    <div class="px-4 py-2.5 flex items-center justify-between">
                        <div>
                            <span class="text-gray-700 dark:text-gray-200 font-semibold">Credit Wallet</span>
                            <span class="text-[10px] text-gray-500 dark:text-gray-400 block font-normal">Available / Credit Limit</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="text-right">
                                <span class="text-xs sm:text-sm font-extrabold text-emerald-600 dark:text-emerald-400" 
                                      x-text="customer ? customer.available_credit_formatted : '₹ 0.00'"></span>
                                <span class="text-[10px] text-gray-500 dark:text-gray-400 block font-medium">
                                    Limit: <span x-text="customer ? customer.credit_limit_formatted : '₹ 0.00'"></span>
                                </span>
                            </div>
                            <button type="button" 
                                    @click="openCreditLimitModal()"
                                    class="px-2 py-1 rounded-md text-[11px] font-bold bg-[#1555a8]/10 text-[#1555a8] hover:bg-[#1555a8] hover:text-white transition"
                                    title="Edit or increase customer credit limit">
                                Increase
                            </button>
                        </div>
                    </div>

                    <!-- Account Status Row -->
                    <div class="px-4 py-2.5 flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400 font-medium">Account Status</span>
                        <div class="flex items-center gap-1.5 font-medium text-black dark:text-white capitalize">
                            <span class="h-2 w-2 rounded-full" :class="(customer && customer.is_active) ? 'bg-green-600' : 'bg-yellow-500'"></span>
                            <span x-text="customer ? customer.status : 'Active'"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Transaction History Table (Compact Ledger View with Filters) -->
            <div class="rounded-xl border border-stroke bg-white dark:border-strokedark dark:bg-boxdark shadow-xs overflow-hidden">
                
                <!-- Filter Bar (Type Chips, Date Dropdown, Live Search) -->
                <div class="p-2.5 border-b border-stroke dark:border-strokedark bg-gray-50/70 dark:bg-meta-4/20 flex flex-wrap items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-1">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mr-1">Type:</span>
                        <button type="button" 
                                @click="setTxType('all')" 
                                :class="txFilter.type === 'all' ? 'bg-primary text-white font-bold shadow-xs' : 'bg-white dark:bg-boxdark border border-stroke dark:border-strokedark text-gray-600 dark:text-gray-300 hover:text-black dark:hover:text-white'" 
                                class="px-2.5 py-1 rounded text-[11px] transition">All</button>
                        <button type="button" 
                                @click="setTxType('credit')" 
                                :class="txFilter.type === 'credit' ? 'bg-green-600 text-white font-bold shadow-xs' : 'bg-white dark:bg-boxdark border border-stroke dark:border-strokedark text-gray-600 dark:text-gray-300 hover:text-green-600'" 
                                class="px-2.5 py-1 rounded text-[11px] transition">Credit (+)</button>
                        <button type="button" 
                                @click="setTxType('debit')" 
                                :class="txFilter.type === 'debit' ? 'bg-red-600 text-white font-bold shadow-xs' : 'bg-white dark:bg-boxdark border border-stroke dark:border-strokedark text-gray-600 dark:text-gray-300 hover:text-red-600'" 
                                class="px-2.5 py-1 rounded text-[11px] transition">Debit (-)</button>
                    </div>

                    <!-- Right side: Date Filter & Search Filter -->
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <select x-model="txFilter.date" 
                                @change="loadPage(1)" 
                                class="rounded border border-stroke dark:border-strokedark bg-white dark:bg-boxdark py-1 px-2 text-[11px] font-medium text-black dark:text-white outline-none">
                            <option value="all">All Dates</option>
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="this_week">This Week</option>
                            <option value="this_month">This Month</option>
                        </select>

                        <div class="relative">
                            <input type="text" 
                                   x-model="txFilter.search" 
                                   @input.debounce.300ms="loadPage(1)" 
                                   placeholder="Filter records..." 
                                   class="rounded border border-stroke dark:border-strokedark bg-white dark:bg-boxdark py-1 pl-2 pr-5 text-[11px] text-black dark:text-white outline-none w-28 sm:w-36 focus:border-primary" />
                            <button x-show="txFilter.search" 
                                    @click="txFilter.search = ''; loadPage(1)" 
                                    class="absolute right-1 top-1/2 -translate-y-1/2 text-gray-400 hover:text-black dark:hover:text-white text-[10px] p-0.5">✕</button>
                        </div>
                    </div>
                </div>

                <div class="max-w-full overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-stroke dark:border-strokedark bg-gray-50/80 dark:bg-meta-4/30 text-[10px] font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                <th class="py-2 px-3">Date</th>
                                <th class="py-2 px-2">Trans. ID</th>
                                <th class="py-2 px-2 text-center">Type</th>
                                <th class="py-2 px-3">Description</th>
                                <th class="py-2 px-3 text-right">Amount</th>
                                <th class="py-2 px-3 text-right">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stroke dark:divide-strokedark">
                            <!-- Loading State -->
                            <tr x-show="txLoading">
                                <td colspan="6" class="py-6 text-center text-gray-400 text-xs">
                                    <div class="inline-flex items-center gap-2">
                                        <svg class="w-4 h-4 animate-spin text-primary" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>Updating ledger...</span>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty State -->
                            <tr x-show="!txLoading && (!transactions || transactions.length === 0)">
                                <td colspan="6" class="py-6 text-center text-gray-400 text-xs">
                                    No transaction records found matching the filter.
                                </td>
                            </tr>

                            <!-- Transaction Records from Screenshot (Compact Ledger Rows) -->
                            <template x-for="tx in transactions" :key="tx.id">
                                <tr class="hover:bg-gray-50/60 dark:hover:bg-meta-4/20 transition">
                                    <td class="py-1.5 px-3 whitespace-nowrap text-gray-700 dark:text-gray-300 font-medium text-[11px]" x-text="tx.date"></td>
                                    <td class="py-1.5 px-2 whitespace-nowrap font-mono text-[10px] text-gray-500 dark:text-gray-400" x-text="tx.txn_id"></td>
                                    <td class="py-1.5 px-2 whitespace-nowrap text-center text-[10px] font-bold">
                                        <span class="px-1.5 py-0.5 rounded text-[10px]"
                                              :class="tx.is_credit ? 'bg-green-50 text-green-700 dark:bg-green-950/40 dark:text-green-400' : 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400'"
                                              x-text="tx.type"></span>
                                    </td>
                                    <td class="py-1.5 px-3 text-gray-800 dark:text-gray-200 max-w-[140px] truncate text-[11px]" 
                                        :title="tx.description" 
                                        x-text="tx.description"></td>
                                    <td class="py-1.5 px-3 whitespace-nowrap text-right font-bold text-xs"
                                        :class="tx.is_credit ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                                        x-text="tx.amount_formatted"></td>
                                    <td class="py-1.5 px-3 whitespace-nowrap text-right font-semibold text-gray-900 dark:text-white text-xs"
                                        x-text="tx.balance_formatted"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Compact Pagination Bar (Initial 10 Records Per Page) -->
                <div x-show="pagination && pagination.total > 0" class="px-3 py-2 border-t border-stroke dark:border-strokedark flex items-center justify-between text-[11px] text-gray-500">
                    <div>
                        Showing <span class="font-bold text-black dark:text-white" x-text="pagination ? pagination.from : 0"></span>
                        to <span class="font-bold text-black dark:text-white" x-text="pagination ? pagination.to : 0"></span>
                        of <span class="font-bold text-black dark:text-white" x-text="pagination ? pagination.total : 0"></span> records
                    </div>
                    <div class="flex items-center gap-1" x-show="pagination && pagination.last_page > 1">
                        <button @click="loadPage(pagination.current_page - 1)" 
                                :disabled="!pagination || pagination.current_page <= 1 || txLoading"
                                class="px-2 py-0.5 border rounded border-stroke dark:border-strokedark bg-white dark:bg-boxdark disabled:opacity-30 hover:bg-gray-50 dark:hover:bg-meta-4/40 font-medium">Prev</button>
                        <span class="px-1 text-black dark:text-white font-bold" x-text="pagination ? pagination.current_page : 1"></span>
                        <span>/</span>
                        <span x-text="pagination ? pagination.last_page : 1"></span>
                        <button @click="loadPage(pagination.current_page + 1)" 
                                :disabled="!pagination || pagination.current_page >= pagination.last_page || txLoading"
                                class="px-2 py-0.5 border rounded border-stroke dark:border-strokedark bg-white dark:bg-boxdark disabled:opacity-30 hover:bg-gray-50 dark:hover:bg-meta-4/40 font-medium">Next</button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ============================================================== -->
    <!-- FLOATING / FIXED TO SCREEN BOTTOM ACTION BAR                   -->
    <!-- (ADD WALLET MONEY & SHOP AMOUNT BUTTONS)                       -->
    <!-- ============================================================== -->
    <div x-show="customer" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="translate-y-full opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         class="fixed bottom-0 left-0 lg:left-72 right-0 z-40 bg-white/95 dark:bg-boxdark/95 backdrop-blur-md border-t border-stroke dark:border-strokedark py-2.5 px-4 shadow-[0_-4px_25px_rgba(0,0,0,0.12)]">
        <div class="mx-auto max-w-3xl grid grid-cols-2 gap-3">
            <!-- Button 1: ADD WALLET MONEY (Theme Blue) -->
            <button @click="openAddMoneyModal()"
                    type="button"
                    class="rounded-xl bg-primary hover:bg-primary-dark active:scale-[0.99] py-2.5 px-3 text-center text-white shadow-md transition flex flex-col items-center justify-center">
                <div class="text-xs sm:text-sm font-extrabold uppercase tracking-wide leading-tight flex items-center gap-1.5">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>ADD MONEY</span>
                </div>
                <div class="text-[10px] text-white/80 leading-tight mt-0.5">
                    (To Wallet)
                </div>
            </button>

            <!-- Button 2: SHOP AMOUNT (Dark Theme) -->
            <button @click="openShopDeductModal()"
                    type="button"
                    class="rounded-xl bg-gray-900 hover:bg-black active:scale-[0.99] py-2.5 px-3 text-center text-white shadow-md transition flex flex-col items-center justify-center">
                <div class="text-xs sm:text-sm font-extrabold uppercase tracking-wide leading-tight flex items-center gap-1.5">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span>SHOP AMOUNT</span>
                </div>
                <div class="text-[10px] text-white/80 leading-tight mt-0.5">
                    (or Deduct)
                </div>
            </button>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- AJAX MODAL 1: ADD WALLET MONEY (Smooth Popup)                  -->
    <!-- ============================================================== -->
    <div x-show="modals.addMoney" 
         x-transition
         class="fixed inset-0 z-99999 flex items-center justify-center bg-black/60 p-4"
         @keydown.escape.window="if(!actionLoading) modals.addMoney = false">
        
        <div @click.away="if(!actionLoading) modals.addMoney = false"
             class="w-full max-w-sm rounded-2xl bg-white p-5 shadow-2xl dark:bg-boxdark border border-stroke dark:border-strokedark">
            
            <div class="flex items-center justify-between border-b pb-3 mb-3 border-gray-100 dark:border-gray-800">
                <div>
                    <h3 class="text-sm font-extrabold text-primary uppercase tracking-wide">Add Wallet Money</h3>
                    <p class="text-xs text-gray-500" x-text="customer ? customer.name : ''"></p>
                </div>
                <button @click="modals.addMoney = false" class="text-gray-400 hover:text-black dark:hover:text-white font-bold">✕</button>
            </div>

            <div class="mb-3 rounded bg-blue-50/70 dark:bg-blue-950/20 p-2.5 text-xs flex justify-between items-center">
                <span class="text-gray-500">Current Balance:</span>
                <span class="font-bold text-sm text-primary" x-text="customer ? customer.main_balance_formatted : '₹ 0'"></span>
            </div>

            <div x-show="actionError" class="mb-3 rounded bg-red-50 p-2 text-xs text-red-600" x-text="actionError"></div>

            <form @submit.prevent="submitAddMoney()" class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                        Amount (₹) *
                    </label>
                    <input type="number" 
                           step="1" 
                           min="1" 
                           x-model="actionAmount" 
                           required 
                           placeholder="Enter amount"
                           class="w-full rounded border border-gray-300 bg-white py-2 px-3 text-base font-bold text-black outline-none focus:border-primary dark:border-strokedark dark:bg-boxdark dark:text-white" />

                    <!-- Quick amount chips -->
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <template x-for="chip in [100, 500, 1000, 2000]" :key="chip">
                            <button type="button" 
                                    @click="actionAmount = chip" 
                                    class="rounded border border-gray-200 dark:border-strokedark px-2 py-0.5 text-xs text-gray-600 dark:text-gray-400 hover:border-primary"
                                    x-text="'+ ₹ ' + chip"></button>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                        Description / Note (optional)
                    </label>
                    <input type="text" 
                           x-model="actionNote" 
                           placeholder="e.g. Add Wallet Money"
                           class="w-full rounded border border-gray-300 bg-white py-2 px-3 text-xs text-black outline-none focus:border-primary dark:border-strokedark dark:bg-boxdark dark:text-white" />
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="modals.addMoney = false" class="px-3 py-1.5 text-xs font-semibold text-gray-600 hover:text-black">Cancel</button>
                    <button type="submit" 
                            :disabled="actionLoading || !actionAmount || Number(actionAmount) <= 0"
                            class="rounded bg-primary hover:bg-primary-dark px-4 py-2 text-xs font-bold text-white uppercase disabled:opacity-50">
                        <span x-text="actionLoading ? 'Adding...' : 'Confirm Add Money'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- AJAX MODAL 2: SHOP AMOUNT DEDUCT (Smooth Popup)                -->
    <!-- ============================================================== -->
    <div x-show="modals.shopDeduct" 
         x-transition
         class="fixed inset-0 z-99999 flex items-center justify-center bg-black/60 p-4"
         @keydown.escape.window="if(!actionLoading && !inlineCreditLimitLoading) modals.shopDeduct = false">
        
        <div @click.away="if(!actionLoading && !inlineCreditLimitLoading) modals.shopDeduct = false"
             class="w-full max-w-sm rounded-2xl bg-white p-5 shadow-2xl dark:bg-boxdark border border-stroke dark:border-strokedark">
            
            <div class="flex items-center justify-between border-b pb-3 mb-3 border-gray-100 dark:border-gray-800">
                <div>
                    <h3 class="text-sm font-extrabold text-gray-900 dark:text-white uppercase tracking-wide">Shop Amount (Deduct)</h3>
                    <p class="text-xs text-gray-500" x-text="customer ? customer.name : ''"></p>
                </div>
                <button @click="modals.shopDeduct = false" class="text-gray-400 hover:text-black dark:hover:text-white font-bold">✕</button>
            </div>

            <!-- Balances Display -->
            <div class="mb-3 rounded-xl bg-gray-50 dark:bg-meta-4/20 p-2.5 border border-stroke dark:border-strokedark text-xs space-y-1.5">
                <div class="flex justify-between items-center">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Main Wallet Balance:</span>
                    <span class="font-extrabold text-sm text-gray-900 dark:text-white" x-text="customer ? customer.main_balance_formatted : '₹ 0'"></span>
                </div>
                <div class="flex justify-between items-center pt-1 border-t border-gray-200/60 dark:border-gray-700/60">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Credit Wallet Available:</span>
                    <div class="text-right">
                        <span class="font-bold text-xs text-emerald-600 dark:text-emerald-400" x-text="customer ? customer.available_credit_formatted : '₹ 0.00'"></span>
                        <span class="text-[10px] text-gray-400 block font-normal">(Limit: <span x-text="customer ? customer.credit_limit_formatted : '₹ 0.00'"></span>)</span>
                    </div>
                </div>
            </div>

            <div x-show="actionError" class="mb-3 rounded-lg bg-red-50 p-2.5 text-xs text-red-600 border border-red-200" x-text="actionError"></div>

            <form @submit.prevent="submitShopDeduct()" class="space-y-3">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                            Amount to Deduct (₹) *
                        </label>
                        <template x-if="customer && customer.main_balance > 0">
                            <button type="button" 
                                    @click="actionAmount = customer.main_balance" 
                                    class="text-[11px] text-gray-700 dark:text-gray-300 font-semibold hover:underline">
                                Full Main Balance
                            </button>
                        </template>
                    </div>
                    <input type="number" 
                           step="1" 
                           min="1" 
                           x-model="actionAmount" 
                           required 
                           placeholder="Enter amount"
                           class="w-full rounded border border-gray-300 bg-white py-2 px-3 text-base font-bold text-black outline-none focus:border-black dark:border-strokedark dark:bg-boxdark dark:text-white dark:focus:border-white" />

                    <!-- Live Split Breakdown & Inline Increase Tool -->
                    <template x-if="customer && shopTotal() > 0">
                        <div class="mt-2 space-y-2">
                            <!-- Case A: Amount exceeds main wallet, but remaining is within credit limit -->
                            <div x-show="shopTotal() > mainBal() && creditShortfall() <= 0" 
                                 class="rounded-lg bg-amber-50/90 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/40 p-2.5 text-xs text-amber-900 dark:text-amber-200">
                                <div class="flex items-start gap-1.5 font-bold text-red-600 dark:text-red-400">
                                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>Amount exceeds available balance!</span>
                                </div>
                                <p class="text-[11px] text-gray-700 dark:text-gray-300 mt-1">
                                    The rest amount of <span class="font-bold text-gray-900 dark:text-white">₹ <span x-text="formatMoney(creditDeduct())"></span></span> will be paid through <span class="font-bold text-emerald-700 dark:text-emerald-400">Credit Wallet</span> balance.
                                </p>

                                <!-- Payment Breakdown -->
                                <div class="mt-2 rounded bg-white/80 dark:bg-boxdark/80 p-2 border border-amber-200/60 dark:border-amber-800/30 text-[11px] space-y-1">
                                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                                        <span>From Main Wallet:</span>
                                        <span class="font-bold text-gray-900 dark:text-white">₹ <span x-text="formatMoney(mainDeduct())"></span></span>
                                    </div>
                                    <div class="flex justify-between text-emerald-700 dark:text-emerald-400">
                                        <span>From Credit Wallet:</span>
                                        <span class="font-bold">+ ₹ <span x-text="formatMoney(creditDeduct())"></span></span>
                                    </div>
                                    <div class="flex justify-between border-t pt-1 border-gray-200 dark:border-gray-700 text-gray-500 font-medium">
                                        <span>Remaining Credit after purchase:</span>
                                        <span class="font-bold text-gray-700 dark:text-gray-300">₹ <span x-text="formatMoney(availCredit() - creditDeduct())"></span></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Case B: Amount exceeds main wallet AND available credit limit (Shortfall) -->
                            <div x-show="shopTotal() > mainBal() && creditShortfall() > 0" 
                                 class="rounded-lg bg-red-50/90 dark:bg-red-950/20 border border-red-300 dark:border-red-800/60 p-2.5 text-xs text-red-900 dark:text-red-200">
                                <div class="flex items-start gap-1.5 font-bold text-red-600 dark:text-red-400">
                                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>Amount exceeds available balance!</span>
                                </div>
                                <p class="text-[11px] text-gray-700 dark:text-gray-300 mt-1">
                                    Rest amount needed from Credit Wallet: <span class="font-bold text-gray-900 dark:text-white">₹ <span x-text="formatMoney(creditDeduct())"></span></span>, but available credit is only <span class="font-bold text-red-600">₹ <span x-text="formatMoney(availCredit())"></span></span> (<span class="font-bold text-red-600">Short by ₹ <span x-text="formatMoney(creditShortfall())"></span></span>).
                                </p>

                                <!-- Inline Increase Credit Limit Tool (Without leaving the page!) -->
                                <div class="mt-2.5 rounded-lg bg-white dark:bg-boxdark p-2.5 border border-red-200 dark:border-red-800/40 shadow-xs">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="font-bold text-[#1555a8] dark:text-blue-400 text-[11px] uppercase tracking-wide flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                            Increase Credit Limit
                                        </span>
                                        <span class="text-[10px] text-gray-500">Without leaving page</span>
                                    </div>

                                    <div x-show="inlineCreditLimitError" class="mb-2 text-[11px] text-red-600 font-semibold" x-text="inlineCreditLimitError"></div>

                                    <div class="space-y-1.5">
                                        <div class="flex items-center justify-between text-[11px] text-gray-600 dark:text-gray-400">
                                            <span>Min. required limit:</span>
                                            <span class="font-mono font-bold text-gray-900 dark:text-white">₹ <span x-text="formatMoney(minRequiredCreditLimit())"></span></span>
                                        </div>

                                        <div class="flex gap-1.5">
                                            <div class="relative flex-1">
                                                <span class="absolute inset-y-0 left-0 pl-2 flex items-center text-xs text-gray-400">₹</span>
                                                <input type="number" 
                                                       step="100" 
                                                       x-model="quickCreditLimit" 
                                                       :placeholder="minRequiredCreditLimit()" 
                                                       class="w-full rounded border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-meta-4/20 py-1.5 pl-6 pr-2 text-xs font-bold text-black dark:text-white outline-none focus:border-[#1555a8]" />
                                            </div>
                                            <button type="button" 
                                                    @click="quickCreditLimit = minRequiredCreditLimit()" 
                                                    title="Set minimum required limit"
                                                    class="px-2 py-1 rounded bg-gray-100 hover:bg-gray-200 dark:bg-meta-4/40 text-[10px] font-bold text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                                Auto-Min
                                            </button>
                                            <button type="button" 
                                                    @click="submitInlineIncreaseLimit()" 
                                                    :disabled="inlineCreditLimitLoading || !quickCreditLimit || Number(quickCreditLimit) < minRequiredCreditLimit()"
                                                    class="px-3 py-1.5 rounded bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold transition disabled:opacity-50 whitespace-nowrap flex items-center gap-1">
                                                <svg x-show="inlineCreditLimitLoading" class="w-3 h-3 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                                </svg>
                                                <span x-text="inlineCreditLimitLoading ? 'Updating...' : 'Update Limit'"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Case C: Paid 100% from main wallet -->
                            <div x-show="shopTotal() <= mainBal()" class="rounded-lg bg-green-50/70 dark:bg-green-950/20 p-2 text-[11px] text-green-700 dark:text-green-300 flex items-center justify-between border border-green-200/60 dark:border-green-800/40">
                                <span class="flex items-center gap-1 font-medium">
                                    <svg class="w-3.5 h-3.5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Fully covered by Main Wallet
                                </span>
                                <span class="font-bold">₹ <span x-text="formatMoney(shopTotal())"></span></span>
                            </div>
                        </div>
                    </template>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                        BV Points (optional)
                    </label>
                    <input type="number" 
                           step="0.01" 
                           min="0" 
                           x-model="actionBv" 
                           placeholder="0 (Business Volume)"
                           class="w-full rounded border border-gray-300 bg-white py-2 px-3 text-xs text-black outline-none focus:border-black dark:border-strokedark dark:bg-boxdark dark:text-white dark:focus:border-white" />
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                        Description / Category (optional)
                    </label>
                    <input type="text" 
                           x-model="actionNote" 
                           placeholder="e.g. Shop Amount - Grocery"
                           class="w-full rounded border border-gray-300 bg-white py-2 px-3 text-xs text-black outline-none focus:border-black dark:border-strokedark dark:bg-boxdark dark:text-white dark:focus:border-white" />
                </div>

                <div class="rounded bg-gray-50 dark:bg-meta-4/20 p-2 text-[11px] text-gray-500 flex items-start gap-1.5">
                    <svg class="w-4 h-4 text-primary shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Upline repurchase commission will automatically be credited to sponsor levels as configured in MLM settings.</span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="modals.shopDeduct = false" class="px-3 py-1.5 text-xs font-semibold text-gray-600 hover:text-black">Cancel</button>
                    <button type="submit" 
                            :disabled="actionLoading || !actionAmount || Number(actionAmount) <= 0 || creditShortfall() > 0"
                            class="rounded bg-gray-900 hover:bg-black px-4 py-2 text-xs font-bold text-white uppercase disabled:opacity-50 transition">
                        <span x-text="actionLoading ? 'Deducting...' : (creditDeduct() > 0 ? 'Confirm Deduct (Split)' : 'Confirm Deduct')"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- AJAX MODAL: UPDATE / INCREASE CREDIT LIMIT                     -->
    <!-- ============================================================== -->
    <div x-show="modals.creditLimit" 
         x-transition
         class="fixed inset-0 z-99999 flex items-center justify-center bg-black/60 p-4"
         @keydown.escape.window="if(!creditLimitModalLoading) modals.creditLimit = false">
        
        <div @click.away="if(!creditLimitModalLoading) modals.creditLimit = false"
             class="w-full max-w-sm rounded-2xl bg-white p-5 shadow-2xl dark:bg-boxdark border border-stroke dark:border-strokedark">
            
            <div class="flex items-center justify-between border-b pb-3 mb-3 border-gray-100 dark:border-gray-800">
                <div>
                    <h3 class="text-sm font-extrabold text-[#1555a8] uppercase tracking-wide">Customer Credit Limit</h3>
                    <p class="text-xs text-gray-500" x-text="customer ? customer.name : ''"></p>
                </div>
                <button @click="modals.creditLimit = false" class="text-gray-400 hover:text-black dark:hover:text-white font-bold">✕</button>
            </div>

            <div class="mb-3 rounded-xl bg-gray-50 dark:bg-meta-4/20 p-3 border border-stroke dark:border-strokedark text-xs space-y-1.5">
                <div class="flex justify-between">
                    <span class="text-gray-500">Current Credit Limit:</span>
                    <span class="font-bold text-gray-900 dark:text-white" x-text="customer ? customer.credit_limit_formatted : '₹ 0.00'"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Used Credit:</span>
                    <span class="font-bold text-amber-600" x-text="customer ? customer.used_credit_formatted : '₹ 0.00'"></span>
                </div>
                <div class="flex justify-between border-t pt-1 border-gray-200 dark:border-gray-700">
                    <span class="text-gray-500">Available Credit:</span>
                    <span class="font-extrabold text-emerald-600" x-text="customer ? customer.available_credit_formatted : '₹ 0.00'"></span>
                </div>
            </div>

            <div x-show="creditLimitModalError" class="mb-3 rounded bg-red-50 p-2 text-xs text-red-600" x-text="creditLimitModalError"></div>

            <form @submit.prevent="submitCreditLimitModal()" class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                        New Credit Limit (₹) *
                    </label>
                    <input type="number" 
                           step="100" 
                           min="0" 
                           x-model="creditLimitModalAmount" 
                           required 
                           placeholder="Enter credit limit"
                           class="w-full rounded border border-gray-300 bg-white py-2 px-3 text-base font-bold text-black outline-none focus:border-[#1555a8] dark:border-strokedark dark:bg-boxdark dark:text-white" />

                    <!-- Quick amount chips -->
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <template x-for="chip in [1000, 2000, 5000, 10000, 20000]" :key="chip">
                            <button type="button" 
                                    @click="creditLimitModalAmount = chip" 
                                    class="rounded border border-gray-200 dark:border-strokedark px-2 py-0.5 text-xs text-gray-600 dark:text-gray-400 hover:border-primary"
                                    x-text="'₹ ' + chip.toLocaleString()"></button>
                        </template>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="modals.creditLimit = false" class="px-3 py-1.5 text-xs font-semibold text-gray-600 hover:text-black">Cancel</button>
                    <button type="submit" 
                            :disabled="creditLimitModalLoading || creditLimitModalAmount === '' || Number(creditLimitModalAmount) < 0"
                            class="rounded bg-[#1555a8] hover:bg-[#12468d] px-4 py-2 text-xs font-bold text-white uppercase disabled:opacity-50 transition flex items-center gap-1.5">
                        <svg x-show="creditLimitModalLoading" class="w-3.5 h-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span x-text="creditLimitModalLoading ? 'Saving...' : 'Save Credit Limit'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- AJAX MODAL 3: ADMIN QR SCANNER MODAL (Camera & Fallback)      -->
    <!-- ============================================================== -->
    <div x-show="modals.qrScanner" 
         x-transition
         class="fixed inset-0 z-99999 flex items-center justify-center bg-black/70 p-4"
         @keydown.escape.window="closeQrScannerModal()">
        
        <div @click.away="closeQrScannerModal()"
             class="w-full max-w-md rounded-3xl bg-white p-5 shadow-2xl dark:bg-boxdark border border-stroke dark:border-strokedark overflow-hidden">
            
            <div class="flex items-center justify-between border-b pb-3 mb-3 border-gray-100 dark:border-gray-800">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-gray-900 dark:text-white uppercase tracking-wide">Scan Customer QR</h3>
                        <p class="text-[11px] text-gray-500">Auto-loads profile & Khatabook ledger</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" 
                            x-show="adminCameras.length > 1"
                            @click="switchAdminCamera()" 
                            title="Flip Camera"
                            class="p-1 rounded-lg border border-gray-200 text-gray-500 hover:text-black">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                    <button type="button" @click="closeQrScannerModal()" class="text-gray-400 hover:text-black dark:hover:text-white font-bold p-1">✕</button>
                </div>
            </div>

            <!-- Viewfinder Area -->
            <div class="relative w-full rounded-2xl overflow-hidden bg-black flex items-center justify-center min-h-[250px]">
                <div id="admin-qr-reader" class="w-full h-full"></div>

                <!-- Laser Target Box Overlay -->
                <div x-show="adminScannerActive && !adminScanResolving" class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                    <div class="w-48 h-48 border-2 border-white/60 rounded-2xl relative shadow-[0_0_0_9999px_rgba(0,0,0,0.55)]">
                        <div class="absolute -top-1 -left-1 w-5 h-5 border-t-4 border-l-4 border-emerald-500 rounded-tl"></div>
                        <div class="absolute -top-1 -right-1 w-5 h-5 border-t-4 border-r-4 border-emerald-500 rounded-tr"></div>
                        <div class="absolute -bottom-1 -left-1 w-5 h-5 border-b-4 border-l-4 border-emerald-500 rounded-bl"></div>
                        <div class="absolute -bottom-1 -right-1 w-5 h-5 border-b-4 border-r-4 border-emerald-500 rounded-br"></div>
                        <div class="absolute left-2 right-2 h-0.5 bg-red-500 shadow-[0_0_8px_#ef4444] animate-bounce" style="animation-duration: 2s;"></div>
                    </div>
                </div>

                <!-- Resolving Indicator -->
                <div x-show="adminScanResolving" class="absolute inset-0 bg-black/80 flex flex-col items-center justify-center text-white z-20">
                    <svg class="w-8 h-8 animate-spin text-emerald-400 mb-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span class="text-xs font-bold">Opening customer ledger...</span>
                </div>

                <!-- Camera Error Notice -->
                <div x-show="adminCameraError" class="p-4 text-center text-white text-xs">
                    <p class="font-bold text-yellow-400" x-text="adminCameraError"></p>
                    <p class="text-gray-300 mt-1">Please use manual fallback below.</p>
                </div>
            </div>

            <!-- Manual Input Fallback inside Scanner Modal -->
            <div class="mt-3.5 space-y-2">
                <div class="flex items-center justify-between text-[11px] text-gray-500">
                    <span>Manual ID fallback:</span>
                    <label class="cursor-pointer font-bold text-[#1555a8] hover:underline flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Scan Image</span>
                        <input type="file" accept="image/*" class="hidden" @change="scanAdminFromFile($event)">
                    </label>
                </div>

                <form @submit.prevent="resolveModalManualInput()" class="flex gap-1.5">
                    <input type="text" 
                           x-model="modalManualInput" 
                           placeholder="Type CUST:15, CUST-10015, or ID..." 
                           class="flex-1 rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-meta-4/20 py-1.5 px-3 text-xs text-black dark:text-white outline-none focus:border-[#1555a8]" />
                    <button type="submit" 
                            class="px-3 py-1.5 rounded-lg bg-[#1555a8] text-white text-xs font-bold hover:bg-[#12468d] transition">
                        Open
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- AJAX MODAL 4: CUSTOMER QR CODE DISPLAY & PRINT MODAL           -->
    <!-- ============================================================== -->
    <div x-show="modals.customerQr" 
         x-transition
         class="fixed inset-0 z-99999 flex items-center justify-center bg-black/70 p-4"
         @keydown.escape.window="modals.customerQr = false">
        
        <div @click.away="modals.customerQr = false"
             class="w-full max-w-sm rounded-3xl bg-white p-6 shadow-2xl dark:bg-boxdark border border-stroke dark:border-strokedark text-center">
            
            <div class="flex items-center justify-between border-b pb-3 mb-4 border-gray-100 dark:border-gray-800">
                <div class="text-left">
                    <h3 class="text-sm font-extrabold text-[#1555a8] uppercase tracking-wide" x-text="customer ? customer.name : ''"></h3>
                    <p class="text-xs text-gray-500" x-text="customer ? customer.cust_id : ''"></p>
                </div>
                <button @click="modals.customerQr = false" class="text-gray-400 hover:text-black dark:hover:text-white font-bold">✕</button>
            </div>

            <!-- QR Container -->
            <div class="p-4 bg-gray-50 dark:bg-meta-4/10 rounded-2xl border border-dashed border-[#1555a8]/30 inline-block mb-4">
                <div id="admin-customer-qr-canvas" class="flex items-center justify-center min-w-[200px] min-h-[200px]"></div>
            </div>

            <div class="rounded-xl bg-gray-50 dark:bg-meta-4/20 p-3 text-xs text-left space-y-1.5 mb-4">
                <div class="flex justify-between">
                    <span class="text-gray-500">Payload:</span>
                    <span class="font-mono font-bold text-gray-800 dark:text-gray-200" x-text="customer ? (customer.qr_payload || 'CUST:' + customer.id) : ''"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Current Balance:</span>
                    <span class="font-bold text-green-700 dark:text-green-400" x-text="customer ? customer.main_balance_formatted : ''"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Phone:</span>
                    <span class="font-medium text-gray-800 dark:text-gray-200" x-text="customer ? (customer.phone || 'N/A') : ''"></span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <button type="button" 
                        @click="copyText(customer ? (customer.qr_payload || 'CUST:' + customer.id) : '')" 
                        class="py-2 px-3 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50">
                    Copy Payload
                </button>
                <a :href="'{{ url('/wallet/my-qr') }}?user_id=' + (customer ? customer.id : '')" 
                   target="_blank"
                   class="py-2 px-3 rounded-xl bg-[#1555a8] hover:bg-[#12468d] text-white text-xs font-bold transition flex items-center justify-center gap-1">
                    Full QR Card ↗
                </a>
            </div>
        </div>
    </div>

</div>

<script>
function singleScreenKhataApp(config) {
    return {
        customer: config.initialCustomer,
        transactions: config.initialTransactions || [],
        pagination: config.initialPagination || null,
        searchQuery: '',
        dataLoading: false,
        txLoading: false,
        keyUpLoading: false,
        dropdownOpen: false,
        matches: [],
        highlightedIndex: -1,
        noMatchesFound: false,

        modals: {
            addMoney: false,
            shopDeduct: false,
            creditLimit: false,
            qrScanner: false,
            customerQr: false
        },
        adminScannerInstance: null,
        adminScannerActive: false,
        adminCameras: [],
        adminCameraIndex: 0,
        adminCameraError: '',
        adminScanResolving: false,
        modalManualInput: '',
        txFilter: {
            type: 'all',
            date: 'all',
            search: ''
        },
        actionAmount: '',
        actionBv: '',
        actionNote: '',
        actionLoading: false,
        actionError: '',

        quickCreditLimit: '',
        inlineCreditLimitLoading: false,
        inlineCreditLimitError: '',
        creditLimitModalAmount: '',
        creditLimitModalLoading: false,
        creditLimitModalError: '',

        toast: {
            show: false,
            message: '',
            type: 'success',
            timer: null
        },

        init() {
            window.openAdminCustomerQrScanner = () => this.openQrScannerModal();

            if (window.location.search.includes('scan=1')) {
                this.$nextTick(() => {
                    this.openQrScannerModal();
                });
            }
        },

        clearCustomer() {
            this.customer = null;
            this.transactions = [];
            this.pagination = null;
            this.searchQuery = '';
            this.matches = [];
            this.dropdownOpen = false;
            this.txFilter = { type: 'all', date: 'all', search: '' };
            const cleanUrl = window.location.pathname;
            window.history.replaceState({}, '', cleanUrl);
            this.$nextTick(() => {
                if (this.$refs.searchInput) {
                    this.$refs.searchInput.focus();
                }
            });
        },

        showToast(message, type = 'success') {
            clearTimeout(this.toast.timer);
            this.toast.message = message;
            this.toast.type = type;
            this.toast.show = true;
            this.toast.timer = setTimeout(() => {
                this.toast.show = false;
            }, 3500);
        },

        copyText(txt) {
            if (!txt) return;
            navigator.clipboard.writeText(txt);
            this.showToast('Copied: ' + txt, 'success');
        },

        // Real-time on key-up search
        async onKeyUpSearch() {
            const query = (this.searchQuery || '').trim();

            if (query.length < 1) {
                this.matches = [];
                this.dropdownOpen = false;
                this.highlightedIndex = -1;
                this.noMatchesFound = false;
                return;
            }

            this.keyUpLoading = true;
            this.noMatchesFound = false;

            try {
                const response = await fetch(`${config.routes.search}?q=${encodeURIComponent(query)}&suggest=1`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.matches = data.matches || [];
                    this.dropdownOpen = true;
                    this.highlightedIndex = this.matches.length > 0 ? 0 : -1;
                    this.noMatchesFound = (this.matches.length === 0);
                }
            } catch (err) {
                console.error('Search error:', err);
            } finally {
                this.keyUpLoading = false;
            }
        },

        navigateDropdown(dir) {
            if (!this.dropdownOpen || this.matches.length === 0) return;
            if (dir === 'down') {
                this.highlightedIndex = (this.highlightedIndex + 1) % this.matches.length;
            } else if (dir === 'up') {
                this.highlightedIndex = (this.highlightedIndex - 1 + this.matches.length) % this.matches.length;
            }
        },

        selectHighlightedOrSearch() {
            if (this.dropdownOpen && this.highlightedIndex >= 0 && this.matches[this.highlightedIndex]) {
                this.selectCustomer(this.matches[this.highlightedIndex].id);
            } else if (this.matches.length > 0) {
                this.selectCustomer(this.matches[0].id);
            } else if (this.searchQuery.trim()) {
                this.searchCustomerDirectly(this.searchQuery.trim());
            }
        },

        // Select a customer via AJAX without full page reload
        async selectCustomer(userId) {
            this.dataLoading = true;
            this.dropdownOpen = false;

            try {
                const response = await fetch(`${config.routes.search}?user_id=${userId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.customer = data.customer;
                    this.transactions = data.transactions;
                    this.pagination = data.pagination;
                    this.searchQuery = '';
                    this.matches = [];

                    // Update browser URL query without reload
                    const newUrl = `${window.location.pathname}?user_id=${userId}`;
                    window.history.replaceState({ user_id: userId }, '', newUrl);
                } else {
                    this.showToast(data.message || 'Unable to load customer.', 'error');
                }
            } catch (err) {
                this.showToast('Network error while loading customer.', 'error');
            } finally {
                this.dataLoading = false;
            }
        },

        async searchCustomerDirectly(query) {
            this.dataLoading = true;
            this.dropdownOpen = false;

            try {
                const response = await fetch(`${config.routes.search}?q=${encodeURIComponent(query)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.customer = data.customer;
                    this.transactions = data.transactions;
                    this.pagination = data.pagination;
                    this.searchQuery = '';
                    this.matches = [];

                    const newUrl = `${window.location.pathname}?user_id=${this.customer.id}`;
                    window.history.replaceState({ user_id: this.customer.id }, '', newUrl);
                } else {
                    this.showToast(data.message || 'Customer not found.', 'error');
                }
            } catch (err) {
                this.showToast('Network error while searching.', 'error');
            } finally {
                this.dataLoading = false;
            }
        },

        async refreshCustomerData() {
            if (!this.customer) return;
            this.selectCustomer(this.customer.id);
        },

        async loadPage(page = 1) {
            if (!this.customer) return;
            if (this.pagination && (page < 1 || (this.pagination.last_page > 0 && page > this.pagination.last_page))) {
                return;
            }
            this.txLoading = true;

            let url = config.routes.transactions.replace(':id', this.customer.id) + `?page=${page}&per_page=10`;
            if (this.txFilter.type && this.txFilter.type !== 'all') {
                url += `&type=${encodeURIComponent(this.txFilter.type)}`;
            }
            if (this.txFilter.date && this.txFilter.date !== 'all') {
                url += `&date=${encodeURIComponent(this.txFilter.date)}`;
            }
            if (this.txFilter.search && this.txFilter.search.trim()) {
                url += `&search_tx=${encodeURIComponent(this.txFilter.search.trim())}`;
            }

            try {
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                if (response.ok && data.success) {
                    this.transactions = data.transactions;
                    this.pagination = data.pagination;
                }
            } catch (err) {
                console.error('Failed to load page:', err);
            } finally {
                this.txLoading = false;
            }
        },

        setTxType(type) {
            this.txFilter.type = type;
            this.loadPage(1);
        },

        openAddMoneyModal() {
            this.actionAmount = '';
            this.actionNote = 'Add Wallet Money';
            this.actionError = '';
            this.modals.addMoney = true;
        },

        async submitAddMoney() {
            if (!this.customer) return;
            const amt = Number(this.actionAmount);
            if (!amt || amt <= 0) {
                this.actionError = 'Please enter a valid amount.';
                return;
            }

            this.actionLoading = true;
            this.actionError = '';

            const url = config.routes.addMoney.replace(':id', this.customer.id);

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        amount: amt,
                        note: this.actionNote
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.customer = data.customer;
                    this.transactions = data.transactions;
                    this.pagination = data.pagination;
                    this.modals.addMoney = false;
                    this.showToast(data.message || 'Money added successfully.', 'success');
                } else {
                    this.actionError = data.message || 'Failed to add money.';
                }
            } catch (err) {
                this.actionError = 'Network error. Please try again.';
            } finally {
                this.actionLoading = false;
            }
        },

        formatMoney(val) {
            const num = Number(val) || 0;
            return num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        shopTotal() {
            return Number(this.actionAmount) || 0;
        },

        mainBal() {
            return this.customer ? Math.max(0, Number(this.customer.main_balance) || 0) : 0;
        },

        availCredit() {
            return this.customer ? Math.max(0, Number(this.customer.available_credit) || 0) : 0;
        },

        mainDeduct() {
            return Math.min(this.shopTotal(), this.mainBal());
        },

        creditDeduct() {
            return Math.max(0, this.shopTotal() - this.mainDeduct());
        },

        creditShortfall() {
            return Math.max(0, this.creditDeduct() - this.availCredit());
        },

        minRequiredCreditLimit() {
            const used = this.customer ? (Number(this.customer.used_credit) || 0) : 0;
            return Math.ceil(used + this.creditDeduct());
        },

        openShopDeductModal() {
            this.actionAmount = '';
            this.actionBv = '';
            this.actionNote = 'Shop Amount - Grocery';
            this.actionError = '';
            this.inlineCreditLimitError = '';
            this.quickCreditLimit = '';
            this.modals.shopDeduct = true;
        },

        async submitInlineIncreaseLimit() {
            if (!this.customer) return;
            const limit = Number(this.quickCreditLimit);
            if (isNaN(limit) || limit < 0) {
                this.inlineCreditLimitError = 'Please enter a valid credit limit.';
                return;
            }

            this.inlineCreditLimitLoading = true;
            this.inlineCreditLimitError = '';

            const url = config.routes.updateCreditLimit.replace(':id', this.customer.id);

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        credit_limit: limit
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.customer = data.customer;
                    this.showToast(data.message || 'Credit limit updated successfully.', 'success');
                } else {
                    this.inlineCreditLimitError = data.message || 'Failed to update credit limit.';
                }
            } catch (err) {
                this.inlineCreditLimitError = 'Network error while updating credit limit.';
            } finally {
                this.inlineCreditLimitLoading = false;
            }
        },

        openCreditLimitModal() {
            if (!this.customer) return;
            this.creditLimitModalAmount = this.customer.credit_limit || 0;
            this.creditLimitModalError = '';
            this.modals.creditLimit = true;
        },

        async submitCreditLimitModal() {
            if (!this.customer) return;
            const limit = Number(this.creditLimitModalAmount);
            if (isNaN(limit) || limit < 0) {
                this.creditLimitModalError = 'Please enter a valid credit limit.';
                return;
            }

            this.creditLimitModalLoading = true;
            this.creditLimitModalError = '';

            const url = config.routes.updateCreditLimit.replace(':id', this.customer.id);

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        credit_limit: limit
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.customer = data.customer;
                    this.modals.creditLimit = false;
                    this.showToast(data.message || 'Credit limit updated successfully.', 'success');
                } else {
                    this.creditLimitModalError = data.message || 'Failed to update credit limit.';
                }
            } catch (err) {
                this.creditLimitModalError = 'Network error while updating credit limit.';
            } finally {
                this.creditLimitModalLoading = false;
            }
        },

        async submitShopDeduct() {
            if (!this.customer) return;
            const amt = Number(this.actionAmount);
            if (!amt || amt <= 0) {
                this.actionError = 'Please enter a valid amount.';
                return;
            }

            if (this.creditShortfall() > 0) {
                this.actionError = `Amount exceeds available wallet balance and credit limit. Increase credit limit to proceed.`;
                return;
            }

            this.actionLoading = true;
            this.actionError = '';

            const url = config.routes.shopDeduct.replace(':id', this.customer.id);

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        amount: amt,
                        bv: Number(this.actionBv) || 0,
                        note: this.actionNote
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.customer = data.customer;
                    this.transactions = data.transactions;
                    this.pagination = data.pagination;
                    this.modals.shopDeduct = false;
                    this.showToast(data.message || 'Shop amount deducted successfully.', 'success');
                } else {
                    this.actionError = data.message || 'Failed to deduct shop amount.';
                }
            } catch (err) {
                this.actionError = 'Network error. Please try again.';
            } finally {
                this.actionLoading = false;
            }
        },

        openQrScannerModal() {
            this.modalManualInput = '';
            this.adminCameraError = '';
            this.adminScanResolving = false;
            this.modals.qrScanner = true;
            this.$nextTick(() => {
                this.startAdminScanner();
            });
        },

        async closeQrScannerModal() {
            await this.stopAdminScanner();
            this.modals.qrScanner = false;
        },

        async startAdminScanner() {
            this.adminCameraError = '';
            const initLib = async () => {
                if (typeof Html5Qrcode === 'undefined') {
                    setTimeout(initLib, 100);
                    return;
                }

                try {
                    if (!this.adminScannerInstance) {
                        this.adminScannerInstance = new Html5Qrcode("admin-qr-reader");
                    }

                    const devices = await Html5Qrcode.getCameras();
                    if (!devices || devices.length === 0) {
                        this.adminCameraError = 'No camera found on this device.';
                        return;
                    }
                    this.adminCameras = devices;

                    let selectedCam = devices[0].id;
                    for (let i = 0; i < devices.length; i++) {
                        if (devices[i].label.toLowerCase().includes('back') || devices[i].label.toLowerCase().includes('rear')) {
                            selectedCam = devices[i].id;
                            this.adminCameraIndex = i;
                            break;
                        }
                    }

                    await this.adminScannerInstance.start(
                        selectedCam,
                        { fps: 10, qrbox: { width: 220, height: 220 }, aspectRatio: 1.0 },
                        (decodedText) => this.onAdminQrScanned(decodedText),
                        () => {}
                    );
                    this.adminScannerActive = true;
                } catch (err) {
                    console.error('Admin scanner start error:', err);
                    this.adminScannerActive = false;
                    this.adminCameraError = 'Camera access denied or unavailable.';
                }
            };
            initLib();
        },

        async stopAdminScanner() {
            if (this.adminScannerInstance && this.adminScannerActive) {
                try {
                    await this.adminScannerInstance.stop();
                } catch (e) {}
                this.adminScannerActive = false;
            }
        },

        async switchAdminCamera() {
            if (this.adminCameras.length <= 1) return;
            await this.stopAdminScanner();
            this.adminCameraIndex = (this.adminCameraIndex + 1) % this.adminCameras.length;
            const camId = this.adminCameras[this.adminCameraIndex].id;

            try {
                await this.adminScannerInstance.start(
                    camId,
                    { fps: 10, qrbox: { width: 220, height: 220 }, aspectRatio: 1.0 },
                    (decodedText) => this.onAdminQrScanned(decodedText),
                    () => {}
                );
                this.adminScannerActive = true;
            } catch (err) {
                console.error('Camera flip error:', err);
            }
        },

        async scanAdminFromFile(e) {
            const file = e.target.files[0];
            if (!file) return;

            try {
                if (!this.adminScannerInstance) {
                    this.adminScannerInstance = new Html5Qrcode("admin-qr-reader");
                }
                this.adminScanResolving = true;
                const result = await this.adminScannerInstance.scanFile(file, true);
                this.onAdminQrScanned(result);
            } catch (err) {
                this.showToast('No readable QR code found in image.', 'error');
            } finally {
                this.adminScanResolving = false;
            }
        },

        async onAdminQrScanned(decodedText) {
            if (!decodedText || this.adminScanResolving) return;
            this.adminScanResolving = true;
            await this.stopAdminScanner();
            this.modals.qrScanner = false;
            this.searchCustomerDirectly(decodedText.trim());
        },

        resolveModalManualInput() {
            const q = (this.modalManualInput || '').trim();
            if (!q) return;
            this.closeQrScannerModal();
            this.searchCustomerDirectly(q);
        },

        openCustomerQrModal() {
            if (!this.customer) return;
            this.modals.customerQr = true;
            this.$nextTick(() => {
                const container = document.getElementById('admin-customer-qr-canvas');
                if (!container) return;
                container.innerHTML = '';
                const payload = this.customer.qr_payload || ('CUST:' + this.customer.id);

                const initQr = () => {
                    if (typeof QRCode === 'undefined') {
                        setTimeout(initQr, 80);
                        return;
                    }
                    new QRCode(container, {
                        text: payload,
                        width: 200,
                        height: 200,
                        colorDark: '#0f172a',
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.H
                    });
                };
                initQr();
            });
        }
    };
}
</script>
<script src="{{ asset('vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>
<script src="{{ asset('vendor/qrcode/qrcode.min.js') }}"></script>
@endsection
