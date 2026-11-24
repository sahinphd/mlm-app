@extends('admin.layout')

@section('content')
<div class="w-full"
     x-data="{ 
         viewMode: (new URLSearchParams(window.location.search)).get('view') || localStorage.getItem('admin_payments_view') || 'card',
         modalOpen: false,
         modalActionUrl: '',
         modalTitle: '',
         modalBtnText: '',
         modalBtnClass: '',
         modalAmount: '',
         modalUser: '',
         modalType: '',
         modalNotePlaceholder: '',
         modalNote: '',
         openModal(actionUrl, title, btnText, btnClass, amount, user, type) {
             this.modalActionUrl = actionUrl;
             this.modalTitle = title;
             this.modalBtnText = btnText;
             this.modalBtnClass = btnClass;
             this.modalAmount = amount;
             this.modalUser = user;
             this.modalType = type;
             this.modalNotePlaceholder = type === 'approve' ? 'Approval note (optional)...' : 'Rejection reason...';
             this.modalNote = '';
             this.modalOpen = true;
         },
         openNoteModal(actionUrl, user, amount, currentNote) {
             this.modalActionUrl = actionUrl;
             this.modalTitle = 'Edit Admin Comment';
             this.modalBtnText = 'Save Comment';
             this.modalBtnClass = 'bg-primary';
             this.modalAmount = amount;
             this.modalUser = user;
             this.modalType = 'edit_note';
             this.modalNotePlaceholder = 'Enter comment / note...';
             this.modalNote = currentNote || '';
             this.modalOpen = true;
         }
     }"
     x-init="$watch('viewMode', val => localStorage.setItem('admin_payments_view', val))">

    <!-- Breadcrumb & Top Bar -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md2 font-bold text-black dark:text-white">
                Wallet Top-up Requests (Total: {{ $requests->total() }})
            </h2>
            <p class="text-sm text-body dark:text-bodydark mt-0.5">Manage user wallet recharge requests and approvals</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- View Toggle (List / Card) -->
            <div class="inline-flex items-center rounded-lg border border-stroke bg-gray-2 p-1 dark:border-strokedark dark:bg-meta-4 shadow-sm">
                <button
                    type="button"
                    @click="viewMode = 'list'"
                    :class="viewMode === 'list' 
                        ? 'bg-white text-primary shadow-sm dark:bg-boxdark dark:text-white font-semibold' 
                        : 'text-body hover:text-black dark:text-bodydark dark:hover:text-white'"
                    class="inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm transition-all duration-150"
                    title="List View"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span>List</span>
                </button>
                <button
                    type="button"
                    @click="viewMode = 'card'"
                    :class="viewMode === 'card' 
                        ? 'bg-white text-primary shadow-sm dark:bg-boxdark dark:text-white font-semibold' 
                        : 'text-body hover:text-black dark:text-bodydark dark:hover:text-white'"
                    class="inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm transition-all duration-150"
                    title="Card View"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span>Card</span>
                </button>
            </div>

            <a href="{{ route('admin.payments.export', ['type' => 'csv', 'status' => $status, 'q' => $q]) }}" class="inline-flex items-center justify-center rounded-md border border-stroke py-2 px-5 text-center font-medium text-black hover:bg-gray-1 dark:border-strokedark dark:text-white dark:hover:bg-meta-4 transition">
                Export CSV
            </a>
            <a href="{{ route('admin.payments.export', ['type' => 'pdf', 'status' => $status, 'q' => $q]) }}" class="inline-flex items-center justify-center rounded-md bg-black py-2 px-5 text-center font-medium text-white hover:bg-opacity-90 transition">
                Export PDF
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 flex w-full border-l-6 border-[#34D399] bg-[#34D399] bg-opacity-[15%] px-7 py-4 shadow-md dark:bg-[#1b1b1b] md:p-5">
            <div class="mr-5 flex h-9 w-full max-w-[36px] items-center justify-center rounded-lg bg-[#34D399]">
                <svg width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15.2984 0.826822L15.2868 0.811827L15.2741 0.797751C14.9173 0.401837 14.3238 0.400754 13.9657 0.794406L5.91888 9.53233L2.02771 5.41173C1.6655 5.02795 1.07402 5.02206 0.704179 5.39847C0.334339 5.77488 0.328449 6.37722 0.69066 6.761L5.24211 11.5956L5.24355 11.5972C5.60196 11.9751 6.18434 11.9744 6.54194 11.5953L15.3035 1.95661C15.6669 1.56494 15.6644 0.957512 15.2984 0.826822Z" fill="white" stroke="white"></path>
                </svg>
            </div>
            <div class="w-full">
                <h5 class="text-lg font-semibold text-black dark:text-[#34D399]">Success</h5>
                <p class="text-base leading-relaxed text-body">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 flex w-full border-l-6 border-[#F87171] bg-[#F87171] bg-opacity-[15%] px-7 py-4 shadow-md dark:bg-[#1b1b1b] md:p-5">
             <div class="mr-5 flex h-9 w-full max-w-[36px] items-center justify-center rounded-lg bg-[#F87171]">
                <svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M6.4917 7.65547L11.106 12.2698C11.4259 12.5897 11.9442 12.5897 12.2641 12.2698C12.584 11.9499 12.584 11.4316 12.2641 11.1117L7.64981 6.49734L12.2641 1.88303C12.584 1.56312 12.584 1.04482 12.2641 0.72491C11.9442 0.404997 11.4259 0.404997 11.106 0.72491L6.4917 5.33922L1.87739 0.72491C1.55748 0.404997 1.03918 0.404997 0.719266 0.72491C0.399353 1.04482 0.399353 1.56312 0.719266 1.88303L5.33357 6.49734L0.719266 11.1117C0.399353 11.4316 0.399353 11.9499 0.719266 12.2698C1.03918 12.5897 1.55748 12.5897 1.87739 12.2698L6.4917 7.65547Z" fill="white"></path>
                </svg>
            </div>
            <div class="w-full">
                <h5 class="text-lg font-semibold text-[#B91C1C] dark:text-[#F87171]">Error</h5>
                <p class="text-base leading-relaxed text-body">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3 md:gap-6 2xl:gap-7.5 mb-6">
        <div class="rounded-sm border border-stroke bg-white py-6 px-7.5 shadow-default dark:border-strokedark dark:bg-boxdark text-center">
            <h4 class="text-title-md font-bold text-warning">{{ $stats['pending'] }}</h4>
            <span class="text-sm font-medium">Pending Requests</span>
        </div>
        <div class="rounded-sm border border-stroke bg-white py-6 px-7.5 shadow-default dark:border-strokedark dark:bg-boxdark text-center">
            <h4 class="text-title-md font-bold text-success">{{ $stats['approved'] }}</h4>
            <span class="text-sm font-medium">Approved</span>
        </div>
        <div class="rounded-sm border border-stroke bg-white py-6 px-7.5 shadow-default dark:border-strokedark dark:bg-boxdark text-center">
            <h4 class="text-title-md font-bold text-danger">{{ $stats['rejected'] }}</h4>
            <span class="text-sm font-medium">Rejected</span>
        </div>
    </div>

    <!-- Filter -->
    <div class="rounded-sm border border-stroke bg-white px-5 pt-6 pb-2.5 shadow-default dark:border-strokedark dark:bg-boxdark sm:px-7.5 mb-6">
        <form method="GET" action="{{ route('admin.payments') }}">
            <input type="hidden" name="view" :value="viewMode">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="q" value="{{ $q }}" placeholder="Search by user, email, method, ref..." class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input">
                </div>
                <div class="w-48">
                    <select name="status" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <button type="submit" class="flex justify-center rounded bg-primary py-2 px-6 font-medium text-gray hover:bg-opacity-90 transition">
                    Filter
                </button>
                @if($status || $q)
                    <a href="{{ route('admin.payments') }}" class="flex justify-center rounded border border-stroke py-2 px-6 font-medium text-black hover:shadow-1 dark:border-strokedark dark:text-white transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- List (Table) View -->
    <div x-show="viewMode === 'list'" x-cloak class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark mb-6">
        <div class="max-w-full overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-2 text-left dark:bg-meta-4">
                        <th class="py-4 px-4 font-medium text-black dark:text-white xl:pl-6">User</th>
                        <th class="py-4 px-4 font-medium text-black dark:text-white">Amount</th>
                        <th class="py-4 px-4 font-medium text-black dark:text-white">Method & Ref</th>
                        <th class="py-4 px-4 font-medium text-black dark:text-white">Date</th>
                        <th class="py-4 px-4 font-medium text-black dark:text-white">Status</th>
                        <th class="py-4 px-4 font-medium text-black dark:text-white text-right pr-4 xl:pr-6">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stroke dark:divide-strokedark">
                    @forelse($requests as $r)
                        @php
                            $canProcess = $r->status === 'pending';
                            $statusBadge = match($r->status) {
                                'approved' => 'bg-success/10 text-success border border-success/20',
                                'pending' => 'bg-warning/10 text-warning border border-warning/20',
                                'rejected' => 'bg-danger/10 text-danger border border-danger/20',
                                default => 'bg-gray/10 text-body border border-stroke'
                            };
                        @endphp
                        <tr class="hover:bg-gray-1 dark:hover:bg-meta-4/20 transition">
                            <td class="py-4 px-4 xl:pl-6">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary font-bold text-sm">
                                        {{ strtoupper(substr($r->user?->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <h5 class="font-semibold text-black dark:text-white text-sm">
                                            {{ $r->user?->name ?? 'Unknown User' }}
                                        </h5>
                                        <p class="text-xs text-body dark:text-bodydark">
                                            {{ $r->user?->email ?: '-' }}
                                        </p>
                                        @if($r->user?->phone)
                                            <p class="text-[11px] text-body dark:text-bodydark">
                                                {{ $r->user->phone }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 whitespace-nowrap">
                                <p class="font-bold text-black dark:text-white text-base">
                                    {{ $currency }}{{ number_format((float) $r->amount, 2) }}
                                </p>
                            </td>
                            <td class="py-4 px-4">
                                <div class="text-xs">
                                    <span class="inline-flex items-center rounded bg-gray-2 dark:bg-meta-4 px-2 py-0.5 font-medium text-black dark:text-white uppercase text-[10px]">
                                        {{ $r->method ?: 'N/A' }}
                                    </span>
                                    <p class="mt-1 font-mono text-body dark:text-bodydark text-xs truncate max-w-[160px]" title="{{ $r->reference }}">
                                        Ref: {{ $r->reference ?: '-' }}
                                    </p>
                                </div>
                            </td>
                            <td class="py-4 px-4 whitespace-nowrap text-xs">
                                <span class="block font-medium text-black dark:text-white">
                                    {{ $r->created_at->format('d M Y') }}
                                </span>
                                <span class="text-body dark:text-bodydark text-[11px]">
                                    {{ $r->created_at->format('h:i A') }}
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                <div>
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusBadge }}">
                                        {{ ucfirst($r->status) }}
                                    </span>
                                    @if($r->admin_note)
                                        <p class="mt-1 text-[11px] text-body italic max-w-[180px] truncate" title="{{ $r->admin_note }}">
                                            Note: {{ $r->admin_note }}
                                        </p>
                                    @endif
                                    @if($r->processed_at)
                                        <p class="mt-0.5 text-[10px] text-body dark:text-bodydark">
                                            {{ $r->processed_at->format('d M, h:i A') }}
                                        </p>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-4 text-right whitespace-nowrap pr-4 xl:pr-6">
                                @if($canProcess)
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" 
                                                @click="openModal('{{ route('admin.payments.approve', $r) }}', 'Approve Payment Request', 'Approve & Recharge', 'bg-success', '{{ $currency }}{{ number_format((float) $r->amount, 2) }}', '{{ addslashes($r->user?->name ?? 'User') }}', 'approve')"
                                                class="inline-flex items-center gap-1 rounded bg-success py-1.5 px-3 text-xs font-medium text-white shadow-sm hover:bg-opacity-90 transition">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span>Approve</span>
                                        </button>
                                        <button type="button" 
                                                @click="openModal('{{ route('admin.payments.reject', $r) }}', 'Reject Payment Request', 'Reject', 'bg-danger', '{{ $currency }}{{ number_format((float) $r->amount, 2) }}', '{{ addslashes($r->user?->name ?? 'User') }}', 'reject')"
                                                class="inline-flex items-center gap-1 rounded bg-danger py-1.5 px-3 text-xs font-medium text-white shadow-sm hover:bg-opacity-90 transition">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            <span>Reject</span>
                                        </button>
                                    </div>
                                @elseif($r->status === 'rejected')
                                    <form method="POST" action="{{ route('admin.payments.reopen', $r) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1 rounded border border-primary/30 px-2.5 py-1 text-xs font-medium text-primary hover:bg-primary hover:text-white transition">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            <span>Reopen</span>
                                        </button>
                                    </form>
                                @else
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" 
                                                @click="openNoteModal('{{ route('admin.payments.update-note', $r) }}', '{{ addslashes($r->user?->name ?? 'User') }}', '{{ $currency }}{{ number_format((float) $r->amount, 2) }}', '{{ addslashes($r->admin_note ?? '') }}')"
                                                class="inline-flex items-center gap-1 rounded border border-stroke px-2.5 py-1 text-xs font-medium text-black hover:bg-gray-1 dark:border-strokedark dark:text-white dark:hover:bg-meta-4 transition"
                                                title="Edit Comment">
                                            <svg class="h-3.5 w-3.5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            <span>Edit Comment</span>
                                        </button>
                                        <span class="inline-flex items-center gap-1 text-xs text-success font-medium">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Recharged
                                        </span>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-body dark:text-bodydark">
                                No payment requests found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Card View -->
    <div x-show="viewMode === 'card'" x-cloak class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-2">
        @forelse($requests as $r)
            @php
                $canProcess = $r->status === 'pending';
                $statusColor = match($r->status) {
                    'approved' => 'text-white bg-success',
                    'pending' => 'text-white bg-warning',
                    'rejected' => 'text-white bg-danger',
                    default => 'text-black'
                };
            @endphp
            <div class="rounded-sm border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
                <div class="mb-4 flex items-start justify-between">
                    <div>
                        <h4 class="mb-1 text-title-sm font-bold text-black dark:text-white">
                            {{ $currency }}{{ number_format((float) $r->amount, 2) }}
                        </h4>
                        <p class="text-sm font-medium">Requested by: <span class="text-black dark:text-white">{{ $r->user?->name }}</span></p>
                        <p class="text-xs">{{ $r->user?->email }} | {{ $r->user?->phone }}</p>
                    </div>
                    <span class="inline-flex rounded-full bg-opacity-10 py-1 px-3 text-sm font-medium {{ $statusColor }} bg-current">
                        {{ ucfirst($r->status) }}
                    </span>
                </div>

                <div class="mb-4 grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <span class="block font-semibold">Method:</span>
                        {{ $r->method ?: '-' }}
                    </div>
                    <div>
                        <span class="block font-semibold">Reference:</span>
                        {{ $r->reference ?: '-' }}
                    </div>
                    <div class="col-span-2">
                        <span class="block font-semibold">Date:</span>
                        {{ $r->created_at->format('d M Y, h:i A') }}
                    </div>
                </div>

                @if($canProcess)
                    <div class="mt-4 flex flex-col gap-3">
                        <form method="POST" action="{{ route('admin.payments.approve', $r) }}">
                            @csrf
                            <input type="text" name="admin_note" placeholder="Approval note..." class="mb-2 w-full rounded border border-stroke bg-transparent py-2 px-4 text-sm outline-none focus:border-primary dark:border-strokedark">
                            <button type="submit" class="w-full rounded bg-success py-2 text-sm font-medium text-white hover:bg-opacity-90 transition">
                                Approve & Recharge
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.payments.reject', $r) }}">
                            @csrf
                            <input type="text" name="admin_note" placeholder="Rejection reason..." class="mb-2 w-full rounded border border-stroke bg-transparent py-2 px-4 text-sm outline-none focus:border-primary dark:border-strokedark">
                            <button type="submit" class="w-full rounded bg-danger py-2 text-sm font-medium text-white hover:bg-opacity-90 transition">
                                Reject
                            </button>
                        </form>
                    </div>
                @else
                    <div class="mt-4 border-t border-stroke pt-4 dark:border-strokedark">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-body dark:text-bodydark">Processed at: {{ $r->processed_at?->format('d M Y, h:i A') ?: '-' }}</span>
                            <button type="button" 
                                    @click="openNoteModal('{{ route('admin.payments.update-note', $r) }}', '{{ addslashes($r->user?->name ?? 'User') }}', '{{ $currency }}{{ number_format((float) $r->amount, 2) }}', '{{ addslashes($r->admin_note ?? '') }}')"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-primary hover:underline">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span>Edit Comment</span>
                            </button>
                        </div>
                        <div class="mt-2 rounded bg-gray-2 p-2.5 text-xs dark:bg-meta-4">
                            <span class="font-semibold text-black dark:text-white">Admin Note:</span>
                            <span class="text-body dark:text-bodydark italic">{{ $r->admin_note ?: 'No comment added.' }}</span>
                        </div>
                        @if($r->status === 'rejected')
                            <form method="POST" action="{{ route('admin.payments.reopen', $r) }}" class="mt-2 text-right">
                                @csrf
                                <button type="submit" class="text-xs text-primary underline hover:text-opacity-80">Reopen Request</button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <div class="col-span-2 rounded-sm border border-stroke bg-white p-10 text-center shadow-default dark:border-strokedark dark:bg-boxdark">
                No payment requests found.
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $requests->links() }}
    </div>

    <!-- Action Modal for List View -->
    <div x-show="modalOpen" 
         x-cloak
         class="fixed inset-0 z-99999 flex items-center justify-center bg-black/50 p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div @click.away="modalOpen = false" 
             class="w-full max-w-lg rounded-lg border border-stroke bg-white p-6 shadow-2xl dark:border-strokedark dark:bg-boxdark"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="mb-4 flex items-center justify-between border-b border-stroke pb-3 dark:border-strokedark">
                <h3 class="text-lg font-bold text-black dark:text-white" x-text="modalTitle"></h3>
                <button type="button" @click="modalOpen = false" class="text-body hover:text-black dark:text-bodydark dark:hover:text-white">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mb-4 rounded-md bg-gray-2 p-3 text-sm dark:bg-meta-4">
                <p class="text-body dark:text-bodydark">
                    User: <strong class="text-black dark:text-white" x-text="modalUser"></strong>
                </p>
                <p class="text-body dark:text-bodydark mt-1">
                    Amount: <strong class="text-black dark:text-white" x-text="modalAmount"></strong>
                </p>
            </div>

            <form method="POST" :action="modalActionUrl">
                @csrf
                <div class="mb-5">
                    <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                        Admin Note (Optional)
                    </label>
                    <input type="text" 
                           name="admin_note" 
                           x-model="modalNote"
                           :placeholder="modalNotePlaceholder" 
                           class="w-full rounded-lg border border-stroke bg-transparent py-2.5 px-4 text-sm font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input">
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button" 
                            @click="modalOpen = false"
                            class="rounded-md border border-stroke py-2 px-5 text-sm font-medium text-black hover:bg-gray-1 dark:border-strokedark dark:text-white dark:hover:bg-meta-4 transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            :class="modalBtnClass"
                            class="rounded-md py-2 px-5 text-sm font-medium text-white hover:bg-opacity-90 shadow-sm transition"
                            x-text="modalBtnText">
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
