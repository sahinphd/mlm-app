@extends('layouts.admin')

@section('content')
@php
    $settingsFile = 'settings.json';
    $currency = 'INR';
    if (\Illuminate\Support\Facades\Storage::disk('local')->exists($settingsFile)) {
        $s = json_decode(\Illuminate\Support\Facades\Storage::disk('local')->get($settingsFile), true);
        $currency = $s['currency'] ?? 'INR';
    }
    $currencySymbol = ($currency === 'INR' || empty($currency)) ? '₹' : $currency;

    $user = auth()->user();
    $isRestricted = ($user->role !== 'admin' && $user->kyc_status !== 'approved');

    $useCustomQr = ($systemSettings['use_custom_qr'] ?? 'off') === 'on';
    $customQrPath = (!empty($systemSettings['payment_qr_path'])) ? asset($systemSettings['payment_qr_path']) : null;
    $upiId = $systemSettings['custom_upi_id'] ?? env('APP_QR_CODE', 'sahinahmed.com@ybl');
    $siteName = $systemSettings['site_name'] ?? config('app.name', 'Duare Dokandar');
@endphp

<div class="max-w-xl mx-auto space-y-4" x-data="{
    step: 1,
    amount: '',
    presetAmounts: [100, 200, 500, 1000, 2000, 5000],
    upiId: '{{ $upiId }}',
    siteName: '{{ addslashes($siteName) }}',
    currencySymbol: '{{ $currencySymbol }}',
    transactionId: '',
    isSubmitting: false,
    showStaticQrModal: false,

    setAmount(val) {
        this.amount = val;
    },

    get formattedAmount() {
        const num = parseFloat(this.amount);
        return isNaN(num) ? '0.00' : num.toFixed(2);
    },

    get upiUrl() {
        if (!this.amount || parseFloat(this.amount) <= 0) return '';
        const amt = parseFloat(this.amount).toFixed(2);
        const pa = encodeURIComponent(this.upiId);
        const pn = encodeURIComponent(this.siteName);
        const tn = encodeURIComponent('Wallet Topup');
        return `upi://pay?pa=${pa}&pn=${pn}&am=${amt}&cu=INR&tn=${tn}`;
    },

    get qrCodeUrl() {
        if (!this.upiUrl) return '';
        return `https://quickchart.io/qr?text=${encodeURIComponent(this.upiUrl)}&size=220&margin=1`;
    },

    proceedToPay() {
        const amt = parseFloat(this.amount);
        if (isNaN(amt) || amt < 1) {
            Swal.fire({
                icon: 'warning',
                title: 'Enter Valid Amount',
                text: 'Please enter a valid amount (at least 1) to proceed.',
                confirmButtonColor: '#3085d6',
            });
            return;
        }
        this.step = 2;
    },

    changeAmount() {
        this.step = 1;
    },

    copyUpi() {
        const id = this.upiId;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(id).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Copied!',
                    text: 'UPI ID copied: ' + id,
                    timer: 1500,
                    showConfirmButton: false
                });
            }).catch(() => this.fallbackCopy(id));
        } else {
            this.fallbackCopy(id);
        }
    },

    fallbackCopy(text) {
        const ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        Swal.fire({
            icon: 'success',
            title: 'Copied!',
            text: 'UPI ID copied: ' + text,
            timer: 1500,
            showConfirmButton: false
        });
    },

    async submitPayment() {
        const ref = (this.transactionId || '').trim();
        if (!ref) {
            Swal.fire({
                icon: 'warning',
                title: 'Transaction ID Required',
                text: 'Please enter the 12-digit UPI UTR / Transaction Reference number from your payment app.',
                confirmButtonColor: '#3085d6',
            });
            return;
        }

        this.isSubmitting = true;
        try {
            const res = await fetch('/api/payment-requests', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    amount: this.amount,
                    method: 'UPI',
                    reference: ref
                })
            });

            const data = await res.json();
            if (res.ok) {
                Swal.fire({
                    icon: 'success',
                    title: 'Request Submitted!',
                    text: 'Your top-up request of ' + this.currencySymbol + this.formattedAmount + ' (UTR: ' + ref + ') has been submitted. Wallet will be credited after admin verification.',
                    confirmButtonColor: '#10b981',
                });
                this.amount = '';
                this.transactionId = '';
                this.step = 1;
                if (typeof loadRequests === 'function') loadRequests(1);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed',
                    text: data.message || 'Unable to submit payment request.',
                    confirmButtonColor: '#ef4444',
                });
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Connection Error',
                text: 'Could not connect to the server. Please check your connection and try again.',
            });
        } finally {
            this.isSubmitting = false;
        }
    }
}">

    @if($isRestricted)
        <div class="p-4 rounded-2xl border border-warning-200 bg-warning-50 dark:border-warning-900/50 dark:bg-warning-900/10 flex items-start gap-3.5 shadow-xs">
            <div class="h-10 w-10 shrink-0 rounded-xl bg-warning-100 dark:bg-warning-900/30 flex items-center justify-center text-warning-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div class="flex-1">
                @if($user->kyc_status === 'pending')
                    <h3 class="text-sm font-bold text-warning-800 dark:text-warning-300">KYC Approval Pending</h3>
                    <p class="text-xs text-warning-700 dark:text-warning-400 mt-0.5">Your profile is submitted for KYC verification. Top-up will be unlocked once an admin approves it.</p>
                @else
                    <h3 class="text-sm font-bold text-warning-800 dark:text-warning-300">KYC Approval Required</h3>
                    <p class="text-xs text-warning-700 dark:text-warning-400 mt-0.5">You must complete your KYC profile and get approved before requesting a wallet top-up.</p>
                @endif
                <div class="mt-2.5">
                    <a href="{{ route('profile.edit') }}" class="inline-flex items-center text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                        {{ $user->kyc_status === 'pending' ? 'View KYC Status' : 'Complete Profile Now' }} &rarr;
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Compact Main Payment Card -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6 dark:border-gray-800 dark:bg-white/[0.03] shadow-sm {{ $isRestricted ? 'opacity-50 pointer-events-none select-none' : '' }}">
        
        <!-- Header & Step Indicator -->
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-500/10 text-brand-500 dark:bg-brand-500/20">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">UPI Wallet Top-up</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Instant deposit via UPI or QR code</p>
                </div>
            </div>

            <!-- Steps Badge -->
            <div class="flex items-center gap-1.5 text-xs font-medium">
                <span :class="step === 1 ? 'bg-brand-500 text-white font-semibold shadow-2xs' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 font-semibold'" 
                      class="px-2.5 py-1 rounded-full transition-all">
                    <span x-show="step === 1">1. Amount</span>
                    <span x-show="step === 2">✓ {{ $currencySymbol }}<span x-text="formattedAmount"></span></span>
                </span>
                <span x-show="step === 2" class="px-2.5 py-1 rounded-full bg-brand-500 text-white font-semibold shadow-2xs transition-all">
                    2. Pay & Submit
                </span>
            </div>
        </div>

        <!-- =================== STEP 1: ENTER AMOUNT =================== -->
        <div x-show="step === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="pt-5 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">
                    Enter Amount to Deposit
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500 font-bold text-xl dark:text-gray-400">
                        {{ $currencySymbol }}
                    </div>
                    <input
                        type="number"
                        x-model="amount"
                        @keydown.enter.prevent="proceedToPay()"
                        step="1"
                        min="1"
                        placeholder="500"
                        class="h-14 w-full rounded-xl border border-gray-300 bg-gray-50/50 pl-10 pr-4 text-2xl font-bold text-gray-900 shadow-2xs placeholder:text-gray-300 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-600 dark:focus:border-brand-500"
                        autofocus
                    />
                </div>
            </div>

            <!-- Quick Amount Chips -->
            <div>
                <span class="text-[11px] font-medium text-gray-400 dark:text-gray-500 block mb-2">Quick Select:</span>
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                    <template x-for="preset in presetAmounts" :key="preset">
                        <button
                            type="button"
                            @click="setAmount(preset)"
                            :class="amount == preset ? 'border-brand-500 bg-brand-50 text-brand-600 dark:bg-brand-500/20 dark:border-brand-500 dark:text-brand-300 font-bold shadow-2xs ring-2 ring-brand-500/20' : 'border-gray-200 bg-gray-50 text-gray-700 hover:bg-gray-100 dark:border-gray-800 dark:bg-white/[0.04] dark:text-gray-300 dark:hover:bg-gray-800'"
                            class="py-2.5 px-1 text-center rounded-xl border text-xs transition active:scale-95 cursor-pointer">
                            {{ $currencySymbol }}<span x-text="preset"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Proceed to Pay Button -->
            <div class="pt-2">
                <button
                    type="button"
                    @click="proceedToPay()"
                    :disabled="!amount || parseFloat(amount) < 1"
                    class="w-full h-12 flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 text-sm font-bold text-white shadow-sm transition hover:bg-brand-600 active:scale-[0.99] disabled:opacity-50 disabled:pointer-events-none cursor-pointer">
                    <span>Proceed to Pay</span>
                    <span x-show="amount && parseFloat(amount) >= 1" class="font-mono">({{ $currencySymbol }}<span x-text="formattedAmount"></span>)</span>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- =================== STEP 2: PAY VIA UPI / QR & SUBMIT TRANSACTION =================== -->
        <div x-show="step === 2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="pt-5 space-y-4" style="display: none;">
            
            <!-- Amount Summary Banner with Change Button -->
            <div class="flex items-center justify-between p-3.5 rounded-xl bg-brand-50 dark:bg-brand-500/10 border border-brand-200/60 dark:border-brand-500/20">
                <div>
                    <span class="text-[11px] font-semibold text-brand-700 dark:text-brand-300 uppercase tracking-wider block">Payable Amount</span>
                    <div class="text-2xl font-black text-brand-900 dark:text-white mt-0.5">
                        {{ $currencySymbol }}<span x-text="formattedAmount"></span>
                    </div>
                </div>
                <button
                    type="button"
                    @click="changeAmount()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 shadow-2xs hover:bg-gray-50 active:scale-95 transition cursor-pointer">
                    <svg class="h-3.5 w-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    <span>Change Amount</span>
                </button>
            </div>

            <!-- Primary Action 1: One-click "Open in UPI App" for Mobile -->
            <div class="space-y-1.5 text-center">
                <a
                    :href="upiUrl"
                    class="w-full h-12 flex items-center justify-center gap-2.5 rounded-xl bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 px-4 text-sm font-bold text-white shadow-md hover:opacity-95 active:scale-[0.99] transition cursor-pointer">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>Pay {{ $currencySymbol }}<span x-text="formattedAmount"></span> via UPI App</span>
                </a>
                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                    Tap to open PhonePe, Google Pay, Paytm, or BHIM with this amount.
                </p>
            </div>

            <!-- Divider -->
            <div class="relative flex items-center justify-center pt-1 pb-1">
                <div class="border-t border-gray-200 dark:border-gray-800 w-full"></div>
                <span class="bg-white dark:bg-gray-900 px-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest absolute">OR SCAN QR CODE</span>
            </div>

            <!-- Primary Action 2: Dynamic Amount-Specific QR Code -->
            <div class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-100 dark:border-gray-800/80 bg-gray-50/70 dark:bg-white/[0.02]">
                <div class="p-2 bg-white rounded-xl shadow-xs border border-gray-200 dark:border-gray-700">
                    <img
                        :src="qrCodeUrl"
                        alt="UPI QR Code"
                        class="w-44 h-44 object-contain rounded-lg"
                        loading="lazy"
                        x-on:error="$el.src = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' + encodeURIComponent(upiUrl)"
                    />
                </div>
                
                <p class="text-xs font-bold text-gray-800 dark:text-gray-200 mt-2.5 text-center">
                    Scan to pay {{ $currencySymbol }}<span x-text="formattedAmount"></span>
                </p>

                <!-- Copy UPI ID Pill -->
                <div class="mt-2 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs shadow-2xs">
                    <span class="text-gray-400 font-mono text-[10px] uppercase">UPI ID:</span>
                    <span class="font-mono font-bold text-gray-800 dark:text-gray-200 text-xs">{{ $upiId }}</span>
                    <button
                        type="button"
                        @click="copyUpi()"
                        class="text-brand-600 hover:text-brand-700 dark:text-brand-400 text-xs font-bold ml-1 cursor-pointer">
                        Copy
                    </button>
                </div>

                @if($useCustomQr && $customQrPath)
                    <div class="mt-2">
                        <button
                            type="button"
                            @click="showStaticQrModal = true"
                            class="text-[11px] font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 underline cursor-pointer">
                            View Merchant Shop QR Code
                        </button>
                    </div>
                @endif
            </div>

            <!-- Primary Action 3: Ask Transaction ID / UTR and Submit -->
            <div class="pt-3 border-t border-gray-100 dark:border-gray-800 space-y-3">
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-gray-800 dark:text-white uppercase tracking-wider">
                            UPI Transaction ID / UTR <span class="text-error-500">*</span>
                        </label>
                        <span class="text-[10px] text-gray-400">12-digit number</span>
                    </div>
                    <input
                        type="text"
                        x-model="transactionId"
                        @keydown.enter.prevent="submitPayment()"
                        placeholder="e.g. 412345678901 (from payment app receipt)"
                        class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-3.5 text-sm text-gray-800 shadow-2xs placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500"
                    />
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                        💡 After paying in your UPI app, copy the 12-digit UTR/UPI Ref number and paste it above to submit.
                    </p>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <button
                        type="button"
                        @click="changeAmount()"
                        class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 transition cursor-pointer">
                        Back
                    </button>
                    <button
                        type="button"
                        @click="submitPayment()"
                        :disabled="isSubmitting || !transactionId || transactionId.trim().length < 4"
                        class="flex-1 h-11 flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 text-sm font-bold text-white shadow-sm transition hover:bg-brand-600 disabled:opacity-50 disabled:pointer-events-none cursor-pointer">
                        <svg x-show="isSubmitting" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isSubmitting ? 'Submitting...' : 'Submit Payment Request'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if($useCustomQr && $customQrPath)
        <!-- Optional Modal for Merchant Static QR -->
        <div x-show="showStaticQrModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" style="display: none;" @click.self="showStaticQrModal = false">
            <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 max-w-sm w-full space-y-3 text-center shadow-xl border border-gray-200 dark:border-gray-800">
                <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-800">
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">Merchant Shop QR</h4>
                    <button @click="showStaticQrModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg font-bold">&times;</button>
                </div>
                <img src="{{ $customQrPath }}" alt="Merchant QR" class="w-56 h-56 mx-auto object-contain rounded-xl border border-gray-100 dark:border-gray-800" />
                <p class="text-xs text-gray-500">Scan this code and enter the exact amount manually in your UPI app.</p>
                <button @click="showStaticQrModal = false" class="w-full py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-xs font-semibold rounded-xl text-gray-700 dark:text-gray-300">Close</button>
            </div>
        </div>
    @endif

    <!-- Compact Requests History -->
    <div class="rounded-2xl border border-gray-200 bg-white p-4 md:p-5 dark:border-gray-800 dark:bg-white/[0.03] shadow-xs">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white/90">Your Top-up History</h3>
                <span id="requests-count" class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">0</span>
            </div>
            <button
                type="button"
                onclick="loadRequests(currentPage)"
                class="inline-flex items-center gap-1 text-xs text-brand-600 hover:text-brand-700 dark:text-brand-400 font-medium cursor-pointer">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Refresh</span>
            </button>
        </div>
        <div id="requests" class="space-y-2.5">
            <p class="text-xs text-gray-400 text-center py-4">Loading requests...</p>
        </div>

        <!-- Pagination Controls (Shown when > 5 transactions) -->
        <div id="pagination-wrapper" class="hidden mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-2.5">
            <div id="pagination-info" class="text-[11px] text-gray-500 dark:text-gray-400"></div>
            <div id="pagination-buttons" class="flex items-center gap-1"></div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const CURRENCY_SYMBOL = '{{ $currencySymbol }}';
