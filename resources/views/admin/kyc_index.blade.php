@extends('admin.layout')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
    <!-- Header -->
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md2 font-bold text-black dark:text-white flex items-center gap-2">
                <span>User Onboarding & KYC Center</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">
                    Single-Point Approvals
                </span>
            </h2>
            <p class="text-sm text-gray-500">
                Review KYC, activate user accounts, and approve credit limits all from a single screen.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 flex w-full border-l-6 border-[#34D399] bg-[#34D399] bg-opacity-[15%] px-7 py-4 shadow-md dark:bg-[#1b1b1b] md:p-5 rounded-r">
            <div class="mr-5 flex h-9 w-full max-w-[36px] items-center justify-center rounded-lg bg-[#34D399]">
                <svg width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15.2984 0.826822L15.2868 0.811827L15.2741 0.797751C14.9173 0.401837 14.3238 0.400754 13.9657 0.794406L5.91888 9.53233L2.02771 5.41173C1.6655 5.02795 1.07402 5.02206 0.704179 5.39847C0.334339 5.77488 0.328449 6.37722 0.69066 6.761L5.24211 11.5956L5.24355 11.5972C5.60196 11.9751 6.18434 11.9744 6.54194 11.5953L15.3035 1.95661C15.6669 1.56494 15.6644 0.957512 15.2984 0.826822Z" fill="white" stroke="white"></path>
                </svg>
            </div>
            <div class="w-full">
                <p class="text-base font-medium leading-relaxed text-body text-[#15803D] dark:text-[#34D399]">
                    {{ session('success') }}
                </p>
            </div>
        </div>
    @endif

    <!-- Tab Navigation with Counts -->
    <div class="mb-5 flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.kyc.index', ['status' => 'pending']) }}" class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold transition {{ $status === 'pending' ? 'bg-primary text-white shadow-md' : 'bg-white border border-stroke text-black hover:bg-gray-50 dark:bg-boxdark dark:border-strokedark dark:text-white' }}">
            <span>Pending Approvals</span>
            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs {{ $status === 'pending' ? 'bg-white/20 text-white' : 'bg-warning-50 text-warning-600 font-bold' }}">
                {{ $stats['pending'] ?? 0 }}
            </span>
        </a>
        <a href="{{ route('admin.kyc.index', ['status' => 'approved']) }}" class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold transition {{ $status === 'approved' ? 'bg-success text-white shadow-md' : 'bg-white border border-stroke text-black hover:bg-gray-50 dark:bg-boxdark dark:border-strokedark dark:text-white' }}">
            <span>Approved & Active</span>
            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs {{ $status === 'approved' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700' }}">
                {{ $stats['approved'] ?? 0 }}
            </span>
        </a>
        <a href="{{ route('admin.kyc.index', ['status' => 'rejected']) }}" class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold transition {{ $status === 'rejected' ? 'bg-danger text-white shadow-md' : 'bg-white border border-stroke text-black hover:bg-gray-50 dark:bg-boxdark dark:border-strokedark dark:text-white' }}">
            <span>Correction Requested</span>
            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs {{ $status === 'rejected' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700' }}">
                {{ $stats['rejected'] ?? 0 }}
            </span>
        </a>
        <a href="{{ route('admin.kyc.index', ['status' => 'unfilled']) }}" class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold transition {{ $status === 'unfilled' ? 'bg-gray-700 text-white shadow-md' : 'bg-white border border-stroke text-black hover:bg-gray-50 dark:bg-boxdark dark:border-strokedark dark:text-white' }}">
            <span>Unfilled KYC</span>
            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs {{ $status === 'unfilled' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700' }}">
                {{ $stats['unfilled'] ?? 0 }}
            </span>
        </a>
    </div>

    <!-- Main Table Card -->
    <div class="rounded-xl border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark overflow-hidden">
        <div class="max-w-full overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-2 text-left dark:bg-meta-4 border-b border-stroke dark:border-strokedark">
                        <th class="py-4 px-4 font-semibold text-black dark:text-white">User & Contact</th>
                        <th class="py-4 px-4 font-semibold text-black dark:text-white">Sponsor / Tree</th>
                        <th class="py-4 px-4 font-semibold text-black dark:text-white">Submitted KYC Details</th>
                        <th class="py-4 px-4 font-semibold text-black dark:text-white">Account & Credit</th>
                        <th class="py-4 px-4 font-semibold text-black dark:text-white text-right">Unified Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stroke dark:divide-strokedark">
                    @forelse($users as $user)
                    @php
                        $parentName = $user->referralRecord?->parent?->name ?? 'None';
                        $referralCode = $user->referralRecord?->referral_code ?? '-';
                        $creditLimit = (float) ($user->creditAccount->credit_limit ?? 5000);
                        $creditStatus = $user->creditAccount->approval_status ?? 'pending';
                    @endphp
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-meta-4/30 transition">
                        <!-- User & Contact -->
                        <td class="py-5 px-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $user->avatar_url }}" alt="User" class="h-11 w-11 rounded-full border-2 border-primary object-cover">
                                <div>
                                    <p class="font-bold text-black dark:text-white text-sm">{{ $user->name }}</p>
                                    <p class="text-xs text-blue-600 dark:text-blue-400">{{ $user->email }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        {{ $user->phone ?: 'No phone' }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        <!-- Sponsor / Tree -->
                        <td class="py-5 px-4">
                            <div class="text-xs space-y-1">
                                <p class="text-gray-500 font-medium">Referred By:</p>
                                <p class="font-semibold text-black dark:text-white">{{ $parentName }}</p>
                                <span class="inline-block px-1.5 py-0.5 rounded bg-gray-100 dark:bg-meta-4 text-[10px] font-mono text-gray-600 dark:text-gray-300 border border-stroke dark:border-strokedark">
                                    Code: {{ $referralCode }}
                                </span>
                            </div>
                        </td>

                        <!-- Submitted KYC Details -->
                        <td class="py-5 px-4">
                            <div class="text-xs space-y-1 max-w-[280px]">
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 font-semibold w-16">Aadhaar:</span>
                                    <span class="font-mono text-black dark:text-white font-medium">{{ $user->aadhaar_number ?: 'Not provided' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 font-semibold w-16">PAN:</span>
                                    <span class="font-mono text-black dark:text-white font-medium">{{ $user->pan_number ?: 'Not provided' }}</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span class="text-gray-500 font-semibold w-16 shrink-0">Address:</span>
                                    <span class="text-gray-700 dark:text-gray-300 truncate" title="{{ $user->address }}">{{ $user->address ?: 'Not provided' }}</span>
                                </div>
                                @if($user->nominee_name)
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 font-semibold w-16">Nominee:</span>
                                    <span class="text-gray-700 dark:text-gray-300">{{ $user->nominee_name }} ({{ $user->nominee_relation ?: 'Nominee' }})</span>
                                </div>
                                @endif
                                @if($user->kyc_notes)
                                <div class="mt-1 p-1.5 rounded bg-warning-50 dark:bg-meta-4 text-warning-700 text-[11px] border border-warning-200">
                                    <strong>Note:</strong> {{ $user->kyc_notes }}
                                </div>
                                @endif
                            </div>
                        </td>

                        <!-- Account & Credit -->
                        <td class="py-5 px-4">
                            <div class="space-y-1.5 text-xs">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-gray-500">Status:</span>
                                    @if($user->status === 'active')
                                        <span class="px-2 py-0.5 rounded-full font-semibold bg-success-50 text-success-600 text-[11px]">Active</span>
                                    @elseif($user->status === 'pending')
                                        <span class="px-2 py-0.5 rounded-full font-semibold bg-warning-50 text-warning-600 text-[11px]">Pending</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full font-semibold bg-danger-50 text-danger-600 text-[11px]">Blocked</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-gray-500">Credit:</span>
                                    <span class="font-bold text-black dark:text-white">₹{{ number_format($creditLimit, 2) }}</span>
                                    <span class="text-[10px] uppercase font-bold {{ $creditStatus === 'approved' ? 'text-success' : 'text-warning' }}">({{ $creditStatus }})</span>
                                </div>
                                <p class="text-[10px] text-gray-400">Joined: {{ $user->created_at?->format('M d, Y') }}</p>
                            </div>
                        </td>

                        <!-- Unified Actions -->
                        <td class="py-5 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($user->kyc_status === 'pending' || $user->status === 'pending')
                                    <!-- 1-Click Complete Approval Form -->
                                    <form action="{{ route('admin.users.quick-onboard', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Instant 1-Click Onboard:\n\nThis will immediately approve {{ $user->name }}\'s KYC, activate their account, grant ₹{{ number_format($defaultCreditLimit) }} credit limit, and distribute MLM joining commissions.\n\nProceed?');">
                                        @csrf
                                        <input type="hidden" name="status" value="active">
                                        <input type="hidden" name="kyc_status" value="approved">
                                        <input type="hidden" name="credit_limit" value="{{ $defaultCreditLimit }}">
                                        <input type="hidden" name="credit_approval" value="approved">
                                        <button type="submit" class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold py-2 px-3 rounded-lg shadow-sm transition" title="1-Click: Approves KYC, Activates Account & Grants ₹{{ number_format($defaultCreditLimit) }} Credit Limit">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                            1-Click Approve
                                        </button>
                                    </form>

                                    <!-- Review & Onboard Modal Trigger -->
                                    <button type="button" 
                                            class="js-quick-onboard-btn inline-flex items-center gap-1 border border-primary text-primary hover:bg-primary hover:text-white text-xs font-semibold py-2 px-3 rounded-lg transition"
                                            data-id="{{ $user->id }}"
                                            data-name="{{ $user->name }}"
                                            data-email="{{ $user->email }}"
                                            data-phone="{{ $user->phone ?? '-' }}"
                                            data-avatar="{{ $user->avatar_url }}"
                                            data-aadhaar="{{ $user->aadhaar_number ?? 'Not provided' }}"
                                            data-pan="{{ $user->pan_number ?? 'Not provided' }}"
                                            data-address="{{ $user->address ?? 'Not provided' }}"
                                            data-nominee="{{ $user->nominee_name ? ($user->nominee_name . ' (' . ($user->nominee_relation ?? 'Nominee') . ')') : 'Not provided' }}"
                                            data-upline="{{ $parentName }}"
                                            data-credit-limit="{{ $creditLimit }}"
                                            data-kyc-status="{{ $user->kyc_status }}"
                                            data-status="{{ $user->status }}"
                                            title="Open Interactive Onboarding Modal">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Review
                                    </button>

                                    <!-- Request Correction / Reject -->
                                    <button type="button" 
                                            onclick="openRejectPromptDirect({{ $user->id }})"
                                            class="inline-flex items-center text-xs font-medium text-danger border border-danger/30 hover:bg-danger hover:text-white py-2 px-2.5 rounded-lg transition"
                                            title="Request Correction / Unlock Profile">
                                        Reject
                                    </button>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-success bg-success-50 px-2.5 py-1.5 rounded-lg">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Verified & Active
                                    </span>
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="text-xs text-blue-600 hover:underline px-2 py-1">
                                        Edit
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-base font-semibold text-gray-700 dark:text-gray-300">No {{ $status }} requests found</p>
                                <p class="text-xs text-gray-400 mt-1">Users submitting KYC will automatically appear here for 1-click onboarding.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="p-4 border-t border-stroke dark:border-strokedark">
            {{ $users->appends(['status' => $status])->links() }}
        </div>
        @endif
    </div>
</div>

@include('admin.partials.onboard_modal')

<script>
    function openRejectPromptDirect(userId) {
        const rejectModal = document.getElementById('rejectModal');
        const rejectForm = document.getElementById('rejectForm');
        rejectForm.action = `/admin/users/${userId}/quick-reject`;
        document.getElementById('reject_notes_input').value = '';
        rejectModal.classList.remove('hidden');
        rejectModal.classList.add('flex');
    }
</script>
@endsection
