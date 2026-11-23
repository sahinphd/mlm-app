{{-- resources/views/partials/mobile_scan_pay_fab.blade.php --}}
<!-- ============================================================== -->
<!-- PHONEPE-STYLE BOTTOM FIXED SCAN & PAY BUTTON                    -->
<!-- ============================================================== -->
<style id="phonepe-fab-styles">
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
    
    /* Fixed Positioning: Centered at bottom on mobile, bottom-right on desktop */
    #phonepeScanPayFab {
        position: fixed !important;
        z-index: 99990 !important;
        bottom: max(1.25rem, calc(0.75rem + env(safe-area-inset-bottom, 0px))) !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.75rem !important;
        padding: 0.75rem 1.35rem !important;
        border-radius: 9999px !important;
        text-decoration: none !important;
        cursor: pointer !important;
        user-select: none !important;
        -webkit-user-select: none !important;
        -webkit-tap-highlight-color: transparent !important;
        background: linear-gradient(135deg, #5f259f 0%, #4a148c 45%, #1555a8 100%) !important;
        box-shadow: 0 10px 25px -4px rgba(95, 37, 159, 0.6),
                    0 4px 14px -2px rgba(21, 85, 168, 0.45),
                    0 0 0 1px rgba(255, 255, 255, 0.28) inset !important;
        transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.2s ease !important;
    }

    /* On larger screens (tablets/desktops), dock to bottom right so it is also available and testable */
    @media (min-width: 768px) {
        #phonepeScanPayFab {
            left: auto !important;
            right: 1.5rem !important;
            transform: none !important;
        }
    }

    #phonepeScanPayFab:hover {
        background: linear-gradient(135deg, #6c2bb2 0%, #56199f 45%, #1a64c8 100%) !important;
        box-shadow: 0 14px 28px -3px rgba(95, 37, 159, 0.7),
                    0 6px 18px -2px rgba(21, 85, 168, 0.5),
                    0 0 0 1px rgba(255, 255, 255, 0.35) inset !important;
        color: #ffffff !important;
    }

    #phonepeScanPayFab:active {
        transform: translateX(-50%) scale(0.96) !important;
        box-shadow: 0 6px 16px -2px rgba(95, 37, 159, 0.5),
                    0 2px 8px -1px rgba(21, 85, 168, 0.35) !important;
    }
    @media (min-width: 768px) {
        #phonepeScanPayFab:active {
            transform: scale(0.96) !important;
        }
    }

    /* Laser Line Animation */
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

    /* All inner elements ignore pointer events so the anchor tag receives all clicks directly */
    #phonepeScanPayFab * {
        pointer-events: none !important;
    }
</style>

<a href="{{ route('wallet.scan-pay') }}" 
   id="phonepeScanPayFab"
   class="phonepe-fab-btn"
   onclick="window.location.href='{{ route('wallet.scan-pay') }}';"
   aria-label="Scan and Pay">
    
    <!-- Viewfinder Icon Container with Animated Laser Line -->
    <div style="position: relative; display: flex; align-items: center; justify-content: center; width: 2rem; height: 2rem; border-radius: 0.75rem; background: rgba(255, 255, 255, 0.18); border: 1px solid rgba(255, 255, 255, 0.3); flex-shrink: 0; overflow: hidden;">
        <!-- QR Viewfinder SVG (PhonePe style) -->
        <svg style="width: 1.25rem; height: 1.25rem; color: #ffffff;" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <!-- 4 Corner Brackets -->
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
    <div style="display: flex; flex-direction: column; text-align: left; line-height: 1.15; padding-right: 0.25rem;">
        <div style="display: flex; align-items: center; gap: 0.375rem;">
            <span style="font-size: 0.675rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; color: #ffffff;">Scan &amp; Pay</span>
            <!-- Live Pulse Dot -->
            <span style="position: relative; display: flex; width: 0.5rem; height: 0.5rem;">
                <span class="animate-ping" style="position: absolute; display: inline-flex; height: 100%; width: 100%; border-radius: 9999px; background-color: #34d399; opacity: 0.75;"></span>
                <span style="position: relative; display: inline-flex; border-radius: 9999px; height: 0.5rem; width: 0.5rem; background-color: #34d399;"></span>
            </span>
        </div>
        <span style="font-size: 0.25rem; font-weight: 500; color: rgba(255, 255, 255, 0.82); letter-spacing: -0.01em;">Instant QR Payment</span>
    </div>

    <!-- Right Arrow Badge -->
    <div style="display: flex; align-items: center; justify-content: center; width: 1.5rem; height: 1.5rem; border-radius: 9999px; background: rgba(255, 255, 255, 0.18); color: rgba(255, 255, 255, 0.95);">
        <svg style="width: 0.875rem; height: 0.875rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
        </svg>
    </div>
</a>

<script>
    (function() {
        const fab = document.getElementById('phonepeScanPayFab');
        if (fab) {
            fab.addEventListener('touchend', function(e) {
                e.preventDefault();
                window.location.href = "{{ route('wallet.scan-pay') }}";
            });
        }
    })();
</script>
