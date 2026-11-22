<!-- ============================================================== -->
<!-- MODULAR AJAX KHATABOOK STATEMENT & MANAGEMENT MODAL (SPA/REACT-LIKE) -->
<!-- ============================================================== -->
<div id="khataModal" 
     class="fixed inset-0 z-99999 hidden items-center justify-center bg-black/60 backdrop-blur-xs p-2 sm:p-4 overflow-y-auto transition-opacity duration-200">
    
    <div class="relative w-full max-w-2xl bg-white dark:bg-boxdark rounded-2xl border border-gray-200 dark:border-strokedark shadow-2xl overflow-hidden my-auto flex flex-col max-h-[92vh] transition-all">
        
        <!-- 1. Header Bar (#1555a8) -->
        <div class="bg-[#1555a8] px-3.5 py-3 text-white flex items-center justify-between shrink-0 shadow-xs">
            <div class="flex items-center gap-2.5 min-w-0">
                <!-- Back / Close Button -->
                <button type="button" 
                        onclick="closeKhataModal()" 
                        title="Back to Customer Directory"
                        class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10 active:scale-95 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </button>

                <!-- Avatar Circle with Initials -->
                <div id="km_avatar_circle" class="flex h-9 w-9 items-center justify-center rounded-full bg-white/20 text-white font-bold text-xs shrink-0 border border-white/20">
                    <span id="km_avatar_text">CU</span>
                </div>

                <!-- Customer Name & Subtitle -->
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5">
                        <h3 id="km_name" class="text-sm sm:text-base font-bold text-white leading-tight truncate">Customer Khata</h3>
                        <span id="km_status_badge" class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase tracking-wider bg-white/20 text-white shrink-0">Active</span>
                    </div>
                    <p id="km_subtitle" class="text-[11px] text-white/80 leading-tight font-mono truncate mt-0.5">Live Khata Ledger</p>
                </div>
            </div>

            <!-- Header Action Controls -->
            <div class="flex items-center gap-1 shrink-0">
                <!-- Open Dedicated Full Screen in New Tab -->
                <a id="km_full_screen_link" 
                   href="#" 
                   target="_blank" 
                   title="Open in dedicated full screen tab"
                   class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[11px] font-semibold text-white/90 hover:text-white hover:bg-white/10 transition">
                    <span class="hidden sm:inline">Full Page</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>

                <!-- Close ✕ -->
                <button type="button" 
                        onclick="closeKhataModal()" 
                        class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10 active:scale-95 transition"
                        title="Close Khata Sheet">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- 2. Loading Spinner Placeholder -->
        <div id="km_loading_state" class="p-12 text-center space-y-3">
            <div class="w-8 h-8 mx-auto border-3 border-[#1555a8] border-t-transparent rounded-full animate-spin"></div>
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Loading live customer statement &amp; ledger...</p>
        </div>

        <!-- 3. Modal Body Content (Hidden while loading) -->
        <div id="km_body_content" class="hidden overflow-y-auto p-3 sm:p-4 space-y-3 max-h-[82vh]">
            
            <!-- Toast notification inside modal -->
            <div id="km_toast" class="hidden rounded-lg p-2.5 text-xs font-semibold flex items-center justify-between transition-all">
                <span id="km_toast_msg">Action completed</span>
                <button type="button" onclick="$('#km_toast').addClass('hidden')" class="font-bold ml-2">✕</button>
            </div>

            <!-- Financial Summary Matrix (2x2 Grid) -->
            <div class="grid grid-cols-2 gap-2 sm:gap-2.5">
                <!-- 1. Total Udhar Due -->
                <div class="p-2.5 sm:p-3 rounded-xl bg-red-50/70 dark:bg-red-950/25 border border-red-200/80 dark:border-red-900/40 flex flex-col justify-between">
                    <div class="flex items-center justify-between gap-1">
                        <span class="text-[10px] font-bold text-red-700 dark:text-red-400 uppercase tracking-wider">Khata Due (Udhar)</span>
                        <span class="text-[9px] bg-red-100 dark:bg-red-900/60 text-red-700 dark:text-red-300 font-bold px-1.5 py-0.2 rounded-full">You'll Get ↙</span>
                    </div>
                    <div id="km_used_credit" class="text-base sm:text-lg font-black text-red-600 dark:text-red-400 mt-1 tabular-nums">₹0.00</div>
                </div>

                <!-- 2. Credit Balance & Limit -->
                <div class="p-2.5 sm:p-3 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/25 border border-emerald-200/80 dark:border-emerald-900/40 flex flex-col justify-between">
                    <div class="flex items-center justify-between gap-1">
                        <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Available Credit</span>
                        <span id="km_credit_limit_chip" class="text-[9px] bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 font-bold px-1.5 py-0.2 rounded-full truncate">Limit: ₹0</span>
                    </div>
                    <div id="km_available_credit" class="text-base sm:text-lg font-black text-emerald-600 dark:text-emerald-400 mt-1 tabular-nums">₹0.00</div>
                </div>

                <!-- 3. Main Wallet Balance -->
                <div class="p-2.5 sm:p-3 rounded-xl bg-blue-50/70 dark:bg-blue-950/25 border border-blue-200/80 dark:border-blue-900/40 flex flex-col justify-between">
                    <div class="flex items-center justify-between gap-1">
                        <span class="text-[10px] font-bold text-blue-700 dark:text-blue-400 uppercase tracking-wider">Main Wallet</span>
                        <span class="text-[9px] bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 font-bold px-1.5 py-0.2 rounded-full">Cash Balance</span>
                    </div>
                    <div id="km_wallet_balance" class="text-base sm:text-lg font-black text-blue-700 dark:text-blue-300 mt-1 tabular-nums">₹0.00</div>
                </div>

                <!-- 4. Total Shopping Done -->
                <div class="p-2.5 sm:p-3 rounded-xl bg-purple-50/70 dark:bg-purple-950/25 border border-purple-200/80 dark:border-purple-900/40 flex flex-col justify-between">
                    <div class="flex items-center justify-between gap-1">
                        <span class="text-[10px] font-bold text-purple-700 dark:text-purple-400 uppercase tracking-wider">Shopping Done</span>
                        <span class="text-[9px] bg-purple-100 dark:bg-purple-900/60 text-purple-700 dark:text-purple-300 font-bold px-1.5 py-0.2 rounded-full">Turnover</span>
                    </div>
                    <div id="km_earning_or_shop" class="text-base sm:text-lg font-black text-purple-700 dark:text-purple-300 mt-1 tabular-nums">₹0.00</div>
                </div>
            </div>

            <!-- Quick Action Tabs Toolbar (Single-Click Modals/Panels) -->
            <div class="border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50/80 dark:bg-gray-900/60 p-2">
                <div class="flex items-center justify-between gap-1.5">
                    <button type="button" 
                            onclick="toggleKhataActionPanel('add_money')" 
                            class="km-action-tab flex-1 py-1.5 px-2 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white shadow-2xs">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Add Money</span>
                    </button>

                    <button type="button" 
                            onclick="toggleKhataActionPanel('shop_deduct')" 
                            class="km-action-tab flex-1 py-1.5 px-2 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1 bg-[#1555a8] hover:bg-[#12468d] text-white shadow-2xs">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span>Shop Deduct</span>
                    </button>

                    <button type="button" 
                            onclick="toggleKhataActionPanel('update_limit')" 
                            class="km-action-tab flex-1 py-1.5 px-2 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1 bg-amber-600 hover:bg-amber-700 text-white shadow-2xs">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>Set Limit</span>
                    </button>
                </div>

                <!-- Action Panel 1: Add Money Form -->
                <div id="km_panel_add_money" class="hidden mt-2.5 pt-2.5 border-t border-gray-200 dark:border-gray-700 space-y-2">
                    <div class="text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center justify-between">
                        <span>Deposit / Add Money (Credits Wallet)</span>
                        <span class="text-[10px] text-gray-400 font-normal">Auto-clears overdue EMIs</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-0.5">Amount (₹)</label>
                            <input type="number" id="km_add_amount" min="1" step="0.01" placeholder="e.g. 500" 
                                   class="w-full text-xs font-bold p-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-0.5">Note / Reference (Optional)</label>
                            <input type="text" id="km_add_note" placeholder="Cash received / UPI" 
                                   class="w-full text-xs p-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-emerald-500">
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-1">
                        <button type="button" onclick="toggleKhataActionPanel(null)" class="px-2.5 py-1 text-xs font-semibold text-gray-500 hover:text-gray-700 dark:text-gray-400">Cancel</button>
                        <button type="button" id="km_btn_submit_add" onclick="submitKhataAddMoney()" class="px-3 py-1.5 text-xs font-bold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition flex items-center gap-1">
                            <span>Confirm &amp; Add Money</span>
                        </button>
                    </div>
                </div>

                <!-- Action Panel 2: Shop Purchase Deduct Form -->
                <div id="km_panel_shop_deduct" class="hidden mt-2.5 pt-2.5 border-t border-gray-200 dark:border-gray-700 space-y-2">
                    <div class="text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center justify-between">
                        <span>Process Shop Purchase (Debits Wallet &amp; Khata)</span>
                        <span class="text-[10px] text-gray-400 font-normal">Wallet balance used first; remainder from credit</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-0.5">Bill Amount (₹)</label>
                            <input type="number" id="km_deduct_amount" min="1" step="0.01" placeholder="e.g. 1200" 
                                   class="w-full text-xs font-bold p-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-[#1555a8]">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-0.5">BV Points (Optional)</label>
                            <input type="number" id="km_deduct_bv" min="0" step="1" placeholder="e.g. 50" 
                                   class="w-full text-xs p-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-[#1555a8]">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-0.5">Bill No. / Note (Optional)</label>
                            <input type="text" id="km_deduct_note" placeholder="Grocery Invoice #..." 
                                   class="w-full text-xs p-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-[#1555a8]">
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-1">
                        <button type="button" onclick="toggleKhataActionPanel(null)" class="px-2.5 py-1 text-xs font-semibold text-gray-500 hover:text-gray-700 dark:text-gray-400">Cancel</button>
                        <button type="button" id="km_btn_submit_deduct" onclick="submitKhataShopDeduct()" class="px-3 py-1.5 text-xs font-bold rounded-lg bg-[#1555a8] hover:bg-[#12468d] text-white transition flex items-center gap-1">
                            <span>Process Shop Purchase</span>
                        </button>
                    </div>
                </div>

                <!-- Action Panel 3: Update Credit Limit Form -->
                <div id="km_panel_update_limit" class="hidden mt-2.5 pt-2.5 border-t border-gray-200 dark:border-gray-700 space-y-2">
                    <div class="text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center justify-between">
                        <span>Set / Adjust Customer Approved Credit Limit</span>
                        <span class="text-[10px] text-gray-400 font-normal">Increases purchasing power pool</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-0.5">New Limit (₹)</label>
                            <input type="number" id="km_limit_amount" min="0" step="500" placeholder="e.g. 15000" 
                                   class="w-full text-xs font-bold p-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-amber-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-0.5">Reason / Note (Optional)</label>
                            <input type="text" id="km_limit_note" placeholder="Good repayment track / KYC approved" 
                                   class="w-full text-xs p-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-amber-500">
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-1">
                        <button type="button" onclick="toggleKhataActionPanel(null)" class="px-2.5 py-1 text-xs font-semibold text-gray-500 hover:text-gray-700 dark:text-gray-400">Cancel</button>
                        <button type="button" id="km_btn_submit_limit" onclick="submitKhataUpdateLimit()" class="px-3 py-1.5 text-xs font-bold rounded-lg bg-amber-600 hover:bg-amber-700 text-white transition flex items-center gap-1">
                            <span>Save New Credit Limit</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recent Ledger Transactions List -->
            <div class="space-y-1.5 pt-1">
                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 font-semibold px-0.5">
                    <span>Recent Ledger Transactions</span>
                    <span id="km_tx_count" class="text-[10px] text-gray-400">0 records</span>
                </div>
                
                <div class="max-h-56 overflow-y-auto rounded-xl border border-gray-100 dark:border-gray-700/80 shadow-2xs">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700 text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-800/80 text-[10px] font-bold text-gray-500 uppercase">
                            <tr>
                                <th class="px-2.5 py-1.5">Date / Time</th>
                                <th class="px-2.5 py-1.5">Description</th>
                                <th class="px-2.5 py-1.5 text-right">Amount</th>
                                <th class="px-2.5 py-1.5 text-right">Balance After</th>
                            </tr>
                        </thead>
                        <tbody id="km_tx_table_body" class="divide-y divide-gray-100 dark:divide-gray-700/60 bg-white dark:bg-gray-900">
                            <tr>
                                <td colspan="4" class="p-4 text-center text-xs text-gray-400">Loading ledger records...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- 4. Footer Bar -->
        <div class="p-2.5 sm:p-3 bg-gray-50/90 dark:bg-gray-800/90 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between text-xs shrink-0">
            <span class="text-[11px] text-gray-400">
                <span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-500 mr-1 animate-pulse"></span>
                Instant Khata Sync
            </span>
            <div class="flex items-center gap-2">
                <button type="button" 
                        onclick="closeKhataModal()" 
                        class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-bold hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                    Done / Return
                </button>
            </div>
        </div>

    </div>
</div>

<script>
    let currentKhataCustomerId = null;
    let currentKhataCustomerData = null;

    function openKhataModal(userId) {
        if (!userId) return;
        currentKhataCustomerId = userId;

        const modal = $('#khataModal');
        const loading = $('#km_loading_state');
        const content = $('#km_body_content');
        const toast = $('#km_toast');

        toast.addClass('hidden');
        toggleKhataActionPanel(null); // Reset active action form
        
        loading.removeClass('hidden');
        content.addClass('hidden');
        modal.removeClass('hidden').addClass('flex');

        // Fetch customer data via AJAX
        $.ajax({
            url: "{{ route('admin.customer-lookup.search') }}",
            type: "GET",
            data: { user_id: userId },
            success: function(res) {
                if (res.success && res.customer) {
                    currentKhataCustomerData = res.customer;
                    populateKhataModal(res.customer, res.transactions || []);
                    loading.addClass('hidden');
                    content.removeClass('hidden');
                } else {
                    Swal.fire('Error', res.message || 'Customer record not found.', 'error');
                    closeKhataModal();
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Failed to load customer Khata.';
                Swal.fire('Error', msg, 'error');
                closeKhataModal();
            }
        });
    }

    function closeKhataModal() {
        $('#khataModal').addClass('hidden').removeClass('flex');
        currentKhataCustomerId = null;
        currentKhataCustomerData = null;
        toggleKhataActionPanel(null);

        // Silently reload Users Table without resetting active pagination or filter
        if (typeof usersDataTable !== 'undefined' && usersDataTable) {
            usersDataTable.ajax.reload(null, false);
        }
    }

    function populateKhataModal(customer, transactions) {
        $('#km_name').text(customer.name || 'Customer');
        $('#km_avatar_text').text(customer.initials || 'CU');
        $('#km_subtitle').text((customer.phone ? customer.phone + ' • ' : '') + 'ID #' + customer.id);
        
        // Status badge
        const badge = $('#km_status_badge');
        if (customer.credit_approval_status === 'approved') {
            badge.text('Creditor').removeClass('bg-white/20').addClass('bg-orange-500 text-white');
        } else {
            badge.text(customer.status || 'Active').removeClass('bg-orange-500').addClass('bg-white/20 text-white');
        }

        // Dedicated link in new tab
        $('#km_full_screen_link').attr('href', '/admin/customer-lookup?user_id=' + customer.id);

        // Metric cards
        const usedCreditNum = parseFloat(customer.used_credit || 0);
        $('#km_used_credit').text('₹' + usedCreditNum.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        
        const availCreditNum = parseFloat(customer.available_credit || 0);
        $('#km_available_credit').text('₹' + availCreditNum.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#km_credit_limit_chip').text('Limit: ₹' + parseFloat(customer.credit_limit || 0).toLocaleString());

        const walletNum = parseFloat(customer.main_balance || 0);
        $('#km_wallet_balance').text('₹' + walletNum.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

        // Earning / shopping
        const shopNum = parseFloat(customer.earning_balance || 0);
        $('#km_earning_or_shop').text('₹' + shopNum.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

        // Pre-fill default credit limit input
        $('#km_limit_amount').val(parseFloat(customer.credit_limit || 0));

        // Render transactions list
        renderKhataModalTransactions(transactions);
    }

    function renderKhataModalTransactions(transactions) {
        const tbody = $('#km_tx_table_body');
        tbody.empty();

        if (!transactions || transactions.length === 0) {
            tbody.html('<tr><td colspan="4" class="p-4 text-center text-xs text-gray-400">No ledger transactions recorded yet.</td></tr>');
            $('#km_tx_count').text('0 records');
            return;
        }

        $('#km_tx_count').text(transactions.length + ' records');

        transactions.slice(0, 15).forEach(function(tx) {
            const isCredit = tx.type === 'credit';
            const badgeClass = isCredit 
                ? 'text-emerald-600 dark:text-emerald-400 font-bold' 
                : 'text-red-600 dark:text-red-400 font-bold';
            const sign = isCredit ? '+' : '-';

            const row = `
                <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/50">
                    <td class="px-2.5 py-1.5 text-gray-500 dark:text-gray-400 whitespace-nowrap text-[11px]">${tx.created_at_human || tx.date || '-'}</td>
                    <td class="px-2.5 py-1.5 font-medium text-gray-800 dark:text-gray-200 max-w-[200px] truncate" title="${tx.description || ''}">${tx.description || 'Transaction'}</td>
                    <td class="px-2.5 py-1.5 text-right whitespace-nowrap ${badgeClass}">${sign}₹${parseFloat(tx.amount || 0).toFixed(2)}</td>
                    <td class="px-2.5 py-1.5 text-right text-gray-700 dark:text-gray-300 font-mono whitespace-nowrap text-[11px]">${tx.running_balance_formatted || '₹' + parseFloat(tx.balance_after || 0).toFixed(2)}</td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    function toggleKhataActionPanel(panelName) {
        $('#km_panel_add_money').addClass('hidden');
        $('#km_panel_shop_deduct').addClass('hidden');
        $('#km_panel_update_limit').addClass('hidden');

        if (panelName === 'add_money') {
            $('#km_panel_add_money').removeClass('hidden');
            $('#km_add_amount').focus();
        } else if (panelName === 'shop_deduct') {
            $('#km_panel_shop_deduct').removeClass('hidden');
            $('#km_deduct_amount').focus();
        } else if (panelName === 'update_limit') {
            $('#km_panel_update_limit').removeClass('hidden');
            $('#km_limit_amount').focus();
        }
    }

    function showKhataModalToast(message, isSuccess = true) {
        const toast = $('#km_toast');
        const msgEl = $('#km_toast_msg');
        msgEl.text(message);
        toast.removeClass('hidden bg-red-100 text-red-800 bg-emerald-100 text-emerald-800 dark:bg-red-950 dark:text-red-300 dark:bg-emerald-950 dark:text-emerald-300');
        
        if (isSuccess) {
            toast.addClass('bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800');
        } else {
            toast.addClass('bg-red-100 text-red-800 dark:bg-red-950/80 dark:text-red-300 border border-red-300 dark:border-red-800');
        }
    }

    function submitKhataAddMoney() {
        if (!currentKhataCustomerId) return;
        const amount = parseFloat($('#km_add_amount').val());
        const note = $('#km_add_note').val();

        if (isNaN(amount) || amount <= 0) {
            showKhataModalToast('Please enter a valid amount to deposit.', false);
            return;
        }

        const btn = $('#km_btn_submit_add');
        btn.prop('disabled', true).text('Processing...');

        $.ajax({
            url: `/admin/customer-lookup/${currentKhataCustomerId}/add-money`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                amount: amount,
                note: note
            },
            success: function(res) {
                btn.prop('disabled', false).text('Confirm & Add Money');
                if (res.success) {
                    showKhataModalToast(res.message, true);
                    $('#km_add_amount').val('');
                    $('#km_add_note').val('');
                    toggleKhataActionPanel(null);

                    if (res.customer) {
                        populateKhataModal(res.customer, res.transactions || []);
                    }
                    if (typeof usersDataTable !== 'undefined' && usersDataTable) {
                        usersDataTable.ajax.reload(null, false);
                    }
                } else {
                    showKhataModalToast(res.message || 'Deposit failed.', false);
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).text('Confirm & Add Money');
                const err = xhr.responseJSON?.message || 'Failed to process deposit.';
                showKhataModalToast(err, false);
            }
        });
    }

    function submitKhataShopDeduct() {
        if (!currentKhataCustomerId) return;
        const amount = parseFloat($('#km_deduct_amount').val());
        const bv = parseFloat($('#km_deduct_bv').val()) || 0;
        const note = $('#km_deduct_note').val();

        if (isNaN(amount) || amount <= 0) {
            showKhataModalToast('Please enter a valid bill amount to deduct.', false);
            return;
        }

        const btn = $('#km_btn_submit_deduct');
        btn.prop('disabled', true).text('Processing...');

        $.ajax({
            url: `/admin/customer-lookup/${currentKhataCustomerId}/shop-deduct`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                amount: amount,
                bv: bv,
                note: note
            },
            success: function(res) {
                btn.prop('disabled', false).text('Process Shop Purchase');
                if (res.success) {
                    showKhataModalToast(res.message, true);
                    $('#km_deduct_amount').val('');
                    $('#km_deduct_bv').val('');
                    $('#km_deduct_note').val('');
                    toggleKhataActionPanel(null);

                    if (res.customer) {
                        populateKhataModal(res.customer, res.transactions || []);
                    }
                    if (typeof usersDataTable !== 'undefined' && usersDataTable) {
                        usersDataTable.ajax.reload(null, false);
                    }
                } else {
                    showKhataModalToast(res.message || 'Deduction failed.', false);
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).text('Process Shop Purchase');
                const err = xhr.responseJSON?.message || 'Failed to process shop deduction.';
                showKhataModalToast(err, false);
            }
        });
    }

    function submitKhataUpdateLimit() {
        if (!currentKhataCustomerId) return;
        const limit = parseFloat($('#km_limit_amount').val());
        const note = $('#km_limit_note').val();

        if (isNaN(limit) || limit < 0) {
            showKhataModalToast('Please enter a valid credit limit.', false);
            return;
        }

        const btn = $('#km_btn_submit_limit');
        btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: `/admin/customer-lookup/${currentKhataCustomerId}/update-credit-limit`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                credit_limit: limit,
                note: note
            },
            success: function(res) {
                btn.prop('disabled', false).text('Save New Credit Limit');
                if (res.success) {
                    showKhataModalToast(res.message, true);
                    $('#km_limit_note').val('');
                    toggleKhataActionPanel(null);

                    if (res.customer) {
                        populateKhataModal(res.customer, res.transactions || []);
                    }
                    if (typeof usersDataTable !== 'undefined' && usersDataTable) {
                        usersDataTable.ajax.reload(null, false);
                    }
                } else {
                    showKhataModalToast(res.message || 'Failed to update credit limit.', false);
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).text('Save New Credit Limit');
                const err = xhr.responseJSON?.message || 'Failed to update credit limit.';
                showKhataModalToast(err, false);
            }
        });
    }

    // Close on Escape key press
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && !$('#khataModal').hasClass('hidden')) {
            closeKhataModal();
        }
    });

    // Close on backdrop click (click outside modal card)
    $(document).on('click', '#khataModal', function(e) {
        if ($(e.target).is('#khataModal')) {
            closeKhataModal();
        }
    });
</script>
