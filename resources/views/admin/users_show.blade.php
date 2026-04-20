@extends('admin.layout')

@section('content')
<div class="mx-auto max-w-270">
    <!-- Breadcrumb -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            User Details: {{ $user->name }}
        </h2>
        <div class="flex gap-3">
            <a href="{{ route('admin.users.genealogy', $user->id) }}" class="inline-flex items-center justify-center rounded-md bg-success py-2 px-6 text-center font-medium text-white hover:bg-opacity-90">
                View Genealogy
            </a>
            <a href="{{ route('admin.users.edit', $user->id) }}" class="inline-flex items-center justify-center rounded-md bg-primary py-2 px-6 text-center font-medium text-white hover:bg-opacity-90">
                Edit User
            </a>
            <a href="{{ route('admin.users') }}" class="inline-flex items-center justify-center rounded-md bg-gray py-2 px-6 text-center font-medium text-black hover:bg-opacity-90 dark:bg-meta-4 dark:text-white">
                Back to List
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-3 2xl:gap-7.5 mb-6">
        <!-- Main Balance -->
        <div class="rounded-sm border border-stroke bg-white py-6 px-7.5 shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="mt-4 flex items-end justify-between">
                <div>
                    <h4 class="text-title-md font-bold text-black dark:text-white">Rs.{{ number_format($user->wallet->main_balance ?? 0, 2) }}</h4>
                    <span class="text-sm font-medium">Main Balance</span>
                </div>
            </div>
        </div>

        <!-- Earning Balance -->
        <div class="rounded-sm border border-stroke bg-white py-6 px-7.5 shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="mt-4 flex items-end justify-between">
                <div>
                    <h4 class="text-title-md font-bold text-black dark:text-white text-success">Rs.{{ number_format($user->wallet->earning_balance ?? 0, 2) }}</h4>
                    <span class="text-sm font-medium text-success">Earning Balance</span>
                </div>
            </div>
        </div>

        <!-- Credit Balance/Limit -->
        <div class="rounded-sm border border-stroke bg-white py-6 px-7.5 shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="mt-4 flex items-end justify-between">
                <div>
                    <h4 class="text-title-md font-bold text-black dark:text-white">Rs.{{ number_format($user->creditAccount->available_credit ?? 0, 2) }}</h4>
                    <span class="text-sm font-medium">Available Credit (Limit: Rs.{{ number_format($user->creditAccount->credit_limit ?? 0, 2) }})</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-9 sm:grid-cols-2 mb-6">
        <div class="flex flex-col gap-9">
            <!-- Profile Info -->
            <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
                <div class="border-b border-stroke py-4 px-6.5 dark:border-strokedark">
                    <h3 class="font-medium text-black dark:text-white">Personal Information</h3>
                </div>
                <div class="p-6.5">
                    <div class="mb-4.5">
                        <label class="mb-2.5 block text-black dark:text-white">Name: <span class="text-body">{{ $user->name }}</span></label>
                    </div>
                    <div class="mb-4.5">
                        <label class="mb-2.5 block text-black dark:text-white">Email: <span class="text-body">{{ $user->email }}</span></label>
                    </div>
                    <div class="mb-4.5">
                        <label class="mb-2.5 block text-black dark:text-white">Phone: <span class="text-body">{{ $user->phone ?? 'N/A' }}</span></label>
                    </div>
                    <div class="mb-4.5">
                        <label class="mb-2.5 block text-black dark:text-white">Status: 
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $user->status === 'active' ? 'bg-success-50 text-success-600' : ($user->status === 'pending' ? 'bg-warning-50 text-warning-600' : 'bg-danger-50 text-danger-600') }}">
                                {{ ucfirst($user->status) }}
                            </span>
                        </label>
                    </div>
                    <div>
                        <label class="mb-2.5 block text-black dark:text-white">Joined: <span class="text-body">{{ $user->created_at->format('d M Y') }}</span></label>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-9">
            <!-- MLM Info -->
            <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
                <div class="border-b border-stroke py-4 px-6.5 dark:border-strokedark">
                    <h3 class="font-medium text-black dark:text-white">Referral & MLM Details</h3>
                </div>
                <div class="p-6.5">
                    <div class="mb-4.5">
                        <label class="mb-2.5 block text-black dark:text-white">Referral Code: <span class="text-body font-bold text-primary">{{ $user->referralRecord->referral_code ?? 'N/A' }}</span></label>
                    </div>
                    <div class="mb-4.5">
                        <label class="mb-2.5 block text-black dark:text-white">Upline (Parent): 
                            @if($user->referralRecord && $user->referralRecord->parent_id)
                                @php $upline = \App\Models\User::find($user->referralRecord->parent_id); @endphp
                                <a href="{{ route('admin.users.show', $upline->id) }}" class="text-primary hover:underline">{{ $upline->name }} (#{{ $upline->id }})</a>
                            @else
                                <span class="text-body italic">No Parent (Top Level)</span>
                            @endif
                        </label>
                    </div>
                    <div class="mb-4.5">
                        <label class="mb-2.5 block text-black dark:text-white">Tree Depth: <span class="text-body">{{ $user->referralRecord->level_depth ?? 0 }}</span></label>
                    </div>
                    <div class="mb-4.5">
                        <label class="mb-2.5 block text-black dark:text-white">Total Direct Referrals: <span class="text-body">{{ \App\Models\Referral::where('parent_id', $user->id)->count() }}</span></label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- EMI Schedules -->
    <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark mb-6">
        <div class="border-b border-stroke py-4 px-6.5 dark:border-strokedark flex justify-between items-center">
            <h3 class="font-medium text-black dark:text-white">EMI Schedule History</h3>
        </div>
        <div class="p-6.5">
            <div class="max-w-full overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-2 text-left dark:bg-meta-4">
                            <th class="py-4 px-4 font-medium text-black dark:text-white">Order ID</th>
                            <th class="py-4 px-4 font-medium text-black dark:text-white text-right">Amount</th>
                            <th class="py-4 px-4 font-medium text-black dark:text-white">Due Date</th>
                            <th class="py-4 px-4 font-medium text-black dark:text-white">Status</th>
                            <th class="py-4 px-4 font-medium text-black dark:text-white text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($emis as $emi)
                        <tr>
                            <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                                <a href="{{ route('admin.orders.show', $emi->order_id) }}" class="text-primary hover:underline">#{{ $emi->order_id }}</a>
                            </td>
                            <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark text-right">
                                <p class="text-black dark:text-white font-bold">Rs.{{ number_format($emi->installment_amount, 2) }}</p>
                            </td>
                            <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                                <p class="text-black dark:text-white">{{ \Carbon\Carbon::parse($emi->due_date)->format('d M Y') }}</p>
                            </td>
                            <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                                @if($emi->status == 'paid')
                                    <span class="inline-flex rounded-full bg-success bg-opacity-10 py-1 px-3 text-sm font-medium text-success">Paid</span>
                                @elseif($emi->status == 'overdue')
                                    <span class="inline-flex rounded-full bg-danger bg-opacity-10 py-1 px-3 text-sm font-medium text-danger">Overdue</span>
                                @else
                                    <span class="inline-flex rounded-full bg-warning bg-opacity-10 py-1 px-3 text-sm font-medium text-warning">Pending</span>
                                @endif
                            </td>
                            <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark text-right">
                                <button onclick="sendUserReminder({{ $emi->id }})" class="inline-flex items-center justify-center rounded bg-primary py-1.5 px-3 text-center text-xs font-medium text-white hover:bg-opacity-90" title="Send Push Notification">
                                    Send Reminder
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-5 px-4 text-center">No EMI schedules found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function sendUserReminder(emiId) {
            if(!confirm('Send a push notification reminder to this user?')) return;

            $.ajax({
                url: `/admin/emis/${emiId}/remind`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    alert(res.message);
                },
                error: function(err) {
                    alert('Error: ' + (err.responseJSON ? err.responseJSON.message : 'Unknown error'));
                }
            });
        }
    </script>

    <!-- Wallet Transactions -->
    <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="border-b border-stroke py-4 px-6.5 dark:border-strokedark flex justify-between items-center">
            <h3 class="font-medium text-black dark:text-white">Recent Wallet Transactions</h3>
        </div>
        <div class="p-6.5">
            <div class="max-w-full overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-2 text-left dark:bg-meta-4">
                            <th class="py-4 px-4 font-medium text-black dark:text-white">Type</th>
                            <th class="py-4 px-4 font-medium text-black dark:text-white">Source</th>
                            <th class="py-4 px-4 font-medium text-black dark:text-white">Amount</th>
                            <th class="py-4 px-4 font-medium text-black dark:text-white">Description</th>
                            <th class="py-4 px-4 font-medium text-black dark:text-white">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                        <tr>
                            <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                                <span class="inline-flex rounded-full px-3 py-1 text-sm font-medium {{ $tx->type === 'credit' ? 'bg-success bg-opacity-10 text-white' : 'bg-danger bg-opacity-10 text-white' }}">
                                    {{ ucfirst($tx->type) }}
                                </span>
                            </td>
                            <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                                <p class="text-black dark:text-white">{{ ucfirst($tx->source) }}</p>
                            </td>
                            <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                                <p class="font-bold {{ $tx->type === 'credit' ? 'text-white' : 'text-danger' }}">
                                    {{ $tx->type === 'credit' ? '+' : '-' }}Rs.{{ number_format($tx->amount, 2) }}
                                </p>
                            </td>
                            <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                                <p class="text-black dark:text-white text-xs">{{ $tx->description }}</p>
                                <p class="text-[10px] text-gray-400">Ref: {{ $tx->reference_id }}</p>
                            </td>
                            <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                                <p class="text-black dark:text-white">{{ $tx->created_at->format('d M Y, H:i') }}</p>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-5 px-4 text-center">No transactions found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transactions instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
