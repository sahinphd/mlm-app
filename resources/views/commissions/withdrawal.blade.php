@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-270">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Payout & Conversions</h2>
        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Manage your earnings, withdrawals, and BV point conversions.</p>
    </div>

    @if(session('success'))
    <div class="mb-6 flex w-full border-l-6 border-green-500 bg-green-500 bg-opacity-[15%] px-7 py-4 shadow-md dark:bg-[#1b1b1b] md:p-5">
        <div class="mr-5 flex h-9 w-full max-w-[36px] items-center justify-center rounded-lg bg-green-500">
            <svg width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15.2984 0.826822L15.2868 0.811827L15.2741 0.797751C14.9173 0.401837 14.3238 0.400754 13.9657 0.794406L5.91888 9.53233L2.02771 5.41173C1.6655 5.02795 1.07402 5.02206 0.704179 5.39847C0.334339 5.77488 0.328449 6.37722 0.69066 6.761L5.24211 11.5956L5.24355 11.5972C5.60196 11.9751 6.18434 11.9744 6.54194 11.5953L15.3035 1.95661C15.6669 1.56494 15.6644 0.957512 15.2984 0.826822Z" fill="white"></path>
            </svg>
        </div>
        <div class="w-full">
            <h5 class="text-lg font-semibold text-black dark:text-green-500">Success</h5>
            <p class="text-base leading-relaxed text-body">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 flex w-full border-l-6 border-red-500 bg-red-500 bg-opacity-[15%] px-7 py-4 shadow-md dark:bg-[#1b1b1b] md:p-5">
        <div class="mr-5 flex h-9 w-full max-w-[36px] items-center justify-center rounded-lg bg-red-500">
            <svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M6.4917 7.65547L1.11151 13H0L5.38019 7.65547L0 2.31094H1.11151L6.4917 7.65547L11.8719 2.31094H12.9834L7.60321 7.65547L13 13H11.8885L6.4917 7.65547Z" fill="white"></path>
            </svg>
        </div>
        <div class="w-full">
            <h5 class="text-lg font-semibold text-black dark:text-red-500">Error</h5>
            <p class="text-base leading-relaxed text-body">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between mb-1">
                <h4 class="text-sm font-medium text-gray-500">Total Earned</h4>
                <button title="Your all-time gross earnings (Cash + BV Cash Value)." class="text-gray-400 hover:text-brand-500 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </button>
            </div>
            <p class="text-2xl font-bold text-gray-800 dark:text-white">₹{{ number_format($totalEarned, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between mb-1">
                <h4 class="text-sm font-medium text-gray-500">Total Withdrawn</h4>
                <button title="Total amount transferred to your main balance (Cash + BV Conversions)." class="text-gray-400 hover:text-blue-500 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </button>
            </div>
            <p class="text-2xl font-bold text-blue-600 dark:text-white">₹{{ number_format($totalWithdrawn, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between mb-1">
                <h4 class="text-sm font-medium text-gray-500">Withdrawable</h4>
                <button title="Total amount currently eligible for payout/conversion (Cash + BV Cash Value)." class="text-gray-400 hover:text-green-500 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3-1.343-3-3S10.343 2 12 2s3 1.343 3 3-1.343 3-3 3zM6 20c0-3.866 3.582-7 8-7s8 3.134 8 7" />
                    </svg>
                </button>
            </div>
            <p class="text-2xl font-bold text-green-600 dark:text-white">₹{{ number_format($totalWithdrawable, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between mb-1">
                <h4 class="text-sm font-medium text-gray-500">Locked Balance</h4>
                @if($nextRelease)
                <span class="text-xs font-bold text-orange-500 uppercase">Rel: {{ \Carbon\Carbon::parse($nextRelease)->format('M d') }}</span>
                @else
                <button title="Amount currently in the verification period (Cash + BV Cash Value)." class="text-gray-400 hover:text-orange-500 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </button>
                @endif
            </div>
            <p class="text-2xl font-bold text-orange-500 dark:text-orange-400">₹{{ number_format($totalLocked, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between mb-1">
                <h4 class="text-sm font-medium text-gray-500">Total TDS</h4>
                <button title="Total Tax Deducted at Source (TDS) on your commission withdrawals." class="text-gray-400 hover:text-red-500 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </button>
            </div>
            <p class="text-2xl font-bold text-red-600 dark:text-white">₹{{ number_format($totalTDS, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between mb-1">
                <h4 class="text-sm font-medium text-gray-500">Service Charge</h4>
                <button title="Total Service Charges deducted during commission withdrawals." class="text-gray-400 hover:text-red-500 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </button>
            </div>
            <p class="text-2xl font-bold text-red-600 dark:text-white">₹{{ number_format($totalServiceCharge, 2) }}</p>
        </div>
    </div>

    <!-- BV Specific Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between mb-1">
                <h4 class="text-sm font-medium text-gray-500">Withdrawable BV Points</h4>
                <button title="BV points that have passed the lock-in period and are eligible for conversion." class="text-gray-400 hover:text-green-500 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </button>
            </div>
            <p class="text-2xl font-bold text-green-600 dark:text-white">{{ number_format($withdrawableBvPoints, 2) }} Points</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between mb-1">
                <h4 class="text-sm font-medium text-gray-500">Locked BV Points</h4>
                <button title="Recently earned BV points still in the 30-day verification period." class="text-gray-400 hover:text-orange-500 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </button>
            </div>
            <p class="text-2xl font-bold text-orange-500 dark:text-orange-400">{{ number_format($lockedBvPoints, 2) }} Points</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Commission Withdrawal Card -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-default dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center gap-4 mb-6">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-500/10 text-brand-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Commission Payout</h3>
                    <p class="text-sm text-gray-500">Total Cash Balance: ₹{{ number_format($wallet->commission_balance, 2) }}</p>
                </div>
            </div>

            <div class="space-y-4 mb-6">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Withdrawable Cash:</span>
                    <span class="font-bold text-green-600">₹{{ number_format($cashWithdrawable, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Min. Required:</span>
                    <span class="font-medium text-gray-800 dark:text-gray-200">₹{{ number_format($settings['min_commission_withdrawal'] ?? 500, 2) }}</span>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-white/5 rounded-lg border border-gray-100 dark:border-gray-800">
                    <p class="text-xs text-gray-500 leading-tight">
                        <strong>Deductions:</strong> {{ $settings['commission_withdrawal_tds_percent'] ?? 5 }}% TDS and {{ $settings['commission_withdrawal_service_charge'] ?? 5 }}% Service Charge will be applied during transfer.
                    </p>
                </div>
            </div>

            <form id="cashWithdrawForm" action="{{ route('commissions.withdraw') }}" method="POST" onsubmit="return handleWithdrawal(event, 'cash')">
                @csrf
                <button type="submit" 
                    class="w-full flex justify-center items-center gap-2 rounded-lg bg-brand-500 py-3 px-4 font-medium text-white hover:bg-brand-600 transition">
                    <span>Withdraw Withdrawable Cash</span>
                </button>
            </form>
        </div>

        <!-- BV Point Conversion Card -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-default dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center gap-4 mb-6">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-500/10 text-orange-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3-1.343-3-3S10.343 2 12 2s3 1.343 3 3-1.343 3-3 3zM6 20c0-3.866 3.582-7 8-7s8 3.134 8 7" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">BV Point Conversion</h3>
                    <p class="text-sm text-gray-500">Total BV Balance: {{ number_format($wallet->earning_balance, 2) }} Points</p>
                </div>
            </div>

            <div class="space-y-4 mb-6">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Withdrawable BV:</span>
                    <span class="font-bold text-green-600">{{ number_format($withdrawableBvPoints, 2) }} Points</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Cash Value (Rate: ₹{{ (float)($settings['bv_conversion_rate'] ?? 1.0) }}):</span>
                    <span class="font-bold text-brand-600">₹{{ number_format($withdrawableBvPoints * ($settings['bv_conversion_rate'] ?? 1.0), 2) }}</span>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-white/5 rounded-lg border border-gray-100 dark:border-gray-800">
                    <p class="text-xs text-gray-500 leading-tight">
                        <strong>Min. Conversion:</strong> {{ number_format($settings['min_bv_withdrawal'] ?? 100, 2) }} withdrawable BV required. Only unlocked points will be converted.
                    </p>
                </div>
            </div>

            <form id="bvConvertForm" action="{{ route('commissions.convert-bv') }}" method="POST" onsubmit="return handleWithdrawal(event, 'bv')">
                @csrf
                <button type="submit" 
                    class="w-full flex justify-center items-center gap-2 rounded-lg bg-orange-500 py-3 px-4 font-medium text-white hover:bg-orange-600 transition">
                    <span>Convert Withdrawable BV</span>
                </button>
            </form>
        </div>
    </div>

    <!-- History Table -->
    <div class="mt-8 rounded-2xl border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="py-5 px-6 border-b border-gray-100 dark:border-gray-800">
            <h4 class="text-lg font-bold text-gray-800 dark:text-white">Recent Payout History</h4>
        </div>

        <div class="max-w-full overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left dark:bg-white/5">
                        <th class="py-4 px-6 font-medium text-gray-500 dark:text-gray-400">Type</th>
                        <th class="py-4 px-6 font-medium text-gray-500 dark:text-gray-400">Amount</th>
                        <th class="py-4 px-6 font-medium text-gray-500 dark:text-gray-400">Date</th>
                        <th class="py-4 px-6 font-medium text-gray-500 dark:text-gray-400">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @php
                        $recentPayouts = \App\Models\WalletTransaction::where('wallet_id', $wallet->id)
                            ->whereIn('source', ['commission_withdrawal', 'bv_withdrawal'])
                            ->orderBy('created_at', 'desc')
                            ->take(10)
                            ->get();
                    @endphp
                    @forelse($recentPayouts as $tx)
                        <tr>
                            <td class="py-4 px-6">
                                <span class="inline-flex rounded-full bg-opacity-10 py-1 px-3 text-xs font-semibold {{ $tx->source == 'commission_withdrawal' ? 'bg-green-500 text-green-600' : 'bg-orange-500 text-orange-600' }}">
                                    {{ $tx->source == 'commission_withdrawal' ? 'Commission' : 'BV Conversion' }}
                                </span>
                            </td>
                            <td class="py-4 px-6 font-bold text-gray-800 dark:text-white">
                                ₹{{ number_format($tx->amount, 2) }}
                            </td>
                            <td class="py-4 px-6 text-gray-500">
                                {{ $tx->created_at->format('M d, Y') }}
                                <span class="block text-[10px]">{{ $tx->created_at->format('H:i A') }}</span>
                            </td>
                            <td class="py-4 px-6 text-gray-600 dark:text-gray-400">
                                {{ $tx->description }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-gray-500">No recent payout history found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function handleWithdrawal(event, type) {
    event.preventDefault();
    const form = event.target;
    
    let current, min, unit, title, confirmText;
    
    if (type === 'cash') {
        current = @json($cashWithdrawable);
        min = @json((float)($settings['min_commission_withdrawal'] ?? 500));
        unit = '₹';
        title = 'Withdraw Commission?';
        confirmText = 'Yes, withdraw to main balance';
    } else {
        current = @json($withdrawableBvPoints);
        min = @json((float)($settings['min_bv_withdrawal'] ?? 100));
        unit = '';
        title = 'Convert BV Points?';
        confirmText = 'Yes, convert points';
    }

    if (current < min) {
        Swal.fire({
            icon: 'error',
            title: 'Threshold Not Met',
            text: `Minimum required for ${type === 'cash' ? 'withdrawal' : 'conversion'} is ${unit}${min.toLocaleString()}. Your current withdrawable balance is ${unit}${current.toLocaleString()}.`,
            confirmButtonColor: '#3085d6',
        });
        return false;
    }

    Swal.fire({
        title: title,
        text: `Are you sure you want to proceed with this ${type === 'cash' ? 'withdrawal' : 'conversion'}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: confirmText
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
    
    return false;
}
</script>
@endsection
