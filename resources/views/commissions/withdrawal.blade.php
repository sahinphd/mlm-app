@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-270">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            Payout & Conversions
        </h2>
    </div>

    @if(session('success'))
        <div class="mb-6 flex w-full border-l-6 border-[#34D399] bg-[#34D399] bg-opacity-[15%] px-7 py-4 shadow-md dark:bg-[#1b1b1b] md:p-5">
            <div class="mr-5 flex h-9 w-full max-w-[36px] items-center justify-center rounded-lg bg-[#34D399]">
                <svg width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15.2984 0.826822L15.2868 0.811827L15.2741 0.797751C14.9173 0.401837 14.3238 0.400754 13.9657 0.794406L5.91888 9.53233L2.02771 5.41173C1.6655 5.02795 1.07402 5.02206 0.704179 5.39847C0.334339 5.77488 0.328449 6.37722 0.69066 6.761L5.24211 11.5956L5.24355 11.5972C5.60196 11.9751 6.18434 11.9744 6.54194 11.5953L15.3035 1.95661C15.6669 1.56494 15.6644 0.957512 15.2984 0.826822Z" fill="white" stroke="white"></path>
                </svg>
            </div>
            <div class="w-full">
                <h5 class="text-lg font-semibold text-black dark:text-[#34D399]">Success</h5>
                <p class="text-base leading-relaxed text-body">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 flex w-full border-l-6 border-[#F87171] bg-[#F87171] bg-opacity-[15%] px-7 py-4 shadow-md dark:bg-[#1b1b1b] md:p-5">
            <div class="mr-5 flex h-9 w-full max-w-[36px] items-center justify-center rounded-lg bg-[#F87171]">
                <svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M6.4917 7.65547L1.11151 13H0L5.38019 7.65547L0 2.31094H1.11151L6.4917 7.65547L11.8719 2.31094H12.9834L7.60321 7.65547L13 13H11.8885L6.4917 7.65547Z" fill="white" stroke="white"></path>
                </svg>
            </div>
            <div class="w-full">
                <h5 class="text-lg font-semibold text-black dark:text-[#F87171]">Error</h5>
                <p class="text-base leading-relaxed text-body">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-2 2xl:gap-7.5">
        <!-- Commission Wallet Card -->
        <div class="rounded-sm border border-stroke bg-white py-6 px-7.5 shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="flex items-start justify-between">
                <div>
                    <h4 class="text-title-md font-bold text-black dark:text-white">
                        {{ $settings['currency'] ?? 'INR' }} {{ number_format($wallet->commission_balance, 2) }}
                    </h4>
                    <span class="text-sm font-medium">Commission Wallet Balance</span>
                </div>
                <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-meta-2 dark:bg-meta-4">
                    <i class="fas fa-wallet text-primary"></i>
                </div>
            </div>

            <div class="mt-4 border-t border-stroke pt-4 dark:border-strokedark">
                <div class="flex justify-between mb-2">
                    <span class="text-sm text-gray-500">Withdrawable:</span>
                    <span class="text-sm font-bold text-green-500">{{ $settings['currency'] ?? 'INR' }} {{ number_format($withdrawableAmount, 2) }}</span>
                </div>
                <div class="flex justify-between mb-4">
                    <span class="text-sm text-gray-500">Locked (Pending Period):</span>
                    <span class="text-sm font-bold text-orange-500">{{ $settings['currency'] ?? 'INR' }} {{ number_format($pendingCommissions, 2) }}</span>
                </div>

                <div class="text-xs text-gray-500 mb-4">
                    * Lock Period: {{ $settings['commission_lock_period_days'] ?? 30 }} days from earning.<br>
                    * Min. Payout: {{ $settings['currency'] ?? 'INR' }} {{ number_format($settings['min_commission_withdrawal'] ?? 500, 2) }}<br>
                    * Deductions: {{ $settings['commission_withdrawal_tds_percent'] ?? 5 }}% TDS + {{ $settings['commission_withdrawal_service_charge'] ?? 5 }}% Service Charge.
                </div>

                <form action="{{ route('commissions.withdraw') }}" method="POST">
                    @csrf
                    <button type="submit" 
                        {{ $withdrawableAmount < ($settings['min_commission_withdrawal'] ?? 500) ? 'disabled' : '' }}
                        class="flex w-full justify-center rounded bg-primary p-3 font-medium text-gray hover:bg-opacity-90 disabled:bg-opacity-50 disabled:cursor-not-allowed">
                        Transfer Withdrawable to Main Balance
                    </button>
                </form>
            </div>
        </div>

        <!-- BV Wallet Card -->
        <div class="rounded-sm border border-stroke bg-white py-6 px-7.5 shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="flex items-start justify-between">
                <div>
                    <h4 class="text-title-md font-bold text-black dark:text-white">
                        {{ number_format($wallet->earning_balance, 2) }} BV
                    </h4>
                    <span class="text-sm font-medium">BV Point Balance</span>
                </div>
                <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-meta-2 dark:bg-meta-4">
                    <i class="fas fa-coins text-warning"></i>
                </div>
            </div>

            <div class="mt-4 border-t border-stroke pt-4 dark:border-strokedark">
                <div class="flex justify-between mb-2">
                    <span class="text-sm text-gray-500">Conversion Rate:</span>
                    <span class="text-sm font-bold">1 BV = ₹{{ (float)($settings['bv_conversion_rate'] ?? 1.0) }}</span>
                </div>
                <div class="flex justify-between mb-4">
                    <span class="text-sm text-gray-500">Equivalent Main Balance:</span>
                    <span class="text-sm font-bold text-green-500">₹{{ number_format($wallet->earning_balance * ($settings['bv_conversion_rate'] ?? 1.0), 2) }}</span>
                </div>

                <div class="text-xs text-gray-500 mb-4">
                    * Min. BV for Conversion: {{ number_format($settings['min_bv_withdrawal'] ?? 100, 2) }} BV<br>
                    * Entire BV balance will be converted to main balance.
                </div>

                <form action="{{ route('commissions.convert-bv') }}" method="POST">
                    @csrf
                    <button type="submit" 
                        {{ $wallet->earning_balance < ($settings['min_bv_withdrawal'] ?? 100) ? 'disabled' : '' }}
                        class="flex w-full justify-center rounded bg-warning p-3 font-medium text-black hover:bg-opacity-90 disabled:bg-opacity-50 disabled:cursor-not-allowed">
                        Convert BV to Main Balance
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Recent Payout Transactions -->
    <div class="mt-6 rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="py-6 px-4 md:px-6 xl:px-7.5">
            <h4 class="text-xl font-bold text-black dark:text-white">Recent Payout History</h4>
        </div>

        <div class="max-w-full overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-2 text-left dark:bg-meta-4">
                        <th class="py-4 px-4 font-medium text-black dark:text-white xl:pl-11">Type</th>
                        <th class="py-4 px-4 font-medium text-black dark:text-white">Amount</th>
                        <th class="py-4 px-4 font-medium text-black dark:text-white">Fee/Rate</th>
                        <th class="py-4 px-4 font-medium text-black dark:text-white">Date</th>
                        <th class="py-4 px-4 font-medium text-black dark:text-white">Description</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $recentPayouts = \App\Models\WalletTransaction::where('wallet_id', $wallet->id)
                            ->whereIn('source', ['commission_withdrawal', 'bv_withdrawal'])
                            ->orderBy('created_at', 'desc')
                            ->take(10)
                            ->get();
                    @endphp
                    @forelse($recentPayouts as $tx)
                        <tr>
                            <td class="border-b border-[#eee] py-5 px-4 pl-9 dark:border-strokedark xl:pl-11">
                                <span class="rounded-full bg-opacity-10 py-1 px-3 text-xs font-medium {{ $tx->source == 'commission_withdrawal' ? 'bg-success text-success' : 'bg-warning text-warning' }}">
                                    {{ $tx->source == 'commission_withdrawal' ? 'Commission' : 'BV Conversion' }}
                                </span>
                            </td>
                            <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                                <p class="text-black dark:text-white">₹{{ number_format($tx->amount, 2) }}</p>
                            </td>
                            <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                                <p class="text-black dark:text-white">{{ $tx->fee > 0 ? '₹'.number_format($tx->fee, 2) : '-' }}</p>
                            </td>
                            <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                                <p class="text-black dark:text-white">{{ $tx->created_at->format('M d, Y H:i') }}</p>
                            </td>
                            <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                                <p class="text-sm">{{ $tx->description }}</p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-5 text-center text-gray-500">No recent payout history found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
