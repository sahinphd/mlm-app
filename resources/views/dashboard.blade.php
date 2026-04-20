@extends('layouts.admin')

@section('content')
<div class="grid grid-cols-1 gap-4 md:gap-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 text-center">
        <h2 class="mb-3 text-2xl font-semibold text-gray-800 dark:text-white/90">Dashboard</h2>
        <p class="mb-6 text-gray-500 dark:text-gray-400">Welcome, {{ auth()->user()->name }} — please complete your profile and request a wallet top-up to get started.</p>
        
        @if($referralRecord)
        <div class="mb-6 p-4 bg-gray-50 dark:bg-white/5 rounded-xl inline-block text-left">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Your Referral Link:</p>
            <div class="flex items-center gap-2">
                <input type="text" id="refLinkInputDash" readonly value="{{ url('/register?ref=' . $referralRecord->referral_code) }}" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded px-3 py-2 text-sm w-64">
                <button onclick="copyRefLinkDash()" class="bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 px-4 py-2 rounded text-sm hover:bg-gray-300 dark:hover:bg-gray-700 transition">Copy</button>
                <button onclick="shareRefLinkDash()" class="bg-brand-500 text-white px-4 py-2 rounded text-sm hover:bg-brand-600 transition flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                    Share
                </button>
            </div>
        </div>
        @endif

        <div class="flex flex-wrap items-center justify-center gap-4">
            <a href="{{ route('profile.edit') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-5 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800">
                Complete Profile
            </a>
            <a href="/payments" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-5 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800">
                Payments
            </a>
            <a href="{{ route('shop.index') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white hover:bg-brand-600">
                Shop
            </a>
            <a href="{{ route('orders.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-5 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800">
                My Orders
            </a>
            <a href="{{ route('referrals.index') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white hover:bg-brand-600">
                My Referrals
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h4 class="text-sm font-medium text-gray-500 mb-1">My Balance</h4>
            <p class="text-2xl font-bold text-gray-800 dark:text-white">₹{{ number_format(auth()->user()->wallet?->main_balance ?? 0, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h4 class="text-sm font-medium text-gray-500 mb-1">Total Earnings</h4>
            <p class="text-2xl font-bold text-gray-800 dark:text-white">₹{{ number_format(auth()->user()->wallet?->earning_balance ?? 0, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h4 class="text-sm font-medium text-gray-500 mb-1">Approved Credit</h4>
            <p class="text-2xl font-bold text-brand-600 dark:text-white">₹{{ number_format(auth()->user()->creditAccount?->credit_limit ?? 0, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h4 class="text-sm font-medium text-gray-500 mb-1">Available Credit</h4>
            <p class="text-2xl font-bold text-green-600 dark:text-white">₹{{ number_format(auth()->user()->creditAccount?->available_credit ?? 0, 2) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-500">Personal BV</h4>
                <p class="text-xl font-bold text-gray-800 dark:text-white">{{ number_format($personalBv, 0) }} Points</p>
            </div>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-500/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-500">Active Team</h4>
                <p class="text-xl font-bold text-gray-800 dark:text-white">{{ $activeReferrals }} / {{ $totalReferrals }}</p>
            </div>
        </div>
    </div>

    @if(count($upcomingEmis) > 0)
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Upcoming EMIs</h3>
            <a href="{{ route('credit.emis') }}" class="text-sm text-brand-500 hover:underline">Full Schedule</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-gray-800">
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Due Date</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Order #</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Amount</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach($upcomingEmis as $emi)
                    <tr>
                        <td class="px-4 py-3 dark:text-gray-300">{{ \Carbon\Carbon::parse($emi->due_date)->format('M d, Y') }}</td>
                        <td class="px-4 py-3 dark:text-gray-300">#{{ $emi->order_id }}</td>
                        <td class="px-4 py-3 text-right font-bold dark:text-white">₹{{ number_format($emi->installment_amount, 2) }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($emi->status === 'overdue')
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-600">Overdue</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-600">Pending</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <form action="{{ route('credit.emis.pay', $emi->id) }}" method="POST" onsubmit="return confirm('Pay this EMI?')">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-brand-500 py-1.5 px-3 text-center text-xs font-medium text-white hover:bg-brand-600 transition">
                                    Pay Now
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Recent Activity -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Recent Activity</h3>
            <a href="{{ route('wallet.history') }}" class="text-sm text-brand-500 hover:underline">View History</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-gray-800">
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Date</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Description</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($recentTransactions as $tx)
                    <tr>
                        <td class="px-4 py-3 dark:text-gray-300 text-xs">
                            {{ $tx->created_at->format('M d, Y') }}
                            <span class="block text-[10px] text-gray-500">{{ $tx->created_at->diffForHumans() }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full {{ $tx->type === 'credit' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }} dark:bg-white/5">
                                    @if($tx->type === 'credit')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                        </svg>
                                    @endif
                                </div>
                                <span class="text-sm font-medium text-gray-800 dark:text-white truncate max-w-[200px] md:max-w-md">{{ $tx->description }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="text-sm font-bold {{ $tx->type === 'credit' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $tx->type === 'credit' ? '+' : '-' }}₹{{ number_format($tx->amount, 2) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-gray-500">No recent transactions.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function copyRefLinkDash() {
    const copyText = document.getElementById('refLinkInputDash');
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);
    alert("Referral link copied!");
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
            alert("Share API not supported. Referral link copied to clipboard!");
        }
    } catch (err) {
        console.log('Error sharing:', err);
    }
}
</script>
@endsection
