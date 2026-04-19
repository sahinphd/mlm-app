@extends('admin.layout')

@section('content')
<div class="mx-auto max-w-270">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            All BV Commissions
        </h2>
        <a href="{{ route('admin.commissions.bv.export', request()->query()) }}" class="inline-flex items-center justify-center rounded-md bg-success py-2 px-6 text-center font-medium text-white hover:bg-opacity-90 lg:px-8 xl:px-10">
            Export to CSV
        </a>
    </div>
    <!-- Filter & Search -->
    <div class="rounded-sm border border-stroke bg-white px-5 pt-6 pb-2.5 shadow-default dark:border-strokedark dark:bg-boxdark sm:px-7.5 mb-6">
        <form method="GET" action="{{ route('admin.commissions.bv') }}" id="filterForm">
            <div class="flex flex-wrap items-center gap-4">
                <!-- Receiver Search -->
                <div class="flex-1 min-w-[200px] relative">
                    <label class="mb-1 block text-sm font-medium text-black dark:text-white">Receiver (Upline)</label>
                    <input type="text" id="receiver_search" value="{{ $receiverName }}" placeholder="Search Receiver..." autocomplete="off" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
                    <input type="hidden" name="receiver_id" id="receiver_id" value="{{ $receiverId }}">
                    <input type="hidden" name="receiver_name" id="receiver_name_hidden" value="{{ $receiverName }}">
                    <div id="receiver_results" class="absolute z-50 w-full bg-white dark:bg-boxdark border border-stroke dark:border-strokedark rounded-b shadow-lg hidden max-h-60 overflow-y-auto"></div>
                </div>

                <!-- From User Search -->
                <div class="flex-1 min-w-[200px] relative">
                    <label class="mb-1 block text-sm font-medium text-black dark:text-white">From User (Downline)</label>
                    <input type="text" id="from_user_search" value="{{ $fromUserName }}" placeholder="Search From User..." autocomplete="off" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
                    <input type="hidden" name="from_user_id" id="from_user_id" value="{{ $fromUserId }}">
                    <input type="hidden" name="from_user_name" id="from_user_name_hidden" value="{{ $fromUserName }}">
                    <div id="from_user_results" class="absolute z-50 w-full bg-white dark:bg-boxdark border border-stroke dark:border-strokedark rounded-b shadow-lg hidden max-h-60 overflow-y-auto"></div>
                </div>

                <div class="w-32">
                    <label class="mb-1 block text-sm font-medium text-black dark:text-white">Level</label>
                    <select name="level" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
                        <option value="">All</option>
                        @foreach([1, 2, 3, 4, 5] as $l)
                            <option value="{{ $l }}" {{ $level == $l ? 'selected' : '' }}>Level {{ $l }}</option>
                        @endforeach
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
                    <a href="{{ route('admin.commissions.bv') }}" class="flex justify-center rounded border border-stroke py-2 px-6 font-medium text-black hover:shadow-1 dark:border-strokedark dark:text-white transition">
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

            setupAutocomplete('#receiver_search', '#receiver_results', '#receiver_id', '#receiver_name_hidden');
            setupAutocomplete('#from_user_search', '#from_user_results', '#from_user_id', '#from_user_name_hidden');
            
            $('#receiver_search').on('change', function() { if(!$(this).val()) $('#receiver_id').val(''); $('#receiver_name_hidden').val(''); });
            $('#from_user_search').on('change', function() { if(!$(this).val()) $('#from_user_id').val(''); $('#from_user_name_hidden').val(''); });
        });
    </script>

    <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="max-w-full overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-2 text-left dark:bg-meta-4">
                        <th class="min-w-[150px] py-4 px-4 font-medium text-black dark:text-white">Receiver</th>
                        <th class="min-w-[150px] py-4 px-4 font-medium text-black dark:text-white">From User</th>
                        <th class="min-w-[100px] py-4 px-4 font-medium text-black dark:text-white">Level</th>
                        <th class="min-w-[120px] py-4 px-4 font-medium text-black dark:text-white text-right">BV Amount</th>
                        <th class="py-4 px-4 font-medium text-black dark:text-white">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($commissions as $comm)
                    <tr>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            <p class="text-black dark:text-white font-medium">
                                <a href="{{ route('admin.users.show', $comm->user_id) }}" class="hover:text-primary">
                                    {{ $comm->user->name ?? 'Deleted User' }}
                                </a>
                            </p>
                            <p class="text-xs">{{ $comm->user->email ?? '' }}</p>
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            <p class="text-black dark:text-white font-medium">
                                <a href="{{ route('admin.users.show', $comm->from_user_id) }}" class="hover:text-primary">
                                    {{ $comm->fromUser->name ?? 'N/A' }}
                                </a>
                            </p>
                            <p class="text-xs">{{ $comm->fromUser->email ?? '' }}</p>
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            <span class="inline-flex rounded-full bg-meta-2 py-1 px-3 text-sm font-medium text-black dark:bg-meta-4 dark:text-white">
                                Level {{ $comm->level }}
                            </span>
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark text-right">
                            <p class="text-black dark:text-white font-bold">{{ number_format($comm->amount, 2) }} BV</p>
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            <p class="text-black dark:text-white text-sm">{{ $comm->created_at->format('d M Y, H:i') }}</p>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $commissions->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
