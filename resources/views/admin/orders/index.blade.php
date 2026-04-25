@extends('admin.layout')

@section('content')
<div class="w-full">
    <!-- Breadcrumb -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            Order Management
        </h2>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-3 2xl:gap-7.5 mb-6">
        <!-- Total Orders -->
        <div class="rounded-sm border border-stroke bg-white py-4 px-6 shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-xl font-bold text-black dark:text-white">{{ $stats['total'] }}</h4>
                    <span class="text-sm font-medium">Total Orders</span>
                </div>
                <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-meta-2 dark:bg-meta-4">
                    <i class="fas fa-shopping-bag text-primary"></i>
                </div>
            </div>
        </div>

        <!-- Processing Orders -->
        <div class="rounded-sm border border-stroke bg-white py-4 px-6 shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-xl font-bold text-black dark:text-white">{{ $stats['processing'] }}</h4>
                    <span class="text-sm font-medium text-primary">Processing</span>
                </div>
                <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-meta-2 dark:bg-meta-4">
                    <i class="fas fa-sync fa-spin text-primary"></i>
                </div>
            </div>
        </div>

        <!-- Completed Orders -->
        <div class="rounded-sm border border-stroke bg-white py-4 px-6 shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-xl font-bold text-black dark:text-white">{{ $stats['completed'] }}</h4>
                    <span class="text-sm font-medium text-success">Completed</span>
                </div>
                <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-meta-2 dark:bg-meta-4">
                    <i class="fas fa-check-circle text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="rounded-sm border border-stroke bg-white px-5 pt-6 pb-2.5 shadow-default dark:border-strokedark dark:bg-boxdark sm:px-7.5 mb-6">
        <form method="GET" action="{{ route('admin.orders') }}">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label class="mb-1 block text-sm font-medium text-black dark:text-white">Search</label>
                    <input type="text" name="q" value="{{ $q }}" placeholder="ID or User..." class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
                </div>
                <div class="w-48">
                    <label class="mb-1 block text-sm font-medium text-black dark:text-white">Status</label>
                    <select name="status" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ $status === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="shipped" {{ $status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="w-48">
                    <label class="mb-1 block text-sm font-medium text-black dark:text-white">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
                </div>
                <div class="w-48">
                    <label class="mb-1 block text-sm font-medium text-black dark:text-white">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
                </div>
                <div class="w-32">
                    <label class="mb-1 block text-sm font-medium text-black dark:text-white">Per Page</label>
                    <select name="per_page" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
                        @foreach([15, 30, 50, 100] as $val)
                            <option value="{{ $val }}" {{ $perPage == $val ? 'selected' : '' }}>{{ $val }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2 pt-6">
                    <button type="submit" class="flex justify-center rounded bg-primary py-2 px-6 font-medium text-gray hover:bg-opacity-90 transition">
                        Filter
                    </button>
                    <a href="{{ route('admin.orders') }}" class="flex justify-center rounded border border-stroke py-2 px-6 font-medium text-black hover:shadow-1 dark:border-strokedark dark:text-white transition">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="rounded-sm border border-stroke bg-white px-5 pt-6 pb-2.5 shadow-default dark:border-strokedark dark:bg-boxdark sm:px-7.5">
        <div class="max-w-full overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-2 text-left dark:bg-meta-4">
                        <th class="min-w-[120px] py-4 px-4 font-medium text-black dark:text-white xl:pl-11">
                            Order ID
                        </th>
                        <th class="min-w-[150px] py-4 px-4 font-medium text-black dark:text-white">
                            User
                        </th>
                        <th class="min-w-[120px] py-4 px-4 font-medium text-black dark:text-white">
                            Amount
                        </th>
                        <th class="min-w-[120px] py-4 px-4 font-medium text-black dark:text-white">
                            Date
                        </th>
                        <th class="min-w-[120px] py-4 px-4 font-medium text-black dark:text-white">
                            Status
                        </th>
                        <th class="py-4 px-4 font-medium text-black dark:text-white">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="border-b border-[#eee] py-5 px-4 pl-9 dark:border-strokedark xl:pl-11">
                                <h5 class="font-medium text-black dark:text-white">#{{ $order->id }}</h5>
                            </td>
                            <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                                <p class="text-black dark:text-white">{{ $order->user?->name ?? 'Deleted User' }}</p>
                                <p class="text-xs">{{ $order->user?->email }}</p>
                            </td>
                            <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                                <p class="text-black dark:text-white">₹{{ number_format($order->total_amount, 2) }}</p>
                            </td>
                            <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                                <p class="text-black dark:text-white">{{ $order->created_at->format('d M, Y') }}</p>
                            </td>
                            <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                                @php
                                    $badgeClass = match($order->status) {
                                        'completed' => 'bg-success text-white',
                                        'pending' => 'bg-warning text-white',
                                        'cancelled' => 'bg-danger text-white',
                                        'processing' => 'bg-primary text-white',
                                        'shipped' => 'bg-primary text-white',
                                        'returned' => 'bg-danger text-white',
                                        'refunded' => 'bg-danger text-white',
                                        'failed' => 'bg-danger text-white',
                                        

                                        default => 'bg-gray text-white'
                                    };
                                @endphp
                                <p class="inline-flex rounded-full bg-opacity-10 py-1 px-3 text-sm font-medium {{ $badgeClass }}">
                                    {{ ucfirst($order->status) }}
                                </p>
                            </td>
                            <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                                <div class="flex items-center space-x-3.5">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="hover:text-primary transition">
                                        <svg class="fill-current" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M8.99981 14.8219C3.43106 14.8219 0.674805 9.50624 0.562305 9.28124C0.47793 9.11249 0.47793 8.88749 0.562305 8.71874C0.674805 8.49374 3.43106 3.17812 8.99981 3.17812C14.5686 3.17812 17.3248 8.49374 17.4373 8.71874C17.5217 8.88749 17.5217 9.11249 17.4373 9.28124C17.3248 9.50624 14.5686 14.8219 8.99981 14.8219ZM1.85605 8.99999C2.4748 10.0406 4.89356 13.5562 8.99981 13.5562C13.1061 13.5562 15.5248 10.0406 16.1436 8.99999C15.5248 7.95937 13.1061 4.44374 8.99981 4.44374C4.89356 4.44374 2.4748 7.95937 1.85605 8.99999Z" fill=""></path>
                                            <path d="M9 11.3906C7.67812 11.3906 6.60938 10.3219 6.60938 9C6.60938 7.67812 7.67812 6.60938 9 6.60938C10.3219 6.60938 11.3906 7.67812 11.3906 9C11.3906 10.3219 10.3219 11.3906 9 11.3906ZM9 7.875C8.38125 7.875 7.875 8.38125 7.875 9C7.875 9.61875 8.38125 10.125 9 10.125C9.61875 10.125 10.125 9.61875 10.125 9C10.125 8.38125 9.61875 7.875 9 7.875Z" fill=""></path>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-5 px-4 text-center dark:border-strokedark">
                                No orders found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $orders->appends(request()->input())->links() }}
        </div>
    </div>
</div>
@endsection
