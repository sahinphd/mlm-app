@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-2xl">
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-8">
        <div class="mb-6 text-center">
            <h2 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">Transfer Balance</h2>
            <p class="text-gray-500 dark:text-gray-400">Transfer funds from your main balance to another user's wallet.</p>
        </div>

        <div class="mb-8 flex justify-center">
            <div class="rounded-2xl border border-brand-100 bg-brand-50/50 p-6 dark:border-gray-800 dark:bg-white/[0.03] text-center w-full max-w-sm">
                <h4 class="text-sm font-medium text-gray-500 mb-2 uppercase tracking-wider">Available Main Balance</h4>
                <p class="text-3xl font-bold text-brand-600 dark:text-white">₹{{ number_format($wallet->main_balance ?? 0, 2) }}</p>
            </div>
        </div>

        <form action="{{ route('wallet.transfer.post') }}" method="POST" class="space-y-6">
            @csrf

            @if($errors->has('error'))
                <div class="rounded-lg bg-red-50 p-4 text-sm text-red-600 dark:bg-red-500/10 dark:text-red-400">
                    {{ $errors->first('error') }}
                </div>
            @endif

            <div>
                <label for="email" class="mb-2.5 block font-medium text-gray-700 dark:text-gray-300">
                    Recipient Email ID
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter recipient's email address"
                    value="{{ old('email') }}"
                    class="w-full rounded-lg border border-gray-300 bg-transparent px-5 py-3 text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white @error('email') border-red-500 @enderror"
                    required
                />
                @error('email')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="amount" class="mb-2.5 block font-medium text-gray-700 dark:text-gray-300">
                    Amount to Transfer (₹)
                </label>
                <div class="relative">
                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-500">₹</span>
                    <input
                        type="number"
                        id="amount"
                        name="amount"
                        step="0.01"
                        min="1"
                        max="{{ $wallet->main_balance }}"
                        placeholder="0.00"
                        value="{{ old('amount') }}"
                        class="w-full rounded-lg border border-gray-300 bg-transparent py-3 pl-10 pr-5 text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white @error('amount') border-red-500 @enderror"
                        required
                    />
                </div>
                @error('amount')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500 italic">Minimum transfer amount: ₹1.00</p>
            </div>

            <div>
                <label for="remarks" class="mb-2.5 block font-medium text-gray-700 dark:text-gray-300">
                    Remarks (Optional)
                </label>
                <textarea
                    id="remarks"
                    name="remarks"
                    rows="3"
                    placeholder="E.g., Personal transfer, Payment for goods, etc."
                    class="w-full rounded-lg border border-gray-300 bg-transparent px-5 py-3 text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                >{{ old('remarks') }}</textarea>
                @error('remarks')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4">
                <button
                    type="submit"
                    onclick="return confirm('Are you sure you want to transfer this amount? This action cannot be undone.')"
                    class="flex w-full justify-center rounded-lg bg-brand-500 p-3 font-medium text-white transition hover:bg-brand-600"
                >
                    Confirm Transfer
                </button>
            </div>

            <div class="text-center">
                <a href="{{ route('wallet.history') }}" class="text-sm text-gray-500 hover:text-brand-500 dark:text-gray-400">
                    Cancel and Return
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
