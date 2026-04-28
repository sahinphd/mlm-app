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
                    <span class="text-sm font-medium text-success">BV Earnings</span>
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
                                    <span class="inline-flex rounded-full bg-success bg-opacity-10 py-1 px-3 text-sm font-medium text-white">Paid</span>
                                @elseif($emi->status == 'overdue')
                                    <span class="inline-flex rounded-full bg-danger bg-opacity-10 py-1 px-3 text-sm font-medium text-white">Overdue</span>
                                @else
                                    <span class="inline-flex rounded-full bg-warning bg-opacity-10 py-1 px-3 text-sm font-medium text-white">Pending</span>
                                @endif
                            </td>
                            <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="sendUserReminder({{ $emi->id }})" class="inline-flex items-center justify-center rounded bg-primary py-1.5 px-3 text-center text-xs font-medium text-white hover:bg-opacity-90" title="Send Push Notification">
                                        Send Reminder
                                    </button>

                                    @if($user->phone)
                                        @php
                                            $appName = config('app.name', 'MLM App');
                                            if($emi->status === 'paid') {
                                                $message = "Hello " . ($user->name ?? 'User') . ",\n\nThank you! Your EMI installment of ₹" . number_format($emi->installment_amount, 2) . " for Order #" . $emi->order_id . " has been successfully paid.\n\nWe appreciate your timely payment!\n\nRegards,\nTeam " . $appName;
                                            } else {
                                                $message = "Hello " . ($user->name ?? 'User') . ",\n\nThis is a reminder from " . $appName . " for your EMI installment of ₹" . number_format($emi->installment_amount, 2) . " for Order #" . $emi->order_id . ".\n\nDue Date: " . \Carbon\Carbon::parse($emi->due_date)->format('d M Y') . ".\n\nPlease ensure to make the payment on time to avoid penalties.\n\nThank you!";
                                            }

                                            $phone = preg_replace('/[^0-9]/', '', $user->phone);
                                            if (strlen($phone) == 10) {
                                                $phone = '91' . $phone;
                                            }
                                            $whatsappUrl = "https://wa.me/" . $phone . "?text=" . urlencode($message);
                                            $smsUrl = "sms:+" . $phone . "?body=" . urlencode($message);
                                        @endphp
                                        <a href="{{ $whatsappUrl }}" target="_blank" class="inline-flex items-center justify-center rounded bg-[#25D366] py-1.5 px-3 text-center text-xs font-medium text-white hover:bg-opacity-90" title="{{ $emi->status === 'paid' ? 'Send Thank You Message' : 'Send WhatsApp Reminder' }}">
                                            <svg class="fill-current mr-1" width="14" height="14" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                            </svg>
                                            {{ $emi->status === 'paid' ? 'Thank You' : 'WhatsApp' }}
                                        </a>
                                        <a href="{{ $smsUrl }}" class="inline-flex items-center justify-center rounded bg-meta-3 py-1.5 px-3 text-center text-xs font-medium text-white hover:bg-opacity-90" title="{{ $emi->status === 'paid' ? 'Send Thank You SMS' : 'Send SMS Reminder' }}">
                                            <svg class="fill-current mr-1" width="14" height="14" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                                            </svg>
                                            {{ $emi->status === 'paid' ? 'Thank You' : 'SMS' }}
                                        </a>
                                    @endif
                                </div>
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
            Swal.fire({
                title: 'Send Reminder?',
                text: "Send a push notification reminder to this user?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, send it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/admin/emis/${emiId}/remind`,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: res.message
                            });
                        },
                        error: function(err) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error: ' + (err.responseJSON ? err.responseJSON.message : 'Unknown error')
                            });
                        }
                    });
                }
            });
        }
    </script>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @endpush

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
