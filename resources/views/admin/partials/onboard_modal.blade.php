<!-- Unified User Onboarding & Approval Modal -->
<div id="onboardModal" class="fixed inset-0 z-99999 hidden items-center justify-center bg-black/60 p-4 backdrop-blur-sm transition-all overflow-y-auto">
    <div class="relative w-full max-w-2xl rounded-xl bg-white shadow-2xl dark:bg-boxdark border border-stroke dark:border-strokedark my-8 overflow-hidden transform transition-all">
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-stroke px-6 py-4 dark:border-strokedark bg-gray-50/70 dark:bg-meta-4">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-black dark:text-white">Unified User Approval & Onboarding</h3>
                    <p class="text-xs text-gray-500">Approve KYC, activate account status, and grant credit limit in one place.</p>
                </div>
            </div>
            <button type="button" onclick="closeOnboardModal()" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-200 hover:text-black dark:hover:bg-meta-4 dark:hover:text-white transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="onboardForm" method="POST" action="">
            @csrf
            <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto">
                <!-- User Profile Summary Card -->
                <div class="flex items-center gap-4 p-4 rounded-lg bg-gray-50 dark:bg-meta-4 border border-stroke dark:border-strokedark">
                    <img id="onboard_avatar" src="" alt="Avatar" class="w-14 h-14 rounded-full border-2 border-primary object-cover">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h4 id="onboard_name" class="font-bold text-black dark:text-white truncate text-base">User Name</h4>
                            <span id="onboard_current_status" class="px-2 py-0.5 rounded-full text-xs font-semibold bg-warning-50 text-warning-600">Pending</span>
                        </div>
                        <p id="onboard_email" class="text-xs text-gray-500 truncate">email@example.com</p>
                        <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-gray-600 dark:text-gray-300">
                            <span><strong>Phone:</strong> <span id="onboard_phone">-</span></span>
                            <span>&bull;</span>
                            <span><strong>Sponsor:</strong> <span id="onboard_upline">-</span></span>
                        </div>
                    </div>
                </div>

                <!-- KYC Submitted Verification Details -->
                <div class="rounded-lg border border-stroke dark:border-strokedark p-4">
                    <h5 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                        </svg>
                        Submitted KYC Documents & Details
                    </h5>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div class="p-2.5 rounded bg-gray-50/70 dark:bg-meta-4 border border-stroke dark:border-strokedark">
                            <span class="text-xs text-gray-500 block mb-0.5 font-medium">Aadhaar Number</span>
                            <span id="onboard_aadhaar" class="font-mono font-semibold text-black dark:text-white">N/A</span>
                        </div>
                        <div class="p-2.5 rounded bg-gray-50/70 dark:bg-meta-4 border border-stroke dark:border-strokedark">
                            <span class="text-xs text-gray-500 block mb-0.5 font-medium">PAN Number</span>
                            <span id="onboard_pan" class="font-mono font-semibold text-black dark:text-white">N/A</span>
                        </div>
                        <div class="p-2.5 rounded bg-gray-50/70 dark:bg-meta-4 border border-stroke dark:border-strokedark sm:col-span-2">
                            <span class="text-xs text-gray-500 block mb-0.5 font-medium">Permanent Address</span>
                            <span id="onboard_address" class="text-black dark:text-white">N/A</span>
                        </div>
                        <div class="p-2.5 rounded bg-gray-50/70 dark:bg-meta-4 border border-stroke dark:border-strokedark sm:col-span-2">
                            <span class="text-xs text-gray-500 block mb-0.5 font-medium">Nominee & Relation</span>
                            <span id="onboard_nominee" class="font-medium text-black dark:text-white">N/A</span>
                        </div>
                    </div>
                </div>

                <!-- Unified Approval Configuration -->
                <div class="rounded-lg border-2 border-primary/20 bg-primary/5 p-4 space-y-4">
                    <h5 class="text-xs font-bold uppercase tracking-wider text-primary flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Approval Settings (Configured in 1-Step)
                    </h5>

                    <!-- Status Controls -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">KYC Status</label>
                            <select name="kyc_status" id="onboard_kyc_status" class="w-full rounded-lg border border-stroke bg-white py-2.5 px-3 text-sm font-semibold text-black outline-none focus:border-primary dark:border-strokedark dark:bg-boxdark dark:text-white">
                                <option value="approved" selected>✔ Approved (Verified)</option>
                                <option value="pending">⏳ Pending Review</option>
                                <option value="rejected">✖ Rejected (Need Corrections)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">User Account Status</label>
                            <select name="status" id="onboard_account_status" class="w-full rounded-lg border border-stroke bg-white py-2.5 px-3 text-sm font-semibold text-black outline-none focus:border-primary dark:border-strokedark dark:bg-boxdark dark:text-white">
                                <option value="active" selected>✔ Active (Full Access)</option>
                                <option value="pending">⏳ Pending</option>
                                <option value="blocked">⛔ Blocked</option>
                            </select>
                        </div>
                    </div>

                    <!-- Credit Limit Controls -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                Approved Credit Limit (₹)
                            </label>
                            <span class="text-[11px] text-gray-500">Auto-approves credit account & triggers MLM joining commission</span>
                        </div>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-gray-400 font-bold">₹</span>
                            <input type="number" step="100" min="0" name="credit_limit" id="onboard_credit_limit" value="{{ $systemSettings['default_credit_limit'] ?? 5000 }}" class="w-full rounded-lg border border-stroke bg-white py-2.5 pl-8 pr-3 text-sm font-bold text-black outline-none focus:border-primary dark:border-strokedark dark:bg-boxdark dark:text-white">
                            <input type="hidden" name="credit_approval" id="onboard_credit_approval" value="approved">
                        </div>

                        <!-- Quick Limit Preset Chips -->
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-[11px] text-gray-500 font-medium">Quick Presets:</span>
                            <button type="button" onclick="setCreditPreset(2000)" class="px-2.5 py-1 text-xs font-semibold rounded bg-white dark:bg-boxdark border border-stroke dark:border-strokedark hover:border-primary hover:text-primary transition">₹2,000</button>
                            <button type="button" onclick="setCreditPreset({{ $systemSettings['default_credit_limit'] ?? 5000 }})" class="px-2.5 py-1 text-xs font-semibold rounded bg-primary/10 text-primary border border-primary/30 hover:bg-primary hover:text-white transition">₹{{ number_format($systemSettings['default_credit_limit'] ?? 5000) }} (Default)</button>
                            <button type="button" onclick="setCreditPreset(10000)" class="px-2.5 py-1 text-xs font-semibold rounded bg-white dark:bg-boxdark border border-stroke dark:border-strokedark hover:border-primary hover:text-primary transition">₹10,000</button>
                            <button type="button" onclick="setCreditPreset(25000)" class="px-2.5 py-1 text-xs font-semibold rounded bg-white dark:bg-boxdark border border-stroke dark:border-strokedark hover:border-primary hover:text-primary transition">₹25,000</button>
                        </div>
                    </div>

                    <!-- Optional Note -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Admin Note (Visible to user)</label>
                        <textarea name="kyc_notes" id="onboard_kyc_notes" rows="2" placeholder="e.g. KYC verified and initial credit limit approved." class="w-full rounded-lg border border-stroke bg-white py-2 px-3 text-xs text-black outline-none focus:border-primary dark:border-strokedark dark:bg-boxdark dark:text-white"></textarea>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between border-t border-stroke px-6 py-4 dark:border-strokedark bg-gray-50/70 dark:bg-meta-4">
                <button type="button" onclick="openRejectPrompt()" class="text-xs font-semibold text-danger hover:underline">
                    Reject / Request Changes Instead &rarr;
                </button>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="closeOnboardModal()" class="rounded-lg border border-stroke px-5 py-2.5 text-sm font-medium text-black hover:bg-gray-100 dark:border-strokedark dark:text-white dark:hover:bg-boxdark transition">
                        Cancel
                    </button>
                    <button type="submit" id="onboardSubmitBtn" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-opacity-90 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Approve Everything & Activate
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Quick Rejection Modal -->
<div id="rejectModal" class="fixed inset-0 z-99999 hidden items-center justify-center bg-black/60 p-4 backdrop-blur-sm transition-all overflow-y-auto">
    <div class="relative w-full max-w-md rounded-xl bg-white shadow-2xl dark:bg-boxdark border border-stroke dark:border-strokedark my-8 overflow-hidden">
        <div class="flex items-center justify-between border-b border-stroke px-6 py-4 dark:border-strokedark bg-danger/10">
            <h3 class="text-base font-bold text-danger">Request KYC Corrections</h3>
            <button type="button" onclick="closeRejectModal()" class="rounded-lg p-1 text-gray-400 hover:text-black dark:hover:text-white">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="rejectForm" method="POST" action="">
            @csrf
            <div class="p-6 space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    This will mark the KYC as rejected, unlock the user's profile for editing, and display your note to them.
                </p>
                <div>
                    <label class="block text-xs font-bold text-black dark:text-white mb-2">Reason / Correction Instructions <span class="text-danger">*</span></label>
                    <textarea name="kyc_notes" id="reject_notes_input" rows="3" required placeholder="e.g. Aadhaar number is blurry or PAN does not match name. Please re-upload clear photos." class="w-full rounded-lg border border-stroke bg-transparent py-2.5 px-3 text-sm text-black outline-none focus:border-danger dark:border-strokedark dark:text-white"></textarea>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-stroke px-6 py-4 dark:border-strokedark bg-gray-50/50 dark:bg-meta-4">
                <button type="button" onclick="closeRejectModal()" class="rounded-lg border border-stroke px-4 py-2 text-sm font-medium text-black hover:bg-gray-100 dark:border-strokedark dark:text-white">
                    Cancel
                </button>
                <button type="submit" class="rounded-lg bg-danger px-5 py-2 text-sm font-bold text-white hover:bg-opacity-90 transition">
                    Send Correction Request
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentOnboardUser = null;

    function openOnboardModal(user) {
        currentOnboardUser = user;
        const modal = document.getElementById('onboardModal');
        const form = document.getElementById('onboardForm');

        form.action = `/admin/users/${user.id}/quick-onboard`;

        document.getElementById('onboard_avatar').src = user.avatar || '/images/user/owner.png';
        document.getElementById('onboard_name').textContent = user.name || 'N/A';
        document.getElementById('onboard_email').textContent = user.email || 'N/A';
        document.getElementById('onboard_phone').textContent = user.phone || 'N/A';
        document.getElementById('onboard_upline').textContent = user.upline || 'None';

        document.getElementById('onboard_aadhaar').textContent = user.aadhaar || 'Not provided';
        document.getElementById('onboard_pan').textContent = user.pan || 'Not provided';
        document.getElementById('onboard_address').textContent = user.address || 'Not provided';
        document.getElementById('onboard_nominee').textContent = user.nominee || 'Not provided';

        document.getElementById('onboard_credit_limit').value = user.creditLimit || {{ $systemSettings['default_credit_limit'] ?? 5000 }};
        document.getElementById('onboard_kyc_status').value = 'approved';
        document.getElementById('onboard_account_status').value = 'active';
        document.getElementById('onboard_credit_approval').value = 'approved';

        const statusEl = document.getElementById('onboard_current_status');
        statusEl.textContent = (user.kycStatus || 'pending').toUpperCase();
        statusEl.className = 'px-2 py-0.5 rounded-full text-xs font-semibold ' + 
            (user.kycStatus === 'approved' ? 'bg-success-50 text-success-600' : 
            (user.kycStatus === 'rejected' ? 'bg-danger-50 text-danger-600' : 'bg-warning-50 text-warning-600'));

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeOnboardModal() {
        const modal = document.getElementById('onboardModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function setCreditPreset(amount) {
        document.getElementById('onboard_credit_limit').value = amount;
    }

    function openRejectPrompt() {
        if (!currentOnboardUser) return;
        closeOnboardModal();
        const rejectModal = document.getElementById('rejectModal');
        const rejectForm = document.getElementById('rejectForm');
        rejectForm.action = `/admin/users/${currentOnboardUser.id}/quick-reject`;
        document.getElementById('reject_notes_input').value = '';
        rejectModal.classList.remove('hidden');
        rejectModal.classList.add('flex');
    }

    function closeRejectModal() {
        const rejectModal = document.getElementById('rejectModal');
        rejectModal.classList.add('hidden');
        rejectModal.classList.remove('flex');
    }

    // Attach click events on document for dynamically rendered data buttons
    document.addEventListener('DOMContentLoaded', function() {
        document.body.addEventListener('click', function(e) {
            const btn = e.target.closest('.js-quick-onboard-btn');
            if (!btn) return;
            e.preventDefault();

            const d = btn.dataset;
            openOnboardModal({
                id: d.id,
                name: d.name,
                email: d.email,
                phone: d.phone,
                avatar: d.avatar,
                aadhaar: d.aadhaar,
                pan: d.pan,
                address: d.address,
                nominee: d.nomineeName ? (d.nomineeName + (d.nomineeRel ? ' (' + d.nomineeRel + ')' : '')) : (d.nominee || 'N/A'),
                upline: d.upline,
                creditLimit: d.creditLimit,
                kycStatus: d.kycStatus || 'pending',
                status: d.status || 'pending'
            });
        });
    });
</script>
