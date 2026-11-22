<!-- ============================================================== -->
<!-- HIDDEN RIGHT-EDGE LANGUAGE TRAY (PULL-TAB & SLIDE-OUT PANEL)   -->
<!-- ============================================================== -->

<style id="custom-language-tray-styles">
    /* Tray Container: Fixed to the right edge, vertically centered, hidden off-screen by default */
    #sideLanguageTray {
        position: fixed !important;
        right: 0 !important;
        top: 50% !important;
        transform: translateY(-50%) translateX(100%) !important;
        transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1) !important;
        z-index: 2147483645 !important;
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        pointer-events: auto !important;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
    }

    /* When Opened: slides into view */
    #sideLanguageTray.tray-open {
        transform: translateY(-50%) translateX(0) !important;
    }

    /* The Tab Handle: Attached to the left of the tray, sleek micro-pill peeking at the screen edge */
    #trayHandleTab {
        position: absolute !important;
        right: 100% !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        cursor: pointer !important;
        user-select: none !important;
        background: rgba(255, 255, 255, 0.88) !important;
        backdrop-filter: blur(6px) !important;
        -webkit-backdrop-filter: blur(6px) !important;
        border: 1px solid rgba(203, 213, 225, 0.85) !important;
        border-right: none !important;
        border-radius: 9px 0 0 9px !important;
        box-shadow: -2px 2px 7px rgba(0, 0, 0, 0.08) !important;
        padding: 5px 2px !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 2px !important;
        transition: transform 0.2s ease, opacity 0.2s ease, background-color 0.2s ease !important;
        width: 24px !important;
        box-sizing: border-box !important;
        opacity: 0.85 !important;
    }

    #trayHandleTab:hover {
        transform: translateY(-50%) translateX(-4px) !important;
        opacity: 1 !important;
        background: #ffffff !important;
    }

    #sideLanguageTray.tray-open #trayHandleTab:hover {
        transform: translateY(-50%) !important;
        opacity: 1 !important;
    }

    /* Dark Mode styles for Tab Handle */
    .dark #trayHandleTab {
        background: rgba(30, 41, 59, 0.88) !important;
        border-color: rgba(71, 85, 105, 0.85) !important;
        box-shadow: -2px 2px 8px rgba(0, 0, 0, 0.3) !important;
    }
    .dark #trayHandleTab:hover {
        background: #1e293b !important;
    }

    /* Micro Tab Icon */
    .tray-tab-icon {
        width: 17px !important;
        height: 17px !important;
        border-radius: 4px !important;
        background: #eff6ff !important;
        color: #1555a8 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .tray-tab-icon svg {
        width: 11px !important;
        height: 11px !important;
    }
    .dark .tray-tab-icon {
        background: rgba(30, 58, 138, 0.6) !important;
        color: #60a5fa !important;
    }

    /* Compact 2-letter Code Indicator (EN / BN / HI) */
    .tray-tab-label {
        font-size: 7.5px !important;
        font-weight: 800 !important;
        letter-spacing: 0.02em !important;
        text-transform: uppercase !important;
        color: #475569 !important;
        line-height: 1 !important;
        margin: 1px 0 0 0 !important;
        text-align: center !important;
    }
    .dark .tray-tab-label {
        color: #94a3b8 !important;
    }

    /* Tray Body: Card containing language options */
    #trayBody {
        width: 275px !important;
        max-width: 85vw !important;
        background: #ffffff !important;
        border: 1.5px solid #cbd5e1 !important;
        border-right: none !important;
        border-radius: 14px 0 0 14px !important;
        box-shadow: -6px 4px 24px rgba(0, 0, 0, 0.2) !important;
        padding: 0 !important;
        overflow: hidden !important;
        display: flex !important;
        flex-direction: column !important;
        box-sizing: border-box !important;
    }
    .dark #trayBody {
        background: #0f172a !important;
        border-color: #334155 !important;
        box-shadow: -8px 4px 30px rgba(0, 0, 0, 0.6) !important;
    }

    /* Header */
    .tray-header-bar {
        padding: 12px 14px !important;
        background: #f8fafc !important;
        border-bottom: 1px solid #e2e8f0 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
    }
    .dark .tray-header-bar {
        background: #1e293b !important;
        border-color: #334155 !important;
    }

    .tray-title-group {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
    }
    .tray-title-text {
        font-size: 13px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        line-height: 1.2 !important;
    }
    .dark .tray-title-text {
        color: #f8fafc !important;
    }
    .tray-subtitle-text {
        font-size: 10px !important;
        color: #64748b !important;
        line-height: 1.2 !important;
    }
    .dark .tray-subtitle-text {
        color: #94a3b8 !important;
    }

    .tray-close-btn {
        background: transparent !important;
        border: none !important;
        width: 26px !important;
        height: 26px !important;
        border-radius: 6px !important;
        cursor: pointer !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: #64748b !important;
        font-size: 16px !important;
        transition: background 0.15s, color 0.15s !important;
    }
    .tray-close-btn:hover {
        background: #e2e8f0 !important;
        color: #0f172a !important;
    }
    .dark .tray-close-btn:hover {
        background: #334155 !important;
        color: #f8fafc !important;
    }

    /* Content Area */
    .tray-content-area {
        padding: 12px 14px !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 8px !important;
        max-height: 70vh !important;
        overflow-y: auto !important;
        box-sizing: border-box !important;
    }

    .tray-section-title {
        font-size: 10px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        color: #94a3b8 !important;
        margin-bottom: 2px !important;
    }

    /* Language Option Button */
    .tray-lang-btn {
        width: 100% !important;
        padding: 9px 10px !important;
        border-radius: 10px !important;
        border: 1.5px solid #e2e8f0 !important;
        background: #ffffff !important;
        cursor: pointer !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        text-align: left !important;
        transition: all 0.15s ease !important;
        box-sizing: border-box !important;
    }
    .tray-lang-btn:hover {
        border-color: #1555a8 !important;
        background: #f0f7ff !important;
        transform: translateY(-1px) !important;
    }
    .dark .tray-lang-btn {
        background: #1e293b !important;
        border-color: #334155 !important;
    }
    .dark .tray-lang-btn:hover {
        border-color: #3b82f6 !important;
        background: #172554 !important;
    }

    /* Active Language Highlight */
    .tray-lang-btn.active-lang {
        border-color: #1555a8 !important;
        background: #eff6ff !important;
        box-shadow: 0 1px 3px rgba(21, 85, 168, 0.15) !important;
    }
    .dark .tray-lang-btn.active-lang {
        border-color: #60a5fa !important;
        background: #1e3a8a !important;
    }

    .tray-lang-btn-left {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
    }
    .tray-lang-code-pill {
        width: 28px !important;
        height: 28px !important;
        border-radius: 7px !important;
        background: #f1f5f9 !important;
        color: #1e293b !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        border: 1px solid #cbd5e1 !important;
    }
    .dark .tray-lang-code-pill {
        background: #334155 !important;
        color: #f1f5f9 !important;
        border-color: #475569 !important;
    }
    .tray-lang-name {
        font-size: 12px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        line-height: 1.2 !important;
    }
    .dark .tray-lang-name {
        color: #f8fafc !important;
    }
    .tray-lang-sub {
        font-size: 10px !important;
        color: #64748b !important;
        line-height: 1.2 !important;
    }
    .dark .tray-lang-sub {
        color: #94a3b8 !important;
    }

    .tray-lang-check {
        width: 18px !important;
        height: 18px !important;
        border-radius: 50% !important;
        background: #1555a8 !important;
        color: #ffffff !important;
        display: none !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 11px !important;
        font-weight: 800 !important;
    }
    .dark .tray-lang-check {
        background: #3b82f6 !important;
    }
    .tray-lang-btn.active-lang .tray-lang-check {
        display: flex !important;
    }

    /* Google Native Box */
    .tray-google-box {
        margin-top: 4px !important;
        padding: 8px !important;
        border-radius: 10px !important;
        background: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
    }
    .dark .tray-google-box {
        background: #1e293b !important;
        border-color: #334155 !important;
    }

    /* Footer */
    .tray-footer-bar {
        padding: 8px 14px !important;
        background: #f8fafc !important;
        border-top: 1px solid #e2e8f0 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
    }
    .dark .tray-footer-bar {
        background: #1e293b !important;
        border-color: #334155 !important;
    }

    /* Subtle Full-Screen Click-Outside Backdrop */
    #sideTrayBackdrop {
        position: fixed !important;
        inset: 0 !important;
        background: rgba(0, 0, 0, 0.25) !important;
        z-index: 2147483640 !important;
        display: none !important;
    }
    #sideTrayBackdrop.backdrop-active {
        display: block !important;
    }

    /* Google translate override so the combo looks clean */
    #google_translate_element .goog-te-gadget {
        font-size: 11px !important;
        color: #64748b !important;
    }
    #google_translate_element .goog-te-combo {
        width: 100% !important;
        box-sizing: border-box !important;
        margin-top: 4px !important;
        padding: 5px 8px !important;
        border-radius: 6px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        outline: none !important;
        cursor: pointer !important;
    }
    .dark #google_translate_element .goog-te-combo {
        background-color: #0f172a !important;
        border-color: #475569 !important;
        color: #f8fafc !important;
    }
