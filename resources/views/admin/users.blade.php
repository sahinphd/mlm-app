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

    <div class="bg-white rounded shadow p-4">
        <table id="usersTable" class="min-w-full divide-y" style="width:100%">
            <thead>
                <tr>
                    <th class="px-3 py-2 text-left">ID</th>
                    <th class="px-3 py-2 text-left">Avatar</th>
                    <th class="px-3 py-2 text-left">Name</th>
                    <th class="px-3 py-2 text-left">Email</th>
                    <th class="px-3 py-2 text-left">Phone</th>
                    <th class="px-3 py-2 text-left">Role</th>
                    <th class="px-3 py-2 text-left">Status</th>
                    <th class="px-3 py-2 text-left">Created</th>
                    <th class="px-3 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- server-side fallback rows (also used by DataTables AJAX) -->
                @if(!empty($users) && $users->count())
                    @foreach($users as $u)
                        <tr>
                            <td class="px-3 py-2">{{ $u->id }}</td>
                            <td class="px-3 py-2"><img src="{{ $u->avatar_url }}" alt="avatar" class="w-8 h-8 rounded-full" /></td>
                            <td class="px-3 py-2">{{ $u->name }}</td>
                            <td class="px-3 py-2"><a href="mailto:{{ $u->email }}" class="text-blue-600">{{ $u->email }}</a></td>
                            <td class="px-3 py-2">{{ $u->phone ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $u->role ?? 'user' }}</td>
                            <td class="px-3 py-2">
                                @if($u->status === 'active')
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-success-50 text-success-600">Active</span>
                                @elseif($u->status === 'pending')
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-warning-50 text-warning-600">Pending</span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-danger-50 text-danger-600">Blocked</span>
                                @endif
                            </td>
                            <td class="px-3 py-2">{{ $u->created_at?->format('Y-m-d') }}</td>
                            <td class="px-3 py-2"> <a href="/admin/users/{{ $u->id }}/edit" class="text-sm text-blue-600 mr-2">Edit</a> <a href="#" data-id="{{ $u->id }}" class="text-sm text-red-600 js-delete">Delete</a></td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->appends(request()->query())->links() }}
    </div>
</div>

<!-- DataTables CDN -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function(){
        // Use client-side DataTables on the already-rendered table so rows
        // seeded by the server are visible immediately. Server-side AJAX
        // mode can be re-enabled later if desired.
        const table = $('#usersTable').DataTable({
            paging: false,
            searching: true,
            info: false,
            order: [[0,'desc']]
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
                            ).then(() => {
                                window.location.reload(); 
                            });
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
