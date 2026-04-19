@extends('admin.layout')

@section('content')
<div class="mx-auto max-w-270">
    <!-- Breadcrumb -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            Edit User: {{ $user->name }}
        </h2>
    </div>

    <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="border-b border-stroke py-4 px-7 dark:border-strokedark">
            <h3 class="font-medium text-black dark:text-white">User Information</h3>
        </div>
        <div class="p-7">
            <form action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-5.5 flex items-center gap-4">
                    <div class="h-14 w-14 rounded-full overflow-hidden">
                        <img src="{{ $user->avatar_url }}" alt="User" class="h-full w-full object-cover" id="avatar-preview">
                    </div>
                    <div>
                        <label class="mb-3 block text-sm font-medium text-black dark:text-white">User Avatar (Max 10 KB)</label>
                        <input type="file" name="avatar" accept="image/*" class="w-full cursor-pointer rounded-lg border-[1.5px] border-stroke bg-transparent font-medium outline-none transition file:mr-5 file:border-collapse file:cursor-pointer file:border-0 file:border-r file:border-solid file:border-stroke file:bg-whiter file:py-2 file:px-5 file:hover:bg-primary file:hover:bg-opacity-10 focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:file:border-form-strokedark dark:file:bg-white/10 dark:focus:border-primary">
                        @error('avatar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mb-5.5 flex flex-col gap-5.5 sm:flex-row">
                    <div class="w-full sm:w-1/2">
                        <label class="mb-3 block text-sm font-medium text-black dark:text-white">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="w-full sm:w-1/2">
                        <label class="mb-3 block text-sm font-medium text-black dark:text-white">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mb-5.5 flex flex-col gap-5.5 sm:flex-row">
                    <div class="w-full sm:w-1/2">
                        <label class="mb-3 block text-sm font-medium text-black dark:text-white">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="w-full sm:w-1/2">
                        <label class="mb-3 block text-sm font-medium text-black dark:text-white">Role</label>
                        <select name="role" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                            <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>User</option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mb-5.5 flex flex-col gap-5.5 sm:flex-row">
                    <div class="w-full sm:w-1/2">
                        <label class="mb-3 block text-sm font-medium text-black dark:text-white">Status</label>
                        <select name="status" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                            <option value="pending" {{ old('status', $user->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="blocked" {{ old('status', $user->status) === 'blocked' ? 'selected' : '' }}>Blocked</option>
                        </select>
                        @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="w-full sm:w-1/2">
                        <label class="mb-3 block text-sm font-medium text-black dark:text-white">Password (leave blank to keep current)</label>
                        <input type="password" name="password" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mb-5.5 border-t border-stroke pt-5.5 dark:border-strokedark">
                    <h4 class="mb-4 text-lg font-semibold text-black dark:text-white">Credit Management</h4>
                    <div class="flex flex-col gap-5.5 sm:flex-row">
                        <div class="w-full sm:w-1/2">
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Credit Limit</label>
                            <input type="number" step="0.01" name="credit_limit" value="{{ old('credit_limit', $user->creditAccount->credit_limit ?? 5000) }}" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                            @error('credit_limit') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="w-full sm:w-1/2">
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Credit Approval Status</label>
                            <select name="credit_approval" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                                <option value="pending" {{ old('credit_approval', $user->creditAccount->approval_status ?? 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ old('credit_approval', $user->creditAccount->approval_status ?? 'pending') === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ old('credit_approval', $user->creditAccount->approval_status ?? 'pending') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                            @error('credit_approval') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4.5">
                    <a href="{{ route('admin.users') }}" class="flex justify-center rounded border border-stroke py-2 px-6 font-medium text-black hover:shadow-1 dark:border-strokedark dark:text-white">
                        Cancel
                    </a>
                    <button type="submit" class="flex justify-center rounded bg-primary py-2 px-6 font-medium text-gray hover:bg-opacity-90">
                        Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelector('input[name="avatar"]').addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(ex) {
                document.getElementById('avatar-preview').src = ex.target.result;
            }
            reader.readAsDataURL(e.target.files[0]);
        }
    });
</script>
@endpush