</style>

<!-- Invisible / Dim Backdrop for Click-Outside -->
<div id="sideTrayBackdrop" onclick="closeLanguageTray()"></div>

<!-- Floating Side Tray Container -->
<div id="sideLanguageTray">
    
    <!-- 1. The Pull Tab Handle attached to the left of the tray (Micro Sleek Pill) -->
    <div id="trayHandleTab" onclick="toggleLanguageTray(event)" title="Change Language / ভাষা / भाषा">
        <div class="tray-tab-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
            </svg>
        </div>
        <span class="tray-tab-label" id="trayCurrentLangLabel">EN</span>
    </div>

    <!-- 2. The Tray Body (Language Selection Card) -->
    <div id="trayBody">
        
        <!-- Header -->
        <div class="tray-header-bar">
            <div class="tray-title-group">
                <div class="tray-tab-icon" style="width: 26px; height: 26px;">
                    <svg style="width: 15px; height: 15px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                    </svg>
                </div>
                <div>
                    <div class="tray-title-text">Select Language</div>
                    <div class="tray-subtitle-text">ভাষা পরিবর্তন / भाषा चुनें</div>
                </div>
            </div>
            <button type="button" class="tray-close-btn" onclick="closeLanguageTray()" title="Close Tray">✕</button>
        </div>

        <!-- Content Area -->
        <div class="tray-content-area">
            
            <div class="tray-section-title">Quick Switch</div>

            <!-- Option 1: English -->
            <button type="button" class="tray-lang-btn" data-lang="en" onclick="setAppLanguage('en')">
                <div class="tray-lang-btn-left">
                    <span class="tray-lang-code-pill">EN</span>
                    <div>
                        <div class="tray-lang-name">English</div>
                        <div class="tray-lang-sub">Default (Original)</div>
                    </div>
                </div>
                <div class="tray-lang-check">✓</div>
            </button>

            <!-- Option 2: Bengali -->
            <button type="button" class="tray-lang-btn" data-lang="bn" onclick="setAppLanguage('bn')">
                <div class="tray-lang-btn-left">
                    <span class="tray-lang-code-pill" style="color: #059669; border-color: #a7f3d0; background: #ecfdf5;">BN</span>
                    <div>
                        <div class="tray-lang-name">বাংলা (Bengali)</div>
                        <div class="tray-lang-sub">বাংলা অনুবাদ</div>
                    </div>
                </div>
                <div class="tray-lang-check" style="background: #059669;">✓</div>
            </button>

            <!-- Option 3: Hindi -->
            <button type="button" class="tray-lang-btn" data-lang="hi" onclick="setAppLanguage('hi')">
                <div class="tray-lang-btn-left">
                    <span class="tray-lang-code-pill" style="color: #ea580c; border-color: #fed7aa; background: #fff7ed;">HI</span>
                    <div>
                        <div class="tray-lang-name">हिन्दी (Hindi)</div>
                        <div class="tray-lang-sub">हिन्दी अनुवाद</div>
                    </div>
                </div>
                <div class="tray-lang-check" style="background: #ea580c;">✓</div>
            </button>

            <!-- Native Google Translate Selector Option -->
            <div class="tray-google-box">
                <div class="tray-section-title" style="margin-bottom: 2px;">All Languages / Google</div>
                <div id="google_translate_element"></div>
            </div>

        </div>

        <!-- Footer -->
        <div class="tray-footer-bar">
            <span style="font-size: 10px; color: #64748b; display: flex; align-items: center; gap: 5px;">
                <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #10b981;"></span>
                Live Auto-Translate
            </span>
            <button type="button" 
                    onclick="closeLanguageTray()" 
                    style="background: #1555a8; color: #fff; border: none; border-radius: 6px; padding: 4px 10px; font-size: 11px; font-weight: 700; cursor: pointer;">
                Done
            </button>
        </div>

    </div>

