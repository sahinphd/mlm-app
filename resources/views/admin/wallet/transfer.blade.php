@extends('admin.layout')

@section('content')
<div class="mx-auto max-w-270">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            Admin Balance Transfer
        </h2>
        <a href="{{ route('admin.wallet.history') }}" class="flex items-center gap-2 rounded bg-primary py-2 px-6 font-medium text-white hover:bg-opacity-90">
            <svg class="fill-current" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15 8.25H5.8725L10.065 4.0575L9 3L3 9L9 15L10.0575 13.9425L5.8725 9.75H15V8.25Z" fill=""></path>
            </svg>
            Back to History
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 flex w-full border-l-6 border-[#34D399] bg-[#34D399] bg-opacity-[15%] px-7 py-4 shadow-md dark:bg-[#1b1b1b] md:p-5">
            <div class="mr-5 flex h-9 w-full max-w-[36px] items-center justify-center rounded-lg bg-[#34D399]">
                <svg width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15.2984 0.826822L15.2868 0.811827C14.9363 0.362657 14.3142 0.334114 13.9242 0.749007L6.6713 8.44851L2.03058 3.51139C1.6429 3.09848 1.0189 3.06733 0.612198 3.44296C0.205267 3.8188 0.165201 4.4533 0.525546 4.87634L5.91899 11.2185C6.1102 11.4423 6.38202 11.5714 6.66914 11.5714C6.95627 11.5714 7.22808 11.4423 7.41929 11.2185L15.2536 2.16453C15.6312 1.72412 15.655 1.0772 15.2984 0.826822Z" fill="white"></path>
                </svg>
            </div>
            <div class="w-full">
                <h5 class="mb-3 text-lg font-bold text-black dark:text-[#34D399]">
                    Success
                </h5>
                <p class="text-base leading-relaxed text-body">
                    {{ session('success') }}
                </p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 flex w-full border-l-6 border-[#F87171] bg-[#F87171] bg-opacity-[15%] px-7 py-4 shadow-md dark:bg-[#1b1b1b] md:p-5">
            <div class="mr-5 flex h-9 w-full max-w-[36px] items-center justify-center rounded-lg bg-[#F87171]">
                <svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M6.4917 7.65547L11.1097 12.2645C11.4304 12.5843 11.9505 12.5843 12.2712 12.2645C12.5919 11.9448 12.5919 11.4259 12.2712 11.1062L7.65322 6.49712L12.2712 1.88803C12.5919 1.56835 12.5919 1.04936 12.2712 0.729683C11.9505 0.410003 11.4304 0.410003 11.1097 0.729683L6.4917 5.33877L1.87373 0.729683C1.55306 0.410003 1.03288 0.410003 0.712215 0.729683C0.391547 1.04936 0.391547 1.56835 0.712215 1.88803L5.33018 6.49712L0.712215 11.1062C0.391547 11.4259 0.391547 11.9448 0.712215 12.2645C1.03288 12.5843 1.55306 12.5843 1.87373 12.2645L6.4917 7.65547Z" fill="white"></path>
                </svg>
            </div>
            <div class="w-full">
                <h5 class="mb-3 text-lg font-bold text-black dark:text-[#F87171]">
                    Error
                </h5>
                <ul class="list-disc list-inside text-base leading-relaxed text-body">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="border-b border-stroke py-4 px-6.5 dark:border-strokedark">
            <h3 class="font-medium text-black dark:text-white">
                Transfer Details
            </h3>
        </div>
        <form action="{{ route('admin.wallet.transfer.post') }}" method="POST">
            @csrf
            <div class="p-6.5">
                <div class="mb-4.5">
                    <label class="mb-2.5 block text-black dark:text-white">
                        Sender User (Admin) <span class="text-meta-1">*</span>
                    </label>
                    <input type="text" value="{{ $admin->name }} ({{ $admin->email }})" class="w-full rounded border-[1.5px] border-stroke bg-gray-100 py-3 px-5 font-medium outline-none transition dark:border-form-strokedark dark:bg-form-input cursor-not-allowed" readonly>
                    <p class="mt-1 text-xs text-gray-500 italic">Available Balance: ₹{{ number_format($admin->wallet->main_balance ?? 0, 2) }}</p>
                </div>

                <div class="mb-4.5">
                    <label class="mb-2.5 block text-black dark:text-white">
                        Recipient Email <span class="text-meta-1">*</span>
                    </label>
                    <input type="email" name="recipient_email" value="{{ old('recipient_email') }}" placeholder="Enter recipient email" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-3 px-5 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary" required>
                </div>

                <div class="mb-4.5">
                    <label class="mb-2.5 block text-black dark:text-white">
                        Amount (Main Balance) <span class="text-meta-1">*</span>
                    </label>
                    <input type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="1" placeholder="Enter amount" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-3 px-5 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary" required>
                </div>

                <div class="mb-6">
                    <label class="mb-2.5 block text-black dark:text-white">
                        Description / Remarks
                    </label>
                    <textarea rows="4" name="description" placeholder="Optional description" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-3 px-5 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">{{ old('description') }}</textarea>
                </div>

                <button type="submit" class="flex w-full justify-center rounded bg-primary p-3 font-medium text-gray hover:bg-opacity-90">
                    Process Transfer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
