@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-xl font-sans" x-data="myQrCardApp({
    qrPayload: '{{ $qrPayload }}',
    customerId: '{{ $customer['cust_id'] }}',
    customerName: '{{ addslashes($customer['name']) }}',
    customerPhone: '{{ addslashes($customer['phone']) }}',
    customerEmail: '{{ addslashes($customer['email']) }}',
    referralCode: '{{ addslashes($customer['referral_code']) }}',
    avatarUrl: '{{ $customer['avatar_url'] }}',
    initials: '{{ $customer['initials'] }}'
})" x-init="init()">

    <!-- Top Action Bar -->
    <div class="mb-6 flex flex-col sm:flex-row items-center justify-between gap-3 no-print">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <svg class="w-7 h-7 text-[#1555a8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                <span>{{ isset($isAdminView) ? $customer['name'] . "'s QR Code" : 'My Customer QR Code' }}</span>
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                Scan this unique QR code to instantly send/receive wallet money or process shop transactions.
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if(isset($isAdminView))
                <a href="{{ route('admin.customer-lookup.index', ['user_id' => $user->id]) }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-xs transition">
                    ← Back to Customer Lookup
                </a>
            @else
                <a href="{{ route('wallet.scan-pay') }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-[#1555a8] hover:bg-[#12468d] text-white text-xs font-bold shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Scan & Pay</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Main QR Card Container -->
    <div class="flex justify-center mb-6">
        <div id="customer-qr-card" 
             class="w-full max-w-sm rounded-3xl bg-white dark:bg-boxdark border border-gray-200 dark:border-gray-800 shadow-xl overflow-hidden transition-all duration-300 hover:shadow-2xl">
            
            <!-- Card Header: Royal Brand Gradient -->
            <div class="bg-gradient-to-r from-[#1555a8] via-[#1e66c7] to-[#1555a8] p-5 text-white text-center relative overflow-hidden">
                <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10 blur-xs"></div>
                <div class="absolute -left-6 -bottom-6 w-20 h-20 rounded-full bg-white/10 blur-xs"></div>

                <div class="relative z-10 flex flex-col items-center">
                    <div class="flex items-center gap-2 mb-1">
                        <img src="{{ asset('logo.png') }}" alt="Logo" class="h-6 w-6 rounded-md bg-white/20 p-0.5 object-contain" onerror="this.style.display='none'">
                        <span class="text-xs uppercase tracking-widest font-extrabold text-white/90">{{ config('app.name', 'Duaredokandar') }}</span>
                    </div>
                    <h3 class="text-lg font-black tracking-tight text-white mt-1">{{ $customer['name'] }}</h3>
                    <div class="inline-flex items-center gap-1.5 mt-1 px-2.5 py-0.5 rounded-full bg-white/20 backdrop-blur-xs text-[11px] font-mono font-bold tracking-wider">
                        <span>{{ $customer['cust_id'] }}</span>
                        <span class="text-white/60">•</span>
                        <span>#{{ $customer['id'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Card Body: QR Frame -->
            <div class="p-6 text-center flex flex-col items-center bg-gray-50/50 dark:bg-meta-4/10">
                
                <!-- Crisp QR Code Wrapper with Target Markers -->
                <div class="relative p-4 rounded-2xl bg-white shadow-md border-2 border-dashed border-[#1555a8]/30 inline-block transition-transform hover:scale-[1.02]">
                    <div id="qrcode-canvas-container" class="flex items-center justify-center min-w-[210px] min-h-[210px]">
                        <!-- Rendered by QRCode.js -->
                    </div>
                    
                    <!-- Subtle Central Logo Overlay -->
                    <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                        <div class="w-10 h-10 rounded-full bg-white shadow-md border-2 border-[#1555a8] flex items-center justify-center text-[#1555a8] font-bold text-xs"
                             x-text="initials">
                        </div>
                    </div>
                </div>

                <!-- Customer Details Grid -->
                <div class="mt-4 w-full rounded-xl bg-white dark:bg-boxdark p-3.5 border border-gray-100 dark:border-gray-800 text-xs shadow-xs space-y-2">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                        <span>Payload ID:</span>
                        <span class="font-mono font-bold text-gray-800 dark:text-gray-200" x-text="qrPayload"></span>
                    </div>
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                        <span>Mobile:</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $customer['phone'] ?: 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                        <span>Wallet Balance:</span>
                        <span class="font-extrabold text-green-700 dark:text-green-400">{{ $customer['main_balance_formatted'] }}</span>
                    </div>
                    @if(!empty($customer['referral_code']) && $customer['referral_code'] !== 'N/A')
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                        <span>Referral Code:</span>
                        <span class="font-mono font-bold text-[#1555a8]">{{ $customer['referral_code'] }}</span>
                    </div>
                    @endif
                </div>

                <p class="mt-3 text-[11px] text-gray-400 dark:text-gray-500">
                    Scan with any Duaredokandar scanner for instant transfer & billing.
                </p>
            </div>

            <!-- Card Footer: Quick Actions -->
            <div class="p-4 border-t border-gray-100 dark:border-gray-800 bg-white dark:bg-boxdark flex items-center justify-between gap-2 no-print">
                <button type="button" 
                        @click="copyPayload()" 
                        class="flex-1 py-2 px-2 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 text-xs font-semibold transition flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <span>Copy ID</span>
                </button>

                <button type="button" 
                        @click="downloadCardAsPng()" 
                        :disabled="isGeneratingImage"
                        class="flex-1 py-2 px-2 rounded-xl bg-[#1555a8] hover:bg-[#12468d] text-white text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-xs disabled:opacity-50">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span x-text="isGeneratingImage ? 'Saving...' : 'Save PNG'"></span>
                </button>

                <button type="button" 
                        @click="printCard()" 
                        class="py-2 px-3 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 text-xs font-semibold transition flex items-center justify-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Print</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Instruction Info Banner -->
    <div class="rounded-2xl border border-blue-100 bg-blue-50/70 dark:border-blue-900/30 dark:bg-blue-950/20 p-4 text-xs text-blue-800 dark:text-blue-300 no-print">
        <div class="flex items-start gap-2.5">
            <svg class="w-5 h-5 text-[#1555a8] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <p class="font-bold">Instant Real-Time Ledger Settlements</p>
                <p class="mt-0.5 text-blue-700/80 dark:text-blue-300/80">
                    When someone scans this QR code and enters an amount, your digital wallet balance is credited immediately and an immutable double-entry ledger is recorded with complete audit details.
                </p>
            </div>
        </div>
    </div>