</div>

<!-- Google Translate Scripts & Tray Control Logic -->
<script>
    function toggleLanguageTray(e) {
        if (e) {
            e.stopPropagation();
            e.preventDefault();
        }
        const tray = document.getElementById('sideLanguageTray');
        const backdrop = document.getElementById('sideTrayBackdrop');
        if (!tray) return;

        if (tray.classList.contains('tray-open')) {
            closeLanguageTray();
        } else {
            openLanguageTray();
        }
    }

    function openLanguageTray() {
        const tray = document.getElementById('sideLanguageTray');
        const backdrop = document.getElementById('sideTrayBackdrop');
        if (!tray) return;

        tray.classList.add('tray-open');
        if (backdrop) backdrop.classList.add('backdrop-active');
        highlightActiveLangOption();
    }

    function closeLanguageTray() {
        const tray = document.getElementById('sideLanguageTray');
        const backdrop = document.getElementById('sideTrayBackdrop');
        if (!tray) return;

        tray.classList.remove('tray-open');
        if (backdrop) backdrop.classList.remove('backdrop-active');
    }

    function googleTranslateElementInit() {
        if (typeof google !== 'undefined' && google.translate) {
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'en,bn,hi',
                autoDisplay: false
            }, 'google_translate_element');
        }
    }

    function setAppLanguage(langCode) {
        const host = window.location.hostname;

        if (langCode === 'en') {
            // Clear translation cookies to restore original English
            document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=" + host + ";";
            document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=." + host + ";";
            try { localStorage.removeItem('selected_app_lang'); } catch(e) {}

            const select = document.querySelector('.goog-te-combo');
            if (select) {
                select.value = '';
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }
            highlightActiveLangOption('en');
            setTimeout(closeLanguageTray, 200);

            // If still translated, a reload will cleanly reset to original
            const currentTrans = document.cookie.match(/googtrans=\/en\/([a-z]+)/i);
            if (currentTrans) {
                window.location.reload();
            }
            return;
        }

        // Set Google Translate Combo Value
        const select = document.querySelector('.goog-te-combo');
        if (select) {
            select.value = langCode;
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }

        // Set persistent googtrans cookies
        const cookieVal = '/en/' + langCode;
        document.cookie = 'googtrans=' + cookieVal + '; path=/;';
        document.cookie = 'googtrans=' + cookieVal + '; path=/; domain=' + host + ';';
        document.cookie = 'googtrans=' + cookieVal + '; path=/; domain=.' + host + ';';
        try { localStorage.setItem('selected_app_lang', langCode); } catch(e) {}

        highlightActiveLangOption(langCode);
        setTimeout(closeLanguageTray, 200);

        // If the combo select was not ready, reload to let Google Translate read cookie
        if (!select) {
            window.location.reload();
        }
    }

    function highlightActiveLangOption(code) {
        let current = code;
        if (!current) {
            const select = document.querySelector('.goog-te-combo');
            if (select && select.value) {
                current = select.value;
            } else {
                const cookieMatch = document.cookie.match(/googtrans=\/en\/([a-z]+)/i);
                current = cookieMatch ? cookieMatch[1] : (localStorage.getItem('selected_app_lang') || 'en');
            }
        }
        current = (current || 'en').toLowerCase();

        // Update tab vertical badge
        const tabBadge = document.getElementById('trayCurrentLangLabel');
        if (tabBadge) {
            tabBadge.textContent = current.toUpperCase();
        }

        // Highlight corresponding button
        document.querySelectorAll('.tray-lang-btn').forEach(btn => {
            const btnLang = btn.getAttribute('data-lang');
            if (btnLang === current) {
                btn.classList.add('active-lang');
            } else {
                btn.classList.remove('active-lang');
            }
        });
    }

    // Close on Escape Key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeLanguageTray();
        }
    });

    // Initialize highlight on DOM ready and after a short delay
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(highlightActiveLangOption, 300);
        setTimeout(highlightActiveLangOption, 1200);
    });
</script>

<!-- Google Translate Widget Library -->
<script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
