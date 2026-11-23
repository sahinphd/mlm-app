@extends('layouts.admin')

@section('content')
<div class="grid grid-cols-1 gap-4 md:gap-6 pb-28 md:pb-6">
    <!-- Dashboard Header & Referral -->
    <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 text-center">
        <h2 class="mb-3 text-xl font-semibold text-gray-800 dark:text-white/90 md:text-2xl">Dashboard</h2>
        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 md:text-base">Welcome, {{ auth()->user()->name }} — please complete your profile and request a wallet top-up to get started.</p>
        
        @if($referralRecord)
        <div class="mb-6 mx-auto w-full max-w-xl text-left">
            <div class="relative overflow-hidden rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50/70 via-white to-indigo-50/40 p-4 sm:p-5 shadow-xs dark:border-gray-800 dark:from-gray-900/80 dark:via-gray-900 dark:to-blue-950/20">
                <!-- Top Row: Icon, Title & Referral Code Badge -->
                <div class="flex flex-wrap items-center justify-between gap-2.5 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#1555a8]/10 text-[#1555a8] dark:bg-[#1555a8]/25 dark:text-blue-400">
                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white">Your Referral Link</span>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">Invite friends & earn direct rewards when they join</p>
                        </div>
                    </div>
                    
                    <button type="button" onclick="copyRefCodeDash('{{ $referralRecord->referral_code }}')" 
                            class="group inline-flex items-center gap-1.5 rounded-lg border border-blue-200/80 bg-white/90 px-2.5 py-1 text-xs font-semibold text-gray-700 shadow-2xs transition-all hover:bg-blue-50 hover:border-blue-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700" 
                            title="Click to copy Referral Code">
                        <span class="text-[10px] uppercase tracking-wider text-gray-400 dark:text-gray-400">Code:</span>
                        <span class="font-mono font-bold text-[#1555a8] dark:text-blue-400">{{ $referralRecord->referral_code }}</span>
                        <svg class="h-3.5 w-3.5 text-gray-400 group-hover:text-[#1555a8] dark:group-hover:text-blue-400 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </button>
                </div>

                <!-- Input & Action Controls -->
                <div class="mt-1 flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                    <div class="relative flex-1 min-w-0">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 dark:text-gray-500">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                        </div>
                        <input type="text" 
                               id="refLinkInputDash" 
                               readonly 
                               value="{{ url('/register?ref=' . $referralRecord->referral_code) }}" 
                               onclick="this.select()"
                               class="w-full rounded-xl border border-gray-200 bg-white py-2 pl-9 pr-3 text-xs sm:text-sm font-medium text-gray-800 shadow-2xs focus:border-[#1555a8] focus:outline-none focus:ring-2 focus:ring-[#1555a8]/20 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100" />
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" 
                                id="btnCopyDash"
                                onclick="copyRefLinkDash()" 
                                class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs sm:text-sm font-semibold text-gray-700 shadow-2xs transition-all hover:bg-gray-50 hover:text-black dark:border-gray-800 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                            <svg id="copyIconDash" class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <span id="copyTextDash">Copy</span>
                        </button>

                        <a href="https://api.whatsapp.com/send?text={{ rawurlencode('Join me on ' . config('app.name', 'MLM App') . '! Register using my referral link: ' . url('/register?ref=' . $referralRecord->referral_code)) }}" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#25D366] hover:bg-[#20ba59] px-3.5 py-2 text-xs sm:text-sm font-semibold text-white shadow-2xs transition-all"
                           title="Share on WhatsApp">
                            <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                            </svg>
                            <span class="hidden xs:inline">WhatsApp</span>
                        </a>

                        <button type="button" 
                                onclick="shareRefLinkDash()" 
                                class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#1555a8] hover:bg-[#12468d] px-3.5 py-2 text-xs sm:text-sm font-semibold text-white shadow-2xs transition-all"
                                title="Share Referral Link">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                            <span>Share</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('wallet.scan-pay') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-[#1555a8] px-3.5 py-2 text-xs font-bold text-white hover:bg-[#12468d] md:px-5 md:py-3 md:text-sm shadow-xs transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                <span>Scan & Pay</span>
            </a>
            <a href="{{ route('wallet.my-qr') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-bold text-gray-800 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800 md:px-5 md:py-3 md:text-sm shadow-xs transition">
                <svg class="w-4 h-4 text-[#1555a8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                <span>My QR Code</span>
            </a>
            <a href="{{ route('profile.edit') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 md:px-5 md:py-3 md:text-sm">Complete Profile</a>
            <a href="/payments" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 md:px-5 md:py-3 md:text-sm">Payments</a>
            <a href="{{ route('shop.index') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-3 py-2 text-xs font-medium text-white hover:bg-brand-600 md:px-5 md:py-3 md:text-sm">Shop</a>
            <a href="{{ route('orders.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 md:px-5 md:py-3 md:text-sm">My Orders</a>
            <a href="{{ route('referrals.index') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-3 py-2 text-xs font-medium text-white hover:bg-brand-600 md:px-5 md:py-3 md:text-sm">My Referrals</a>
        </div>
    </div>

    <!-- Next Payout Alert (unchanged) -->
    @if($nextRelease)
    <div class="mb-4 flex w-full border-l-6 border-brand-500 bg-yellow-50 dark:bg-yellow-900/20 px-5 py-3 shadow-md md:p-5">
        <div class="mr-4 flex h-8 w-8 items-center justify-center rounded-lg bg-brand-500 md:h-9 md:w-9">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 8V12L15 14M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </div>
        <div class="w-full">
            <h5 class="text-base font-semibold text-black dark:text-brand-500 md:text-lg">Next Payout Release</h5>
            <p class="text-sm leading-relaxed text-gray-700 dark:text-gray-300">
                Your next locked commission of ₹{{ number_format($lockedCommissions ?? 0, 2) }} will begin releasing on 
                <strong>{{ optional(\Carbon\Carbon::parse($nextRelease))->format('M d, Y') ?? 'soon' }}</strong>.
            </p>
        </div>
    </div>
    @endif

    <!-- ================== COMPACT FINANCIAL METRICS ================== -->
    <div class="mb-6">
        <h3 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2 ml-1">Wallet & Credit Balances</h3>
        <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
            <!-- 1. Main Balance -->
            <div class="rounded-2xl border border-gray-100 bg-white p-3 md:p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <h4 class="text-[10px] sm:text-xs font-medium text-gray-500 uppercase mb-1">My Balance</h4>
                <p class="text-lg sm:text-xl md:text-2xl font-bold text-gray-800 dark:text-white">₹{{ number_format(auth()->user()->wallet?->main_balance ?? 0, 2) }}</p>
            </div>
            <!-- 2. Available Credit -->
            <div class="rounded-2xl border border-gray-100 bg-white p-3 md:p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <h4 class="text-[10px] sm:text-xs font-medium text-gray-500 uppercase mb-1">Available Credit</h4>
                <p class="text-lg sm:text-xl md:text-2xl font-bold text-green-600 dark:text-green-400">
                    @if(auth()->user()->creditAccount?->approval_status === 'pending')
                        <span class="text-xs text-orange-500">Pending</span>
                    @elseif(auth()->user()->creditAccount?->approval_status === 'rejected')
                        <span class="text-xs text-red-500">Rejected</span>
                    @else
                        ₹{{ number_format(auth()->user()->creditAccount?->available_credit ?? 0, 2) }}
                    @endif
                </p>
            </div>
            <!-- 3. BV Balance -->
            <div class="rounded-2xl border border-gray-100 bg-white p-3 md:p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <h4 class="text-[10px] sm:text-xs font-medium text-gray-500 uppercase mb-1">BV Balance</h4>
                <p class="text-lg sm:text-xl md:text-2xl font-bold text-gray-800 dark:text-white">{{ number_format(auth()->user()->wallet?->earning_balance ?? 0, 2) }}</p>
            </div>
            <!-- 4. Total Earned -->
            <div class="rounded-2xl border border-gray-100 bg-white p-3 md:p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <h4 class="text-[10px] sm:text-xs font-medium text-gray-500 uppercase mb-1">Total Earned</h4>
                <p class="text-lg sm:text-xl md:text-2xl font-bold text-brand-600 dark:text-brand-400">₹{{ number_format($totalEarned ?? 0, 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Commissions & Deductions Section -->
    <div class="mb-6">
        <h3 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2 ml-1">Cash Commission & Deductions</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
            <!-- Withdrawable -->
            <div class="rounded-2xl border border-gray-100 bg-white p-3 md:p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <h4 class="text-[10px] font-medium text-gray-500 uppercase mb-1">Withdrawable Cash</h4>
                <p class="text-base sm:text-lg font-bold text-green-600">₹{{ number_format($withdrawableCommissions ?? 0, 2) }}</p>
            </div>
            <!-- Locked -->
            <div class="rounded-2xl border border-gray-100 bg-white p-3 md:p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between mb-1">
                    <h4 class="text-[10px] font-medium text-gray-500 uppercase">Locked Cash</h4>
                    @if($nextRelease)
                    <span class="text-[8px] font-bold text-orange-500 uppercase">Rel: {{ \Carbon\Carbon::parse($nextRelease)->format('M d') }}</span>
                    @endif
                </div>
                <p class="text-base sm:text-lg font-bold text-orange-500">₹{{ number_format($lockedCommissions ?? 0, 2) }}</p>
            </div>
            <!-- TDS -->
            <div class="rounded-2xl border border-gray-100 bg-white p-3 md:p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <h4 class="text-[10px] font-medium text-gray-500 uppercase mb-1">Total TDS</h4>
                <p class="text-base sm:text-lg font-bold text-red-600">₹{{ number_format($totalTDS ?? 0, 2) }}</p>
            </div>
            <!-- Service Charge -->
            <div class="rounded-2xl border border-gray-100 bg-white p-3 md:p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <h4 class="text-[10px] font-medium text-gray-500 uppercase mb-1">Service Charge</h4>
                <p class="text-base sm:text-lg font-bold text-red-600">₹{{ number_format($totalServiceCharge ?? 0, 2) }}</p>
            </div>
        </div>
    </div>

    <!-- BV Breakdown Section -->
    <div class="mb-6 grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div>
            <h3 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2 ml-1">BV Points Details</h3>
            <div class="grid grid-cols-2 gap-3 md:gap-4">
                <div class="rounded-2xl border border-gray-100 bg-white p-3 md:p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h4 class="text-[10px] font-medium text-gray-500 uppercase mb-1">Withdrawable BV</h4>
                    <p class="text-base sm:text-lg font-bold text-green-600">{{ number_format($withdrawableBvPoints ?? 0, 2) }}</p>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-white p-3 md:p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-center justify-between mb-1">
                        <h4 class="text-[10px] font-medium text-gray-500 uppercase">Locked BV</h4>
                        @if($nextRelease)
                        <span class="text-[8px] font-bold text-orange-500 uppercase">Rel: {{ \Carbon\Carbon::parse($nextRelease)->format('M d') }}</span>
                        @endif
                    </div>
                    <p class="text-base sm:text-lg font-bold text-orange-500">{{ number_format($lockedBvPoints ?? 0, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3 md:gap-4 content-end">
            <div class="rounded-2xl border border-gray-100 bg-white p-3 md:p-4 dark:border-gray-800 dark:bg-white/[0.03] flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-500/10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-[10px] font-medium text-gray-500 uppercase leading-none">Personal BV</h4>
                    <p class="text-sm font-bold text-gray-800 dark:text-white">{{ number_format($personalBv ?? 0, 0) }}</p>
                </div>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-white p-3 md:p-4 dark:border-gray-800 dark:bg-white/[0.03] flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-50 text-purple-600 dark:bg-purple-500/10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-[10px] font-medium text-gray-500 uppercase leading-none">Active Team</h4>
                    <p class="text-sm font-bold text-gray-800 dark:text-white">{{ $activeReferrals ?? 0 }}/{{ $totalReferrals ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming EMIs (unchanged, just responsive) -->
    @if(count($upcomingEmis ?? []) > 0)
    <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] md:p-5">
        <div class="flex items-center justify-between mb-3 md:mb-4">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white md:text-lg">Upcoming EMIs</h3>
            <a href="{{ route('credit.emis') }}" class="text-xs text-brand-500 hover:underline md:text-sm">Full Schedule</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs md:text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-gray-800">
                        <th class="px-2 py-2 text-left font-medium text-gray-500 dark:text-gray-400 md:px-4 md:py-3">Due Date</th>
                        <th class="px-2 py-2 text-left font-medium text-gray-500 dark:text-gray-400 md:px-4 md:py-3">Order #</th>
                        <th class="px-2 py-2 text-right font-medium text-gray-500 dark:text-gray-400 md:px-4 md:py-3">Amount</th>
                        <th class="px-2 py-2 text-center font-medium text-gray-500 dark:text-gray-400 md:px-4 md:py-3">Status</th>
                        <th class="px-2 py-2 text-center font-medium text-gray-500 dark:text-gray-400 md:px-4 md:py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach($upcomingEmis as $emi)
                    <tr>
                        <td class="px-2 py-2 dark:text-gray-300 md:px-4 md:py-3">{{ \Carbon\Carbon::parse($emi->due_date)->format('M d, Y') }}</td>
                        <td class="px-2 py-2 dark:text-gray-300 md:px-4 md:py-3">#{{ $emi->order_id }}</td>
                        <td class="px-2 py-2 text-right font-bold dark:text-white md:px-4 md:py-3">₹{{ number_format($emi->installment_amount, 2) }}</td>
                        <td class="px-2 py-2 text-center md:px-4 md:py-3">
                            @if($emi->status === 'overdue')
                                <span class="px-1 py-0.5 rounded-full text-[10px] font-semibold bg-red-50 text-red-600 md:px-2 md:py-1 md:text-xs">Overdue</span>
                            @else
                                <span class="px-1 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-600 md:px-2 md:py-1 md:text-xs">Pending</span>
                            @endif
                        </td>
                        <td class="px-2 py-2 text-center md:px-4 md:py-3">
                            <form id="pay-emi-{{ $emi->id }}" action="{{ route('credit.emis.pay', $emi->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="button" onclick="confirmPayment({{ $emi->id }})" class="inline-flex items-center justify-center rounded-lg bg-brand-500 py-1 px-2 text-[10px] font-medium text-white hover:bg-brand-600 transition md:py-1.5 md:px-3 md:text-xs">Pay Now</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Recent Activity (unchanged, just responsive) -->
    <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] md:p-5">
        <div class="flex items-center justify-between mb-3 md:mb-4">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white md:text-lg">Recent Activity</h3>
            <a href="{{ route('wallet.history') }}" class="text-xs text-brand-500 hover:underline md:text-sm">View History</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs md:text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-gray-800">
                        <th class="px-2 py-2 text-left font-medium text-gray-500 dark:text-gray-400 md:px-4 md:py-3 whitespace-nowrap">Date</th>
                        <th class="px-2 py-2 text-left font-medium text-gray-500 dark:text-gray-400 md:px-4 md:py-3">Description</th>
                        <th class="px-2 py-2 text-right font-medium text-gray-500 dark:text-gray-400 md:px-4 md:py-3 whitespace-nowrap">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($recentTransactions ?? [] as $tx)
                    <tr>
                        <td class="px-2 py-2 dark:text-gray-300 whitespace-nowrap md:px-4 md:py-3">
                            {{ optional($tx->created_at)->format('M d, Y') ?? 'N/A' }}
                            <span class="block text-[9px] text-gray-500 md:text-[10px]">{{ optional($tx->created_at)->diffForHumans() ?? '' }}</span>
                        </td>
                        <td class="px-2 py-2 md:px-4 md:py-3">
                            <div class="flex items-center gap-1.5">
                                <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full {{ $tx->type === 'credit' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }} dark:bg-white/5">
                                    @if($tx->type === 'credit')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                        </svg>
                                    @endif
                                </div>
                                <span class="text-xs font-medium text-gray-800 dark:text-white truncate max-w-[120px] sm:max-w-[200px] md:max-w-md">{{ $tx->description }}</span>
                            </div>
                        </td>
                        <td class="px-2 py-2 text-right whitespace-nowrap md:px-4 md:py-3">
                            <span class="text-xs font-bold {{ $tx->type === 'credit' ? 'text-green-600' : 'text-red-600' }} md:text-sm">
                                {{ $tx->type === 'credit' ? '+' : '-' }}₹{{ number_format($tx->amount, 2) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-2 py-6 text-center text-xs text-gray-500 md:px-4 md:py-8 md:text-sm">No recent transactions.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- PhonePe Style Mobile Bottom Fixed Scan & Pay Button -->
@include('partials.mobile_scan_pay_fab')

<script>
function confirmPayment(emiId) {
    Swal.fire({
        title: 'Pay this EMI?',
        text: "The amount will be deducted from your wallet.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, pay it!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('pay-emi-' + emiId).submit();
        }
    });
}

function copyRefLinkDash() {
    const input = document.getElementById('refLinkInputDash');
    if (!input) return;
    input.select();
    input.setSelectionRange(0, 99999);
    
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(input.value);
    } else {
        document.execCommand('copy');
    }
    
    const copyText = document.getElementById('copyTextDash');
    const copyIcon = document.getElementById('copyIconDash');
    if (copyText) {
        const origText = copyText.textContent;
        copyText.textContent = 'Copied!';
        if (copyIcon) copyIcon.classList.add('text-green-600', 'dark:text-green-400');
        setTimeout(() => {
            copyText.textContent = origText;
            if (copyIcon) copyIcon.classList.remove('text-green-600', 'dark:text-green-400');
        }, 2000);
    }

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Copied!',
            text: 'Referral link copied to clipboard.',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
        });
    }
}

function copyRefCodeDash(code) {
    if (!code) return;
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(code);
    } else {
        const temp = document.createElement('textarea');
        temp.value = code;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
    }
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Code Copied!',
            text: 'Referral code ' + code + ' copied to clipboard.',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
        });
    }
}

async function shareRefLinkDash() {
    const shareData = {
        title: @json(config('app.name', 'MLM App')),
        text: 'Join me on ' + @json(config('app.name', 'MLM App')) + ' and start earning! Register using my link:',
        url: '{{ url('/register?ref=' . ($referralRecord->referral_code ?? '')) }}'
    };

    try {
        if (navigator.share) {
            await navigator.share(shareData);
        } else {
            copyRefLinkDash();
        }
    } catch (err) {
        console.log('Error sharing:', err);
    }
}
</script>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
@endsection