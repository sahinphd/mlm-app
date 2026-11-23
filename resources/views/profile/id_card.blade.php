@extends('layouts.admin')

@php
    $rawHost = config('app.url') ? parse_url(config('app.url'), PHP_URL_HOST) : '';
    $cleanHost = (!empty($rawHost) && !in_array($rawHost, ['localhost', '127.0.0.1'])) ? $rawHost : 'mabia.in';
    $brandName = $idCardBrandName ?? 'JANASEVA MIDIO VANDAR';
@endphp

@section('content')
<div class="mx-auto max-w-270">
    <!-- Top Action Bar -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between no-print">
        <div>
            <h2 class="text-title-md2 font-bold text-black dark:text-white flex items-center gap-2">
                <svg class="w-7 h-7 text-[#1555a8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                </svg>
                <span>Digital ID Card</span>
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                Standard CR80 format (85.6mm &times; 54mm) with Member Identity Pass &amp; Scan &amp; Pay Wallet Pass.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('wallet.my-qr') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition shadow-xs">
                <svg class="mr-1.5 h-4 w-4 text-[#1555a8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                Open Full QR Card
            </a>

            <button onclick="window.print()" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition shadow-md">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print ID Card (Both Sides)
            </button>
        </div>
    </div>

    <!-- ID Cards Display & Print Area (Front & Back Side-by-Side) -->
    <div id="id-cards-print-area" class="flex flex-col xl:flex-row items-center justify-center gap-8 py-8">
        
        <!-- ========================================== -->
        <!-- FRONT SIDE: Member Identity Pass           -->
        <!-- ========================================== -->
        <div class="flex flex-col items-center">
            <div class="mb-2 flex items-center justify-between w-full max-w-[85.6mm] px-1 no-print">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Front Side</span>
                <span class="text-[10px] text-gray-400">Identity &amp; Membership</span>
            </div>

            <div id="id-card-front" class="id-card-render select-none">
                <!-- Top Header Bar -->
                <div class="card-header-bar">
                    <div class="card-header-accent"></div>
                    <div class="card-header-accent-2"></div>
                    
                    <div class="flex items-center gap-2 z-10 min-w-0">
                        <div class="header-logo-box">
                            <img src="{{ asset('images/logo/auth-logo.svg') }}" class="h-5 w-auto" alt="Logo" onerror="this.src='{{ asset('logo.png') }}'">
                        </div>
                        <div class="min-w-0">
                            <h3 class="card-brand-title">{{ $brandName }}</h3>
                            <p class="card-brand-sub">Official Member</p>
                        </div>
                    </div>
                    <div class="card-badge-right z-10">
                        <p class="card-badge-title">ID CARD</p>
                        <p class="card-badge-sub">Digital Pass</p>
                    </div>
                </div>

                <!-- Card Body -->
                <div class="front-body">
                    <!-- Photo with Subtle 1px Border & Soft Background -->
                    <div class="avatar-frame">
                        @if($user->avatar)
                            <img src="{{ asset($user->avatar) }}" alt="User">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        @endif
                    </div>

                    <!-- Personal Metadata -->
                    <div class="meta-container">
                        <p class="meta-name-label">Name</p>
                        <p class="meta-name-value">{{ strtoupper($user->name) }}</p>
                        
                        <div class="meta-grid">
                            <div class="min-w-0">
                                <p class="meta-field-label">User ID</p>
                                <p class="meta-field-value">#{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}</p>
                            </div>
                            <div class="min-w-0">
                                <p class="meta-field-label">Joined</p>
                                <p class="meta-field-value">{{ $user->created_at ? $user->created_at->format('M Y') : 'N/A' }}</p>
                            </div>
                            <div class="min-w-0">
                                <p class="meta-field-label">Phone</p>
                                <p class="meta-field-value">{{ $user->phone ?? 'N/A' }}</p>
                            </div>
                            <div class="min-w-0">
                                <p class="meta-field-label">KYC / ID</p>
                                <p class="meta-field-value">{{ $user->aadhaar_number ?? 'Verified' }}</p>
                            </div>
                        </div>

                        @if($user->address)
                        <div class="meta-address-row">
                            <p class="meta-field-label">Address</p>
                            <p class="meta-address-text">{{ $user->address }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Front Footer Bar with Barcode, Mini QR & Authorized Signatory -->
                <div class="front-footer">
                    <!-- Left: Ref Code, Barcode & Mini QR -->
                    <div class="flex flex-col items-start min-w-0">
                        <div class="ref-label">
                            <span>REF:</span>
                            <span class="ref-code">{{ strtoupper($user->referralRecord->referral_code ?? 'U'.$user->id) }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <div class="barcode-wrapper" title="Member Barcode">
                                <svg id="barcode"></svg>
                            </div>
                            <div class="mini-qr-wrapper" title="Customer QR Code">
                                <div id="idcard-qrcode"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Authorized Signature -->
                    <div class="signature-box">
                        <div class="signature-img-container">
                            @if(!empty($signaturePath) && file_exists(public_path($signaturePath)))
                                <img src="{{ asset($signaturePath) }}?v={{ time() }}" class="signature-img" alt="Signature">
                            @elseif(file_exists(public_path('images/signatures/default-signature.svg')))
                                <img src="{{ asset('images/signatures/default-signature.svg') }}" class="signature-img" alt="Signature">
                            @else
                                <span class="signature-fallback">Authorized</span>
                            @endif
                        </div>
                        <div class="signature-rule"></div>
                        <p class="signature-title">
                            {{ strtoupper($signatureTitle ?? 'AUTHORIZED SIGNATORY') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- BACK SIDE: Scan & Pay Wallet QR Code       -->
        <!-- ========================================== -->
        <div class="flex flex-col items-center">
            <div class="mb-2 flex items-center justify-between w-full max-w-[85.6mm] px-1 no-print">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Back Side</span>
                <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1">
                    <svg class="w-3 h-3 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Scan &amp; Pay Enabled
                </span>
            </div>

            <div id="id-card-back" class="id-card-render select-none">
                <!-- Top Header Bar (Cohesive Slate-900 Accent) -->
                <div class="card-header-bar">
                    <div class="card-header-accent"></div>
                    <div class="card-header-accent-green"></div>
                    
                    <div class="flex items-center gap-2 z-10 min-w-0">
                        <div class="header-logo-box">
                            <img src="{{ asset('images/logo/auth-logo.svg') }}" class="h-4 w-auto" alt="Logo" onerror="this.src='{{ asset('logo.png') }}'">
                        </div>
                        <div class="min-w-0">
                            <h3 class="card-brand-title">{{ $brandName }}</h3>
                            <p class="card-brand-sub-green">Instant Wallet Pass</p>
                        </div>
                    </div>
                    <div class="scan-pay-pill z-10">
                        <svg class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        <span>SCAN &amp; PAY</span>
                    </div>
                </div>

                <!-- Back Body: Payment QR Code & Payee Details -->
                <div class="back-body">
                    <!-- Left Column: Sized QR Code & Visible Caption -->
                    <div class="qr-frame">
                        <div class="qr-box">
                            <div id="idcard-payment-qrcode"></div>
                        </div>
                        <p class="qr-caption">
                            SCAN WITH PHONE CAMERA
                        </p>
                    </div>

                    <!-- Right Column: Payee Info & Clean ID Badges -->
                    <div class="back-details">
                        <p class="payee-label">Payee Name</p>
                        <p class="payee-name">{{ strtoupper($user->name) }}</p>

                        <!-- Clean ID Badges (CUST Pill & Member ID) -->
                        <div class="badges-group">
                            <span class="cust-badge">
                                {{ $customer['cust_id'] ?? 'CUST-' . (10000 + $user->id) }}
                            </span>
                            <span class="member-id-tag">
                                #{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>

                        <!-- Payment Helper Text Constrained in Right Column -->
                        <p class="payment-helper-text">
                            Scanning this QR code opens the direct payment page to transfer wallet balance.
                        </p>

                        <!-- Instant Settlement Indicator -->
                        <div class="settlement-badge">
                            <svg class="w-2.5 h-2.5 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Instant Settlement Enabled</span>
                        </div>
                    </div>
                </div>

                <!-- Back Footer Bar: Clean Placeholder & Legal Notice -->
                <div class="back-footer">
                    <p class="card-footer-left">
                        Non-transferable &bull; Official Pass of {{ $brandName }}
                    </p>
                    <p class="card-footer-right">
                        {{ $cleanHost }}
                    </p>
                </div>
            </div>
        </div>

    </div>

    <!-- Instruction Info Banner & Quick Links -->
    <div class="mt-4 text-center no-print">
        <div class="inline-flex flex-col sm:flex-row items-center gap-4 p-4 rounded-xl bg-blue-50 border border-blue-100 dark:bg-white/[0.03] dark:border-gray-800">
            <div class="text-left">
                <p class="text-sm text-blue-800 dark:text-blue-300 font-semibold flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-[#1555a8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>How the Payment QR Works</span>
                </p>
                <p class="text-xs text-blue-600 dark:text-blue-400 mt-1 max-w-xl">
                    When someone scans the <strong>Back Side QR Code</strong> with any smartphone camera or the Duaredokandar app, it opens the payment page with this account pre-selected so they can enter an amount and transfer money instantly.
                </p>
            </div>
            
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ $paymentUrl }}" target="_blank" class="px-3.5 py-2 rounded-lg bg-[#1555a8] hover:bg-[#12468d] text-white text-xs font-bold transition shadow-xs whitespace-nowrap flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    <span>Test Payment Page</span>
                </a>

                @if(Auth::check() && (Auth::user()->is_admin ?? (Auth::user()->role === 'admin' || Auth::user()->email === 'admin@example.com' || (method_exists(Auth::user(), 'isAdmin') && Auth::user()->isAdmin()))))
                <a href="{{ route('admin.settings') }}" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 text-xs font-semibold transition shadow-xs whitespace-nowrap flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>ID Card Settings</span>
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
/* ========================================================= */
/* CR80 EXACT CARD CONTAINER & METRICS                       */
/* ========================================================= */
.id-card-render {
    width: 85.6mm;
    height: 54mm;
    min-width: 85.6mm;
    min-height: 54mm;
    max-width: 85.6mm;
    max-height: 54mm;
    position: relative;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
    overflow: hidden !important;
    border: 1px solid #cbd5e1;
    font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    user-select: none;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
}

/* ========================================================= */
/* CARD HEADER                                              */
/* ========================================================= */
.card-header-bar {
    position: relative;
    height: 38px;
    background: #0f172a;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 12px;
    z-index: 10;
    overflow: hidden;
    flex-shrink: 0;
}
.card-header-accent {
    position: absolute;
    top: 0;
    right: 0;
    width: 85px;
    height: 100%;
    background: #2563eb;
    transform: skewX(-20deg) translateX(30px);
    opacity: 0.25;
}
.card-header-accent-2 {
    position: absolute;
    top: 0;
    right: 0;
    width: 45px;
    height: 100%;
    background: #2563eb;
    transform: skewX(-20deg) translateX(15px);
    opacity: 0.45;
}
.card-header-accent-green {
    position: absolute;
    top: 0;
    right: 0;
    width: 48px;
    height: 100%;
    background: #10b981;
    transform: skewX(-20deg) translateX(15px);
    opacity: 0.35;
}
.header-logo-box {
    background: #ffffff;
    padding: 2px 3px;
    border-radius: 4px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.card-brand-title {
    font-size: 9.5px;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #ffffff;
    line-height: 1.15;
    max-width: 175px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin: 0;
}
.card-brand-sub {
    font-size: 6.5px;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #93c5fd;
    line-height: 1;
    margin: 1px 0 0 0;
}
.card-brand-sub-green {
    font-size: 6.5px;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #6ee7b7;
    line-height: 1;
    margin: 1px 0 0 0;
}
.card-badge-right {
    text-align: right;
    flex-shrink: 0;
}
.card-badge-title {
    font-size: 8.5px;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #ffffff;
    line-height: 1;
    margin: 0;
}
.card-badge-sub {
    font-size: 5.5px;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #94a3b8;
    line-height: 1;
    margin: 2px 0 0 0;
}
.scan-pay-pill {
    display: flex;
    align-items: center;
    gap: 3px;
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.25);
    padding: 2px 7px;
    border-radius: 9999px;
    font-size: 6.5px;
    font-weight: 800;
    letter-spacing: 0.1em;
    color: #ffffff;
    text-transform: uppercase;
    flex-shrink: 0;
}

/* ========================================================= */
/* FRONT CARD BODY & METADATA                                */
/* ========================================================= */
.front-body {
    flex: 1;
    padding: 7px 12px 3px 12px;
    display: flex;
    gap: 12px;
    align-items: flex-start;
    overflow: hidden;
    box-sizing: border-box;
}

/* Subtle 1px Avatar Frame */
.avatar-frame {
    width: 66px;
    height: 80px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    padding: 2px;
    box-sizing: border-box;
}
.avatar-frame img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 6px;
}

/* Personal Metadata */
.meta-container {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}
.meta-name-label {
    font-size: 6.5px;
    font-weight: 700;
    color: #94a3b8;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    line-height: 1;
    margin: 0;
}
.meta-name-value {
    font-size: 11.5px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.2;
    margin: 1px 0 4px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.meta-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    column-gap: 8px;
    row-gap: 3.5px;
}
.meta-field-label {
    font-size: 6px;
    font-weight: 700;
    color: #94a3b8;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    line-height: 1;
    margin: 0 0 1px 0;
}
.meta-field-value {
    font-size: 8.5px;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.15;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin: 0;
}
.meta-address-row {
    margin-top: 3px;
    padding-top: 2.5px;
    border-top: 1px solid #f1f5f9;
}
.meta-address-text {
    font-size: 6.5px;
    color: #64748b;
    line-height: 1.15;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin: 0;
}

/* ========================================================= */
/* FRONT FOOTER BAR                                          */
/* ========================================================= */
.front-footer {
    height: 38px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 12px;
    box-sizing: border-box;
    flex-shrink: 0;
}
.ref-label {
    font-size: 7.5px;
    font-weight: 800;
    color: #475569;
    letter-spacing: 0.08em;
    line-height: 1;
    margin: 0 0 2px 0;
    display: flex;
    align-items: center;
    gap: 3px;
}
.ref-code {
    color: #2563eb;
    font-family: monospace;
    font-weight: 800;
}
.barcode-wrapper {
    background: #ffffff;
    padding: 1px 3px;
    border-radius: 3px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    display: flex;
    align-items: center;
    justify-content: center;
    height: 18px;
}
#barcode {
    height: 14px;
    max-width: 65px;
    display: block;
}
.mini-qr-wrapper {
    background: #ffffff;
    padding: 1px;
    border-radius: 3px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    display: flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
}
#idcard-qrcode {
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Authorized Signature Section */
.signature-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    flex-shrink: 0;
    padding-left: 8px;
}
.signature-img-container {
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.signature-img {
    height: 18px;
    max-width: 85px;
    object-fit: contain;
}
.signature-fallback {
    font-family: Georgia, serif;
    font-style: italic;
    font-size: 8.5px;
    font-weight: bold;
    color: #1e3a8a;
    letter-spacing: 0.05em;
}
.signature-rule {
    width: 80px;
    height: 1px;
    background: #94a3b8;
    margin: 2px 0 1.5px 0;
}
.signature-title {
    font-size: 7.5px;
    font-weight: 700;
    color: #64748b;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    line-height: 1;
    white-space: nowrap;
    margin: 0;
}

/* ========================================================= */
/* BACK CARD BODY & QR                                       */
/* ========================================================= */
.back-body {
    flex: 1;
    padding: 8px 12px 6px 12px;
    display: flex;
    align-items: center;
    gap: 12px;
    overflow: hidden;
    box-sizing: border-box;
}
.qr-frame {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex-shrink: 0;
}
.qr-box {
    width: 74px;
    height: 74px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3px;
    box-sizing: border-box;
}
#idcard-payment-qrcode {
    width: 68px;
    height: 68px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.qr-caption {
    font-size: 6.5px;
    font-weight: 800;
    color: #64748b;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin: 4px 0 0 0;
    line-height: 1;
    text-align: center;
}

/* Right Details Column */
.back-details {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.payee-label {
    font-size: 7px;
    font-weight: 700;
    color: #94a3b8;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    line-height: 1;
    margin: 0;
}
.payee-name {
    font-size: 12.5px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.2;
    margin: 1px 0 0 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.badges-group {
    display: flex;
    align-items: center;
    gap: 6px;
    margin: 3px 0 4px 0;
}
.cust-badge {
    font-size: 8.5px;
    font-family: monospace;
    font-weight: 700;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    padding: 1px 6px;
    border-radius: 9999px;
    line-height: 1.3;
}
.member-id-tag {
    font-size: 8.5px;
    font-family: monospace;
    font-weight: 600;
    color: #64748b;
    line-height: 1.3;
}
.payment-helper-text {
    font-size: 10px;
    line-height: 1.35;
    color: #64748b;
    margin: 0;
    word-break: normal;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.settlement-badge {
    display: flex;
    align-items: center;
    gap: 3.5px;
    font-size: 7px;
    font-weight: 700;
    color: #059669;
    margin-top: 3.5px;
    line-height: 1;
}

/* ========================================================= */
/* BACK FOOTER BAR                                           */
/* ========================================================= */
.back-footer {
    height: 24px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 12px;
    box-sizing: border-box;
    flex-shrink: 0;
}
.card-footer-left {
    font-size: 7px;
    font-weight: 500;
    color: #94a3b8;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 210px;
    margin: 0;
}
.card-footer-right {
    font-size: 7.5px;
    font-weight: 700;
    color: #1555a8;
    white-space: nowrap;
    flex-shrink: 0;
    margin: 0;
}

/* ========================================================= */
/* PRINT SPECIFICATIONS                                      */
/* ========================================================= */
@media print {
    body * {
        visibility: hidden !important;
    }

    #id-cards-print-area, #id-cards-print-area * {
        visibility: visible !important;
    }

    #id-cards-print-area {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: wrap !important;
        gap: 6mm !important;
        justify-content: flex-start !important;
    }

    .id-card-render {
        position: relative !important;
        width: 85.6mm !important;
        height: 54mm !important;
        min-width: 85.6mm !important;
        min-height: 54mm !important;
        box-shadow: none !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 3.18mm !important;
        margin: 0 !important;
        overflow: hidden !important;
        page-break-inside: avoid !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .card-header-bar {
        background: #0f172a !important;
        -webkit-print-color-adjust: exact !important;
    }

    .card-header-accent, .card-header-accent-2 {
        background: #2563eb !important;
        -webkit-print-color-adjust: exact !important;
    }

    .card-header-accent-green {
        background: #10b981 !important;
        -webkit-print-color-adjust: exact !important;
    }

    .front-footer, .back-footer {
        background: #f8fafc !important;
        -webkit-print-color-adjust: exact !important;
    }

    .no-print {
        display: none !important;
    }

    @page {
        size: auto;
        margin: 8mm;
    }
}
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="{{ asset('vendor/qrcode/qrcode.min.js') }}"></script>
<script>
    // Fallback if local vendor script fails
    if (typeof QRCode === 'undefined') {
        const s = document.createElement('script');
        s.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js';
        document.head.appendChild(s);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // 1. Render Barcode on Front Side
        try {
            if (typeof JsBarcode !== 'undefined' && document.getElementById('barcode')) {
                JsBarcode("#barcode", "{{ $user->referralRecord->referral_code ?? 'U'.$user->id }}", {
                    format: "CODE128",
                    width: 1.2,
                    height: 14,
                    displayValue: false,
                    margin: 0,
                    background: "transparent"
                });
            }
        } catch (e) {
            console.error('JsBarcode render error:', e);
        }

        // Helper to safely render QR codes once QRCode library is ready
        function renderCardQRCodes() {
            if (typeof QRCode === 'undefined') {
                setTimeout(renderCardQRCodes, 80);
                return;
            }

            // 2. Render Front Mini QR Code
            const frontQr = document.getElementById('idcard-qrcode');
            if (frontQr && frontQr.children.length === 0) {
                new QRCode(frontQr, {
                    text: "{{ $qrPayload ?? 'CUST:' . $user->id }}",
                    width: 16,
                    height: 16,
                    colorDark: '#0f172a',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M
                });
            }

            // 3. Render Back Payment QR Code (Opens Payment Page when scanned)
            const backQr = document.getElementById('idcard-payment-qrcode');
            if (backQr && backQr.children.length === 0) {
                new QRCode(backQr, {
                    text: "{{ $paymentUrl }}",
                    width: 68,
                    height: 68,
                    colorDark: '#0f172a',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M
                });
            }
        }

        renderCardQRCodes();
    });
</script>
@endpush
@endsection
