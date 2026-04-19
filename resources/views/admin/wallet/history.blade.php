@extends('admin.layout')

@section('content')
<div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
    <div class="border-b border-stroke py-4 px-6.5 dark:border-strokedark">
        <h3 class="font-medium text-black dark:text-white">
            All Users Wallet History
        </h3>
    </div>

    <!-- Filters -->
    <div class="p-6.5">
        <div class="flex flex-wrap items-end gap-4">
            <div class="w-full sm:w-auto">
                <label class="mb-2.5 block text-black dark:text-white">User</label>
                <select id="filter-user" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-auto">
                <label class="mb-2.5 block text-black dark:text-white">Start Date</label>
                <input type="date" id="filter-start-date" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
            </div>
            <div class="w-full sm:w-auto">
                <label class="mb-2.5 block text-black dark:text-white">End Date</label>
                <input type="date" id="filter-end-date" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
            </div>
            <div class="w-full sm:w-auto">
                <label class="mb-2.5 block text-black dark:text-white">Type</label>
                <select id="filter-type" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
                    <option value="">All Types</option>
                    <option value="credit">Credit</option>
                    <option value="debit">Debit</option>
                </select>
            </div>
            <div class="w-full sm:w-auto">
                <button id="apply-filters" class="flex justify-center rounded bg-primary py-2 px-6 font-medium text-white hover:bg-opacity-90">
                    Apply
                </button>
            </div>
        </div>
    </div>

    <div class="p-6.5">
        <div class="max-w-full overflow-x-auto">
            <table id="admin-wallet-table" class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-2 text-left dark:bg-meta-4">
                        <th class="py-4 px-4 font-medium text-black dark:text-white">Date</th>
                        <th class="py-4 px-4 font-medium text-black dark:text-white">User</th>
                        <th class="py-4 px-4 font-medium text-black dark:text-white text-center">Type</th>
                        <th class="py-4 px-4 font-medium text-black dark:text-white text-center">Source</th>
                        <th class="py-4 px-4 font-medium text-black dark:text-white text-right">Amount</th>
                        <th class="py-4 px-4 font-medium text-black dark:text-white">Description</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <!-- DataTables will populate this -->
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        const table = $('#admin-wallet-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.wallet.history.data') }}",
                data: function(d) {
                    d.user_id = $('#filter-user').val();
                    d.start_date = $('#filter-start-date').val();
                    d.end_date = $('#filter-end-date').val();
                    d.type = $('#filter-type').val();
                }
            },
            columns: [
                { data: 'date', name: 'created_at' },
                { data: 'user', name: 'wallet_id' },
                { data: 'type', name: 'type', className: 'text-center' },
                { data: 'source', name: 'source', className: 'text-center' },
                { data: 'amount', name: 'amount', className: 'text-right font-bold' },
                { data: 'description', name: 'description' }
            ],
            order: [[0, 'desc']],
            language: {
                searchPlaceholder: "Search ID, User, Amount...",
                search: ""
            }
        });

        $('#apply-filters').click(function() {
            table.draw();
        });

        $('.dataTables_filter input').addClass('rounded border border-stroke bg-transparent py-2 px-4 font-medium outline-none focus:border-primary dark:border-strokedark');
    });
</script>
<style>
    .dataTables_wrapper .dataTables_length select {
        padding-right: 2.5rem !important;
        background-color: transparent !important;
        border: 1px solid #dee4ee !important;
        border-radius: 4px !important;
    }
    .dark .dataTables_wrapper .dataTables_length select {
        border-color: #313d4a !important;
        color: white !important;
    }
    .dark .dataTables_wrapper .dataTables_length, 
    .dark .dataTables_wrapper .dataTables_filter, 
    .dark .dataTables_wrapper .dataTables_info, 
    .dark .dataTables_wrapper .dataTables_paginate {
        color: #9ca3af !important;
    }
    .dark table.dataTable tbody tr {
        background-color: #1a222c !important;
        color: #d1d5db !important;
    }
    .dark table.dataTable thead th {
        background-color: #24303f !important;
        border-bottom: 1px solid #313d4a !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.5rem 1rem !important;
        border-radius: 4px !important;
    }
</style>
@endpush
@endsection
