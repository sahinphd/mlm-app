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
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-4 2xl:gap-7.5 mb-6">
        <!-- Total Orders -->
        <div class="rounded-sm border border-stroke bg-white py-6 px-7.5 shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-meta-2 dark:bg-meta-4">
                <svg class="fill-primary dark:fill-white" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18.5625 5.5H16.5V4.8125C16.5 2.71563 14.7844 0.61875 12.6875 0.61875H9.3125C7.21562 0.61875 5.5 2.33438 5.5 4.8125V5.5H3.4375C2.3375 5.5 1.44375 6.42813 1.375 7.52813L0.6875 17.5312C0.61875 18.7 1.54688 19.6625 2.71562 19.6625H19.2844C20.4531 19.6625 21.3812 18.7344 21.3125 17.5312L20.625 7.52813C20.5562 6.42813 19.6625 5.5 18.5625 5.5ZM7.5625 4.8125C7.5625 3.85 8.35312 3.05938 9.3125 3.05938H12.6875C13.6469 3.05938 14.4375 3.85 14.4375 4.8125V5.5H7.5625V4.8125ZM18.5625 17.225H3.4375L3.9875 10.3125H5.5V11C5.5 11.6875 6.05 12.2375 6.7375 12.2375C7.425 12.2375 7.975 11.6875 7.975 11V10.3125H14.025V11C14.025 11.6875 14.575 12.2375 15.2625 12.2375C15.95 12.2375 16.5 11.6875 16.5 11V10.3125H18.0125L18.5625 17.225Z" fill=""></path>
                </svg>
            </div>
            <div class="mt-4 flex items-end justify-between">
                <div>
                    <h4 class="text-title-md font-bold text-black dark:text-white">{{ $stats['total'] }}</h4>
                    <span class="text-sm font-medium">Total Orders</span>
                </div>
            </div>
        </div>

        <!-- Pending Orders -->
        <div class="rounded-sm border border-stroke bg-white py-6 px-7.5 shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-meta-2 dark:bg-meta-4">
                <svg class="fill-warning" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M11 0C5.075 0 0 4.075 0 9.1C0 14.125 5.075 18.2 11 18.2C12.1 18.2 13.15 18.025 14.15 17.725L18.6 19.675C18.8 19.75 19.05 19.75 19.225 19.625C19.4 19.5 19.5 19.3 19.5 19.1V15.2C21.1 13.525 22 11.425 22 9.1C22 4.075 16.925 0 11 0ZM11 16.25C6.15 16.25 2.15 13.05 2.15 9.1C2.15 5.15 6.15 1.95 11 1.95C15.85 1.95 19.85 5.15 19.85 9.1C19.85 10.9 19.15 12.575 17.9 13.85C17.75 14.025 17.675 14.25 17.675 14.475V16.7L15 15.525C14.825 15.45 14.65 15.425 14.475 15.425C13.375 15.95 12.225 16.25 11 16.25Z" fill=""></path>
                </svg>
            </div>
            <div class="mt-4 flex items-end justify-between">
                <div>
                    <h4 class="text-title-md font-bold text-black dark:text-white">{{ $stats['pending'] }}</h4>
                    <span class="text-sm font-medium text-warning">Pending</span>
                </div>
            </div>
        </div>

        <!-- Revenue -->
        <div class="rounded-sm border border-stroke bg-white py-6 px-7.5 shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-meta-2 dark:bg-meta-4">
                <svg class="fill-success" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19.1125 7.15H17.4375V6.325C17.4375 4.54688 15.9906 3.1 14.2125 3.1H7.8125C6.03438 3.1 4.5875 4.54688 4.5875 6.325V7.15H2.9125C1.675 7.15 0.65 8.15625 0.65 9.425V16.3C0.65 17.5687 1.675 18.575 2.9125 18.575H19.1125C20.35 18.575 21.375 17.5687 21.375 16.3V9.425C21.375 8.15625 20.35 7.15 19.1125 7.15ZM6.2375 6.325C6.2375 5.45937 6.94688 4.75 7.8125 4.75H14.2125C15.0781 4.75 15.7875 5.45937 15.7875 6.325V7.15H6.2375V6.325ZM19.725 16.3C19.725 16.6344 19.4531 16.925 19.1125 16.925H2.9125C2.57188 16.925 2.3 16.6344 2.3 16.3V9.425C2.3 9.09063 2.57188 8.8 2.9125 8.8H19.1125C19.4531 8.8 19.725 9.09063 19.725 9.425V16.3Z" fill=""></path>
                    <path d="M11.0125 11.275C9.74062 11.275 8.7125 12.2781 8.7125 13.5187C8.7125 14.7594 9.71562 15.7625 11.0125 15.7625C12.3094 15.7625 13.3125 14.7594 13.3125 13.5187C13.3125 12.2781 12.2844 11.275 11.0125 11.275ZM11.0125 14.1125C10.6875 14.1125 10.4125 13.8437 10.4125 13.5187C10.4125 13.1937 10.6875 12.925 11.0125 12.925C11.3375 12.925 11.6125 13.1937 11.6125 13.5187C11.6125 13.8437 11.3375 14.1125 11.0125 14.1125Z" fill=""></path>
                </svg>
            </div>
            <div class="mt-4 flex items-end justify-between">
                <div>
                    <h4 class="text-title-md font-bold text-black dark:text-white">₹{{ number_format($stats['revenue'], 2) }}</h4>
                    <span class="text-sm font-medium text-success">Total Revenue</span>
                </div>
            </div>
        </div>

        <!-- Completed Orders -->
        <div class="rounded-sm border border-stroke bg-white py-6 px-7.5 shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-meta-2 dark:bg-meta-4">
                <svg class="fill-success" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M11 22C4.93458 22 0 17.0654 0 11C0 4.93458 4.93458 0 11 0C17.0654 0 22 4.93458 22 11C22 17.0654 17.0654 22 11 22ZM11 1.65421C5.84579 1.65421 1.65421 5.84579 1.65421 11C1.65421 16.1542 5.84579 20.3458 11 20.3458C16.1542 20.3458 20.3458 16.1542 20.3458 11C20.3458 5.84579 16.1542 1.65421 11 1.65421Z" fill=""></path>
                    <path d="M9.13084 14.9439C8.91121 14.9439 8.70561 14.8645 8.54673 14.7056L5.84579 12.0047C5.52804 11.6869 5.52804 11.1682 5.84579 10.8505C6.16355 10.5327 6.68224 10.5327 7 10.8505L9.13084 12.9813L15 6.85514C15.3178 6.53738 15.8364 6.53738 16.1542 6.85514C16.472 7.1729 16.472 7.69159 16.1542 8.00935L9.71495 14.7056C9.55607 14.8645 9.35047 14.9439 9.13084 14.9439Z" fill=""></path>
                </svg>
            </div>
            <div class="mt-4 flex items-end justify-between">
                <div>
                    <h4 class="text-title-md font-bold text-black dark:text-white">{{ $stats['completed'] }}</h4>
                    <span class="text-sm font-medium text-success">Completed</span>
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
