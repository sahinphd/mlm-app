@extends('admin.layout')

@section('content')
<div class="mx-auto">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            All Users Credit History
        </h2>
    </div>

    <!-- Filter & Search (Inspired by Commissions Page) -->
    <div class="rounded-sm border border-stroke bg-white px-5 pt-6 pb-2.5 shadow-default dark:border-strokedark dark:bg-boxdark sm:px-7.5 mb-6">
        <div class="flex flex-wrap items-center gap-4">
            <!-- User Search Autocomplete -->
            <div class="flex-1 min-w-[250px] relative">
                <label class="mb-1 block text-sm font-medium text-black dark:text-white">Search User</label>
                <input type="text" id="user_search" placeholder="Name, Email or ID..." autocomplete="off" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
                <input type="hidden" id="filter-user" value="">
                <div id="search_results" class="absolute z-50 w-full bg-white dark:bg-boxdark border border-stroke dark:border-strokedark rounded-b shadow-lg hidden max-h-60 overflow-y-auto"></div>
            </div>

            <div class="w-44">
                <label class="mb-1 block text-sm font-medium text-black dark:text-white">Type</label>
                <select id="filter-type" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
                    <option value="">All Types</option>
                    <option value="credit">Repayment/Limit</option>
                    <option value="debit">Usage</option>
                </select>
            </div>

            <div class="w-44">
                <label class="mb-1 block text-sm font-medium text-black dark:text-white">Start Date</label>
                <input type="date" id="filter-start-date" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
            </div>

            <div class="w-44">
                <label class="mb-1 block text-sm font-medium text-black dark:text-white">End Date</label>
                <input type="date" id="filter-end-date" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
            </div>

            <div class="flex items-end gap-2 pt-6">
                <button id="apply-filters" class="flex justify-center rounded bg-primary py-2 px-6 font-medium text-gray hover:bg-opacity-90 transition">
                    Filter
                </button>
                <button id="reset-filters" class="flex justify-center rounded border border-stroke py-2 px-6 font-medium text-black hover:shadow-1 dark:border-strokedark dark:text-white transition">
                    Reset
                </button>
            </div>
        </div>
    </div>

    <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="max-w-full overflow-x-auto">
            <table id="admin-credit-table" class="w-full table-auto">
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
        const table = $('#admin-credit-table').DataTable({
            processing: true,
            serverSide: true,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            ajax: {
                url: "{{ route('admin.credit.history.data') }}",
                data: function(d) {
                    d.user_id = $('#filter-user').val();
                    d.start_date = $('#filter-start-date').val();
                    d.end_date = $('#filter-end-date').val();
                    d.type = $('#filter-type').val();
                }
            },
            columns: [
                { data: 'date', name: 'created_at' },
                { data: 'user', name: 'credit_account_id' },
                { data: 'type', name: 'type', className: 'text-center' },
                { data: 'source', name: 'source', className: 'text-center' },
                { data: 'amount', name: 'amount', className: 'text-right font-bold' },
                { data: 'description', name: 'description' }
            ],
            order: [[0, 'desc']],
            language: {
                searchPlaceholder: "Search description, amount...",
                search: ""
            },
            drawCallback: function() {
                $('.dataTables_paginate > .paginate_button').addClass('px-3 py-1 border rounded-md mx-1 hover:bg-gray-100 dark:hover:bg-gray-800 transition');
            }
        });

        // Autocomplete Logic (Matching Commission Page Style)
        const $searchInput = $('#user_search');
        const $resultsDiv = $('#search_results');
        const $userIdInput = $('#filter-user');
        let timeout = null;

        $searchInput.on('keyup', function() {
            clearTimeout(timeout);
            const query = $(this).val();
            if (query.length < 2) {
                if(!query) $userIdInput.val('');
                $resultsDiv.hide();
                return;
            }

            timeout = setTimeout(function() {
                $.ajax({
                    url: "{{ route('admin.users.search') }}",
                    data: { q: query },
                    success: function(data) {
                        let html = '';
                        data.forEach(user => {
                            html += `<div class="p-2 hover:bg-gray-100 dark:hover:bg-meta-4 cursor-pointer border-b border-stroke dark:border-strokedark last:border-0" data-id="${user.id}" data-name="${user.name}">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="font-bold text-black dark:text-white">${user.name}</span>
                                    <span class="text-[10px] bg-gray-200 dark:bg-meta-4 px-1.5 rounded text-gray-600 dark:text-gray-400">ID: ${user.id}</span>
                                </div>
                                <div class="text-xs text-gray-500 flex flex-col">
                                    <span>${user.email}</span>
                                </div>
                            </div>`;
                        });
                        if (html) $resultsDiv.html(html).show();
                        else $resultsDiv.hide();
                    }
                });
            }, 300);
        });

        $resultsDiv.on('click', 'div[data-id]', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            $searchInput.val(name);
            $userIdInput.val(id);
            $resultsDiv.hide();
            table.draw();
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#user_search').length && !$(e.target).closest('#search_results').length) {
                $resultsDiv.hide();
            }
        });

        $('#apply-filters').click(function() {
            table.draw();
        });

        $('#reset-filters').click(function() {
            $('#filter-user').val('');
            $('#user_search').val('');
            $('#filter-start-date').val('');
            $('#filter-end-date').val('');
            $('#filter-type').val('');
            table.draw();
        });

        // Styling for DataTables Search Input
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
        background-color: transparent !important;
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
