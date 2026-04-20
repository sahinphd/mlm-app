@extends('layouts.admin')

@section('content')
<div class="grid grid-cols-1 gap-4 md:gap-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 text-center">
        <h2 class="mb-3 text-2xl font-semibold text-gray-800 dark:text-white/90">EMI & Penalty Management</h2>
        <p class="mb-6 text-gray-500 dark:text-gray-400">Manage your credit repayments and track any outstanding penalties.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <!-- EMI Section -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <h3 class="mb-5 text-xl font-semibold text-gray-800 dark:text-white/90">Your EMI Schedule</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Due Date</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Order #</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400 text-right">Amount</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400 text-center">Status</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                    @forelse($emis as $emi)
                        <tr>
                            <td class="py-4">{{ \Carbon\Carbon::parse($emi->due_date)->format('M d, Y') }}</td>
                            <td class="py-4">#{{ $emi->order_id }}</td>
                            <td class="py-4 text-right font-bold">₹{{ number_format($emi->installment_amount, 2) }}</td>
                            <td class="py-4 text-center">
                                @if($emi->status === 'paid')
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-600">Paid</span>
                                @elseif($emi->status === 'overdue')
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-600">Overdue</span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-600">Pending</span>
                                @endif
                            </td>
                            <td class="py-4 text-center">
                                @if($emi->status !== 'paid')
                                    <form action="{{ route('credit.emis.pay', $emi->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to pay this EMI from your main wallet?')">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-primary py-2 px-4 text-center text-sm font-medium text-white hover:bg-opacity-90">
                                            Pay Now
                                        </button>
                                    </form>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-gray-500">No EMI schedules found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $emis->links() }}
        </div>
    </div>

    <!-- Penalties Section -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <h3 class="mb-5 text-xl font-semibold text-gray-800 dark:text-white/90">Penalties</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Date</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">EMI Ref #</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400 text-right">Amount</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400 text-center">Status</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                    @forelse($penalties as $penalty)
                        <tr>
                            <td class="py-4">{{ $penalty->created_at->format('M d, Y') }}</td>
                            <td class="py-4">EMI #{{ $penalty->emi_schedule_id }}</td>
                            <td class="py-4 text-right font-bold">₹{{ number_format($penalty->amount, 2) }}</td>
                            <td class="py-4 text-center">
                                @if($penalty->status === 'paid')
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-600">Paid</span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-600">Unpaid</span>
                                @endif
                            </td>
                            <td class="py-4 text-center">
                                @if($penalty->status !== 'paid')
                                    <form action="{{ route('credit.penalties.pay', $penalty->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to pay this penalty from your main wallet?')">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-red-500 py-2 px-4 text-center text-sm font-medium text-white hover:bg-opacity-90">
                                            Pay Penalty
                                        </button>
                                    </form>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-gray-500">No penalties found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $penalties->links() }}
        </div>
    </div>
</div>
@endsection