let currentPage = 1;
const PER_PAGE = 5;

async function loadRequests(page = 1) {
    currentPage = page;
    const el = document.getElementById('requests');
    const countEl = document.getElementById('requests-count');
    const pagWrapper = document.getElementById('pagination-wrapper');
    const pagInfo = document.getElementById('pagination-info');
    const pagButtons = document.getElementById('pagination-buttons');

    try {
        const res = await fetch(`/api/payment-requests?page=${page}&per_page=${PER_PAGE}`, {credentials:'same-origin'});
        const json = await res.json();
        el.innerHTML = '';
        
        const list = json.data || [];
        const total = (json.total !== undefined) ? json.total : list.length;
        const lastPage = json.last_page || 1;
        const from = json.from || (list.length > 0 ? (page - 1) * PER_PAGE + 1 : 0);
        const to = json.to || (list.length > 0 ? from + list.length - 1 : 0);

        if (countEl) countEl.innerText = total;

        if (list.length === 0) {
            el.innerHTML = '<p class="text-xs text-gray-400 text-center py-3">No payment requests submitted yet.</p>';
            if (pagWrapper) pagWrapper.classList.add('hidden');
            return;
        }

        list.forEach(r => {
            const d = document.createElement('div');
            d.className = 'p-3 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/70 dark:bg-white/[0.02] flex flex-col gap-1.5';
            
            let statusBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">PENDING</span>';
            if (r.status === 'approved') {
                statusBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">APPROVED</span>';
            } else if (r.status === 'rejected') {
                statusBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400">REJECTED</span>';
            }

            const date = new Date(r.created_at).toLocaleString(undefined, {
                month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
            });

            d.innerHTML = `
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-gray-900 dark:text-white">${CURRENCY_SYMBOL}${parseFloat(r.amount).toLocaleString(undefined, {minimumFractionDigits: 2})}</span>
                        <span class="text-[10px] font-semibold text-gray-400 uppercase">${r.method || 'UPI'}</span>
                    </div>
                    ${statusBadge}
                </div>
                <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400">
                    <span class="font-mono truncate max-w-[200px]" title="${r.reference || ''}">Ref: ${r.reference || 'N/A'}</span>
                    <span class="text-[10px] text-gray-400">${date}</span>
                </div>
                ${r.admin_note ? `
                    <div class="mt-1 p-2 rounded-lg bg-white dark:bg-gray-800/80 border border-gray-200/60 dark:border-gray-700/60 text-[11px] text-gray-600 dark:text-gray-300">
                        <strong class="font-semibold text-gray-700 dark:text-gray-200">Admin Note:</strong> ${r.admin_note}
                    </div>
                ` : ''}
            `;
            el.appendChild(d);
        });

        // Handle Pagination Controls
        if (lastPage > 1 && pagWrapper) {
            pagWrapper.classList.remove('hidden');
            if (pagInfo) {
                pagInfo.innerHTML = `Showing <span class="font-semibold text-gray-700 dark:text-gray-200">${from}–${to}</span> of <span class="font-semibold text-gray-700 dark:text-gray-200">${total}</span>`;
            }
            if (pagButtons) {
                let btns = '';
                // Previous button
                btns += `
                    <button type="button" 
                            onclick="loadRequests(${page - 1})" 
                            ${page <= 1 ? 'disabled' : ''} 
                            class="px-2.5 py-1 text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 disabled:opacity-40 disabled:pointer-events-none transition cursor-pointer">
                        &larr; Prev
                    </button>
                `;

                // Page numbers
                for (let p = 1; p <= lastPage; p++) {
                    if (p === 1 || p === lastPage || (p >= page - 1 && p <= page + 1)) {
                        btns += `
                            <button type="button" 
                                    onclick="loadRequests(${p})" 
                                    class="h-7 min-w-7 px-1.5 text-xs rounded-lg border text-center transition cursor-pointer ${p === page ? 'bg-brand-500 border-brand-500 text-white font-bold shadow-2xs' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50'}">
                                ${p}
                            </button>
                        `;
                    } else if (p === page - 2 || p === page + 2) {
                        btns += `<span class="px-1 text-xs text-gray-400">...</span>`;
                    }
                }

                // Next button
                btns += `
                    <button type="button" 
                            onclick="loadRequests(${page + 1})" 
                            ${page >= lastPage ? 'disabled' : ''} 
                            class="px-2.5 py-1 text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 disabled:opacity-40 disabled:pointer-events-none transition cursor-pointer">
                        Next &rarr;
                    </button>
                `;
                pagButtons.innerHTML = btns;
            }
        } else if (pagWrapper) {
            pagWrapper.classList.add('hidden');
        }

    } catch(e) {
        el.innerHTML = '<p class="text-xs text-rose-500 text-center py-2">Failed to load requests.</p>';
    }
}

// Initial load of history (page 1)
loadRequests(1);
</script>
@endpush
@endsection
