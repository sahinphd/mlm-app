@extends('admin.layout')

@section('content')
<div class="mx-auto max-w-270">
    <!-- Breadcrumb -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            Create New User
        </h2>
    </div>

    @if(session('error'))
        <div class="mb-6 flex w-full border-l-6 border-[#F87171] bg-[#F87171] bg-opacity-[15%] px-7 py-4 shadow-md dark:bg-[#1b1b1b] md:p-5">
            <div class="w-full">
                <h5 class="text-lg font-semibold text-[#F87171]">Error</h5>
                <p class="text-base leading-relaxed text-body">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="border-b border-stroke py-4 px-7 dark:border-strokedark">
            <h3 class="font-medium text-black dark:text-white">User Information</h3>
        </div>
        <div class="p-7">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="mb-5.5 flex flex-col gap-5.5 sm:flex-row">
                    <div class="w-full sm:w-1/2">
                        <label class="mb-3 block text-sm font-medium text-black dark:text-white">Full Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="w-full sm:w-1/2">
                        <label class="mb-3 block text-sm font-medium text-black dark:text-white">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mb-5.5 flex flex-col gap-5.5 sm:flex-row">
                    <div class="w-full sm:w-1/2">
                        <label class="mb-3 block text-sm font-medium text-black dark:text-white">Password</label>
                        <input type="password" name="password" required class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="w-full sm:w-1/2">
                        <label class="mb-3 block text-sm font-medium text-black dark:text-white">Role</label>
                        <select name="role" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                            <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>User</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mb-5.5 flex flex-col gap-5.5 sm:flex-row">
                    <div class="w-full sm:w-1/2">
                        <label class="mb-3 block text-sm font-medium text-black dark:text-white">Upline / Referral Code</label>
                        <div class="relative">
                            <input type="text" id="referral_search" placeholder="Search upline by name, email or code..." class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                            <input type="hidden" name="parent_id" id="parent_id_hidden" value="{{ old('parent_id') }}">
                            <div id="referral_results" class="absolute z-10 w-full bg-white dark:bg-boxdark border border-stroke dark:border-strokedark rounded-b shadow-lg hidden max-h-60 overflow-y-auto"></div>
                        </div>
                        <p id="selected_referral_info" class="mt-2 text-sm text-gray-500 {{ old('parent_id') ? '' : 'hidden' }}">
                            Selected Upline: <span class="font-bold text-primary"></span>
                        </p>
                        @error('parent_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="w-full sm:w-1/2">
                        <label class="mb-3 block text-sm font-medium text-black dark:text-white">Initial Status</label>
                        <select name="status" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                            <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="blocked" {{ old('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                        </select>
                        @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-4.5">
                    <a href="{{ route('admin.users') }}" class="flex justify-center rounded border border-stroke py-2 px-6 font-medium text-black hover:shadow-1 dark:border-strokedark dark:text-white">
                        Cancel
                    </a>
                    <button type="submit" class="flex justify-center rounded bg-primary py-2 px-6 font-medium text-gray hover:bg-opacity-90">
                        Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        const $input = $('#referral_search');
        const $results = $('#referral_results');
        const $hidden = $('#parent_id_hidden');
        const $info = $('#selected_referral_info');
        let timeout = null;

        $input.on('keyup', function() {
            clearTimeout(timeout);
            const query = $(this).val();
            if (query.length < 2) {
                $results.hide();
                return;
            }

            timeout = setTimeout(function() {
                $.ajax({
                    url: "{{ route('admin.users.search') }}",
                    data: { q: query },
                    success: function(data) {
                        let html = '';
                        data.forEach(user => {
                            html += `<div class="p-3 hover:bg-gray-100 dark:hover:bg-meta-4 cursor-pointer border-b border-stroke dark:border-strokedark last:border-0" 
                                data-id="${user.id}" data-name="${user.name}" data-code="${user.referral_code}">
                                <div class="flex justify-between items-center">
                                    <div class="font-bold text-black dark:text-white">${user.name}</div>
                                    <div class="text-[10px] bg-primary/10 text-primary px-1.5 rounded">${user.referral_code}</div>
                                </div>
                                <div class="text-xs text-gray-500">${user.email}</div>
                            </div>`;
                        });
                        if (html) {
                            $results.html(html).show();
                        } else {
                            $results.hide();
                        }
                    }
                });
            }, 300);
        });

        $results.on('click', 'div', function() {
            const name = $(this).data('name');
            const id = $(this).data('id');
            
            $input.val('');
            $hidden.val(id);
            $info.find('span').text(`${name} (#${id})`);
            $info.removeClass('hidden');
            $results.hide();
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#referral_search').length && !$(e.target).closest('#referral_results').length) {
                $results.hide();
            }
        });
    });
</script>
@endpush
@endsection
