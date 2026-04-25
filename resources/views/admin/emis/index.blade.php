@extends('admin.layout')

@section('content')
<div class="mx-auto max-w-270">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            Global EMI Schedules
        </h2>
    </div>

    <!-- Filter & Search -->
    <div class="rounded-sm border border-stroke bg-white px-5 pt-6 pb-2.5 shadow-default dark:border-strokedark dark:bg-boxdark sm:px-7.5 mb-6">
        <form method="GET" action="{{ route('admin.emis.index') }}" id="filterForm">
            <div class="flex flex-wrap items-center gap-4">
                <!-- User Search -->
                <div class="flex-1 min-w-[200px] relative">
                    <label class="mb-1 block text-sm font-medium text-black dark:text-white">User</label>
                    <input type="text" id="user_search" value="{{ $userName }}" placeholder="Search User..." autocomplete="off" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
                    <input type="hidden" name="user_id" id="user_id" value="{{ $userId }}">
                    <input type="hidden" name="user_name" id="user_name_hidden" value="{{ $userName }}">
                    <div id="user_results" class="absolute z-50 w-full bg-white dark:bg-boxdark border border-stroke dark:border-strokedark rounded-b shadow-lg hidden max-h-60 overflow-y-auto"></div>
                </div>

                <div class="w-44">
                    <label class="mb-1 block text-sm font-medium text-black dark:text-white">Status</label>
                    <select name="status" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ $status == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="overdue" {{ $status == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>

                <div class="w-44">
                    <label class="mb-1 block text-sm font-medium text-black dark:text-white">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
                </div>

                <div class="w-44">
                    <label class="mb-1 block text-sm font-medium text-black dark:text-white">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
                </div>

                <div class="w-32">
                    <label class="mb-1 block text-sm font-medium text-black dark:text-white">Per Page</label>
                    <select name="per_page" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
                        @foreach([20, 50, 100, 200] as $val)
                            <option value="{{ $val }}" {{ $perPage == $val ? 'selected' : '' }}>{{ $val }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2 pt-6">
                    <button type="submit" class="flex justify-center rounded bg-primary py-2 px-6 font-medium text-gray hover:bg-opacity-90 transition">
                        Filter
                    </button>
                    <a href="{{ route('admin.emis.index') }}" class="flex justify-center rounded border border-stroke py-2 px-6 font-medium text-black hover:shadow-1 dark:border-strokedark dark:text-white transition">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            function setupAutocomplete(inputId, resultsId, hiddenId, hiddenNameId) {
                const $input = $(inputId);
                const $results = $(resultsId);
                const $hidden = $(hiddenId);
                const $hiddenName = $(hiddenNameId);
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
                                    html += `<div class="p-2 hover:bg-gray-100 dark:hover:bg-meta-4 cursor-pointer border-b border-stroke dark:border-strokedark last:border-0" data-id="${user.id}" data-name="${user.name}">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="font-bold text-black dark:text-white">${user.name}</span>
                                            <span class="text-[10px] bg-gray-200 dark:bg-meta-4 px-1.5 rounded text-gray-600 dark:text-gray-400">ID: ${user.id}</span>
                                        </div>
                                        <div class="text-xs text-gray-500 flex flex-col">
                                            <span>${user.email}</span>
                                            ${user.phone ? `<span class="text-gray-400">${user.phone}</span>` : ''}
                                        </div>
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

                $results.on('click', 'div[data-id]', function() {
                    const id = $(this).data('id');
                    const name = $(this).data('name');
                    $input.val(name);
                    $hidden.val(id);
                    $hiddenName.val(name);
                    $results.hide();
                });

                $(document).on('click', function(e) {
                    if (!$(e.target).closest(inputId).length && !$(e.target).closest(resultsId).length) {
                        $results.hide();
                    }
                });
            }

            setupAutocomplete('#user_search', '#user_results', '#user_id', '#user_name_hidden');
            
            $('#user_search').on('change', function() { if(!$(this).val()) $('#user_id').val(''); $('#user_name_hidden').val(''); });
        });

        function sendReminder(emiId) {
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

    <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="max-w-full overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-2 text-left dark:bg-meta-4">
                        <th class="min-w-[150px] py-4 px-4 font-medium text-black dark:text-white">User</th>
                        <th class="min-w-[100px] py-4 px-4 font-medium text-black dark:text-white">Order</th>
                        <th class="min-w-[120px] py-4 px-4 font-medium text-black dark:text-white">Amount</th>
                        <th class="min-w-[120px] py-4 px-4 font-medium text-black dark:text-white">Due Date</th>
                        <th class="min-w-[100px] py-4 px-4 font-medium text-black dark:text-white">Status</th>
                        <th class="py-4 px-4 font-medium text-black dark:text-white">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($emis as $emi)
                    <tr>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            <p class="text-black dark:text-white font-medium">
                                <a href="{{ route('admin.users.show', $emi->user_id) }}" class="hover:text-primary">
                                    {{ $emi->user->name ?? 'Deleted User' }}
                                </a>
                            </p>
                            <p class="text-xs">{{ $emi->user->email ?? '' }}</p>
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            <p class="text-black dark:text-white font-medium">
                                <a href="{{ route('admin.orders.show', $emi->order_id) }}" class="text-primary hover:underline">
                                    #{{ $emi->order_id }}
                                </a>
                            </p>
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            <p class="text-black dark:text-white font-bold">₹{{ number_format($emi->installment_amount, 2) }}</p>
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            <p class="text-black dark:text-white text-sm">{{ \Carbon\Carbon::parse($emi->due_date)->format('d M Y') }}</p>
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            @if($emi->status == 'paid')
                                <span class="inline-flex rounded-full bg-success bg-opacity-10 py-1 px-3 text-sm font-medium text-white">
                                    Paid
                                </span>
                            @elseif($emi->status == 'overdue')
                                <span class="inline-flex rounded-full bg-danger bg-opacity-10 py-1 px-3 text-sm font-medium text-white">
                                    Overdue
                                </span>
                            @else
                                <span class="inline-flex rounded-full bg-warning bg-opacity-10 py-1 px-3 text-sm font-medium text-white">
                                    Pending
                                </span>
                            @endif
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            <div class="flex items-center gap-2">
                                <button onclick="sendReminder({{ $emi->id }})" class="inline-flex items-center justify-center rounded bg-primary py-1.5 px-3 text-center text-xs font-medium text-white hover:bg-opacity-90" title="Send Push Notification">
                                    Send Reminder
                                </button>
                                
                                @if($emi->user && $emi->user->phone)
                                    @php
                                        $appName = config('app.name', 'MLM App');
                                        if($emi->status === 'paid') {
                                            $message = "Hello " . ($emi->user->name ?? 'User') . ",\n\nThank you! Your EMI installment of ₹" . number_format($emi->installment_amount, 2) . " for Order #" . $emi->order_id . " has been successfully paid.\n\nWe appreciate your timely payment!\n\nRegards,\nTeam " . $appName;
                                        } else {
                                            $message = "Hello " . ($emi->user->name ?? 'User') . ",\n\nThis is a reminder from " . $appName . " for your EMI installment of ₹" . number_format($emi->installment_amount, 2) . " for Order #" . $emi->order_id . ".\n\nDue Date: " . \Carbon\Carbon::parse($emi->due_date)->format('d M Y') . ".\n\nPlease ensure to make the payment on time to avoid penalties.\n\nThank you!";
                                        }
                                        
                                        $phone = preg_replace('/[^0-9]/', '', $emi->user->phone);
                                        // Add country code if not present (assuming India +91 if 10 digits)
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
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $emis->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
