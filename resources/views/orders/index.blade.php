@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6">
    <!-- Breadcrumb Start -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            My Order History
        </h2>
    </div>
    <!-- Breadcrumb End -->

    <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="max-w-full overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-2 text-left dark:bg-meta-4">
                        <th class="min-w-[150px] py-4 px-4 font-medium text-black dark:text-white xl:pl-11">
                            Order ID
                        </th>
                        <th class="min-w-[250px] py-4 px-4 font-medium text-black dark:text-white">
                            Items
                        </th>
                        <th class="min-w-[120px] py-4 px-4 font-medium text-black dark:text-white text-right">
                            Total Amount
                        </th>
                        <th class="min-w-[100px] py-4 px-4 font-medium text-black dark:text-white text-right">
                            Total BV
                        </th>
                        <th class="min-w-[150px] py-4 px-4 font-medium text-black dark:text-white">
                            Payment
                        </th>
                        <th class="min-w-[120px] py-4 px-4 font-medium text-black dark:text-white">
                            Status
                        </th>
                        <th class="py-4 px-4 font-medium text-black dark:text-white">
                            Date
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
                            <p class="text-black dark:text-white text-sm">
                                @foreach($order->items as $item)
                                    @if($item->product)
                                        {{ $item->product->name }}
                                    @elseif($item->package)
                                        {{ $item->package->name }}
                                    @else
                                        Unknown Item
                                    @endif
                                    (x{{ $item->quantity }})@if(!$loop->last), @endif
                                @endforeach
                            </p>
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark text-right">
                            <p class="text-black dark:text-white">{{ number_format($order->total_amount, 2) }}</p>
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark text-right">
                            <p class="text-black dark:text-white">{{ number_format($order->total_bv, 2) }}</p>
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            <p class="text-black dark:text-white text-sm">{{ str_replace('_', ' ', ucfirst($order->payment_method)) }}</p>
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            @php
                                $statusColor = match($order->status) {
                                    'completed' => 'success',
                                    'pending' => 'warning',
                                    'failed', 'cancelled' => 'danger',
                                    default => 'primary',
                                };
                            @endphp
                            <p class="inline-flex rounded-full bg-{{ $statusColor }} bg-opacity-10 py-1 px-3 text-sm font-medium text-{{ $statusColor }}">
                                {{ ucfirst($order->status) }}
                            </p>
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            <p class="text-black dark:text-white text-sm">{{ $order->created_at->format('Y-m-d H:i') }}</p>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="border-b border-[#eee] py-5 px-4 text-center dark:border-strokedark">
                            No orders found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($orders->hasPages())
        <div class="p-4 border-t border-stroke dark:border-strokedark">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
