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
            <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                @csrf
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
