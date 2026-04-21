@extends('admin.layout')

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
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            System Penalties History
        </h2>
    </div>

    <!-- Filter Card -->
    <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark mb-6">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">User</label>
                    <select id="user_filter" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-3 outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">Status</label>
                    <select id="status_filter" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-3 outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input">
                        <option value="">All Statuses</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">Start Date</label>
                    <input type="date" id="start_date" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-3 outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">End Date</label>
                    <input type="date" id="end_date" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-3 outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input">
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <button id="apply_filters" class="flex justify-center rounded bg-primary py-2 px-6 font-medium text-white hover:bg-opacity-90">
                    Apply Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table id="penalty-admin-table" class="w-full text-left">
                    <thead>
                        <tr class="border-b border-stroke dark:border-strokedark">
                            <th class="pb-3 text-sm font-medium text-black dark:text-white">Date</th>
                            <th class="pb-3 text-sm font-medium text-black dark:text-white">User</th>
                            <th class="pb-3 text-sm font-medium text-black dark:text-white text-right">Amount</th>
                            <th class="pb-3 text-sm font-medium text-black dark:text-white text-center">Status</th>
                            <th class="pb-3 text-sm font-medium text-black dark:text-white text-center">Reference</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        const table = $('#penalty-admin-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.penalties.history.data') }}",
                data: function(d) {
                    d.user_id = $('#user_filter').val();
                    d.status = $('#status_filter').val();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                }
            },
            columns: [
                { data: 'date', name: 'created_at' },
                { data: 'user', name: 'user_id' },
                { data: 'amount', name: 'amount', className: 'text-right font-bold' },
                { data: 'status', name: 'status', className: 'text-center' },
                { data: 'emi', name: 'emi_schedule_id', className: 'text-center' }
            ],
            order: [[0, 'desc']],
            language: {
                searchPlaceholder: "Search by amount or status...",
                search: ""
            }
        });

        $('#apply_filters').click(function() {
            table.draw();
        });

        $('.dataTables_filter input').addClass('rounded border border-stroke bg-transparent px-4 py-2 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:text-white');
    });
</script>
@endpush
@endsection
