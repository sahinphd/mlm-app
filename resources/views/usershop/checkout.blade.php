@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-270">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            Checkout
        </h2>
    </div>

    @if(session('error'))
        <div class="mb-6 flex w-full border-l-6 border-[#F87171] bg-[#F87171] bg-opacity-[15%] px-7 py-4 shadow-md dark:bg-[#1b1b1b] md:p-5">
            <div class="w-full">
                <p class="text-base leading-relaxed text-body">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-9 sm:grid-cols-2">
        <div class="flex flex-col gap-9">
            <!-- Item Details -->
            <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
                <div class="border-b border-stroke py-4 px-6.5 dark:border-strokedark">
                    <h3 class="font-medium text-black dark:text-white">Order Summary</h3>
                </div>
                <div class="p-6.5">
                    <div class="mb-4 flex items-center justify-between">
                        <span class="font-medium text-black dark:text-white">Item:</span>
                        <span>{{ $item->name }} ({{ ucfirst($type) }})</span>
                    </div>
                    <div class="mb-4 flex items-center justify-between">
                        <span class="font-medium text-black dark:text-white">Quantity:</span>
                        <span>{{ $quantity }}</span>
                    </div>
                    <div class="mb-4 flex items-center justify-between">
                        <span class="font-medium text-black dark:text-white">Price:</span>
                        <span>Rs.{{ number_format($item->price, 2) }}</span>
                    </div>
                    <div class="mb-4 flex items-center justify-between">
                        <span class="font-medium text-black dark:text-white">BV Points:</span>
                        <span class="text-meta-3">{{ $item->bv * $quantity }}</span>
                    </div>
                    <div class="mt-6 border-t border-stroke pt-4 dark:border-strokedark flex items-center justify-between">
                        <span class="text-xl font-bold text-black dark:text-white">Total:</span>
                        <span class="text-xl font-bold text-primary">Rs.{{ number_format($item->price * $quantity, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-9">
            <!-- Payment Form -->
            <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
                <div class="border-b border-stroke py-4 px-6.5 dark:border-strokedark">
                    <h3 class="font-medium text-black dark:text-white">Payment Method</h3>
                </div>
                <form action="{{ route('shop.place-order') }}" method="POST" class="p-6.5">
                    @csrf
                    <input type="hidden" name="item_id" value="{{ $item->id }}">
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="hidden" name="quantity" value="{{ $quantity }}">

                    <div class="mb-6">
                        <div class="flex flex-col gap-4">
                            <!-- Main Wallet -->
                            <label class="relative flex cursor-pointer select-none items-center gap-4 rounded border border-stroke p-4 dark:border-strokedark">
                                <input type="radio" name="payment_method" value="main_wallet" required class="h-5 w-5" {{ $wallet->main_balance >= ($item->price * $quantity) ? 'checked' : 'disabled' }}>
                                <div class="flex flex-col">
                                    <span class="font-medium text-black dark:text-white">Main Wallet</span>
                                    <span class="text-sm">Available: Rs.{{ number_format($wallet->main_balance, 2) }}</span>
                                    @if($wallet->main_balance < ($item->price * $quantity))
                                        <span class="text-xs text-danger">Insufficient balance</span>
                                    @endif
                                </div>
                            </label>

                            <!-- Credit Wallet -->
                            <label class="relative flex cursor-pointer select-none items-center gap-4 rounded border border-stroke p-4 dark:border-strokedark">
                                <input type="radio" name="payment_method" value="credit_wallet" required class="h-5 w-5" {{ ($creditAccount && $creditAccount->approval_status === 'approved' && $creditAccount->available_credit >= ($item->price * $quantity)) ? '' : 'disabled' }}>
                                <div class="flex flex-col">
                                    <span class="font-medium text-black dark:text-white">Credit Wallet (EMI)</span>
                                    @if($creditAccount && $creditAccount->approval_status === 'approved')
                                        <span class="text-sm">Limit: Rs.{{ number_format($creditAccount->available_credit, 2) }}</span>
                                        @if($creditAccount->available_credit < ($item->price * $quantity))
                                            <span class="text-xs text-danger">Insufficient credit limit</span>
                                        @endif
                                    @else
                                        <span class="text-sm text-danger">Credit account not approved</span>
                                    @endif
                                </div>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="flex w-full justify-center rounded bg-primary py-3 px-6 font-medium text-gray hover:bg-opacity-90">
                        Confirm & Place Order
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
