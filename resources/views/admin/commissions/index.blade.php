@extends('admin.layout')

@section('content')
<div class="mx-auto max-w-270">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            All Referral Commissions
        </h2>
    </div>

    <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="max-w-full overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-2 text-left dark:bg-meta-4">
                        <th class="min-w-[150px] py-4 px-4 font-medium text-black dark:text-white">Receiver</th>
                        <th class="min-w-[150px] py-4 px-4 font-medium text-black dark:text-white">From User</th>
                        <th class="min-w-[100px] py-4 px-4 font-medium text-black dark:text-white">Level</th>
                        <th class="min-w-[120px] py-4 px-4 font-medium text-black dark:text-white">Amount</th>
                        <th class="min-w-[120px] py-4 px-4 font-medium text-black dark:text-white">Type</th>
                        <th class="py-4 px-4 font-medium text-black dark:text-white">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($commissions as $comm)
                    <tr>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            <p class="text-black dark:text-white">{{ $comm->user->name }}</p>
                            <p class="text-xs">{{ $comm->user->email }}</p>
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            <p class="text-black dark:text-white">{{ $comm->fromUser->name }}</p>
                            <p class="text-xs">{{ $comm->fromUser->email }}</p>
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            <p class="text-black dark:text-white">L{{ $comm->level }}</p>
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            <p class="text-black dark:text-white">{{ number_format($comm->amount, 2) }}</p>
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            <span class="inline-flex rounded-full bg-opacity-10 py-1 px-3 text-sm font-medium {{ $comm->type === 'joining' ? 'bg-success text-success' : 'bg-primary text-primary' }}">
                                {{ ucfirst($comm->type) }}
                            </span>
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            <p class="text-black dark:text-white">{{ $comm->created_at->format('Y-m-d H:i') }}</p>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $commissions->links() }}
        </div>
    </div>
</div>
@endsection
