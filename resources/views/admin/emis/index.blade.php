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
                            <button onclick="sendReminder({{ $emi->id }})" class="inline-flex items-center justify-center rounded bg-primary py-1.5 px-3 text-center text-xs font-medium text-white hover:bg-opacity-90" title="Send Push Notification">
                                Send Reminder
                            </button>
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
