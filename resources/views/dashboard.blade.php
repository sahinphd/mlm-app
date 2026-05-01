@extends('layouts.admin')

@section('content')
<div class="grid grid-cols-1 gap-4 md:gap-6">
    <!-- Dashboard Header & Referral -->
    <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 text-center">
        <h2 class="mb-3 text-xl font-semibold text-gray-800 dark:text-white/90 md:text-2xl">Dashboard</h2>
        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 md:text-base">Welcome, {{ auth()->user()->name }} — please complete your profile and request a wallet top-up to get started.</p>
        
        @if($referralRecord)
        <div class="mb-6 p-3 bg-gray-50 dark:bg-white/5 rounded-xl inline-block text-left">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2 md:text-sm">Your Referral Link:</p>
            <div class="flex flex-wrap items-center justify-center gap-2">
                <input type="text" id="refLinkInputDash" readonly value="{{ url('/register?ref=' . $referralRecord->referral_code) }}" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded px-2 py-1 text-xs md:text-sm w-48 md:w-64">
                <button onclick="copyRefLinkDash()" class="bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 px-3 py-1 rounded text-xs md:text-sm hover:bg-gray-300 dark:hover:bg-gray-700 transition">Copy</button>
                <button onclick="shareRefLinkDash()" class="bg-brand-500 text-white px-3 py-1 rounded text-xs md:text-sm hover:bg-brand-600 transition flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 md:h-4 md:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                    Share
                </button>
            </div>
        </div>
        @endif

        <div class="flex flex-wrap items-center justify-center gap-3">
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
        <h3 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2 ml-1">Commission & Deductions</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
            <!-- Withdrawable -->
            <div class="rounded-2xl border border-gray-100 bg-white p-3 md:p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <h4 class="text-[10px] font-medium text-gray-500 uppercase mb-1">Withdrawable</h4>
                <p class="text-base sm:text-lg font-bold text-green-600">₹{{ number_format($withdrawableCommissions ?? 0, 2) }}</p>
            </div>
            <!-- Locked -->
            <div class="rounded-2xl border border-gray-100 bg-white p-3 md:p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between mb-1">
                    <h4 class="text-[10px] font-medium text-gray-500 uppercase">Locked</h4>
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
    navigator.clipboard.writeText(input.value);
    
    Swal.fire({
        icon: 'success',
        title: 'Copied!',
        text: 'Referral link copied to clipboard.',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
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