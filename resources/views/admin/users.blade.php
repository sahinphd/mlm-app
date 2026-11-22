@extends('admin.layout')

@section('content')
<div class="space-y-2 sm:space-y-3">
    <!-- Top Header & Action Buttons (Compact Mobile App Bar) -->
    <div class="flex items-center justify-between gap-2">
        <div class="min-w-0">
            <h1 class="text-base sm:text-xl font-bold text-gray-900 dark:text-white flex items-center gap-1.5 truncate">
                <span>Users &amp; Khata</span>
                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-950/80 dark:text-blue-300 shrink-0">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    {{ number_format($khataSummary['total_users'] ?? 0) }}
                </span>
            </h1>
            <p class="hidden sm:block text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">
                Real-time ledger overview of customer accounts, active creditors, and cash non-creditors.
            </p>
        </div>
        <div class="flex items-center gap-1.5 shrink-0">
            <a href="{{ route('admin.customer-lookup.index') }}" 
               class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-[#1555a8] hover:bg-[#12468d] text-white text-[11px] sm:text-xs font-semibold rounded-lg shadow-2xs hover:shadow transition-all">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                </svg>
                <span class="hidden xs:inline">Single Khata</span>
                <span class="xs:hidden">Khata</span>
            </a>

            <a href="{{ route('admin.users.create') }}" 
               class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] sm:text-xs font-semibold rounded-lg shadow-2xs hover:shadow transition-all">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>+ User</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="flex w-full border-l-4 border-emerald-500 bg-emerald-50/90 dark:bg-emerald-950/30 p-3 sm:p-3.5 rounded-r-xl shadow-xs">
            <div class="flex items-center gap-2.5">
                <div class="h-7 w-7 rounded-lg bg-emerald-500 text-white flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <p class="text-xs sm:text-sm font-medium text-emerald-900 dark:text-emerald-200">
                    {{ session('success') }}
                </p>
            </div>
        </div>
    @endif

    <!-- ======================================================== -->
    <!-- KHATABOOK COMPACT SPLIT LEDGER SUMMARY -->
    <!-- ======================================================== -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-2xs overflow-hidden">
        <!-- 2-Column Primary Split Ledger -->
        <div class="grid grid-cols-2 divide-x divide-gray-100 dark:divide-gray-700/80">
            <!-- 1. Total Udhar (Due) / You'll Get -->
            <div onclick="switchTab('creditor')" 
                 class="cursor-pointer p-2 sm:p-2.5 bg-red-50/30 dark:bg-red-950/20 hover:bg-red-50/60 dark:hover:bg-red-950/40 transition-all flex flex-col justify-between active:scale-[0.99]">
                <div class="flex items-center justify-between text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    <span class="flex items-center gap-1 truncate">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 shrink-0"></span>
                        <span class="truncate">Udhar (You'll Get)</span>
                    </span>
                    <span class="text-red-500 font-bold shrink-0">↓</span>
                </div>
                <div class="mt-0.5 flex items-baseline justify-between gap-1">
                    <span class="text-sm sm:text-base font-black text-red-600 dark:text-red-400 tracking-tight tabular-nums">
                        {{ ($symbol ?? '₹') . number_format($khataSummary['total_credit_used'] ?? 0, 2) }}
                    </span>
                    <span class="text-[9px] font-bold text-red-600 dark:text-red-300 bg-red-100 dark:bg-red-900/60 px-1 py-0.2 rounded shrink-0">
                        Due
                    </span>
                </div>
            </div>

            <!-- 2. Available Credit Limit -->
            <div class="p-2 sm:p-2.5 bg-emerald-50/30 dark:bg-emerald-950/20 hover:bg-emerald-50/60 dark:hover:bg-emerald-950/40 transition-all flex flex-col justify-between">
                <div class="flex items-center justify-between text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    <span class="flex items-center gap-1 truncate">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                        <span class="truncate">Credit Available</span>
                    </span>
                    <span class="text-emerald-500 font-bold shrink-0">↑</span>
                </div>
                <div class="mt-0.5 flex items-baseline justify-between gap-1">
                    <span class="text-sm sm:text-base font-black text-emerald-600 dark:text-emerald-400 tracking-tight tabular-nums">
                        {{ ($symbol ?? '₹') . number_format($khataSummary['total_available_credit'] ?? 0, 2) }}
                    </span>
                    <span class="text-[9px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-900/60 px-1 py-0.2 rounded shrink-0">
                        Pool
                    </span>
                </div>
            </div>
        </div>

        <!-- Bottom Micro Strip: Total Shopping & Overdue EMIs -->
        <div class="px-2.5 py-1.5 bg-gray-50/90 dark:bg-gray-900/70 border-t border-gray-100 dark:border-gray-700/80 flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400">
            <div class="flex items-center gap-1 truncate">
                <span class="text-gray-400">Shop Done:</span>
                <span class="font-bold text-blue-700 dark:text-blue-300 tabular-nums">
                    {{ ($symbol ?? '₹') . number_format($khataSummary['total_shopping_done'] ?? 0, 2) }}
                </span>
            </div>
            <div class="flex items-center gap-1 cursor-pointer shrink-0" onclick="switchTab('creditor')">
                <span class="text-gray-400">Overdue:</span>
                <span class="font-bold {{ ($khataSummary['overdue_total'] ?? 0) > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-600 dark:text-gray-300' }} tabular-nums">
                    {{ ($symbol ?? '₹') . number_format($khataSummary['overdue_total'] ?? 0, 2) }}
                </span>
                <span class="text-[9px] font-bold px-1 py-0.2 rounded {{ ($khataSummary['overdue_count'] ?? 0) > 0 ? 'bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-200' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }}">
                    {{ $khataSummary['overdue_count'] ?? 0 }} Due
                </span>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- UNIFIED CUSTOMER CONTROLS & DIRECTORY WRAPPER -->
    <!-- ======================================================== -->
    <div id="slidingTableWrapper" class="relative overflow-hidden transition-all duration-300">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xs border border-gray-200 dark:border-gray-700 p-2 sm:p-3 space-y-2">
            <!-- Row 1: Segmented Sliding Glider Tabs -->
            <div class="relative bg-gray-100 dark:bg-gray-900 p-0.5 rounded-lg flex items-center shadow-inner border border-gray-200/80 dark:border-gray-700 overflow-hidden" id="sliderNavContainer">
                <!-- Glider Pill -->
                <div id="slidingGlider" 
                     class="absolute top-0.5 bottom-0.5 rounded-md bg-white dark:bg-[#1555a8] shadow-xs transition-all duration-300 ease-out pointer-events-none"
                     style="left: 2px; width: 33.3%;"></div>

                <!-- Tab 1: All Users -->
                <button type="button" 
                        id="tabBtnAll" 
                        onclick="switchTab('all')" 
                        class="tab-btn flex-1 relative z-10 py-1 sm:py-1.5 text-xs font-bold rounded-md transition-colors text-[#1555a8] dark:text-white flex items-center justify-center gap-1">
                    <span>All</span>
                    <span class="text-[10px] px-1 py-0.2 rounded-full bg-blue-100 dark:bg-blue-900/60 text-[#1555a8] dark:text-blue-200 font-semibold tabular-nums">
                        {{ number_format($khataSummary['total_users'] ?? 0) }}
                    </span>
                </button>

                <!-- Tab 2: Creditors -->
                <button type="button" 
                        id="tabBtnCreditor" 
                        onclick="switchTab('creditor')" 
                        class="tab-btn flex-1 relative z-10 py-1 sm:py-1.5 text-xs font-bold rounded-md transition-colors text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white flex items-center justify-center gap-1">
                    <span>Creditors</span>
                    <span class="text-[10px] px-1 py-0.2 rounded-full bg-orange-100 dark:bg-orange-900/60 text-orange-800 dark:text-orange-200 font-semibold tabular-nums">
                        {{ number_format($khataSummary['creditors_count'] ?? 0) }}
                    </span>
                </button>

                <!-- Tab 3: Non-Creditors / Cash -->
                <button type="button" 
                        id="tabBtnNonCreditor" 
                        onclick="switchTab('non_creditor')" 
                        class="tab-btn flex-1 relative z-10 py-1 sm:py-1.5 text-xs font-bold rounded-md transition-colors text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white flex items-center justify-center gap-1">
                    <span>Cash</span>
                    <span class="text-[10px] px-1 py-0.2 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold tabular-nums">
                        {{ number_format($khataSummary['non_creditors_count'] ?? 0) }}
                    </span>
                </button>
            </div>

            <!-- Row 2: Search Input & View Mode Toggle (In ONE row) -->
            <div class="flex items-center gap-1.5">
                <div class="relative flex-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-gray-400">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" 
                           id="customUserSearchInput" 
                           placeholder="Search name, phone, email..." 
                           autocomplete="off"
                           class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/90 dark:bg-gray-900 py-1.5 pl-8 pr-7 text-xs text-gray-900 dark:text-white outline-none focus:border-[#1555a8] focus:bg-white dark:focus:bg-gray-900 transition-all">
                    <button type="button" 
                            id="clearSearchBtn" 
                            onclick="clearUserSearch()"
                            class="hidden absolute inset-y-0 right-0 pr-2 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- View Mode Toggle Buttons -->
                <div class="inline-flex items-center p-0.5 rounded-lg bg-gray-100 dark:bg-gray-900 border border-gray-200/80 dark:border-gray-700 shrink-0">
                    <button type="button" 
                            id="btnViewCards" 
                            onclick="setUserViewMode('cards')" 
                            title="Cards View"
                            class="p-1.5 sm:px-2.5 sm:py-1 text-xs font-semibold rounded-md transition-all flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span class="hidden sm:inline">Cards</span>
                    </button>
                    <button type="button" 
                            id="btnViewTable" 
                            onclick="setUserViewMode('table')" 
                            title="Table View"
                            class="p-1.5 sm:px-2.5 sm:py-1 text-xs font-semibold rounded-md transition-all flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="hidden sm:inline">Table</span>
                    </button>
                </div>

                <select id="customPageLength" onchange="onPageLengthChange(this.value)" class="hidden sm:block text-xs bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1 text-gray-700 dark:text-gray-300">
                    <option value="10">10</option>
                    <option value="25" selected>25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

            <!-- VIEW 1: COMPACT HIGH-DENSITY CARDS (DEFAULT ON MOBILE) -->
            <div id="mobileCardsViewWrapper" class="pt-1">
                <div id="mobileCardsContainer" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-2 sm:gap-2.5 transition-transform duration-300 ease-out">
                    <!-- Populated dynamically via DataTables drawCallback -->
                </div>
                <div id="cardsEmptyState" class="hidden p-6 text-center text-xs text-gray-500 bg-gray-50/60 dark:bg-gray-900/40 rounded-xl border border-gray-200 dark:border-gray-700">
                    No customers found matching current filters.
                </div>
            </div>

            <!-- VIEW 2: COMPACT TABLE VIEW (WITH HORIZONTAL SCROLL) -->
            <div id="tableViewWrapper" class="hidden pt-1">
                <div id="slidingTableContainer" class="max-w-full overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700/80 shadow-2xs transition-transform duration-300 ease-out">
                    <table id="usersTable" class="min-w-full divide-y divide-gray-100 dark:divide-gray-700 border-collapse text-left" style="width:100%">
                        <thead>
                            <tr class="bg-gray-50/80 dark:bg-meta-4 text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <th class="w-6 px-1.5 py-1.5 text-center"></th>
                                <th class="w-8 px-1 py-1.5 text-center">Avatar</th>
                                <th class="px-2 py-1.5 text-left min-w-[130px]">Customer / Mobile</th>
                                <th class="px-2 py-1.5 text-center whitespace-nowrap">Khata Type</th>
                                <th class="px-2 py-1.5 text-right whitespace-nowrap">Khata Due (Udhar)</th>
                                <th class="px-2 py-1.5 text-right whitespace-nowrap">Credit Balance</th>
                                <th class="px-2 py-1.5 text-right whitespace-nowrap">Main Balance</th>
                                <th class="px-2 py-1.5 text-right whitespace-nowrap">Shop Done</th>
                                <th class="px-2 py-1.5 text-right whitespace-nowrap">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 text-xs">
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Unified Bottom Pagination & Info Bar (Synchronized for Both Views) -->
            <div id="unifiedPaginationBar" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pt-2 border-t border-gray-100 dark:border-gray-700/80 text-xs text-gray-500">
                <div id="customTableInfo" class="text-center sm:text-left text-[11px] sm:text-xs text-gray-500 dark:text-gray-400">
                    <!-- Populated dynamically via DataTables -->
                </div>
                <div id="customPaginationButtons" class="flex items-center justify-center sm:justify-end gap-1 flex-wrap">
                    <!-- Populated dynamically via DataTables -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DataTables & Responsive CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
    /* Clean up default DataTables native containers */
    .dataTables_wrapper {
        position: relative;
        clear: both;
    }
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        display: none !important; /* Managed via unified custom UI controls */
    }
    table.dataTable.no-footer {
        border-bottom: 1px solid #e2e8f0;
    }
    .dark table.dataTable.no-footer {
        border-bottom: 1px solid #2e3a47;
    }
    table.dataTable thead th {
        border-bottom: 1px solid #e2e8f0;
    }
    .dark table.dataTable thead th {
        border-bottom: 1px solid #2e3a47;
    }
    #usersTable {
        width: 100% !important;
        font-size: 0.75rem;
    }
    #usersTable th {
        padding: 0.35rem 0.5rem !important;
        font-size: 0.6875rem !important;
    }
    #usersTable td {
        overflow: visible !important;
        vertical-align: middle;
        padding: 0.25rem 0.5rem !important;
        line-height: 1.25;
    }
    #usersTable tbody tr {
        cursor: pointer;
        transition: background-color 0.15s ease-in-out;
    }
    #usersTable tbody tr:hover {
        background-color: rgba(243, 244, 246, 0.6);
    }
    .dark #usersTable tbody tr:hover {
        background-color: rgba(36, 48, 63, 0.6);
    }
    tr.details-shown {
        background-color: rgba(239, 246, 255, 0.6) !important;
    }
    .dark tr.details-shown {
        background-color: rgba(30, 58, 138, 0.15) !important;
    }
    tr.details-shown > td {
        border-bottom: 0 !important;
    }

    /* Screen sliding animations */
    .slide-left-out {
        transform: translateX(-30px);
        opacity: 0.3;
    }
    .slide-right-out {
        transform: translateX(30px);
        opacity: 0.3;
    }
    .slide-in {
        transform: translateX(0);
        opacity: 1;
    }

    /* Custom scrollbar for horizontal table */
    #slidingTableContainer::-webkit-scrollbar {
        height: 6px;
    }
    #slidingTableContainer::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.04);
        border-radius: 9999px;
    }
    #slidingTableContainer::-webkit-scrollbar-thumb {
        background: rgba(156, 163, 175, 0.5);
        border-radius: 9999px;
    }
    .dark #slidingTableContainer::-webkit-scrollbar-thumb {
        background: rgba(75, 85, 99, 0.6);
    }

    /* User card hover effect */
    .user-mobile-card {
        backface-visibility: hidden;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let currentCreditType = 'all';
    let usersDataTable = null;
    let currentUserView = 'cards';
    const tabOrder = ['all', 'creditor', 'non_creditor'];

    // Detect initial view: on screens < 768px default to 'cards', otherwise use stored preference or 'table'
    function initUserViewMode() {
        const stored = localStorage.getItem('admin_users_view');
        if (stored === 'cards' || stored === 'table') {
            currentUserView = stored;
        } else {
            currentUserView = window.innerWidth < 768 ? 'cards' : 'table';
        }
        applyUserViewMode(currentUserView, false);
    }

    function setUserViewMode(mode) {
        if (currentUserView === mode) return;
        currentUserView = mode;
        localStorage.setItem('admin_users_view', mode);
        applyUserViewMode(mode, true);
    }

    function applyUserViewMode(mode, animate = true) {
        const btnCards = document.getElementById('btnViewCards');
        const btnTable = document.getElementById('btnViewTable');
        const cardsWrapper = document.getElementById('mobileCardsViewWrapper');
        const tableWrapper = document.getElementById('tableViewWrapper');

        if (mode === 'cards') {
            if (cardsWrapper) cardsWrapper.classList.remove('hidden');
            if (tableWrapper) tableWrapper.classList.add('hidden');

            if (btnCards) {
                btnCards.classList.remove('text-gray-600', 'dark:text-gray-400');
                btnCards.classList.add('bg-white', 'dark:bg-[#1555a8]', 'text-[#1555a8]', 'dark:text-white', 'shadow-xs');
            }
            if (btnTable) {
                btnTable.classList.remove('bg-white', 'dark:bg-[#1555a8]', 'text-[#1555a8]', 'dark:text-white', 'shadow-xs');
                btnTable.classList.add('text-gray-600', 'dark:text-gray-400');
            }
        } else {
            if (cardsWrapper) cardsWrapper.classList.add('hidden');
            if (tableWrapper) tableWrapper.classList.remove('hidden');

            if (btnTable) {
                btnTable.classList.remove('text-gray-600', 'dark:text-gray-400');
                btnTable.classList.add('bg-white', 'dark:bg-[#1555a8]', 'text-[#1555a8]', 'dark:text-white', 'shadow-xs');
            }
            if (btnCards) {
                btnCards.classList.remove('bg-white', 'dark:bg-[#1555a8]', 'text-[#1555a8]', 'dark:text-white', 'shadow-xs');
                btnCards.classList.add('text-gray-600', 'dark:text-gray-400');
            }
        }
    }

    function updateGliderPosition(tabType) {
        const glider = document.getElementById('slidingGlider');
        const container = document.getElementById('sliderNavContainer');
        let targetBtn = document.getElementById('tabBtnAll');

        if (tabType === 'creditor') targetBtn = document.getElementById('tabBtnCreditor');
        if (tabType === 'non_creditor') targetBtn = document.getElementById('tabBtnNonCreditor');

        if (glider && targetBtn && container) {
            const containerRect = container.getBoundingClientRect();
            const btnRect = targetBtn.getBoundingClientRect();
            const leftOffset = btnRect.left - containerRect.left;
            
            glider.style.left = `${leftOffset}px`;
            glider.style.width = `${btnRect.width}px`;
        }

        // Update button text styling
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('text-[#1555a8]', 'dark:text-white');
            btn.classList.add('text-gray-600', 'dark:text-gray-400');
        });
        targetBtn.classList.remove('text-gray-600', 'dark:text-gray-400');
        targetBtn.classList.add('text-[#1555a8]', 'dark:text-white');

        // Update heading text
        const headings = {
            'all': 'Showing All Customers',
            'creditor': 'Showing Creditors Only',
            'non_creditor': 'Showing Non-Creditors Only'
        };
        const desktopHeadingEl = document.getElementById('activeFilterHeading');
        const mobileHeadingEl = document.getElementById('mobileFilterHeading');
        if (desktopHeadingEl) desktopHeadingEl.textContent = headings[tabType] || 'Customer Directory';
        if (mobileHeadingEl) mobileHeadingEl.textContent = headings[tabType] || 'Customer Directory';
    }

    function switchTab(tabType, direction = null) {
        if (currentCreditType === tabType && usersDataTable) return;

        const oldIndex = tabOrder.indexOf(currentCreditType);
        const newIndex = tabOrder.indexOf(tabType);
        const slideDir = direction || (newIndex > oldIndex ? 'left' : 'right');

        currentCreditType = tabType;
        updateGliderPosition(tabType);

        // Visual screen sliding animation
        const tableContainer = document.getElementById('slidingTableContainer');
        const cardsContainer = document.getElementById('mobileCardsContainer');
        const activeContainer = currentUserView === 'cards' ? cardsContainer : tableContainer;

        if (activeContainer) {
            activeContainer.classList.add(slideDir === 'left' ? 'slide-left-out' : 'slide-right-out');
            setTimeout(() => {
                activeContainer.classList.remove('slide-left-out', 'slide-right-out');
                activeContainer.classList.add('slide-in');
                if (usersDataTable) {
                    usersDataTable.ajax.reload(null, false);
                }
            }, 180);
        } else if (usersDataTable) {
            usersDataTable.ajax.reload();
        }
    }

    function toggleChildRow(tr) {
        if (!usersDataTable) return;
        const row = usersDataTable.row(tr);
        if (!row) return;

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('details-shown');
            tr.find('.main-chevron, .detail-chevron').removeClass('rotate-180');
        } else {
            const rowData = row.data();
            if (rowData && rowData[13]) {
                const childContainer = $('<div class="p-2 sm:p-3"></div>').html(rowData[13]);
                row.child(childContainer, 'p-0 bg-transparent border-t-0').show();
                tr.addClass('details-shown');
                tr.find('.main-chevron, .detail-chevron').addClass('rotate-180');
            }
        }
    }

    function renderMobileCards(api) {
        const cardsContainer = $('#mobileCardsContainer');
        const emptyState = $('#cardsEmptyState');
        cardsContainer.empty();

        const data = api.rows({ page: 'current' }).data();
        if (data.length === 0) {
            emptyState.removeClass('hidden');
        } else {
            emptyState.addClass('hidden');
            data.each(function(rowData) {
                // If rowData has pre-rendered card at index 14, append it
                if (rowData[14]) {
                    cardsContainer.append(rowData[14]);
                }
            });
        }
    }

    function renderCustomPagination(api) {
        const info = api.page.info();
        const infoEl = $('#customTableInfo');
        const buttonsEl = $('#customPaginationButtons');

        if (info.recordsDisplay === 0) {
            infoEl.text('Showing 0 customers');
            buttonsEl.empty();
            return;
        }

        const start = info.start + 1;
        const end = info.end;
        const total = info.recordsDisplay;
        infoEl.html(`Showing <span class="font-bold text-gray-800 dark:text-gray-200">${start}</span> to <span class="font-bold text-gray-800 dark:text-gray-200">${end}</span> of <span class="font-bold text-gray-800 dark:text-gray-200">${total}</span> customers`);

        buttonsEl.empty();

        // Prev Button
        const prevBtn = $(`<button type="button" class="px-2.5 py-1 text-xs font-semibold rounded-lg transition-colors ${info.page === 0 ? 'opacity-40 cursor-not-allowed bg-gray-100 dark:bg-gray-800 text-gray-400' : 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200'}">Previous</button>`);
        if (info.page > 0) {
            prevBtn.on('click', () => usersDataTable.page('previous').draw('page'));
        }
        buttonsEl.append(prevBtn);

        // Numeric Page Pill
        const pagePill = $(`<span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-[#1555a8] text-white">${info.page + 1} / ${info.pages || 1}</span>`);
        buttonsEl.append(pagePill);

        // Next Button
        const nextBtn = $(`<button type="button" class="px-2.5 py-1 text-xs font-semibold rounded-lg transition-colors ${info.page >= info.pages - 1 ? 'opacity-40 cursor-not-allowed bg-gray-100 dark:bg-gray-800 text-gray-400' : 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200'}">Next</button>`);
        if (info.page < info.pages - 1) {
            nextBtn.on('click', () => usersDataTable.page('next').draw('page'));
        }
        buttonsEl.append(nextBtn);
    }

    function onPageLengthChange(val) {
        if (usersDataTable) {
            usersDataTable.page.len(parseInt(val, 10)).draw();
        }
    }

    function clearUserSearch() {
        const input = document.getElementById('customUserSearchInput');
        if (input) {
            input.value = '';
            document.getElementById('clearSearchBtn').classList.add('hidden');
            if (usersDataTable) {
                usersDataTable.search('').draw();
            }
        }
    }

    $(document).ready(function(){
        initUserViewMode();

        // Initialize glider position
        setTimeout(() => updateGliderPosition('all'), 120);
        window.addEventListener('resize', () => {
            updateGliderPosition(currentCreditType);
        });

        usersDataTable = $('#usersTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: false,
            ajax: {
                url: "{{ route('admin.users.data') }}",
                data: function(d) {
                    d.credit_type = currentCreditType;
                }
            },
            columns: [
                {
                    className: 'dt-control text-center cursor-pointer select-none w-8',
                    orderable: false,
                    searchable: false,
                    data: null,
                    defaultContent: '<button type="button" class="child-chevron-btn p-1 text-gray-400 hover:text-[#1555a8] rounded-md transition-all"><svg class="w-4 h-4 transform transition-transform duration-200 main-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></button>'
                },
                { data: 1, name: 'avatar', orderable: false, searchable: false, className: 'text-center w-10 sm:w-12' },
                { data: 2, name: 'name', className: 'text-left min-w-[140px]' },
                { data: 4, name: 'khata_type', className: 'text-center', orderable: false },
                { data: 8, name: 'used_credit', className: 'text-right' },
                { data: 9, name: 'available_credit', className: 'text-right' },
                { data: 5, name: 'main_balance', className: 'text-right' },
                { data: 6, name: 'shop_done', className: 'text-right' },
                { data: 12, name: 'actions', orderable: false, searchable: false, className: 'text-right' }
            ],
            order: [[2, 'asc']],
            pageLength: 25,
            drawCallback: function() {
                const api = this.api();
                renderMobileCards(api);
                renderCustomPagination(api);
            }
        });

        // Search input debounce sync
        let searchDebounceTimer = null;
        $('#customUserSearchInput').on('input', function() {
            const query = $(this).val();
            const clearBtn = $('#clearSearchBtn');
            if (query.length > 0) {
                clearBtn.removeClass('hidden');
            } else {
                clearBtn.addClass('hidden');
            }

            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(() => {
                if (usersDataTable) {
                    usersDataTable.search(query).draw();
                }
            }, 250);
        });

        // Click on chevron or control cell in Table view
        $('#usersTable tbody').on('click', 'td.dt-control, .child-chevron-btn', function (e) {
            e.stopPropagation();
            toggleChildRow($(this).closest('tr'));
        });

        // Click on Details button in Table action column
        $('#usersTable tbody').on('click', '.js-toggle-details', function (e) {
            e.stopPropagation();
            toggleChildRow($(this).closest('tr'));
        });

        // Click anywhere on table row except interactive links/buttons/inputs/avatar
        $('#usersTable tbody').on('click', 'tr', function (e) {
            if ($(e.target).closest('a, button, input, select, .js-user-info, .dt-control').length) {
                return;
            }
            toggleChildRow($(this));
        });

        // Card Accordion Drawer toggle
        $(document).on('click', '.js-toggle-card-drawer', function(e) {
            e.stopPropagation();
            const card = $(this).closest('.user-mobile-card');
            const drawer = card.find('.card-child-drawer');
            const chevron = $(this).find('.card-chevron');
            
            drawer.slideToggle(200);
            chevron.toggleClass('rotate-180');
        });

        // ==========================================
        // TOUCH SWIPE / SCREEN SLIDING GESTURE
        // ==========================================
        let touchStartX = 0;
        let touchEndX = 0;
        const swipeZone = document.getElementById('slidingTableWrapper');

        if (swipeZone) {
            swipeZone.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            }, { passive: true });

            swipeZone.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipeGesture();
            }, { passive: true });
        }

        function handleSwipeGesture() {
            const swipeDistance = touchEndX - touchStartX;
            const threshold = 60; // minimum swipe distance

            if (Math.abs(swipeDistance) < threshold) return;

            const currentIndex = tabOrder.indexOf(currentCreditType);

            if (swipeDistance < -threshold) {
                // Swiped Left -> Move to Next tab
                if (currentIndex < tabOrder.length - 1) {
                    switchTab(tabOrder[currentIndex + 1], 'left');
                }
            } else if (swipeDistance > threshold) {
                // Swiped Right -> Move to Previous tab
                if (currentIndex > 0) {
                    switchTab(tabOrder[currentIndex - 1], 'right');
                }
            }
        }

        // Handle avatar click for quick financial popup
        $(document).on('click', '.js-user-info', function(e) {
            e.stopPropagation();
            const data = $(this).data();
            const symbol = '₹';
            const isOverdue = parseFloat((data.overdue || '0').toString().replace(/,/g, '')) > 0;
            
            Swal.fire({
                title: `<div class="text-lg sm:text-xl font-bold text-black dark:text-white">${data.name}</div>`,
                html: `
                    <div class="flex flex-col items-center justify-center space-y-3.5 py-3 text-left">
                        <img src="${data.avatar}" class="w-16 h-16 sm:w-20 sm:h-20 rounded-full border-4 border-[#1555a8] shadow-md mb-1 object-cover" />
                        
                        <!-- Financial Overview Grid -->
                        <div class="w-full grid grid-cols-2 gap-2 sm:gap-2.5">
                            <div class="p-2 sm:p-2.5 rounded-xl bg-blue-50/80 dark:bg-meta-4 border border-blue-100 dark:border-strokedark">
                                <div class="text-[10px] text-gray-500 uppercase font-bold">Main Wallet</div>
                                <div class="text-sm sm:text-base font-bold text-blue-600 mt-0.5">${symbol}${data.wallet}</div>
                            </div>
                            <div class="p-2 sm:p-2.5 rounded-xl bg-green-50/80 dark:bg-meta-4 border border-green-100 dark:border-strokedark">
                                <div class="text-[10px] text-gray-500 uppercase font-bold">Total Commission</div>
                                <div class="text-sm sm:text-base font-bold text-green-600 mt-0.5">${symbol}${data.commission}</div>
                            </div>
                            <div class="p-2 sm:p-2.5 rounded-xl bg-orange-50/80 dark:bg-meta-4 border border-orange-100 dark:border-strokedark">
                                <div class="text-[10px] text-gray-500 uppercase font-bold">Credit Due (Udhar)</div>
                                <div class="text-sm sm:text-base font-bold text-orange-600 mt-0.5">${symbol}${data.creditUsed}</div>
                            </div>
                            <div class="p-2 sm:p-2.5 rounded-xl ${isOverdue ? 'bg-red-50' : 'bg-gray-50'} dark:bg-meta-4 border ${isOverdue ? 'border-red-200' : 'border-stroke'} dark:border-strokedark">
                                <div class="text-[10px] ${isOverdue ? 'text-red-600' : 'text-gray-500'} uppercase font-bold">Overdue Amount</div>
                                <div class="text-sm sm:text-base font-bold ${isOverdue ? 'text-red-600' : 'text-gray-800 dark:text-white'} mt-0.5">${symbol}${data.overdue}</div>
                            </div>
                        </div>

                        <!-- User Details Strip -->
                        <div class="w-full bg-gray-50 dark:bg-meta-4 rounded-xl p-3 space-y-1.5 text-xs">
                            <div class="flex justify-between border-b border-stroke dark:border-strokedark pb-1">
                                <span class="font-semibold text-gray-600 dark:text-gray-300">Phone:</span>
                                <span class="text-black dark:text-white font-mono">${data.phone}</span>
                            </div>
                            <div class="flex justify-between border-b border-stroke dark:border-strokedark pb-1">
                                <span class="font-semibold text-gray-600 dark:text-gray-300">KYC Status:</span>
                                <span class="font-bold uppercase text-[11px] ${data.kycStatus === 'approved' ? 'text-success' : (data.kycStatus === 'rejected' ? 'text-danger' : 'text-warning')}">${data.kycStatus || 'Unfilled'}</span>
                            </div>
                            <div class="flex justify-between border-b border-stroke dark:border-strokedark pb-1">
                                <span class="font-semibold text-gray-600 dark:text-gray-300">Aadhaar / PAN:</span>
                                <span class="text-black dark:text-white font-mono">${data.aadhaar || 'N/A'} / ${data.pan || 'N/A'}</span>
                            </div>
                            <div class="flex justify-between border-b border-stroke dark:border-strokedark pb-1">
                                <span class="font-semibold text-gray-600 dark:text-gray-300">Credit Limit:</span>
                                <span class="text-black dark:text-white font-bold">${symbol}${parseFloat(data.creditLimit || 0).toLocaleString()}</span>
                            </div>
                            <div class="flex justify-between border-b border-stroke dark:border-strokedark pb-1">
                                <span class="font-semibold text-gray-600 dark:text-gray-300">Referred By:</span>
                                <span class="text-black dark:text-white font-medium">${data.refBy}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-semibold text-gray-600 dark:text-gray-300">Referral Code:</span>
                                <span class="text-black dark:text-white font-mono bg-white dark:bg-boxdark px-2 rounded border border-stroke dark:border-strokedark">${data.refCode}</span>
                            </div>
                        </div>

                        <!-- Direct Khata Link -->
                        <a href="/admin/customer-lookup?user_id=${data.id}" class="w-full py-2.5 px-4 rounded-xl bg-[#1555a8] hover:bg-[#12468d] text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-2">
                            <span>📖 Open Full Khata Statement</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                `,
                showCancelButton: true,
                showConfirmButton: true,
                confirmButtonText: 'Edit Profile',
                cancelButtonText: 'Close',
                confirmButtonColor: '#1555a8',
                customClass: {
                    popup: 'rounded-2xl max-w-sm sm:max-w-md w-full'
                },
                background: document.documentElement.classList.contains('dark') ? '#24303F' : '#fff',
                color: document.documentElement.classList.contains('dark') ? '#fff' : '#000'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `/admin/users/${data.id}/edit`;
                }
            });
        });

        // Delegate Quick Onboard Click
        $(document).on('click', '.js-quick-onboard-btn', function(e) {
            e.stopPropagation();
            const btn = $(this);
            const user = {
                id: btn.data('id'),
                name: btn.data('name'),
                email: btn.data('email'),
                phone: btn.data('phone'),
                avatar: btn.data('avatar'),
                aadhaar: btn.data('aadhaar'),
                pan: btn.data('pan'),
                address: btn.data('address'),
                nominee: btn.data('nominee-name') ? (btn.data('nominee-name') + ' (' + (btn.data('nominee-rel') || 'Nominee') + ')') : 'N/A',
                upline: btn.data('upline'),
                creditLimit: btn.data('credit-limit') || 5000,
                kycStatus: btn.data('kyc-status'),
                status: btn.data('status')
            };
            if (typeof openOnboardModal === 'function') {
                openOnboardModal(user);
            }
        });

        // Delegate Delete Click
        $(document).on('click', '.js-delete', function(e){
            e.preventDefault();
            e.stopPropagation();
            const id = $(this).data('id');
            
            Swal.fire({
                title: 'Are you sure?',
                text: "All user data including commissions and wallet will be lost.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/users/' + id,
                        method: 'POST',
                        data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                        success(){ 
                            Swal.fire('Deleted!', 'User has been deleted.', 'success');
                            usersDataTable.ajax.reload();
                        },
                        error(){ 
                            Swal.fire('Error!', 'Delete failed. This user might have active orders or dependencies.', 'error');
                        }
                    });
                }
            });
        });
        // Intercept customer-lookup clicks to open React/SPA-style AJAX Khata sheet
        $(document).on('click', 'a[href*="customer-lookup"]', function(e) {
            const href = $(this).attr('href');
            if (!href) return;
            try {
                if (href.includes('user_id=')) {
                    e.preventDefault();
                    e.stopPropagation();
                    const url = new URL(href, window.location.origin);
                    const userId = url.searchParams.get('user_id');
                    if (userId) {
                        openKhataModal(userId);
                    }
                }
            } catch(err) {
                console.error(err);
            }
        });
    });
</script>

@include('admin.partials.onboard_modal')
@include('admin.partials.khata_modal')

@endsection
