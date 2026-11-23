{{-- resources/views/partials/mobile_scan_pay_fab.blade.php --}}
<!-- ============================================================== -->
<!-- PHONEPE-STYLE FIXED BOTTOM SCAN & PAY FLOATING BUTTON (MOBILE)  -->
<!-- ============================================================== -->
<style>
    @keyframes phonepeLaserSweep {
        0%, 100% {
            top: 15%;
            opacity: 0.55;
        }
        50% {
            top: 75%;
            opacity: 1;
        }
    }
    .phonepe-scan-laser-line {
        position: absolute;
        left: 2px;
        right: 2px;
        height: 2px;
        background: #38bdf8;
        box-shadow: 0 0 6px 1px #38bdf8, 0 0 10px 2px rgba(56, 189, 248, 0.7);
        border-radius: 9999px;
        pointer-events: none;
        animation: phonepeLaserSweep 1.8s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    .phonepe-fab-btn {
        background: linear-gradient(135deg, #5f259f 0%, #4a148c 45%, #1555a8 100%);
        box-shadow: 0 10px 25px -4px rgba(95, 37, 159, 0.55),
                    0 4px 14px -2px rgba(21, 85, 168, 0.4),
                    0 0 0 1px rgba(255, 255, 255, 0.25) inset;
        transition: transform 0.18s cubic-bezier(0.16, 1, 0.3, 1),
                    box-shadow 0.18s cubic-bezier(0.16, 1, 0.3, 1),
                    background 0.2s ease;
    }
    .phonepe-fab-btn:hover {
        background: linear-gradient(135deg, #6c2bb2 0%, #56199f 45%, #1a64c8 100%);
        box-shadow: 0 14px 28px -3px rgba(95, 37, 159, 0.65),
                    0 6px 18px -2px rgba(21, 85, 168, 0.45),
                    0 0 0 1px rgba(255, 255, 255, 0.35) inset;
    }
    .phonepe-fab-btn:active {
        transform: scale(0.95);
        box-shadow: 0 6px 16px -2px rgba(95, 37, 159, 0.45),
                    0 2px 8px -1px rgba(21, 85, 168, 0.3),
                    0 0 0 1px rgba(255, 255, 255, 0.2) inset;
    }
</style>

<div class="fixed bottom-5 left-0 right-0 z-40 flex items-center justify-center px-4 pointer-events-none md:hidden"
     style="bottom: max(1.25rem, calc(0.75rem + env(safe-area-inset-bottom, 0px)));">
    <a href="{{ route('wallet.scan-pay') }}" 
       class="phonepe-fab-btn pointer-events-auto group relative flex items-center gap-3 px-5 py-3 rounded-full text-white select-none text-decoration-none"
       aria-label="Scan and Pay">
        
        <!-- Viewfinder Icon Container with Animated Laser Line -->
        <div class="relative flex items-center justify-center w-8 h-8 rounded-xl bg-white/15 border border-white/25 shrink-0 overflow-hidden shadow-inner">
            <!-- QR Viewfinder SVG -->
            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <!-- 4 Corner Brackets (PhonePe style) -->
                <path d="M3 8V5.5A2.5 2.5 0 015.5 3H8" stroke-width="2.5" stroke-linecap="round"/>
                <path d="M16 3h2.5A2.5 2.5 0 0121 5.5V8" stroke-width="2.5" stroke-linecap="round"/>
                <path d="M21 16v2.5a2.5 2.5 0 01-2.5 2.5H16" stroke-width="2.5" stroke-linecap="round"/>
                <path d="M8 21H5.5A2.5 2.5 0 013 18.5V16" stroke-width="2.5" stroke-linecap="round"/>
                <!-- Internal QR Marks -->
                <rect x="6.5" y="6.5" width="3" height="3" rx="0.5" fill="currentColor" stroke="none"/>
                <rect x="14.5" y="6.5" width="3" height="3" rx="0.5" fill="currentColor" stroke="none"/>
                <rect x="6.5" y="14.5" width="3" height="3" rx="0.5" fill="currentColor" stroke="none"/>
                <rect x="13.5" y="13.5" width="4" height="4" rx="0.75" stroke-width="1.3" fill="none"/>
            </svg>
            <!-- Animated Cyan Laser Beam -->
            <div class="phonepe-scan-laser-line"></div>
        </div>

        <!-- Text Labels -->
        <div class="flex flex-col text-left leading-tight pr-1">
            <div class="flex items-center gap-1.5">
                <span class="text-sm font-extrabold tracking-wider uppercase text-white drop-shadow-xs">Scan &amp; Pay</span>
                <!-- Live Pulse Dot -->
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                </span>
            </div>
            <span class="text-[10px] font-medium text-white/80 tracking-tight">Instant QR Payment</span>
        </div>

        <!-- Right Arrow Badge -->
        <div class="flex items-center justify-center w-6 h-6 rounded-full bg-white/15 text-white/90 group-hover:bg-white/25 group-hover:translate-x-0.5 transition-all">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </a>
</div>
