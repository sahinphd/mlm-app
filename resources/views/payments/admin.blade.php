@extends('admin.layout')

@section('content')
<div class="w-full">
    <!-- Breadcrumb -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            Wallet Top-up Requests (Total: {{ $requests->total() }})
        </h2>

        <div class="flex items-center gap-3">
            <a href="{{ route('payments.admin.export', ['type' => 'csv', 'status' => $status, 'q' => $q]) }}" class="inline-flex items-center justify-center rounded-md border border-stroke py-2 px-6 text-center font-medium text-black hover:bg-opacity-90 dark:border-strokedark dark:text-white lg:px-8 xl:px-10">
                Export CSV
            </a>
            <a href="{{ route('payments.admin.export', ['type' => 'pdf', 'status' => $status, 'q' => $q]) }}" class="inline-flex items-center justify-center rounded-md bg-black py-2 px-6 text-center font-medium text-white hover:bg-opacity-90 lg:px-8 xl:px-10">
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
        <form method="GET" action="{{ route('payments.admin') }}">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="q" value="{{ $q }}" placeholder="Search by user, email, method..." class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input">
                </div>
                <div class="w-48">
                    <select name="status" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <button type="submit" class="flex justify-center rounded bg-primary py-2 px-6 font-medium text-gray hover:bg-opacity-90">
                    Filter
                </button>
                @if($status || $q)
                    <a href="{{ route('payments.admin') }}" class="flex justify-center rounded border border-stroke py-2 px-6 font-medium text-black hover:shadow-1 dark:border-strokedark dark:text-white transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Requests List -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-2">
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
                            ৳{{ number_format((float) $r->amount, 2) }}
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
                        <form method="POST" action="{{ route('payments.admin.approve', $r) }}">
                            @csrf
                            <input type="text" name="admin_note" placeholder="Approval note..." class="mb-2 w-full rounded border border-stroke bg-transparent py-2 px-4 text-sm outline-none focus:border-primary dark:border-strokedark">
                            <button type="submit" class="w-full rounded bg-success py-2 text-sm font-medium text-white hover:bg-opacity-90 transition">
                                Approve & Recharge
                            </button>
                        </form>
                        <form method="POST" action="{{ route('payments.admin.reject', $r) }}">
                            @csrf
                            <input type="text" name="admin_note" placeholder="Rejection reason..." class="mb-2 w-full rounded border border-stroke bg-transparent py-2 px-4 text-sm outline-none focus:border-primary dark:border-strokedark">
                            <button type="submit" class="w-full rounded bg-danger py-2 text-sm font-medium text-white hover:bg-opacity-90 transition">
                                Reject
                            </button>
                        </form>
                    </div>
                @else
                    <div class="mt-4 border-t border-stroke pt-4 dark:border-strokedark">
                        <p class="text-xs">Processed at: {{ $r->processed_at?->format('d M Y, h:i A') ?: '-' }}</p>
                        @if($r->admin_note)
                            <p class="mt-1 text-xs italic">Note: {{ $r->admin_note }}</p>
                        @endif
                        @if($r->status === 'rejected')
                            <form method="POST" action="{{ route('payments.admin.reopen', $r) }}" class="mt-2 text-right">
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
</div>
@endsection
