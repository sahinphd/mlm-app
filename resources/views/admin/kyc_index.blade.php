@extends('admin.layout')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
    <!-- Breadcrumb -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            KYC Approval Requests
        </h2>
    </div>

    <div class="mb-5 flex items-center gap-4">
        <a href="{{ route('admin.kyc.index', ['status' => 'pending']) }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ $status === 'pending' ? 'bg-primary text-white' : 'bg-white border border-stroke text-black hover:bg-gray-50' }}">
            Pending Requests
        </a>
        <a href="{{ route('admin.kyc.index', ['status' => 'approved']) }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ $status === 'approved' ? 'bg-success text-white' : 'bg-white border border-stroke text-black hover:bg-gray-50' }}">
            Approved
        </a>
        <a href="{{ route('admin.kyc.index', ['status' => 'rejected']) }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ $status === 'rejected' ? 'bg-danger text-white' : 'bg-white border border-stroke text-black hover:bg-gray-50' }}">
            Rejected
        </a>
    </div>

    <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="max-w-full overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-2 text-left dark:bg-meta-4">
                        <th class="py-4 px-4 font-medium text-black dark:text-white">User</th>
                        <th class="py-4 px-4 font-medium text-black dark:text-white">Submitted Details</th>
                        <th class="py-4 px-4 font-medium text-black dark:text-white">Status</th>
                        <th class="py-4 px-4 font-medium text-black dark:text-white">Date</th>
                        <th class="py-4 px-4 font-medium text-black dark:text-white text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full overflow-hidden">
                                    <img src="{{ $user->avatar_url }}" alt="User">
                                </div>
                                <div>
                                    <p class="font-medium text-black dark:text-white">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            <div class="text-xs space-y-1">
                                <p><span class="font-bold">Aadhaar:</span> {{ $user->aadhaar_number ?: 'N/A' }}</p>
                                <p><span class="font-bold">PAN:</span> {{ $user->pan_number ?: 'N/A' }}</p>
                                <p class="truncate max-w-[200px]"><span class="font-bold">Addr:</span> {{ $user->address ?: 'N/A' }}</p>
                            </div>
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            @php
                                $badgeColor = match($user->kyc_status) {
                                    'pending' => 'bg-warning-50 text-warning-600',
                                    'approved' => 'bg-success-50 text-success-600',
                                    'rejected' => 'bg-danger-50 text-danger-600',
                                    default => 'bg-gray-100 text-gray-600'
                                };
                            @endphp
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $badgeColor }}">
                                {{ strtoupper($user->kyc_status) }}
                            </span>
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            <p class="text-xs">{{ $user->updated_at->format('M d, Y') }}</p>
                            <p class="text-[10px] text-gray-400">{{ $user->updated_at->diffForHumans() }}</p>
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="inline-flex items-center justify-center rounded-md border border-primary py-1 px-3 text-center text-xs font-medium text-primary hover:bg-primary hover:text-white">
                                    Review & Correct
                                </a>
                                @if($user->kyc_status === 'pending')
                                <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="name" value="{{ $user->name }}">
                                    <input type="hidden" name="email" value="{{ $user->email }}">
                                    <input type="hidden" name="phone" value="{{ $user->phone }}">
                                    <input type="hidden" name="role" value="{{ $user->role }}">
                                    <input type="hidden" name="status" value="{{ $user->status }}">
                                    <input type="hidden" name="phone" value="{{ $user->phone }}">
                                    <input type="hidden" name="kyc_status" value="approved">
                                    <button type="submit" class="bg-success text-white py-1 px-3 rounded-md text-xs font-medium hover:bg-opacity-90">
                                        Approve
                                    </button>
                                </form>
                                @endif
                                @if(in_array($user->kyc_status, ['pending', 'approved']))
                                <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="name" value="{{ $user->name }}">
                                    <input type="hidden" name="email" value="{{ $user->email }}">
                                    <input type="hidden" name="phone" value="{{ $user->phone }}">
                                    <input type="hidden" name="role" value="{{ $user->role }}">
                                    <input type="hidden" name="status" value="{{ $user->status }}">
                                    <input type="hidden" name="kyc_status" value="rejected">
                                    <input type="hidden" name="kyc_notes" value="Unlocked by admin for corrections.">
                                    <button type="submit" class="bg-danger text-white py-1 px-3 rounded-md text-xs font-medium hover:bg-opacity-90">
                                        Unlock / Reject
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-gray-500">
                            No {{ $status }} KYC requests found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $users->appends(['status' => $status])->links() }}
        </div>
    </div>
</div>
@endsection
