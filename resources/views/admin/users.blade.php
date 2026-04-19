@extends('admin.layout')

@section('content')
<div class="container mx-auto p-4">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            Users
        </h2>
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center justify-center rounded-md bg-primary py-2 px-6 text-center font-medium text-white hover:bg-opacity-90 lg:px-8 xl:px-10">
            Create New User
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 flex w-full border-l-6 border-[#34D399] bg-[#34D399] bg-opacity-[15%] px-7 py-4 shadow-md dark:bg-[#1b1b1b] md:p-5">
            <div class="mr-5 flex h-9 w-full max-w-[36px] items-center justify-center rounded-lg bg-[#34D399]">
                <svg width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15.2984 0.826822L15.2868 0.811827L15.2741 0.797751C14.9173 0.401837 14.3238 0.400754 13.9657 0.794406L5.91888 9.53233L2.02771 5.41173C1.6655 5.02795 1.07402 5.02206 0.704179 5.39847C0.334339 5.77488 0.328449 6.37722 0.69066 6.761L5.24211 11.5956L5.24355 11.5972C5.60196 11.9751 6.18434 11.9744 6.54194 11.5953L15.3035 1.95661C15.6669 1.56494 15.6644 0.957512 15.2984 0.826822Z" fill="white" stroke="white"></path>
                </svg>
            </div>
            <div class="w-full">
                <p class="text-base leading-relaxed text-body">
                    {{ session('success') }}
                </p>
            </div>
        </div>
    @endif

    <div class="bg-white rounded shadow p-4 dark:bg-boxdark">
        <div class="max-w-full overflow-x-auto">
            <table id="usersTable" class="min-w-full divide-y border-collapse" style="width:100%">
                <thead>
                    <tr class="bg-gray-2 text-left dark:bg-meta-4">
                        <th class="px-3 py-4 font-medium text-black dark:text-white">ID</th>
                        <th class="px-3 py-4 font-medium text-black dark:text-white">Avatar</th>
                        <th class="px-3 py-4 font-medium text-black dark:text-white">Name</th>
                        <th class="px-3 py-4 font-medium text-black dark:text-white">Email</th>
                        <th class="px-3 py-4 font-medium text-black dark:text-white">Role</th>
                        <th class="px-3 py-4 font-medium text-black dark:text-white">Status</th>
                        <th class="px-3 py-4 font-medium text-black dark:text-white text-right">Credit Limit</th>
                        <th class="px-3 py-4 font-medium text-black dark:text-white text-center">Credit Status</th>
                        <th class="px-3 py-4 font-medium text-black dark:text-white">Created</th>
                        <th class="px-3 py-4 font-medium text-black dark:text-white">Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- DataTables CDN -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
<style>
    .dataTables_wrapper .dataTables_length select {
        @apply bg-transparent border border-stroke rounded px-2 py-1 dark:border-strokedark dark:text-white;
    }
    .dataTables_wrapper .dataTables_filter input {
        @apply bg-transparent border border-stroke rounded px-3 py-1 ml-2 dark:border-strokedark dark:text-white;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        @apply border-0 bg-transparent text-body dark:text-bodydark;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        @apply bg-primary text-white border-primary rounded;
    }
    .dataTables_wrapper .dataTables_info {
        @apply text-body dark:text-bodydark;
    }
    table.dataTable.no-footer {
        border-bottom: 1px solid #e2e8f0;
    }
    .dark table.dataTable.no-footer {
        border-bottom: 1px solid #2e3a47;
    }
    table.dataTable thead th {
        border-bottom: 1px solid #e2e8f0;
    }
    .dark table.dataTable thead th {
        border-bottom: 1px solid #2e3a47;
    }
    /* Responsive adjustment */
    #usersTable {
        width: 100% !important;
    }
    /* Tooltip container needs to be visible over table */
    #usersTable td {
        overflow: visible !important;
    }
    .dataTables_scrollBody {
        overflow: visible !important;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function(){
        const table = $('#usersTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: "{{ route('admin.users.data') }}",
            columns: [
                { data: 0, name: 'id' },
                { data: 1, name: 'avatar', orderable: false, searchable: false },
                { data: 2, name: 'name' },
                { data: 3, name: 'email' },
                { data: 4, name: 'role' },
                { data: 5, name: 'status' },
                { data: 6, name: 'credit_limit', className: 'text-right' },
                { data: 7, name: 'credit_status', className: 'text-center' },
                { data: 8, name: 'created_at' },
                { data: 9, name: 'actions', orderable: false, searchable: false }
            ],
            order: [[0, 'desc']],
            pageLength: 25,
            language: {
                searchPlaceholder: "Search users...",
                search: ""
            },
            drawCallback: function() {
                $('.dataTables_paginate > .paginate_button').addClass('px-3 py-1');
            }
        });

        // Handle avatar click for info popup
        $('#usersTable').on('click', '.js-user-info', function() {
            const data = $(this).data();
            Swal.fire({
                title: `<div class="text-xl font-bold text-black dark:text-white">${data.name}</div>`,
                html: `
                    <div class="flex flex-col items-center justify-center space-y-4 py-4 text-left">
                        <img src="${data.avatar}" class="w-24 h-24 rounded-full border-4 border-primary shadow-lg mb-2" />
                        <div class="w-full bg-gray-50 dark:bg-meta-4 rounded-lg p-4 space-y-3">
                            <div class="flex justify-between border-b border-stroke dark:border-strokedark pb-2">
                                <span class="font-semibold text-gray-600 dark:text-gray-300">Phone:</span>
                                <span class="text-black dark:text-white font-medium">${data.phone}</span>
                            </div>
                            <div class="flex justify-between border-b border-stroke dark:border-strokedark pb-2">
                                <span class="font-semibold text-gray-600 dark:text-gray-300">Referred By:</span>
                                <span class="text-black dark:text-white font-medium">${data.refBy}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-semibold text-gray-600 dark:text-gray-300">Referral Code:</span>
                                <span class="text-black dark:text-white font-mono bg-white dark:bg-boxdark px-2 rounded border border-stroke dark:border-strokedark">${data.refCode}</span>
                            </div>
                        </div>
                    </div>
                `,
                showConfirmButton: true,
                confirmButtonText: 'Close',
                confirmButtonColor: '#3C50E0',
                background: document.documentElement.classList.contains('dark') ? '#24303F' : '#fff',
                color: document.documentElement.classList.contains('dark') ? '#fff' : '#000'
            });
        });

        // handle delete
        $('#usersTable').on('click', '.js-delete', function(e){
            e.preventDefault();
            const id = $(this).data('id');
            
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this! All user data including commissions and wallet will be lost.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/users/' + id,
                        method: 'POST',
                        data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                        success(){ 
                            Swal.fire(
                                'Deleted!',
                                'User has been deleted.',
                                'success'
                            );
                            table.ajax.reload();
                        },
                        error(){ 
                            Swal.fire(
                                'Error!',
                                'Delete failed. This user might have active orders or dependencies.',
                                'error'
                            );
                        }
                    });
                }
            });
        });
    });
</script>

@endsection
