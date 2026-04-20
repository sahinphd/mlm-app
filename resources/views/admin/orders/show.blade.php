@extends('admin.layout')

@section('content')
<div class="w-full">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            Order #{{ $order->id }}
        </h2>
        <a href="{{ route('admin.orders') }}" class="flex justify-center rounded border border-stroke py-2 px-6 font-medium text-black hover:shadow-1 dark:border-strokedark dark:text-white transition">
            Back to List
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3 md:gap-6">
        <!-- Order Info -->
        <div class="md:col-span-2 space-y-6">
            <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
                <div class="border-b border-stroke py-4 px-6.5 dark:border-strokedark">
                    <h3 class="font-medium text-black dark:text-white">Order Details</h3>
                </div>
                <div class="p-6.5">
                    <div class="max-w-full overflow-x-auto">
                        <table class="w-full table-auto">
                            <thead>
                                <tr class="bg-gray-2 text-left dark:bg-meta-4">
                                    <th class="py-4 px-4 font-medium text-black dark:text-white">Product</th>
                                    <th class="py-4 px-4 font-medium text-black dark:text-white">Price</th>
                                    <th class="py-4 px-4 font-medium text-black dark:text-white">Qty</th>
                                    <th class="py-4 px-4 font-medium text-black dark:text-white text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                                            <p class="text-black dark:text-white font-medium">{{ $item->product ? $item->product->name : ($item->package ? $item->package->name : 'N/A') }}</p>
                                        </td>
                                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                                            <p class="text-black dark:text-white">${{ number_format($item->price, 2) }}</p>
                                        </td>
                                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                                            <p class="text-black dark:text-white">{{ $item->quantity }}</p>
                                        </td>
                                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark text-right">
                                            <p class="text-black dark:text-white font-bold">${{ number_format($item->price * $item->quantity, 2) }}</p>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="py-5 px-4 text-right font-medium">Grand Total</td>
                                    <td class="py-5 px-4 text-right text-title-sm font-bold text-primary">${{ number_format($order->total_amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer & Action -->
        <div class="space-y-6">
            <!-- Customer Card -->
            <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
                <div class="border-b border-stroke py-4 px-6.5 dark:border-strokedark">
                    <h3 class="font-medium text-black dark:text-white">Customer Info</h3>
                </div>
                <div class="p-6.5 text-center">
                    <div class="mb-4 flex justify-center">
                        <img src="{{ $order->user->avatar_url ?? asset('images/user/owner.jpg') }}" alt="User" class="h-20 w-20 rounded-full border border-stroke p-1">
                    </div>
                    <h4 class="mb-0.5 text-title-sm font-bold text-black dark:text-white">{{ $order->user->name }}</h4>
                    <p class="text-sm font-medium">{{ $order->user->email }}</p>
                    <p class="text-sm font-medium mt-2">Phone: {{ $order->user->phone ?? 'N/A' }}</p>
                </div>
            </div>

            <!-- Action Card -->
            <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
                <div class="border-b border-stroke py-4 px-6.5 dark:border-strokedark">
                    <h3 class="font-medium text-black dark:text-white">Update Status</h3>
                </div>
                <div class="p-6.5">
                    <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST">
                        @csrf
                        <div class="mb-4.5">
                            <label class="mb-2.5 block text-black dark:text-white">Current Status</label>
                            <select name="status" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-3 px-5 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
                                <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="returned" {{ $order->status === 'returned' ? 'selected' : '' }}>Returned</option>
                                <option value="failed" {{ $order->status === 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                        <button type="submit" class="flex w-full justify-center rounded bg-primary p-3 font-medium text-gray transition hover:bg-opacity-90">
                            Update Order Status
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
