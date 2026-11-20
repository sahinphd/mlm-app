@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-xl font-sans" 
     x-data="scanAndPayApp({
        initialRecipient: {{ $initialRecipient ? json_encode($initialRecipient) : 'null' }},
        routes: {
            resolve: '{{ route('wallet.resolve-recipient') }}',
            transfer: '{{ route('wallet.qr-transfer') }}',
            myQr: '{{ route('wallet.my-qr') }}',
            history: '{{ route('wallet.history') }}'
        },
        csrfToken: '{{ csrf_token() }}',
        userBalance: {{ (float) ($wallet->main_balance ?? 0) }}
     })" 
     x-init="init()"
     x-cloak>

    <!-- Global Floating Toast Notification -->
    <div x-show="toast.show" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="fixed top-6 right-6 z-99999 max-w-sm rounded-lg p-4 shadow-xl flex items-center justify-between text-sm font-semibold"
         :class="toast.type === 'success' ? 'bg-[#15803d] text-white' : 'bg-[#b91c1c] text-white'">
        <div class="flex items-center gap-2">
            <span x-text="toast.message"></span>
        </div>
        <button @click="toast.show = false" class="text-white/80 hover:text-white font-bold ml-4">✕</button>
    </div>

    <!-- Header & Available Balance Strip -->
    <div class="mb-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-white dark:bg-boxdark p-4 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xs">
        <div>
            <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-[#1555a8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                <span>Scan QR & Pay</span>
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">Transfer instant digital credit using camera or Customer ID.</p>
        </div>

        <div class="text-right">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-bold">Available Balance</p>
            <p class="text-lg font-black text-green-700 dark:text-green-400" x-text="'₹ ' + Number(currentBalance).toFixed(2)"></p>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- STAGE 1: SCANNER & MANUAL FALLBACK (Shown when no recipient)   -->
    <!-- ============================================================== -->
    <div x-show="!recipient && !receipt" class="space-y-4">
        
        <!-- Live Camera Viewfinder Card -->
        <div class="rounded-2xl border border-stroke bg-white dark:border-strokedark dark:bg-boxdark p-4 shadow-sm overflow-hidden">
            
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                    <span class="inline-block w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                    <span>Live Camera Scanner</span>
                </span>
                
                <div class="flex items-center gap-2">
                    <!-- Switch Camera Button -->
                    <button type="button" 
                            x-show="cameras.length > 1"
                            @click="switchCamera()" 
                            title="Flip Camera"
                            class="p-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                    <!-- Toggle Scanner Button -->
                    <button type="button" 
                            @click="scannerActive ? stopScanner() : startScanner()" 
                            class="px-2.5 py-1 rounded-lg text-xs font-bold transition"
                            :class="scannerActive ? 'bg-red-50 text-red-600 border border-red-200 dark:bg-red-950/30' : 'bg-primary text-white'">
                        <span x-text="scannerActive ? 'Pause Camera' : 'Start Camera'"></span>
                    </button>
                </div>
            </div>

            <!-- Viewfinder Element -->
            <div class="relative w-full rounded-xl overflow-hidden bg-black flex items-center justify-center min-h-[260px] sm:min-h-[300px]">
                
                <!-- HTML5 QRCode Video Mount Point -->
                <div id="qr-reader" class="w-full h-full"></div>

                <!-- Laser scan animation overlay -->
                <div x-show="scannerActive && !resolving" class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                    <div class="w-56 h-56 border-2 border-white/60 rounded-2xl relative shadow-[0_0_0_9999px_rgba(0,0,0,0.55)]">
                        <!-- Corner guides -->
                        <div class="absolute -top-1 -left-1 w-6 h-6 border-t-4 border-l-4 border-brand-500 rounded-tl"></div>
                        <div class="absolute -top-1 -right-1 w-6 h-6 border-t-4 border-r-4 border-brand-500 rounded-tr"></div>
                        <div class="absolute -bottom-1 -left-1 w-6 h-6 border-b-4 border-l-4 border-brand-500 rounded-bl"></div>
                        <div class="absolute -bottom-1 -right-1 w-6 h-6 border-b-4 border-r-4 border-brand-500 rounded-br"></div>
                        
                        <!-- Animated laser line -->
                        <div class="absolute left-2 right-2 h-0.5 bg-red-500 shadow-[0_0_8px_#ef4444] animate-bounce" style="animation-duration: 2s;"></div>
                    </div>
                    <p class="absolute bottom-4 text-xs font-medium text-white/90 bg-black/60 px-3 py-1 rounded-full backdrop-blur-xs">
                        Point camera at Customer QR Code
                    </p>
                </div>

                <!-- Camera Error / Permission Notice -->
                <div x-show="cameraError" class="p-6 text-center text-white space-y-2">
                    <svg class="w-10 h-10 text-yellow-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p class="text-sm font-bold" x-text="cameraError"></p>
                    <p class="text-xs text-gray-300">Allow camera permission in browser settings, or use the manual Customer ID input below.</p>
                    <button type="button" @click="startScanner()" class="mt-2 px-3 py-1.5 rounded-lg bg-white/20 hover:bg-white/30 text-xs font-semibold">
                        Try Again
                    </button>
                </div>

                <!-- Resolving Spinner -->
                <div x-show="resolving" class="absolute inset-0 bg-black/75 flex flex-col items-center justify-center text-white z-20">
                    <svg class="w-8 h-8 animate-spin text-brand-400 mb-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span class="text-xs font-bold">Verifying customer QR code...</span>
                </div>
            </div>

            <!-- Alternative Scan from File/Gallery -->
            <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
                <span>Or scan an image from gallery:</span>
                <label class="cursor-pointer font-bold text-[#1555a8] hover:underline flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Upload Image</span>
                    <input type="file" accept="image/*" class="hidden" @change="scanFromFile($event)">
                </label>
            </div>
        </div>

        <!-- Manual Customer ID Fallback Input Card -->
        <div class="rounded-2xl border border-stroke bg-white dark:border-strokedark dark:bg-boxdark p-4 shadow-sm">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                Manual Fallback: Search Customer ID
            </h3>
            
            <form @submit.prevent="resolveManualInput()" class="flex gap-2">
                <div class="relative flex-1">
                    <input type="text" 
                           x-model="manualInput" 
                           placeholder="Enter CUST:15, CUST-10015, ID, or Phone..." 
                           class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50/50 dark:bg-meta-4/20 py-2.5 px-3.5 text-sm font-medium text-black dark:text-white outline-none focus:border-[#1555a8] focus:bg-white transition" />
                    <button type="button" 
                            x-show="manualInput" 
                            @click="manualInput = ''" 
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-black dark:hover:text-white text-xs p-1">
                        ✕
                    </button>
                </div>
                <button type="submit" 
                        :disabled="resolving || !manualInput.trim()"
                        class="px-4 py-2.5 rounded-xl bg-[#1555a8] hover:bg-[#12468d] text-white text-xs font-bold shadow-xs transition disabled:opacity-50">
                    <span x-text="resolving ? 'Checking...' : 'Find'"></span>
                </button>
            </form>

            <div x-show="resolveError" class="mt-2.5 rounded-lg bg-red-50 dark:bg-red-950/20 p-2.5 text-xs text-red-600 dark:text-red-400" x-text="resolveError"></div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- STAGE 2: RECIPIENT RESOLVED & AMOUNT INPUT FORM                -->
    <!-- ============================================================== -->
    <div x-show="recipient && !receipt" class="space-y-4">
        
        <!-- Recipient Profile Card (Verified) -->
        <div class="rounded-2xl border border-stroke bg-white dark:border-strokedark dark:bg-boxdark p-5 shadow-md">
            
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                <span class="text-[11px] font-bold uppercase tracking-wider text-green-700 dark:text-green-400 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Recipient Verified</span>
                </span>
                
                <button type="button" 
                        @click="clearRecipient()" 
                        class="text-xs font-bold text-gray-500 hover:text-black dark:hover:text-white flex items-center gap-1">
                    <span>Scan Another</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="pt-4 flex items-center gap-3.5">
                <div class="w-14 h-14 rounded-2xl bg-[#1555a8] text-white font-extrabold text-lg flex items-center justify-center shrink-0 shadow-md"
                     x-text="recipient ? recipient.initials : 'CU'">
                </div>

                <div class="min-w-0 flex-1">
                    <h3 class="text-base font-extrabold text-black dark:text-white leading-tight truncate" 
                        x-text="recipient ? recipient.name : ''"></h3>
                    
                    <div class="flex items-center gap-2 mt-1">
                        <span class="inline-flex px-2 py-0.5 rounded-md bg-gray-100 dark:bg-meta-4 text-[11px] font-mono font-bold text-gray-800 dark:text-gray-200" 
                              x-text="recipient ? recipient.cust_id : ''"></span>
                        <span class="text-xs text-gray-500 dark:text-gray-400" 
                              x-text="recipient ? recipient.masked_phone : ''"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Amount & Remarks Entry Form -->
        <div class="rounded-2xl border border-stroke bg-white dark:border-strokedark dark:bg-boxdark p-5 shadow-md">
            
            <form @submit.prevent="openConfirmModal()" class="space-y-4">
                
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                        Transfer Amount (₹) *
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-lg font-bold text-gray-400">₹</span>
                        <input type="number" 
                               step="0.01" 
                               min="1" 
                               :max="currentBalance"
                               x-model="transferAmount" 
                               required
                               placeholder="0.00" 
                               class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-boxdark py-3 pl-9 pr-4 text-2xl font-black text-black dark:text-white outline-none focus:border-[#1555a8] transition" />
                    </div>

                    <!-- Quick Amount Chips -->
                    <div class="mt-2.5 flex flex-wrap gap-1.5">
                        <template x-for="chip in [50, 100, 200, 500, 1000]" :key="chip">
                            <button type="button" 
                                    @click="transferAmount = chip" 
                                    :disabled="chip > currentBalance"
                                    class="rounded-lg border border-gray-200 dark:border-gray-700 px-2.5 py-1 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:border-[#1555a8] hover:text-[#1555a8] disabled:opacity-30 transition"
                                    x-text="'+ ₹' + chip"></button>
                        </template>
                        <template x-if="currentBalance > 0">
                            <button type="button" 
                                    @click="transferAmount = currentBalance" 
                                    class="rounded-lg border border-green-300 dark:border-green-800 bg-green-50/50 dark:bg-green-950/20 px-2.5 py-1 text-xs font-bold text-green-700 dark:text-green-400 hover:bg-green-100 transition">
                                Send All
                            </button>
                        </template>
                    </div>

                    <!-- Insufficient Balance Alert -->
                    <template x-if="Number(transferAmount) > currentBalance">
                        <p class="mt-1.5 text-xs text-red-600 font-semibold">
                            Amount exceeds your available balance of ₹<span x-text="Number(currentBalance).toFixed(2)"></span>!
                        </p>
                    </template>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                        Remarks / Note (Optional)
                    </label>
                    <input type="text" 
                           x-model="transferRemarks" 
                           placeholder="e.g. Grocery payment, lunch share, repayment..." 
                           class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-boxdark py-2.5 px-3.5 text-xs text-black dark:text-white outline-none focus:border-[#1555a8] transition" />
                </div>

                <div x-show="transferError" class="rounded-lg bg-red-50 dark:bg-red-950/20 p-3 text-xs text-red-600 dark:text-red-400" x-text="transferError"></div>

                <!-- Pay Button -->
                <button type="submit" 
                        :disabled="transferLoading || !transferAmount || Number(transferAmount) <= 0 || Number(transferAmount) > currentBalance"
                        class="w-full py-3.5 rounded-xl bg-[#1555a8] hover:bg-[#12468d] active:scale-[0.99] text-white text-sm font-extrabold uppercase tracking-wide shadow-md transition disabled:opacity-50 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                    <span>Proceed to Pay</span>
                </button>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- STAGE 3: CONFIRMATION MODAL (Prevents accidental tap)          -->
    <!-- ============================================================== -->
    <div x-show="confirmModal" 
         x-transition
         class="fixed inset-0 z-99999 flex items-center justify-center bg-black/60 p-4"
         @keydown.escape.window="if(!transferLoading) confirmModal = false">
        
        <div @click.away="if(!transferLoading) confirmModal = false"
             class="w-full max-w-sm rounded-3xl bg-white p-6 shadow-2xl dark:bg-boxdark border border-stroke dark:border-strokedark text-center">
            
            <div class="w-14 h-14 mx-auto rounded-full bg-blue-50 dark:bg-meta-4 flex items-center justify-center text-[#1555a8] mb-3">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3-1.343-3-3S10.343 2 12 2s3 1.343 3 3-1.343 3-3 3zM6 20c0-3.866 3.582-7 8-7s8 3.134 8 7" />
                </svg>
            </div>

            <h3 class="text-base font-black text-gray-900 dark:text-white">Confirm Transfer</h3>
            <p class="text-xs text-gray-500 mt-1">Please verify the recipient and transfer amount before confirming.</p>

            <div class="my-4 rounded-2xl bg-gray-50 dark:bg-meta-4/20 p-4 text-left space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-gray-500">To Recipient:</span>
                    <span class="font-bold text-gray-900 dark:text-white truncate max-w-[170px]" x-text="recipient ? recipient.name : ''"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Customer ID:</span>
                    <span class="font-mono font-bold text-gray-900 dark:text-white" x-text="recipient ? recipient.cust_id : ''"></span>
                </div>
                <div class="flex justify-between border-t pt-2 border-gray-200 dark:border-gray-700">
                    <span class="text-gray-700 dark:text-gray-300 font-bold">Transfer Amount:</span>
                    <span class="font-black text-base text-[#1555a8]" x-text="'₹ ' + Number(transferAmount).toFixed(2)"></span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 pt-2">
                <button type="button" 
                        @click="confirmModal = false" 
                        :disabled="transferLoading"
                        class="py-2.5 px-3 rounded-xl border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                    Cancel
                </button>
                <button type="button" 
                        @click="executeTransfer()" 
                        :disabled="transferLoading"
                        class="py-2.5 px-3 rounded-xl bg-[#1555a8] hover:bg-[#12468d] text-white text-xs font-extrabold uppercase shadow-sm transition disabled:opacity-50 flex items-center justify-center gap-1.5">
                    <svg x-show="transferLoading" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span x-text="transferLoading ? 'Processing...' : 'Confirm & Pay'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- STAGE 4: DIGITAL TRANSACTION RECEIPT CARD                      -->
    <!-- ============================================================== -->
    <div x-show="receipt" class="space-y-4">
        
        <div class="rounded-3xl border border-green-200 bg-white dark:border-green-900/30 dark:bg-boxdark p-6 shadow-xl text-center relative overflow-hidden">
            
            <!-- Success Top Banner -->
            <div class="w-16 h-16 mx-auto rounded-full bg-green-100 dark:bg-green-950/40 text-green-600 dark:text-green-400 flex items-center justify-center mb-3 shadow-xs">
                <svg class="w-9 h-9" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h3 class="text-xl font-black text-gray-900 dark:text-white">Payment Successful!</h3>
            <p class="text-xs text-gray-500 mt-0.5">Your wallet balance and ledger have been updated in real time.</p>

            <!-- Big Amount Display -->
            <div class="my-4 py-3 bg-gray-50 dark:bg-meta-4/20 rounded-2xl">
                <p class="text-3xl font-black text-gray-900 dark:text-white" x-text="receipt ? receipt.amount_formatted : ''"></p>
                <p class="text-[11px] text-gray-500 mt-0.5">Transferred to <strong class="text-gray-900 dark:text-white" x-text="receipt ? receipt.recipient_name : ''"></strong></p>
            </div>

            <!-- Receipt Breakdown Table -->
            <div class="rounded-2xl border border-gray-100 dark:border-gray-800 p-4 text-xs space-y-2.5 text-left bg-white dark:bg-boxdark">
                
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Transaction ID:</span>
                    <div class="flex items-center gap-1.5">
                        <span class="font-mono font-bold text-gray-800 dark:text-gray-200" x-text="receipt ? receipt.transaction_id : ''"></span>
                        <button type="button" 
                                @click="copyText(receipt ? receipt.transaction_id : '')" 
                                title="Copy Transaction ID"
                                class="text-gray-400 hover:text-black dark:hover:text-white p-0.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Recipient ID:</span>
                    <span class="font-mono font-bold text-gray-800 dark:text-gray-200" x-text="receipt ? receipt.recipient_cust_id : ''"></span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Timestamp:</span>
                    <span class="font-medium text-gray-800 dark:text-gray-200" x-text="receipt ? receipt.date : ''"></span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Ledger Status:</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-50 dark:bg-green-950/30 text-green-700 dark:text-green-400 font-bold text-[10px]">
                        <span>✓</span> Completed & Settled
                    </span>
                </div>

                <div class="flex items-center justify-between border-t pt-2 border-gray-100 dark:border-gray-800">
                    <span class="text-gray-700 dark:text-gray-300 font-semibold">Remaining Wallet Balance:</span>
                    <span class="font-black text-green-700 dark:text-green-400" x-text="receipt ? receipt.sender_new_balance_formatted : ''"></span>
                </div>
            </div>

            <!-- Receipt Actions -->
            <div class="mt-5 grid grid-cols-2 gap-2.5">
                <button type="button" 
                        @click="resetTransferFlow()" 
                        class="py-2.5 px-3 rounded-xl bg-[#1555a8] hover:bg-[#12468d] text-white text-xs font-bold shadow-xs transition">
                    Make Another Transfer
                </button>
                <a href="{{ route('wallet.history') }}" 
                   class="py-2.5 px-3 rounded-xl border border-gray-300 dark:border-gray-700 text-gray-800 dark:text-gray-200 text-xs font-bold hover:bg-gray-50 dark:hover:bg-gray-800 transition flex items-center justify-center">
                    View Wallet Ledger
                </a>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="{{ asset('vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>
<script>
// Fallback if local vendor script fails
if (typeof Html5Qrcode === 'undefined') {
    const s = document.createElement('script');
    s.src = 'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js';
    document.head.appendChild(s);
}

function scanAndPayApp(config) {
    return {
        recipient: config.initialRecipient || null,
        currentBalance: config.userBalance || 0,
        manualInput: '',
        transferAmount: '',
        transferRemarks: '',
        
        scannerActive: false,
        scannerInstance: null,
        cameras: [],
        currentCameraIndex: 0,
        cameraError: '',
        resolving: false,
        resolveError: '',
        
        confirmModal: false,
        transferLoading: false,
        transferError: '',
        receipt: null,

        toast: {
            show: false,
            message: '',
            type: 'success',
            timer: null
        },

        init() {
            if (!this.recipient) {
                this.$nextTick(() => {
                    this.startScanner();
                });
            }
        },

        showToast(message, type = 'success') {
            clearTimeout(this.toast.timer);
            this.toast.message = message;
            this.toast.type = type;
            this.toast.show = true;
            this.toast.timer = setTimeout(() => {
                this.toast.show = false;
            }, 3500);
        },

        copyText(txt) {
            if (!txt) return;
            navigator.clipboard.writeText(txt);
            this.showToast('Copied: ' + txt, 'success');
        },

        async startScanner() {
            this.cameraError = '';
            const initLib = async () => {
                if (typeof Html5Qrcode === 'undefined') {
                    setTimeout(initLib, 100);
                    return;
                }

                try {
                    if (!this.scannerInstance) {
                        this.scannerInstance = new Html5Qrcode("qr-reader");
                    }

                    // Get available cameras
                    const devices = await Html5Qrcode.getCameras();
                    if (!devices || devices.length === 0) {
                        this.cameraError = 'No camera found on this device.';
                        return;
                    }
                    this.cameras = devices;

                    // Prefer back/environment camera
                    let selectedCam = devices[0].id;
                    for (let i = 0; i < devices.length; i++) {
                        if (devices[i].label.toLowerCase().includes('back') || devices[i].label.toLowerCase().includes('rear')) {
                            selectedCam = devices[i].id;
                            this.currentCameraIndex = i;
                            break;
                        }
                    }

                    await this.scannerInstance.start(
                        selectedCam,
                        {
                            fps: 10,
                            qrbox: { width: 250, height: 250 },
                            aspectRatio: 1.0
                        },
                        (decodedText) => {
                            this.onQrCodeScanned(decodedText);
                        },
                        (errorMessage) => {
                            // Ignored frame-level parse errors
                        }
                    );

                    this.scannerActive = true;
                } catch (err) {
                    console.error('Camera startup error:', err);
                    this.scannerActive = false;
                    this.cameraError = 'Camera access denied or unavailable.';
                }
            };

            initLib();
        },

        async stopScanner() {
            if (this.scannerInstance && this.scannerActive) {
                try {
                    await this.scannerInstance.stop();
                } catch (e) {
                    // Ignore stop error
                }
                this.scannerActive = false;
            }
        },

        async switchCamera() {
            if (this.cameras.length <= 1) return;
            await this.stopScanner();
            this.currentCameraIndex = (this.currentCameraIndex + 1) % this.cameras.length;
            const targetCamId = this.cameras[this.currentCameraIndex].id;

            try {
                await this.scannerInstance.start(
                    targetCamId,
                    { fps: 10, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 },
                    (decodedText) => this.onQrCodeScanned(decodedText),
                    () => {}
                );
                this.scannerActive = true;
            } catch (err) {
                console.error('Flip error:', err);
            }
        },

        async scanFromFile(e) {
            const file = e.target.files[0];
            if (!file) return;

            try {
                if (!this.scannerInstance) {
                    this.scannerInstance = new Html5Qrcode("qr-reader");
                }
                this.resolving = true;
                const result = await this.scannerInstance.scanFile(file, true);
                this.onQrCodeScanned(result);
            } catch (err) {
                this.showToast('No readable QR code found in the image.', 'error');
            } finally {
                this.resolving = false;
            }
        },

        async onQrCodeScanned(decodedText) {
            if (!decodedText || this.resolving) return;
            await this.stopScanner();
            this.resolveCode(decodedText);
        },

        async resolveManualInput() {
            const query = (this.manualInput || '').trim();
            if (!query) return;
            this.resolveCode(query);
        },

        async resolveCode(code) {
            this.resolving = true;
            this.resolveError = '';

            try {
                const response = await fetch(config.routes.resolve, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ code: code })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.recipient = data.recipient;
                    if (data.sender_balance !== undefined) {
                        this.currentBalance = Number(data.sender_balance);
                    }
                    this.transferAmount = '';
                    this.transferRemarks = '';
                    this.transferError = '';
                    this.showToast('Recipient verified: ' + this.recipient.name, 'success');
                } else {
                    this.resolveError = data.message || 'Customer lookup failed.';
                    this.showToast(this.resolveError, 'error');
                    // Restart scanner if resolve failed
                    this.startScanner();
                }
            } catch (err) {
                this.resolveError = 'Network error while looking up customer.';
                this.showToast(this.resolveError, 'error');
                this.startScanner();
            } finally {
                this.resolving = false;
            }
        },

        clearRecipient() {
            this.recipient = null;
            this.transferAmount = '';
            this.transferRemarks = '';
            this.transferError = '';
            this.receipt = null;
            this.startScanner();
        },

        openConfirmModal() {
            const amt = Number(this.transferAmount);
            if (!amt || amt <= 0) {
                this.transferError = 'Please enter a valid amount.';
                return;
            }
            if (amt > this.currentBalance) {
                this.transferError = `Amount cannot exceed your balance (₹${this.currentBalance.toFixed(2)}).`;
                return;
            }
            this.transferError = '';
            this.confirmModal = true;
        },

        async executeTransfer() {
            if (!this.recipient) return;
            this.transferLoading = true;
            this.transferError = '';

            try {
                const response = await fetch(config.routes.transfer, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        recipient_id: this.recipient.id,
                        amount: Number(this.transferAmount),
                        remarks: this.transferRemarks
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.confirmModal = false;
                    this.receipt = data.receipt;
                    if (this.receipt && this.receipt.sender_new_balance !== undefined) {
                        this.currentBalance = Number(this.receipt.sender_new_balance);
                    }
                    this.showToast('Payment successful!', 'success');
                } else {
                    this.confirmModal = false;
                    this.transferError = data.message || 'Transfer failed.';
                    this.showToast(this.transferError, 'error');
                }
            } catch (err) {
                this.confirmModal = false;
                this.transferError = 'Network connection error during transfer.';
                this.showToast(this.transferError, 'error');
            } finally {
                this.transferLoading = false;
            }
        },

        resetTransferFlow() {
            this.receipt = null;
            this.recipient = null;
            this.transferAmount = '';
            this.transferRemarks = '';
            this.transferError = '';
            this.manualInput = '';
            this.startScanner();
        }
    };
}
</script>
@endpush
@endsection
