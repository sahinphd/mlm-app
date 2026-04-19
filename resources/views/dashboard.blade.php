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

    <!-- Quick Stats or Other Dashboard Content -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h4 class="text-sm font-medium text-gray-500 mb-1">My Balance</h4>
            <p class="text-2xl font-bold text-gray-800 dark:text-white">₹{{ number_format(auth()->user()->wallet->main_balance ?? 0, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h4 class="text-sm font-medium text-gray-500 mb-1">Total Earnings</h4>
            <p class="text-2xl font-bold text-gray-800 dark:text-white">₹{{ number_format(auth()->user()->wallet->earning_balance ?? 0, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h4 class="text-sm font-medium text-gray-500 mb-1">Referral Count</h4>
            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ auth()->user()->referredUsers()->count() }}</p>
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
        title: '{{ config('app.name', 'MLM App') }}',
        text: 'Join me on {{ config('app.name', 'MLM App') }} and start earning! Register using my link:',
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
