<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk ID Cards Print - {{ $brandName }} ({{ $totalCards }} Cards)</title>
    
    <!-- Google Font Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <style>
        /* ========================================================= */
        /* RESET & BASE SCREEN STYLING                               */
        /* ========================================================= */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            padding-bottom: 60px;
        }

        /* ========================================================= */
        /* TOP NON-PRINT TOOLBAR                                     */
        /* ========================================================= */
        .print-toolbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: #0f172a;
            color: #ffffff;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            font-size: 13px;
        }

        .toolbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .toolbar-title {
            font-weight: 800;
            font-size: 15px;
            letter-spacing: -0.01em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .badge-count {
            background: #2563eb;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 9999px;
        }

        .toolbar-controls {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .control-group {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.1);
            padding: 4px 10px;
            border-radius: 8px;
        }

        .control-group label {
            font-size: 11px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .control-select {
            background: #1e293b;
            color: #ffffff;
            border: 1px solid #334155;
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            outline: none;
        }

        .btn-print {
            background: #2563eb;
            color: #ffffff;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 150ms ease;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.4);
        }

        .btn-print:hover {
            background: #1d4ed8;
        }

        .btn-back {
            background: transparent;
            color: #94a3b8;
            border: 1px solid #334155;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 150ms ease;
        }

        .btn-back:hover {
            color: #ffffff;
            border-color: #64748b;
        }

        /* ========================================================= */
        /* PRINT CONTAINER & SHEET MODES                             */
        /* ========================================================= */
        .print-container {
            max-width: 1200px;
            margin: 28px auto;
            padding: 0 16px;
        }

        /* A4 SHEET GRID MODE */
        .sheet-mode-a4 {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8mm;
        }

        /* CR80 SINGLE CARD STREAM MODE */
        .sheet-mode-cr80 {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12mm;
        }

        .user-card-unit {
            display: flex;
            flex-wrap: wrap;
            gap: 6mm;
            align-items: center;
            justify-content: center;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .card-wrapper {
            position: relative;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Cutting guide line around card */
        .has-cutting-guides .card-wrapper::after {
            content: '';
            position: absolute;
            top: -3mm;
            left: -3mm;
            right: -3mm;
            bottom: -3mm;
            border: 0.5px dashed #cbd5e1;
            pointer-events: none;
            border-radius: 4mm;
        }

        /* ========================================================= */
        /* CR80 EXACT CARD CONTAINER (85.6mm x 54mm)                 */
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
            border-radius: 3.18mm;
            box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.08);
            overflow: hidden !important;
            border: 1px solid #cbd5e1;
            user-select: none;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            page-break-inside: avoid;
            break-inside: avoid;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ========================================================= */
        /* CARD HEADER                                              */
        /* ========================================================= */
        .card-header-bar {
            position: relative;
            height: 38px;
            background: #0f172a !important;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 10px;
            z-index: 10;
            overflow: hidden;
            flex-shrink: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .card-header-accent {
            position: absolute;
            top: 0;
            right: 0;
            width: 85px;
            height: 100%;
            background: #2563eb !important;
            transform: skewX(-20deg) translateX(30px);
            opacity: 0.25;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .card-header-accent-2 {
            position: absolute;
            top: 0;
            right: 0;
            width: 45px;
            height: 100%;
            background: #2563eb !important;
            transform: skewX(-20deg) translateX(15px);
            opacity: 0.45;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .card-header-accent-green {
            position: absolute;
            top: 0;
            right: 0;
            width: 48px;
            height: 100%;
            background: #10b981 !important;
            transform: skewX(-20deg) translateX(15px);
            opacity: 0.35;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .header-logo-box {
            background: #ffffff;
            padding: 2px 4px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .card-brand-title {
            font-size: 9.5px;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #ffffff;
            line-height: 1.15;
            max-width: 170px;
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
            background: rgba(255, 255, 255, 0.15) !important;
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
            padding: 6px 10px 2px 10px;
            display: flex;
            gap: 10px;
            align-items: flex-start;
            overflow: hidden;
            box-sizing: border-box;
        }

        .avatar-frame {
            width: 64px;
            height: 78px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
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
            border-radius: 5px;
        }

        .avatar-fallback {
            color: #94a3b8;
            font-size: 28px;
        }

        .meta-container {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .meta-name-label {
            font-size: 6px;
            font-weight: 700;
            color: #94a3b8;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            line-height: 1;
            margin: 0;
        }

        .meta-name-value {
            font-size: 11px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.15;
            margin: 1px 0 3px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2.5px 6px;
        }

        .meta-field-label {
            font-size: 5.5px;
            font-weight: 700;
            color: #94a3b8;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            line-height: 1;
            margin: 0;
        }

        .meta-field-value {
            font-size: 8px;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.1;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .meta-address-row {
            margin-top: 3px;
            padding-top: 2px;
            border-top: 0.5px solid #f1f5f9;
        }

        .meta-address-text {
            font-size: 6.5px;
            color: #475569;
            line-height: 1.15;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* FRONT FOOTER */
        .front-footer {
            height: 30px;
            background: #f8fafc !important;
            border-top: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 2px 10px;
            flex-shrink: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .ref-label {
            display: flex;
            align-items: center;
            gap: 3px;
            font-size: 6.5px;
            font-weight: 700;
            color: #64748b;
            line-height: 1;
        }

        .ref-code {
            font-family: monospace;
            font-weight: 800;
            color: #0f172a;
        }

        .barcode-wrapper {
            height: 14px;
            display: flex;
            align-items: center;
        }

        .barcode-wrapper svg {
            height: 14px;
            max-width: 90px;
        }

        .mini-qr-wrapper {
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            border: 0.5px solid #cbd5e1;
            border-radius: 2px;
            padding: 1px;
        }

        .mini-qr-wrapper img {
            width: 100% !important;
            height: 100% !important;
        }

        .signature-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 72px;
            flex-shrink: 0;
        }

        .signature-img-container {
            height: 13px;
            width: 100%;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }

        .signature-img {
            max-height: 13px;
            max-width: 100%;
            object-fit: contain;
        }

        .signature-fallback {
            font-size: 7.5px;
            font-style: italic;
            color: #64748b;
            font-weight: 600;
        }

        .signature-rule {
            width: 100%;
            height: 0.5px;
            background: #94a3b8;
            margin: 1px 0 1px 0;
        }

        .signature-title {
            font-size: 5px;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin: 0;
            white-space: nowrap;
        }

        /* ========================================================= */
        /* BACK CARD BODY & QR PAYMENT PASS                          */
        /* ========================================================= */
        .back-body {
            flex: 1;
            padding: 6px 12px;
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
            width: 70px;
            height: 70px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .qr-box img {
            width: 100% !important;
            height: 100% !important;
        }

        .qr-caption {
            font-size: 5px;
            font-weight: 800;
            color: #64748b;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .back-details {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .payee-label {
            font-size: 5.5px;
            font-weight: 700;
            color: #94a3b8;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            line-height: 1;
            margin: 0;
        }

        .payee-name {
            font-size: 11px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
            margin: 1px 0 4px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .badges-group {
            display: flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 4px;
        }

        .cust-badge {
            background: #e0f2fe;
            color: #0369a1;
            font-size: 7px;
            font-weight: 800;
            padding: 1.5px 5px;
            border-radius: 4px;
            font-family: monospace;
        }

        .member-id-tag {
            background: #f1f5f9;
            color: #475569;
            font-size: 7px;
            font-weight: 700;
            padding: 1.5px 5px;
            border-radius: 4px;
            font-family: monospace;
        }

        .payment-helper-text {
            font-size: 6px;
            color: #64748b;
            line-height: 1.2;
            margin: 0 0 4px 0;
        }

        .settlement-badge {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            background: #ecfdf5;
            color: #059669;
            font-size: 6px;
            font-weight: 700;
            padding: 1.5px 5px;
            border-radius: 9999px;
            width: fit-content;
        }

        .back-footer {
            height: 20px;
            background: #f8fafc !important;
            border-top: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 10px;
            font-size: 6px;
            color: #64748b;
            flex-shrink: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .card-footer-left {
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 180px;
        }

        .card-footer-right {
            font-weight: 700;
            color: #2563eb;
            font-family: monospace;
        }

        /* ========================================================= */
        /* PRINT SPECIFICATIONS & MEDIA QUERIES                      */
        /* ========================================================= */
        @media print {
            .no-print, .print-toolbar {
                display: none !important;
            }

            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .print-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .id-card-render {
                box-shadow: none !important;
                border: 0.5px solid #cbd5e1 !important;
                margin: 0 !important;
            }

            /* In CR80 Printer Mode: each card is an individual page */
            body.print-mode-cr80 .card-wrapper {
                page-break-after: always !important;
                break-after: page !important;
                margin: 0 !important;
            }

            /* In A4 Sheet Mode: grid layout */
            body.print-mode-a4 .sheet-mode-a4 {
                gap: 6mm !important;
            }

            @page {
                size: auto;
                margin: 6mm;
            }
        }
    </style>
</head>
<body class="print-mode-{{ $layout }} {{ $cuttingGuides ? 'has-cutting-guides' : '' }}">

    <!-- Non-Print Interactive Top Toolbar -->
    <header class="print-toolbar no-print">
        <div class="toolbar-left">
            <a href="{{ route('admin.id-cards.index') }}" class="btn-back">
                &larr; Back to Selection
            </a>
            <div class="toolbar-title">
                <span>Bulk ID Cards Print Preview</span>
                <span class="badge-count">{{ $totalCards }} Users</span>
            </div>
        </div>

        <div class="toolbar-controls">
            <!-- Dynamic Layout Switcher -->
            <div class="control-group">
                <label>Paper:</label>
                <select id="layout-switch" class="control-select" onchange="switchLayout(this.value)">
                    <option value="a4" {{ $layout === 'a4' ? 'selected' : '' }}>A4 Sheet (Multi-Card)</option>
                    <option value="cr80" {{ $layout === 'cr80' ? 'selected' : '' }}>CR80 PVC Card Printer</option>
                </select>
            </div>

            <!-- Dynamic Side Switcher -->
            <div class="control-group">
                <label>Sides:</label>
                <select id="side-switch" class="control-select" onchange="switchSides(this.value)">
                    <option value="both" {{ $side === 'both' ? 'selected' : '' }}>Both Sides</option>
                    <option value="front" {{ $side === 'front' ? 'selected' : '' }}>Front Only</option>
                    <option value="back" {{ $side === 'back' ? 'selected' : '' }}>Back Only</option>
                </select>
            </div>

            <!-- Toggle Cutting Guides -->
            <div class="control-group">
                <label style="cursor: pointer;">
                    <input type="checkbox" id="guide-toggle" {{ $cuttingGuides ? 'checked' : '' }} onchange="toggleGuides(this.checked)" style="vertical-align: middle;">
                    <span>Cut Lines</span>
                </label>
            </div>

            <!-- Trigger Print Button -->
            <button type="button" onclick="window.print()" class="btn-print">
                <i class="fas fa-print"></i>
                <span>Print Cards (Ctrl+P)</span>
            </button>
        </div>
    </header>

    <!-- Main Cards Print Canvas Container -->
    <main class="print-container">
        <div id="print-sheet" class="{{ $layout === 'cr80' ? 'sheet-mode-cr80' : 'sheet-mode-a4' }}">
            @foreach($cardItems as $item)
                @php
                    $u = $item['user'];
                    $cust = $item['customer'];
                @endphp

                <div class="user-card-unit" data-user-id="{{ $u->id }}">

                    <!-- ========================================== -->
                    <!-- FRONT SIDE                                 -->
                    <!-- ========================================== -->
                    @if($side === 'both' || $side === 'front')
                    <div class="card-wrapper card-front-wrapper">
                        <div class="id-card-render">
                            <!-- Card Header Bar -->
                            <div class="card-header-bar">
                                <div class="card-header-accent"></div>
                                <div class="card-header-accent-2"></div>

                                <div style="display: flex; align-items: center; gap: 8px; z-index: 10; min-width: 0;">
                                    <div class="header-logo-box">
                                        <img src="{{ asset('images/logo/auth-logo.svg') }}" style="height: 18px; width: auto;" alt="Logo" onerror="this.src='{{ asset('logo.png') }}'">
                                    </div>
                                    <div style="min-width: 0;">
                                        <h3 class="card-brand-title">{{ $brandName }}</h3>
                                        <p class="card-brand-sub">Official Member Pass</p>
                                    </div>
                                </div>

                                <div class="card-badge-right" style="z-index: 10;">
                                    <p class="card-badge-title">ID CARD</p>
                                    <p class="card-badge-sub">Digital Pass</p>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="front-body">
                                <!-- Photo Frame -->
                                <div class="avatar-frame">
                                    @if($u->avatar)
                                        <img src="{{ asset($u->avatar) }}" alt="{{ $u->name }}">
                                    @else
                                        <i class="fas fa-user avatar-fallback"></i>
                                    @endif
                                </div>

                                <!-- Metadata -->
                                <div class="meta-container">
                                    <p class="meta-name-label">Member Name</p>
                                    <p class="meta-name-value">{{ strtoupper($u->name) }}</p>

                                    <div class="meta-grid">
                                        <div>
                                            <p class="meta-field-label">User ID</p>
                                            <p class="meta-field-value">#{{ str_pad($u->id, 5, '0', STR_PAD_LEFT) }}</p>
                                        </div>
                                        <div>
                                            <p class="meta-field-label">Joined</p>
                                            <p class="meta-field-value">{{ $u->created_at ? $u->created_at->format('M Y') : 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="meta-field-label">Phone</p>
                                            <p class="meta-field-value">{{ $u->phone ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="meta-field-label">KYC / Aadhaar</p>
                                            <p class="meta-field-value">{{ $u->aadhaar_number ?? ($u->kyc_status === 'approved' ? 'Verified' : 'Pending') }}</p>
                                        </div>
                                    </div>

                                    @if($u->address)
                                    <div class="meta-address-row">
                                        <p class="meta-field-label">Address</p>
                                        <p class="meta-address-text">{{ $u->address }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Front Footer with Barcode & Mini QR -->
                            <div class="front-footer">
                                <div style="display: flex; flex-direction: column; align-items: flex-start; min-width: 0;">
                                    <div class="ref-label">
                                        <span>REF:</span>
                                        <span class="ref-code">{{ strtoupper($item['barcodeValue']) }}</span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 5px; margin-top: 1px;">
                                        <div class="barcode-wrapper">
                                            <svg id="barcode-{{ $u->id }}"></svg>
                                        </div>
                                        <div class="mini-qr-wrapper">
                                            <div id="front-qr-{{ $u->id }}"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Signature Box -->
                                <div class="signature-box">
                                    <div class="signature-img-container">
                                        @if(!empty($signaturePath) && file_exists(public_path($signaturePath)))
                                            <img src="{{ asset($signaturePath) }}" class="signature-img" alt="Signature">
                                        @elseif(file_exists(public_path('images/signatures/default-signature.svg')))
                                            <img src="{{ asset('images/signatures/default-signature.svg') }}" class="signature-img" alt="Signature">
                                        @else
                                            <span class="signature-fallback">Authorized</span>
                                        @endif
                                    </div>
                                    <div class="signature-rule"></div>
                                    <p class="signature-title">{{ strtoupper($signatureTitle) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- ========================================== -->
                    <!-- BACK SIDE                                  -->
                    <!-- ========================================== -->
                    @if($side === 'both' || $side === 'back')
                    <div class="card-wrapper card-back-wrapper">
                        <div class="id-card-render">
                            <!-- Card Header Bar -->
                            <div class="card-header-bar">
                                <div class="card-header-accent"></div>
                                <div class="card-header-accent-green"></div>

                                <div style="display: flex; align-items: center; gap: 8px; z-index: 10; min-width: 0;">
                                    <div class="header-logo-box">
                                        <img src="{{ asset('images/logo/auth-logo.svg') }}" style="height: 18px; width: auto;" alt="Logo" onerror="this.src='{{ asset('logo.png') }}'">
                                    </div>
                                    <div style="min-width: 0;">
                                        <h3 class="card-brand-title">{{ $brandName }}</h3>
                                        <p class="card-brand-sub-green">Instant Wallet Pass</p>
                                    </div>
                                </div>

                                <div class="scan-pay-pill" style="z-index: 10;">
                                    <i class="fas fa-qrcode" style="font-size: 7px;"></i>
                                    <span>SCAN &amp; PAY</span>
                                </div>
                            </div>

                            <!-- Back Body -->
                            <div class="back-body">
                                <div class="qr-frame">
                                    <div class="qr-box">
                                        <div id="back-qr-{{ $u->id }}"></div>
                                    </div>
                                    <p class="qr-caption">SCAN TO PAY</p>
                                </div>

                                <div class="back-details">
                                    <p class="payee-label">Payee Account</p>
                                    <p class="payee-name">{{ strtoupper($u->name) }}</p>

                                    <div class="badges-group">
                                        <span class="cust-badge">{{ $cust['cust_id'] ?? 'CUST-'.(10000 + $u->id) }}</span>
                                        <span class="member-id-tag">#{{ str_pad($u->id, 5, '0', STR_PAD_LEFT) }}</span>
                                    </div>

                                    <p class="payment-helper-text">
                                        Scan with smartphone camera to open instant wallet transfer.
                                    </p>

                                    <div class="settlement-badge">
                                        <i class="fas fa-check-circle" style="font-size: 6px;"></i>
                                        <span>Instant Settlement</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Back Footer -->
                            <div class="back-footer">
                                <p class="card-footer-left">Non-transferable &bull; Official Pass of {{ $brandName }}</p>
                                <p class="card-footer-right">{{ $item['cleanHost'] }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            @endforeach
        </div>
    </main>

    <!-- Barcode & QR Code Rendering Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="{{ asset('vendor/qrcode/qrcode.min.js') }}"></script>
    <script>
        // Fallback if local vendor QRCode script is missing
        if (typeof QRCode === 'undefined') {
            const s = document.createElement('script');
            s.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js';
            s.onload = initCardGraphics;
            document.head.appendChild(s);
        } else {
            document.addEventListener('DOMContentLoaded', initCardGraphics);
        }

        const cardsData = @json($cardItems);

        function initCardGraphics() {
            if (typeof QRCode === 'undefined' || typeof JsBarcode === 'undefined') {
                setTimeout(initCardGraphics, 80);
                return;
            }

            cardsData.forEach(item => {
                const uid = item.user.id;

                // 1. Render Barcode on Front Side
                const barcodeEl = document.getElementById('barcode-' + uid);
                if (barcodeEl) {
                    try {
                        JsBarcode(barcodeEl, String(item.barcodeValue || ('U' + uid)), {
                            format: "CODE128",
                            width: 1.1,
                            height: 14,
                            displayValue: false,
                            margin: 0,
                            background: "transparent"
                        });
                    } catch (e) {
                        console.error('Barcode error for user ' + uid, e);
                    }
                }

                // 2. Render Front Mini QR Code
                const frontQrEl = document.getElementById('front-qr-' + uid);
                if (frontQrEl && frontQrEl.children.length === 0) {
                    try {
                        new QRCode(frontQrEl, {
                            text: String(item.qrPayload || ('CUST:' + uid)),
                            width: 16,
                            height: 16,
                            colorDark: '#0f172a',
                            colorLight: '#ffffff',
                            correctLevel: QRCode.CorrectLevel.M
                        });
                    } catch (e) {
                        console.error('Front QR error for user ' + uid, e);
                    }
                }

                // 3. Render Back Payment QR Code
                const backQrEl = document.getElementById('back-qr-' + uid);
                if (backQrEl && backQrEl.children.length === 0) {
                    try {
                        new QRCode(backQrEl, {
                            text: String(item.paymentUrl),
                            width: 68,
                            height: 68,
                            colorDark: '#0f172a',
                            colorLight: '#ffffff',
                            correctLevel: QRCode.CorrectLevel.M
                        });
                    } catch (e) {
                        console.error('Back QR error for user ' + uid, e);
                    }
                }
            });

            @if($autoPrint)
                // Auto trigger print after graphics render
                setTimeout(() => {
                    window.print();
                }, 600);
            @endif
        }

        // Live Controls Interactive Functions
        function switchLayout(mode) {
            document.body.className = document.body.className.replace(/print-mode-\w+/, 'print-mode-' + mode);
            const sheet = document.getElementById('print-sheet');
            sheet.className = mode === 'cr80' ? 'sheet-mode-cr80' : 'sheet-mode-a4';
        }

        function switchSides(side) {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('side', side);
            window.location.href = currentUrl.toString();
        }

        function toggleGuides(enabled) {
            if (enabled) {
                document.body.classList.add('has-cutting-guides');
            } else {
                document.body.classList.remove('has-cutting-guides');
            }
        }
    </script>
</body>
</html>
