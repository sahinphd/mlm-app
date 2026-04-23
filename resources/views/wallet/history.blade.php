@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<style>
    .dataTables_wrapper .dataTables_length select {
        padding-right: 2rem !important;
    }
    .dark .dataTables_wrapper .dataTables_length, 
    .dark .dataTables_wrapper .dataTables_filter, 
    .dark .dataTables_wrapper .dataTables_info, 
    .dark .dataTables_wrapper .dataTables_processing, 
    .dark .dataTables_wrapper .dataTables_paginate {
        color: #9ca3af !important;
    }
    .dark table.dataTable tbody tr {
        background-color: transparent !important;
        color: #d1d5db !important;
    }
    .dark table.dataTable thead th {
        border-bottom: 1px solid #374151 !important;
    }
    .dark .dataTables_wrapper .dataTables_paginate .paginate_button {
        color: #9ca3af !important;
    }
</style>
@endpush

@section('content')
<div class="grid grid-cols-1 gap-4 md:gap-6">
    @if(session('success'))
        <div class="rounded-lg bg-green-50 p-4 text-sm text-green-600 dark:bg-green-500/10 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-lg bg-red-50 p-4 text-sm text-red-600 dark:bg-red-500/10 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 text-center">
        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <div class="text-left mb-4 md:mb-0">
                <h2 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Wallet History</h2>
                <p class="text-gray-500 dark:text-gray-400">View all your wallet transactions, including credits and debits.</p>
            </div>
            <a href="{{ route('wallet.transfer') }}" class="flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                Transfer Balance
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-5xl mx-auto">
            <div class="rounded-2xl border border-gray-100 bg-brand-50/50 p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <h4 class="text-sm font-medium text-gray-500 mb-1">Main Balance</h4>
                <p class="text-2xl font-bold text-brand-600 dark:text-white">₹{{ number_format($wallet->main_balance ?? 0, 2) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-blue-50/50 p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <h4 class="text-sm font-medium text-gray-500 mb-1">Joining Earnings</h4>
                <p class="text-2xl font-bold text-blue-600 dark:text-white">₹{{ number_format($joiningEarnings ?? 0, 2) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-orange-50/50 p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <h4 class="text-sm font-medium text-gray-500 mb-1">Repurchase Earnings</h4>
                <p class="text-2xl font-bold text-orange-600 dark:text-white">₹{{ number_format($repurchaseEarnings ?? 0, 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Transactions Table Section -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <h3 class="mb-5 text-xl font-semibold text-gray-800 dark:text-white/90">Transaction Log</h3>
        
        <div class="overflow-x-auto">
            <table id="wallet-table" class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Date</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400 text-center">Type</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400 text-center">Source</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400 text-right">Amount</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                    <!-- DataTables will populate this -->
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#wallet-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('wallet.history.data') }}",
            columns: [
                { data: 'date', name: 'created_at' },
                { data: 'type', name: 'type', className: 'text-center' },
                { data: 'source', name: 'source', className: 'text-center' },
                { data: 'amount', name: 'amount', className: 'text-right font-bold' },
                { data: 'description', name: 'description' }
            ],
            order: [[0, 'desc']],
            language: {
                searchPlaceholder: "Search transactions...",
                search: ""
            },
            drawCallback: function() {
                $('.dataTables_paginate > .paginate_button').addClass('px-3 py-1 border rounded-md mx-1 hover:bg-gray-100 dark:hover:bg-gray-800');
            }
        });
        
        $('.dataTables_filter input').addClass('rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:text-white');
    });
</script>
@endpush
@endsection
