@extends('admin.layout')

@section('content')
<div class="mx-auto max-w-270 space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md2 font-bold text-black dark:text-white flex items-center gap-2.5">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                </div>
                <span>Bulk ID Cards &amp; QR Passes</span>
            </h2>
            <p class="text-xs sm:text-sm text-body dark:text-bodydark mt-1">
                Generate, filter, and batch print standard CR80 Identity Cards and Scan &amp; Pay Wallet Passes.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('admin.settings') }}#idcard" class="inline-flex items-center gap-1.5 rounded-lg border border-stroke bg-white px-4 py-2 text-xs font-semibold text-black hover:shadow-1 dark:border-strokedark dark:bg-meta-4 dark:text-white transition">
                <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>ID Card Settings</span>
            </a>
            <a href="{{ route('admin.users') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-stroke bg-white px-4 py-2 text-xs font-semibold text-black hover:shadow-1 dark:border-strokedark dark:bg-meta-4 dark:text-white transition">
                <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21a6 6 0 00-9-5.197M15 21a6 6 0 006-6v-1a6 6 0 00-6-6h-1.333a6 6 0 00-11.334 0v1a6 6 0 006 6h1.333z" />
                </svg>
                <span>User Management</span>
            </a>
        </div>
    </div>

    <!-- Quick Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-stroke bg-white p-4 shadow-sm dark:border-strokedark dark:bg-boxdark">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Total Members</span>
            <div class="mt-1 flex items-baseline justify-between">
                <span class="text-2xl font-extrabold text-black dark:text-white">{{ number_format($stats['total']) }}</span>
                <span class="text-[11px] font-bold text-indigo-600 bg-indigo-50 dark:bg-indigo-950/40 px-2 py-0.5 rounded-full">All</span>
            </div>
        </div>

        <div class="rounded-2xl border border-stroke bg-white p-4 shadow-sm dark:border-strokedark dark:bg-boxdark">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">KYC Approved</span>
            <div class="mt-1 flex items-baseline justify-between">
                <span class="text-2xl font-extrabold text-emerald-600">{{ number_format($stats['kyc_approved']) }}</span>
                <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-full">Verified</span>
            </div>
        </div>

        <div class="rounded-2xl border border-stroke bg-white p-4 shadow-sm dark:border-strokedark dark:bg-boxdark">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">With Photo</span>
            <div class="mt-1 flex items-baseline justify-between">
                <span class="text-2xl font-extrabold text-blue-600">{{ number_format($stats['with_avatar']) }}</span>
                <span class="text-[11px] font-bold text-blue-600 bg-blue-50 dark:bg-blue-950/40 px-2 py-0.5 rounded-full">Ready</span>
            </div>
        </div>

        <div class="rounded-2xl border border-stroke bg-white p-4 shadow-sm dark:border-strokedark dark:bg-boxdark">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Customers</span>
            <div class="mt-1 flex items-baseline justify-between">
                <span class="text-2xl font-extrabold text-purple-600">{{ number_format($stats['total_customers']) }}</span>
                <span class="text-[11px] font-bold text-purple-600 bg-purple-50 dark:bg-purple-950/40 px-2 py-0.5 rounded-full">Shoppers</span>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="rounded-2xl border border-stroke bg-white p-5 shadow-sm dark:border-strokedark dark:bg-boxdark">
        <form method="GET" action="{{ route('admin.id-cards.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                <!-- Search Input -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Search Keyword</label>
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Name, Phone, Email, User #, Ref..." 
                               class="w-full rounded-xl border border-stroke bg-gray-50/50 py-2 pl-9 pr-3 text-xs font-medium text-black focus:border-primary focus:bg-white focus:outline-hidden dark:border-strokedark dark:bg-meta-4 dark:text-white" />
                        <svg class="w-4 h-4 absolute left-3 top-2.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Role Filter -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">User Role</label>
                    <select name="role" class="w-full rounded-xl border border-stroke bg-gray-50/50 py-2 px-3 text-xs font-medium text-black focus:border-primary focus:bg-white focus:outline-hidden dark:border-strokedark dark:bg-meta-4 dark:text-white">
                        <option value="all">All Roles</option>
                        <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>Members / Customers</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admins</option>
                    </select>
                </div>

                <!-- KYC Status -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">KYC Status</label>
                    <select name="kyc_status" class="w-full rounded-xl border border-stroke bg-gray-50/50 py-2 px-3 text-xs font-medium text-black focus:border-primary focus:bg-white focus:outline-hidden dark:border-strokedark dark:bg-meta-4 dark:text-white">
                        <option value="all">All Statuses</option>
                        <option value="approved" {{ request('kyc_status') === 'approved' ? 'selected' : '' }}>Approved / Verified</option>
                        <option value="pending" {{ request('kyc_status') === 'pending' ? 'selected' : '' }}>Pending Approval</option>
                        <option value="unfilled" {{ request('kyc_status') === 'unfilled' ? 'selected' : '' }}>Unfilled / None</option>
                        <option value="rejected" {{ request('kyc_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <!-- Has Photo -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Avatar / Photo</label>
                    <select name="has_photo" class="w-full rounded-xl border border-stroke bg-gray-50/50 py-2 px-3 text-xs font-medium text-black focus:border-primary focus:bg-white focus:outline-hidden dark:border-strokedark dark:bg-meta-4 dark:text-white">
                        <option value="all">All Users</option>
                        <option value="yes" {{ request('has_photo') === 'yes' ? 'selected' : '' }}>With Photo Only</option>
                        <option value="no" {{ request('has_photo') === 'no' ? 'selected' : '' }}>Without Photo</option>
                    </select>
                </div>

                <!-- Per Page -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Per Page</label>
                    <select name="per_page" class="w-full rounded-xl border border-stroke bg-gray-50/50 py-2 px-3 text-xs font-medium text-black focus:border-primary focus:bg-white focus:outline-hidden dark:border-strokedark dark:bg-meta-4 dark:text-white">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 cards</option>
                        <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25 cards</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 cards</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 cards</option>
                        <option value="200" {{ request('per_page') == 200 ? 'selected' : '' }}>200 cards</option>
                    </select>
                </div>
            </div>

            <!-- Date Range & Action Buttons Row -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2 border-t border-gray-100 dark:border-gray-800">
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-gray-500">Registered:</span>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-lg border border-stroke bg-gray-50 px-2 py-1 text-xs dark:border-strokedark dark:bg-meta-4 dark:text-white">
                    <span class="text-gray-400">to</span>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-lg border border-stroke bg-gray-50 px-2 py-1 text-xs dark:border-strokedark dark:bg-meta-4 dark:text-white">
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-xs font-semibold text-white hover:bg-opacity-90 transition shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span>Apply Filters</span>
                    </button>
                    @if(request()->hasAny(['search', 'role', 'kyc_status', 'has_photo', 'date_from', 'date_to', 'per_page']))
                        <a href="{{ route('admin.id-cards.index') }}" class="inline-flex items-center gap-1 rounded-lg border border-stroke bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-strokedark dark:bg-meta-4 dark:text-white transition">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Main Bulk Form Wrapper -->
    <form id="bulk-id-card-form" method="POST" action="{{ route('admin.id-cards.print') }}" target="_blank">
        @csrf
        <!-- Hidden carryover of current filters in case "Select All Filtered" is clicked -->
        <input type="hidden" name="search" value="{{ request('search') }}">
        <input type="hidden" name="role" value="{{ request('role') }}">
        <input type="hidden" name="kyc_status" value="{{ request('kyc_status') }}">
        <input type="hidden" name="has_photo" value="{{ request('has_photo') }}">

        <!-- Sticky Floating / Top Bulk Actions Bar -->
        <div class="sticky top-20 z-30 mb-4 rounded-2xl border-2 border-indigo-200 bg-white/95 backdrop-blur p-4 shadow-lg dark:border-indigo-900/60 dark:bg-boxdark/95 transition-all">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                
                <!-- Left: Selection Info & Quick Toggles -->
                <div class="flex flex-wrap items-center gap-3">
                    <label class="inline-flex items-center gap-2 cursor-pointer font-bold text-xs text-black dark:text-white">
                        <input type="checkbox" id="select-all-checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span>Select All On Page</span>
                    </label>

                    <span class="text-gray-300 dark:text-gray-700">|</span>

                    <div class="inline-flex items-center gap-2">
                        <span class="inline-flex items-center justify-center rounded-full bg-indigo-600 px-2.5 py-0.5 text-xs font-extrabold text-white" id="selected-badge">
                            0
                        </span>
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300">users selected</span>
                    </div>

                    <button type="button" id="clear-selection-btn" class="hidden text-xs text-red-500 hover:text-red-700 font-semibold underline ml-1">
                        Clear Selection
                    </button>
                </div>

                <!-- Right: Print Options Controls & Print Trigger -->
                <div class="flex flex-wrap items-center gap-2.5">
                    <!-- Layout Mode Selector -->
                    <div class="flex items-center gap-1.5 bg-gray-100 dark:bg-gray-800 p-1 rounded-xl">
                        <label class="text-[11px] font-bold text-gray-500 dark:text-gray-400 pl-2">Layout:</label>
                        <select name="layout" class="rounded-lg border-0 bg-white py-1 px-2.5 text-xs font-bold text-black shadow-xs focus:ring-0 dark:bg-meta-4 dark:text-white">
                            <option value="a4" selected>A4 Sheet (Multi-Card Grid)</option>
                            <option value="cr80">CR80 Plastic Card Printer (1-by-1)</option>
                        </select>
                    </div>

                    <!-- Sides Selector -->
                    <div class="flex items-center gap-1.5 bg-gray-100 dark:bg-gray-800 p-1 rounded-xl">
                        <label class="text-[11px] font-bold text-gray-500 dark:text-gray-400 pl-2">Sides:</label>
                        <select name="side" class="rounded-lg border-0 bg-white py-1 px-2.5 text-xs font-bold text-black shadow-xs focus:ring-0 dark:bg-meta-4 dark:text-white">
                            <option value="both" selected>Both Sides (Front &amp; Back)</option>
                            <option value="front">Front Side Only</option>
                            <option value="back">Back Side (QR &amp; Wallet)</option>
                        </select>
                    </div>

                    <!-- Cutting Guides Toggle -->
                    <label class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-600 dark:text-gray-400 cursor-pointer pr-1">
                        <input type="checkbox" name="cutting_guides" value="1" checked class="h-3.5 w-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span>Cut Marks</span>
                    </label>

                    <!-- Big Print Button -->
                    <button type="submit" 
                            id="submit-print-btn" 
                            disabled 
                            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-extrabold text-white shadow-md hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span id="submit-print-text">Print Selected (0)</span>
                    </button>
                </div>

            </div>
        </div>

        <!-- Users Table -->
        <div class="rounded-2xl border border-stroke bg-white shadow-sm dark:border-strokedark dark:bg-boxdark overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full table-auto text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 uppercase text-[11px] font-extrabold tracking-wider dark:bg-meta-4 dark:text-gray-400 border-b border-stroke dark:border-strokedark">
                            <th class="py-3.5 px-4 text-center w-10">
                                <span class="sr-only">Select</span>
                            </th>
                            <th class="py-3.5 px-4">Member Info</th>
                            <th class="py-3.5 px-4">Contact</th>
                            <th class="py-3.5 px-4">Member ID / Referral</th>
                            <th class="py-3.5 px-4">KYC Status</th>
                            <th class="py-3.5 px-4">Joined Date</th>
                            <th class="py-3.5 px-4 text-right">Quick Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stroke dark:divide-strokedark text-xs font-medium">
                        @forelse($users as $user)
                        <tr class="hover:bg-gray-50/70 dark:hover:bg-white/[0.02] transition user-row">
                            <!-- Checkbox -->
                            <td class="py-3 px-4 text-center">
                                <input type="checkbox" 
                                       name="user_ids[]" 
                                       value="{{ $user->id }}" 
                                       class="user-select-checkbox h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            </td>

                            <!-- Member Info & Photo -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-9 shrink-0 overflow-hidden rounded-md border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                        @if($user->avatar)
                                            <img src="{{ asset($user->avatar) }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                                        @else
                                            <svg class="h-6 w-6 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-sm text-black dark:text-white truncate">
                                            {{ $user->name }}
                                        </p>
                                        <span class="inline-flex items-center text-[10px] uppercase font-bold tracking-wider px-1.5 py-0.2 rounded bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                            {{ $user->role }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Contact -->
                            <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                                <p class="font-bold text-black dark:text-white">{{ $user->phone ?? 'No Phone' }}</p>
                                <p class="text-[11px] text-gray-400 truncate max-w-[160px]">{{ $user->email }}</p>
                            </td>

                            <!-- Member ID & Ref -->
                            <td class="py-3 px-4">
                                <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400 block">
                                    #{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}
                                </span>
                                <span class="text-[10px] text-gray-500 font-mono">
                                    REF: {{ $user->referralRecord->referral_code ?? 'U'.$user->id }}
                                </span>
                            </td>

                            <!-- KYC Status -->
                            <td class="py-3 px-4">
                                @if($user->kyc_status === 'approved')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Verified
                                    </span>
                                @elseif($user->kyc_status === 'pending')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-600 dark:bg-amber-950/40 dark:text-amber-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Pending
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-bold text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                        Unverified
                                    </span>
                                @endif
                            </td>

                            <!-- Joined Date -->
                            <td class="py-3 px-4 text-gray-500 text-xs">
                                {{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}
                            </td>

                            <!-- Quick Action: Single Print & View -->
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.id-cards.single-print', $user->id) }}" 
                                       target="_blank" 
                                       class="inline-flex items-center gap-1 rounded-lg border border-indigo-200 bg-indigo-50/70 px-2.5 py-1 text-[11px] font-bold text-indigo-700 hover:bg-indigo-100 dark:border-indigo-800 dark:bg-indigo-950/40 dark:text-indigo-300 transition"
                                       title="Print Single Card">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                        <span>Print</span>
                                    </a>

                                    <a href="{{ route('admin.users.show', $user->id) }}" 
                                       class="p-1 rounded-lg text-gray-400 hover:text-black dark:hover:text-white transition"
                                       title="View User Details">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                    </svg>
                                    <p class="font-bold text-sm">No members found matching your search</p>
                                    <p class="text-xs text-gray-400 mt-1">Try adjusting the filters above or clearing the search keyword.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            @if($users->hasPages())
            <div class="p-4 border-t border-stroke dark:border-strokedark">
                {{ $users->links() }}
            </div>
            @endif
        </div>
    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    const userCheckboxes = document.querySelectorAll('.user-select-checkbox');
    const selectedBadge = document.getElementById('selected-badge');
    const submitBtn = document.getElementById('submit-print-btn');
    const submitText = document.getElementById('submit-print-text');
    const clearBtn = document.getElementById('clear-selection-btn');

    function updateSelectionState() {
        const checked = document.querySelectorAll('.user-select-checkbox:checked');
        const count = checked.length;

        selectedBadge.textContent = count;
        submitText.textContent = `Print Selected (${count})`;

        if (count > 0) {
            submitBtn.removeAttribute('disabled');
            clearBtn.classList.remove('hidden');
        } else {
            submitBtn.setAttribute('disabled', 'disabled');
            clearBtn.classList.add('hidden');
        }

        // Update row highlight
        userCheckboxes.forEach(cb => {
            const row = cb.closest('tr');
            if (row) {
                if (cb.checked) {
                    row.classList.add('bg-indigo-50/50', 'dark:bg-indigo-950/20');
                } else {
                    row.classList.remove('bg-indigo-50/50', 'dark:bg-indigo-950/20');
                }
            }
        });

        if (selectAllCheckbox) {
            selectAllCheckbox.checked = (count === userCheckboxes.length && userCheckboxes.length > 0);
            selectAllCheckbox.indeterminate = (count > 0 && count < userCheckboxes.length);
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            userCheckboxes.forEach(cb => {
                cb.checked = selectAllCheckbox.checked;
            });
            updateSelectionState();
        });
    }

    userCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateSelectionState);
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            userCheckboxes.forEach(cb => cb.checked = false);
            if (selectAllCheckbox) selectAllCheckbox.checked = false;
            updateSelectionState();
        });
    }

    // Initialize state
    updateSelectionState();
});
</script>
@endsection
