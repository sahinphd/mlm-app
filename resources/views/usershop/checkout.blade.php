@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-4xl p-4 md:p-6" 
     x-data="{
        openTopupModal: false,
        copied: false,
        topupAmount: '{{ ceil(max(1, ($total ?? 0) - ($wallet->main_balance ?? 0))) }}',
        topupMethod: 'UPI',
        topupRef: '',
        topupSubmitting: false,
        topupSuccess: false,
        topupError: '',
        async submitTopup() {
            if (!this.topupAmount || parseFloat(this.topupAmount) <= 0) {
                this.topupError = 'Please enter a valid amount.';
                return;
            }
            this.topupSubmitting = true;
            this.topupError = '';
            try {
                const res = await fetch('/api/payment-requests', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        amount: parseFloat(this.topupAmount),
                        method: this.topupMethod,
                        reference: this.topupRef
                    })
                });
                const data = await res.json();
                if (res.ok) {
                    this.topupSuccess = true;
                } else {
                    this.topupError = data.message || 'Failed to submit top-up request.';
                }
            } catch (e) {
                this.topupError = 'Network error. Please try again or call support.';
            } finally {
                this.topupSubmitting = false;
            }
        }
     }">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                Checkout & Order Confirmation
            </h2>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                Review your items and choose your preferred payment method.
            </p>
        </div>
        <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-blue-600 hover:text-blue-700">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Shop</span>
        </a>
    </div>

    @if(session('error'))
        <div class="mb-6 flex w-full border-l-4 border-red-500 bg-red-50 p-4 rounded-xl dark:bg-red-950/30">
            <div class="w-full">
                <p class="text-sm font-medium text-red-800 dark:text-red-300">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @php
        $settings = $systemSettings ?? \App\Http\Controllers\Admin\SettingController::getSystemSettings();
        $totalOrder = $total ?? ($item->price * $quantity);
        $totalBv = $total_bv ?? ($item->bv * $quantity);
        $canWallet = ($wallet->main_balance >= $totalOrder);
        $canCredit = ($creditAccount && $creditAccount->approval_status === 'approved' && $creditAccount->available_credit >= $totalOrder);
        $canSplit = ($wallet->main_balance > 0 && $wallet->main_balance < $totalOrder && $creditAccount && $creditAccount->approval_status === 'approved' && ($wallet->main_balance + $creditAccount->available_credit) >= $totalOrder);
        $hasSufficientFunds = ($canWallet || $canCredit || $canSplit);
        $splitCreditNeeded = max(0, $totalOrder - $wallet->main_balance);
        $walletShortage = max(0, $totalOrder - $wallet->main_balance);

        $supportPhone = $settings['contact_phone'] ?? '+91 90910 90858';
        $supportPhoneRaw = preg_replace('/[^0-9\+]/', '', $supportPhone);
        if (empty($supportPhoneRaw)) {
            $supportPhoneRaw = '+919091090858';
        }

        $useCustomQr = ($settings['use_custom_qr'] ?? 'off') === 'on';
        if ($useCustomQr && !empty($settings['payment_qr_path'])) {
            $upiId = $settings['custom_upi_id'] ?? env('APP_QR_CODE', 'sahinahmed.com@ybl');
            $qrUrl = asset($settings['payment_qr_path']);
        } else {
            $upiId = env('APP_QR_CODE', 'sahinahmed.com@ybl');
            $qrUrl = 'https://quickchart.io/qr?text=upi%3A%2F%2Fpay%3Fpa%3D' . urlencode($upiId) . '%26pn%3DDuare%20Dokandar%26cu%3DINR&size=160&margin=2';
        }
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left Column: Itemized Order Summary -->
        <div class="lg:col-span-7 flex flex-col gap-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 pb-4 dark:border-gray-800 flex items-center justify-between">
                    <h3 class="font-bold text-base text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span>Order Summary ({{ count($items ?? []) }} {{ count($items ?? []) === 1 ? 'item' : 'items' }})</span>
                    </h3>
                </div>

                <!-- Items List -->
                <div class="divide-y divide-gray-100 dark:divide-gray-800 my-4 max-h-80 overflow-y-auto">
                    @foreach($items ?? [] as $it)
                    <div class="py-3.5 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-800 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                @if(!empty($it['image']))
                                    <img src="{{ $it['image'] }}" alt="{{ $it['name'] }}" class="w-full h-full object-cover">
                                @else
                                    <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $it['name'] }}</h4>
                                <div class="flex items-center gap-2 text-xs text-gray-500 mt-0.5">
                                    <span>Qty: <strong class="text-gray-700 dark:text-gray-300">{{ $it['quantity'] }}</strong></span>
                                    <span>&bull;</span>
                                    <span>Rs.{{ number_format($it['price'], 2) }} each</span>
                                    <span>&bull;</span>
                                    <span class="text-emerald-600 font-medium">+{{ $it['subtotal_bv'] }} BV</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="text-sm font-bold text-gray-900 dark:text-white">Rs.{{ number_format($it['subtotal'], 2) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Totals Breakdown -->
                <div class="border-t border-gray-100 pt-4 dark:border-gray-800 space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Items Subtotal</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">Rs.{{ number_format($totalOrder, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-emerald-600 dark:text-emerald-400 font-semibold">
                        <span>Total Reward BV Points</span>
                        <span>+{{ $totalBv }} BV</span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Delivery Charges</span>
                        <span class="text-emerald-600 font-medium">FREE Local Delivery</span>
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-3 flex items-center justify-between">
                        <span class="text-base font-bold text-gray-900 dark:text-white">Grand Total:</span>
                        <span class="text-xl font-extrabold text-blue-600 dark:text-blue-400">Rs.{{ number_format($totalOrder, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Payment Form -->
        <div class="lg:col-span-5 flex flex-col gap-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 pb-4 dark:border-gray-800 flex items-center justify-between">
                    <h3 class="font-bold text-base text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <span>Choose Payment Method</span>
                    </h3>

                    <!-- Help / Call Support Icon -->
                    <a href="tel:{{ $supportPhoneRaw }}" 
                       class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/40 dark:text-blue-300 transition" 
                       title="Call Support: {{ $supportPhone }}">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <span>Help Call</span>
                    </a>
                </div>

                <form id="orderPlaceForm" action="{{ route('shop.place-order') }}" method="POST" class="pt-4" onsubmit="handleOrderSubmit()">
                    @csrf
                    
                    {{-- Backward compatibility inputs for single item --}}
                    @if(count($items ?? []) === 1)
                        <input type="hidden" name="item_id" value="{{ $items[0]['id'] }}">
                        <input type="hidden" name="type" value="{{ $items[0]['type'] }}">
                        <input type="hidden" name="quantity" value="{{ $items[0]['quantity'] }}">
                    @endif

                    {{-- Multi-item hidden payload --}}
                    @foreach($items ?? [] as $idx => $it)
                        <input type="hidden" name="items[{{ $idx }}][id]" value="{{ $it['id'] }}">
                        <input type="hidden" name="items[{{ $idx }}][type]" value="{{ $it['type'] }}">
                        <input type="hidden" name="items[{{ $idx }}][quantity]" value="{{ $it['quantity'] }}">
                    @endforeach

                    <div class="space-y-3 mb-6">
                        <!-- Main Wallet Option -->
                        <label class="relative flex cursor-pointer select-none items-start gap-3 rounded-xl border p-4 transition-all {{ $wallet->main_balance >= $totalOrder ? 'border-gray-200 hover:border-blue-400 bg-white dark:bg-gray-800 dark:border-gray-700' : 'border-gray-100 bg-gray-50/50 opacity-60 dark:bg-gray-900 dark:border-gray-800' }}">
                            <input type="radio" 
                                   name="payment_method" 
                                   value="main_wallet" 
                                   required 
                                   class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500" 
                                   {{ $wallet->main_balance >= $totalOrder ? 'checked' : 'disabled' }}>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-sm text-gray-900 dark:text-white">Main Wallet</span>
                                    <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">Rs.{{ number_format($wallet->main_balance, 2) }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5">Pay full amount instantly from your wallet balance.</p>
                                @if($wallet->main_balance < $totalOrder)
                                    <div class="mt-1 flex items-center justify-between">
                                        <span class="text-[11px] font-bold text-red-500">Insufficient balance (needs Rs.{{ number_format($totalOrder - $wallet->main_balance, 2) }} more)</span>
                                        <button type="button" @click="openTopupModal = true" class="text-[11px] font-bold text-emerald-600 hover:underline">
                                            + Top-up
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </label>

                        <!-- Credit Wallet (EMI) Option -->
                        <label class="relative flex cursor-pointer select-none items-start gap-3 rounded-xl border p-4 transition-all {{ ($creditAccount && $creditAccount->approval_status === 'approved' && $creditAccount->available_credit >= $totalOrder) ? 'border-gray-200 hover:border-blue-400 bg-white dark:bg-gray-800 dark:border-gray-700' : 'border-gray-100 bg-gray-50/50 opacity-60 dark:bg-gray-900 dark:border-gray-800' }}">
                            <input type="radio" 
                                   name="payment_method" 
                                   value="credit_wallet" 
                                   required 
                                   class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500" 
                                   {{ ($creditAccount && $creditAccount->approval_status === 'approved' && $creditAccount->available_credit >= $totalOrder) ? ($wallet->main_balance < $totalOrder ? 'checked' : '') : 'disabled' }}>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-sm text-gray-900 dark:text-white">Credit Wallet (EMI)</span>
                                    @if($creditAccount && $creditAccount->approval_status === 'approved')
                                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">Available: Rs.{{ number_format($creditAccount->available_credit, 2) }}</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5">Pay over time with flexible weekly EMI installments.</p>
                                @if(!$creditAccount || $creditAccount->approval_status !== 'approved')
                                    <span class="inline-block mt-1 text-[11px] font-bold text-red-500">Credit account is not approved yet</span>
                                @elseif($creditAccount->available_credit < $totalOrder)
                                    <span class="inline-block mt-1 text-[11px] font-bold text-red-500">Insufficient credit limit</span>
                                @endif
                            </div>
                        </label>

                        <!-- Split Payment Option -->
                        <label class="relative flex cursor-pointer select-none items-start gap-3 rounded-xl border p-4 transition-all {{ $canSplit ? 'border-blue-400 bg-blue-50/20 dark:bg-blue-950/20' : 'border-gray-100 bg-gray-50/50 opacity-60 dark:bg-gray-900 dark:border-gray-800' }}">
                            <input type="radio" 
                                   name="payment_method" 
                                   value="split" 
                                   required 
                                   class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500" 
                                   {{ $canSplit ? ($wallet->main_balance < $totalOrder && (!($creditAccount && $creditAccount->available_credit >= $totalOrder)) ? 'checked' : '') : 'disabled' }}>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-sm text-gray-900 dark:text-white flex items-center gap-1.5">
                                        Split Payment
                                        @if($canSplit)
                                            <span class="text-[10px] bg-blue-600 text-white font-extrabold px-1.5 py-0.5 rounded-full">Recommended</span>
                                        @endif
                                    </span>
                                </div>
                                @if($canSplit)
                                    <p class="text-xs text-gray-700 dark:text-gray-300 mt-1 leading-normal">
                                        Pay <strong>Rs.{{ number_format($wallet->main_balance, 2) }}</strong> from Wallet + remaining <strong>Rs.{{ number_format($splitCreditNeeded, 2) }}</strong> from Credit (EMI).
                                    </p>
                                @else
                                    <p class="text-xs text-gray-500 mt-0.5">Combine your available wallet balance and approved credit limit.</p>
                                @endif
                            </div>
                        </label>
                    </div>

                    @if($hasSufficientFunds)
                        <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-xl bg-blue-600 py-3.5 px-6 font-bold text-sm text-white hover:bg-blue-700 shadow-md hover:shadow-lg transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Confirm &amp; Place Order</span>
                        </button>
                    @else
                        <!-- In the place of checkout: Top-up balance request option and help icon to call -->
                        <div class="space-y-4 pt-1">
                            <!-- Insufficient Balance Notice Box -->
                            <div class="rounded-xl border border-amber-200 bg-amber-50/90 p-4 dark:border-amber-900/50 dark:bg-amber-950/30">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center flex-shrink-0 text-amber-600 dark:text-amber-400">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-xs font-bold text-amber-900 dark:text-amber-200 uppercase tracking-wider">
                                            Insufficient Balance for Checkout
                                        </h4>
                                        <p class="text-xs text-amber-800 dark:text-amber-300 mt-1 leading-relaxed">
                                            Order total is <strong>Rs.{{ number_format($totalOrder, 2) }}</strong>. Your wallet has <strong>Rs.{{ number_format($wallet->main_balance, 2) }}</strong>. 
                                            You need <strong>Rs.{{ number_format($walletShortage, 2) }}</strong> more to complete this order.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Primary Top-up Balance Request Button -->
                            <button type="button" 
                                    @click="openTopupModal = true" 
                                    class="w-full flex items-center justify-center gap-2 rounded-xl bg-emerald-600 py-3.5 px-6 font-bold text-sm text-white hover:bg-emerald-700 shadow-md hover:shadow-lg transition">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                <span>Request Balance Top-up (Add Rs.{{ number_format($walletShortage, 2) }})</span>
                            </button>

                            <!-- Secondary link to full top-up page -->
                            <a href="{{ route('payments.index') }}" 
                               class="w-full flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-gray-50/70 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-750 py-2.5 px-4 font-semibold text-xs text-gray-700 dark:text-gray-300 transition">
                                <span>Open Full Wallet Top-up Page</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>

                            <!-- Help to Call Support with Help Icon -->
                            <div class="rounded-xl border border-blue-100 bg-blue-50/50 p-3.5 dark:border-blue-900/40 dark:bg-blue-950/20 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center flex-shrink-0 shadow-xs">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-gray-900 dark:text-white">Need Help with Payment?</p>
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">{{ $supportPhone }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                    <!-- Help Call Icon & Link -->
                                    <a href="tel:{{ $supportPhoneRaw }}" 
                                       class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition"
                                       title="Call {{ $supportPhone }}">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        <span>Call Help</span>
                                    </a>

                                    <!-- WhatsApp Support Link -->
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $supportPhoneRaw) }}?text={{ urlencode('Hello Duaredokandar, I need help with top-up for my order of Rs. ' . number_format($totalOrder, 2)) }}" 
                                       target="_blank"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-xs"
                                       title="Chat on WhatsApp">
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </form>
            </div>
        </div>

    </div>

    <!-- Quick Top-up Modal -->
    <div x-show="openTopupModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs overflow-y-auto"
         @keydown.escape.window="openTopupModal = false">
        
        <div class="relative w-full max-w-lg rounded-2xl bg-white p-5 sm:p-6 shadow-2xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800"
             @click.outside="openTopupModal = false">
             
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-gray-900 dark:text-white">Request Wallet Top-up</h3>
                        <p class="text-xs text-gray-500">Add funds to complete your order</p>
                    </div>
                </div>
                <button type="button" @click="openTopupModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="py-4 space-y-4">
                <!-- Shortage Summary Box -->
                <div class="rounded-xl bg-gray-50 dark:bg-gray-800/60 p-3.5 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Order Shortage:</span>
                        <div class="font-extrabold text-sm text-red-600 dark:text-red-400">Rs.{{ number_format($walletShortage, 2) }}</div>
                    </div>
                    <div class="text-right">
                        <span class="text-gray-500 dark:text-gray-400">Current Balance:</span>
                        <div class="font-bold text-sm text-gray-800 dark:text-gray-200">Rs.{{ number_format($wallet->main_balance, 2) }}</div>
                    </div>
                </div>

                <!-- UPI QR Code + UPI ID block -->
                <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-gray-850 flex flex-col sm:flex-row items-center gap-4">
                    <div class="bg-white p-2 rounded-xl shadow-xs border border-gray-100 dark:border-gray-700 flex-shrink-0">
                        <img src="{{ $qrUrl }}" alt="UPI QR" class="w-28 h-28 object-contain">
                        <p class="text-center text-[10px] text-gray-400 mt-1 uppercase font-bold">Scan to Pay</p>
                    </div>
                    <div class="flex-1 text-center sm:text-left min-w-0">
                        <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300">Scan QR or Pay to UPI ID</h4>
                        <div class="mt-2 inline-flex items-center gap-2 bg-gray-100 dark:bg-gray-800 px-3 py-1.5 rounded-lg max-w-full">
                            <span class="font-mono text-xs font-bold text-gray-800 dark:text-gray-200 truncate">{{ $upiId }}</span>
                            <button type="button" 
                                    @click="navigator.clipboard.writeText('{{ $upiId }}'); copied = true; setTimeout(() => copied = false, 2000)" 
                                    class="text-blue-600 hover:text-blue-700 font-bold text-xs flex-shrink-0">
                                <span x-show="!copied">Copy</span>
                                <span x-show="copied" x-cloak class="text-emerald-600">Copied!</span>
                            </button>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-2">Pay the amount using any UPI App (GPay, PhonePe, Paytm), then submit the transaction reference below.</p>
                    </div>
                </div>

                <!-- Topup Form -->
                <div x-show="!topupSuccess" class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Amount to Add (Rs.) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               x-model="topupAmount" 
                               step="0.01" 
                               min="1" 
                               required 
                               class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-sm font-semibold text-gray-900 dark:text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Payment Method
                        </label>
                        <input type="text" 
                               x-model="topupMethod" 
                               class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2 text-sm text-gray-900 dark:text-white"
                               placeholder="UPI / GPay / PhonePe / Paytm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            UTR / Transaction Reference (Optional)
                        </label>
                        <input type="text" 
                               x-model="topupRef" 
                               class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2 text-sm text-gray-900 dark:text-white"
                               placeholder="e.g. 12-digit UPI reference ID">
                    </div>

                    <div x-show="topupError" x-cloak class="p-3 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-300 text-xs font-medium">
                        <span x-text="topupError"></span>
                    </div>

                    <button type="button" 
                            @click="submitTopup()" 
                            :disabled="topupSubmitting"
                            class="w-full flex items-center justify-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white py-3 px-4 font-bold text-sm shadow-md transition disabled:opacity-50">
                        <svg x-show="topupSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span x-text="topupSubmitting ? 'Submitting Request...' : 'Submit Top-up Request'"></span>
                    </button>
                </div>

                <!-- Success State -->
                <div x-show="topupSuccess" x-cloak class="text-center py-4 space-y-3">
                    <div class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 mx-auto flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h4 class="font-bold text-sm text-gray-900 dark:text-white">Top-up Request Submitted!</h4>
                    <p class="text-xs text-gray-600 dark:text-gray-300 max-w-sm mx-auto">
                        Your balance top-up request has been recorded. Admin has been notified and will verify & approve it shortly.
                    </p>
                    <div class="pt-2 flex flex-col sm:flex-row gap-2 justify-center">
                        <button type="button" 
                                onclick="window.location.reload()" 
                                class="px-4 py-2 rounded-xl bg-blue-600 text-white font-bold text-xs hover:bg-blue-700 transition">
                            Check & Refresh Checkout
                        </button>
                        <a href="{{ route('payments.index') }}" 
                           class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 font-semibold text-xs hover:bg-gray-100 transition">
                            View All Requests
                        </a>
                    </div>
                </div>
            </div>

            <!-- Modal Footer: Help / Call Support -->
            <div class="pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs">
                <span class="text-gray-500">Need urgent balance approval?</span>
                <a href="tel:{{ $supportPhoneRaw }}" class="font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    <span>Call Support</span>
                </a>
            </div>

        </div>
    </div>
</div>

<script>
function handleOrderSubmit() {
    // Clear client-side cart on order submission
    try {
        localStorage.removeItem('dua_cart');
        window.dispatchEvent(new CustomEvent('cart-updated'));
    } catch(e) {}
}
</script>
@endsection
