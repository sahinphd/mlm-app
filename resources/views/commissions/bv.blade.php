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
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 text-center">
        <h2 class="mb-3 text-2xl font-semibold text-gray-800 dark:text-white/90">My BV Commissions</h2>
        <p class="mb-6 text-gray-500 dark:text-gray-400">Track all your earnings from Business Volume (BV) points.</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl mx-auto">
            <div class="rounded-2xl border border-gray-100 bg-brand-50/50 p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <h4 class="text-sm font-medium text-gray-500 mb-1">Total Earning Balance</h4>
                <p class="text-2xl font-bold text-brand-600 dark:text-white">₹{{ number_format(auth()->user()->wallet->earning_balance ?? 0, 2) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-green-50/50 p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <h4 class="text-sm font-medium text-gray-500 mb-1">My Referral Count</h4>
                <p class="text-2xl font-bold text-green-600 dark:text-white">{{ auth()->user()->referredUsers()->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Commissions Table Section -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <h3 class="mb-5 text-xl font-semibold text-gray-800 dark:text-white/90">BV Commission History</h3>
        
        <div class="overflow-x-auto">
            <table id="bv-commissions-table" class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">From User</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400 text-center">Level</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400 text-center">Type</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400 text-right">Amount</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400 text-right">Date</th>
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
        $('#bv-commissions-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('commissions.data', ['type' => 'bv']) }}",
            columns: [
                { data: 'from_user', name: 'from_user' },
                { data: 'level', name: 'level', className: 'text-center' },
                { data: 'type', name: 'type', className: 'text-center' },
                { data: 'amount', name: 'amount', className: 'text-right font-bold' },
                { data: 'date', name: 'created_at', className: 'text-right' }
            ],
            order: [[4, 'desc']],
            language: {
                searchPlaceholder: "Search BV commissions...",
                search: ""
            },
            drawCallback: function() {
                $('.dataTables_paginate > .paginate_button').addClass('px-3 py-1 border rounded-md mx-1 hover:bg-gray-100 dark:hover:bg-gray-800');
            }
        });
        
        // Custom styling for search input
        $('.dataTables_filter input').addClass('rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:text-white');
    });
</script>
@endpush
@endsection
