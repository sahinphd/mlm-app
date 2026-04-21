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
        <h2 class="mb-3 text-2xl font-semibold text-gray-800 dark:text-white/90">EMI & Penalty Management</h2>
        <p class="mb-6 text-gray-500 dark:text-gray-400">Manage your credit repayments and track any outstanding penalties.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <!-- EMI Filters Card -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">Order ID</label>
                <input type="text" id="order_id_filter" placeholder="e.g. 101" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">EMI ID</label>
                <input type="text" id="emi_id_filter" placeholder="e.g. 5" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">Start Date</label>
                <input type="date" id="start_date_filter" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">End Date</label>
                <input type="date" id="end_date_filter" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:text-white">
            </div>
        </div>
        <div class="mt-4 flex justify-end">
            <button id="apply_emi_filters" class="rounded-lg bg-primary py-2 px-6 font-medium text-white hover:bg-opacity-90">
                Search / Filter
            </button>
        </div>
    </div>

    <!-- EMI Table Section -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <h3 class="mb-5 text-xl font-semibold text-gray-800 dark:text-white/90">EMI Schedule</h3>
        
        <div class="overflow-x-auto">
            <table id="emi-table" class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Due Date</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Order</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">EMI ID</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400 text-right">Amount</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400 text-center">Status</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                </tbody>
            </table>
        </div>
    </div>

    <!-- Penalties Section -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <h3 class="mb-5 text-xl font-semibold text-gray-800 dark:text-white/90">Recent Penalties</h3>
        
        <div class="overflow-x-auto">
            <table id="penalty-mini-table" class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Date</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Amount</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Status</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Reference</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
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
        const emiTable = $('#emi-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('credit.emis.data') }}",
                data: function(d) {
                    d.order_id = $('#order_id_filter').val();
                    d.emi_id = $('#emi_id_filter').val();
                    d.start_date = $('#start_date_filter').val();
                    d.end_date = $('#end_date_filter').val();
                }
            },
            columns: [
                { data: 'due_date', name: 'due_date' },
                { data: 'order_id', name: 'order_id' },
                { data: 'emi_id', name: 'id' },
                { data: 'amount', name: 'installment_amount', className: 'text-right font-bold' },
                { data: 'status', name: 'status', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[0, 'asc']],
            language: {
                searchPlaceholder: "Search EMI/Order ID...",
                search: ""
            }
        });

        $('#apply_emi_filters').click(function() {
            emiTable.draw();
        });

        $('#penalty-mini-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('credit.penalties.history.data') }}",
            columns: [
                { data: 'date', name: 'created_at' },
                { data: 'amount', name: 'amount', className: 'font-bold' },
                { data: 'status', name: 'status' },
                { data: 'emi', name: 'emi_schedule_id' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[0, 'desc']],
            pageLength: 5,
            lengthMenu: [5, 10, 25],
            language: {
                searchPlaceholder: "Search penalties...",
                search: ""
            }
        });

        $('.dataTables_filter input').addClass('rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:text-white');
    });
</script>
@endpush
@endsection