</div>

<!-- Styles for clean printing -->
<style>
@media print {
    body * {
        visibility: hidden !important;
    }
    #customer-qr-card, #customer-qr-card * {
        visibility: visible !important;
    }
    #customer-qr-card {
        position: fixed !important;
        left: 50% !important;
        top: 20px !important;
        transform: translateX(-50%) !important;
        box-shadow: none !important;
        border: 1px solid #cbd5e1 !important;
        width: 340px !important;
    }
    .no-print {
        display: none !important;
    }
}
</style>

@push('scripts')
<script src="{{ asset('vendor/qrcode/qrcode.min.js') }}"></script>
<script>
// Fallback if local vendor script fails
if (typeof QRCode === 'undefined') {
    const s = document.createElement('script');
    s.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js';
    document.head.appendChild(s);
}

function myQrCardApp(config) {
    return {
        qrPayload: config.qrPayload,
        customerId: config.customerId,
        customerName: config.customerName,
        customerPhone: config.customerPhone,
        initials: config.initials,
        isGeneratingImage: false,
        qrCodeObj: null,

        init() {
            this.$nextTick(() => {
                this.renderQrCode();
            });
        },

        renderQrCode() {
            const container = document.getElementById('qrcode-canvas-container');
            if (!container) return;
            container.innerHTML = '';

            const initQr = () => {
                if (typeof QRCode === 'undefined') {
                    setTimeout(initQr, 80);
                    return;
                }

                this.qrCodeObj = new QRCode(container, {
                    text: this.qrPayload,
                    width: 210,
                    height: 210,
                    colorDark: '#0f172a',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.H
                });
            };

            initQr();
        },

        copyPayload() {
            if (!this.qrPayload) return;
            navigator.clipboard.writeText(this.qrPayload);
            if (window.Swal) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Copied: ' + this.qrPayload,
                    showConfirmButton: false,
                    timer: 2000
                });
            } else {
                alert('Copied to clipboard: ' + this.qrPayload);
            }
        },

        printCard() {
            window.print();
        },

        async downloadCardAsPng() {
            this.isGeneratingImage = true;
            try {
                // Find generated canvas or image from QRCode.js
                const container = document.getElementById('qrcode-canvas-container');
                let qrSource = container.querySelector('canvas');
                let qrImg = null;

                if (!qrSource) {
                    const img = container.querySelector('img');
                    if (img && img.src) {
                        qrImg = img;
                    }
                }

                // Create high-res composite canvas
                const canvas = document.createElement('canvas');
                const width = 600;
                const height = 820;
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');

                // 1. Background
                ctx.fillStyle = '#ffffff';
                ctx.roundRect(0, 0, width, height, 32);
                ctx.fill();

                // 2. Header banner
                const gradient = ctx.createLinearGradient(0, 0, width, 0);
                gradient.addColorStop(0, '#1555a8');
                gradient.addColorStop(0.5, '#1e66c7');
                gradient.addColorStop(1, '#1555a8');
                ctx.fillStyle = gradient;
                ctx.beginPath();
                ctx.roundRect(0, 0, width, 180, [32, 32, 0, 0]);
                ctx.fill();

                // 3. Header text
                ctx.fillStyle = '#ffffff';
                ctx.textAlign = 'center';
                ctx.font = 'bold 18px sans-serif';
                ctx.fillText('DUAREDOKANDAR DIGITAL WALLET', width / 2, 45);

                ctx.font = 'bold 30px sans-serif';
                ctx.fillText(this.customerName.toUpperCase(), width / 2, 95);

                ctx.font = '600 20px monospace';
                ctx.fillStyle = 'rgba(255,255,255,0.9)';
                ctx.fillText(`${this.customerId} • ${this.qrPayload}`, width / 2, 135);

                // 4. White QR box with shadow
                ctx.fillStyle = '#f8fafc';
                ctx.beginPath();
                ctx.roundRect(90, 220, 420, 420, 24);
                ctx.fill();
                ctx.strokeStyle = '#1555a8';
                ctx.lineWidth = 3;
                ctx.stroke();

                // Draw QR Code onto canvas
                const drawQr = (srcImgOrCanvas) => {
                    ctx.drawImage(srcImgOrCanvas, 130, 260, 340, 340);

                    // Central initials badge
                    ctx.fillStyle = '#ffffff';
                    ctx.beginPath();
                    ctx.arc(width / 2, 430, 32, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.strokeStyle = '#1555a8';
                    ctx.lineWidth = 3;
                    ctx.stroke();

                    ctx.fillStyle = '#1555a8';
                    ctx.font = 'bold 22px sans-serif';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(this.initials, width / 2, 432);

                    // 5. Footer info
                    ctx.textBaseline = 'alphabetic';
                    ctx.fillStyle = '#334155';
                    ctx.font = '600 18px sans-serif';
                    ctx.fillText('Scan to Pay / Transfer Instantly', width / 2, 690);

                    ctx.fillStyle = '#64748b';
                    ctx.font = '14px sans-serif';
                    ctx.fillText('Duaredokandar Instant Ledger Settlement System', width / 2, 730);

                    // Download trigger
                    const link = document.createElement('a');
                    link.download = `QR_${this.customerId}_${this.customerName.replace(/\s+/g, '_')}.png`;
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                };

                if (qrSource) {
                    drawQr(qrSource);
                } else if (qrImg) {
                    const temp = new Image();
                    temp.crossOrigin = 'anonymous';
                    temp.onload = () => drawQr(temp);
                    temp.src = qrImg.src;
                }
            } catch (err) {
                console.error('Save PNG error:', err);
                alert('Could not generate PNG image automatically. You can take a screenshot or print.');
            } finally {
                this.isGeneratingImage = false;
            }
        }
    };
}
</script>
@endpush
@endsection
