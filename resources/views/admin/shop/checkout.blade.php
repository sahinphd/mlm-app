@extends('admin.layout')

@section('content')
<div class="mx-auto max-w-270">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            Admin Checkout
        </h2>
    </div>

    @if(session('error'))
        <div class="mb-6 flex w-full border-l-6 border-[#F87171] bg-[#F87171] bg-opacity-[15%] px-7 py-4 shadow-md dark:bg-[#1b1b1b] md:p-5">
            <div class="w-full">
                <p class="text-base leading-relaxed text-body">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.shop.place-order') }}" method="POST">
        @csrf
        <input type="hidden" name="item_id" value="{{ $item->id }}">
        <input type="hidden" name="type" value="{{ $type }}">
        <input type="hidden" name="quantity" value="{{ $quantity }}">

        <div class="grid grid-cols-1 gap-9 sm:grid-cols-2">
            <div class="flex flex-col gap-9">
                <!-- User Selection -->
                <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
                    <div class="border-b border-stroke py-4 px-6.5 dark:border-strokedark flex items-center justify-between">
                        <h3 class="font-medium text-black dark:text-white">Select User</h3>
                        <div id="selected-user-badge" class="hidden">
                            <span class="inline-flex items-center rounded-full bg-success/10 px-3 py-1 text-xs font-medium text-success">User Selected</span>
                        </div>
                    </div>
                    <div class="p-6.5">
                        <!-- User Details Top Card -->
                        <div id="selected-user-details" class="mb-6 hidden rounded-lg border border-stroke bg-gray-2 p-4 dark:border-strokedark dark:bg-meta-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[10px] uppercase text-body">Name</p>
                                    <p class="text-sm font-semibold text-black dark:text-white" id="det-name">-</p>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase text-body">Joining Date</p>
                                    <p class="text-sm font-semibold text-black dark:text-white" id="det-join">-</p>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase text-body">Main Wallet</p>
                                    <p class="text-sm font-semibold text-success" id="det-wallet">Rs.0.00</p>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase text-body">Available Credit</p>
                                    <p class="text-sm font-semibold text-primary" id="det-credit">Rs.0.00</p>
                                    <p class="text-[9px] italic" id="det-credit-status"></p>
                                </div>
                                <div class="col-span-2 mt-2 pt-2 border-t border-stroke dark:border-strokedark">
                                    <p class="text-[10px] uppercase text-body">Last Shopping Date</p>
                                    <p class="text-sm font-semibold text-black dark:text-white" id="det-last-shop">-</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4.5">
                            <label class="mb-2.5 block text-black dark:text-white">Search User (Name/Email/Phone)</label>
                            <div class="relative">
                                <span class="absolute left-4.5 top-1/2 -translate-y-1/2">
                                    <svg class="fill-body hover:fill-primary" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15.7501 14.4656L12.5157 11.2313C13.4438 10.05 14.0063 8.55 14.0063 6.975C14.0063 3.12187 10.8844 0 7.03125 0C3.17812 0 0.05625 3.12187 0.05625 6.975C0.05625 10.8281 3.17812 13.95 7.03125 13.95C8.60625 13.95 10.1063 13.3875 11.2875 12.4594L14.5219 15.6938C14.6906 15.8625 14.9156 15.9469 15.1406 15.9469C15.3656 15.9469 15.5906 15.8625 15.7594 15.6938C16.0969 15.3563 16.0969 14.8031 15.7501 14.4656ZM1.51875 6.975C1.51875 3.9375 3.99375 1.4625 7.03125 1.4625C10.0688 1.4625 12.5438 3.9375 12.5438 6.975C12.5438 10.0125 10.0688 12.4875 7.03125 12.4875C3.99375 12.4875 1.51875 10.0125 1.51875 6.975Z" fill=""/>
                                    </svg>
                                </span>
                                <input type="text" id="user-search" autocomplete="off" placeholder="Start typing name, email or phone..." class="w-full rounded border-[1.5px] border-stroke bg-transparent py-3 pl-11.5 pr-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary" />
                                <div id="user-results" class="absolute left-0 right-0 z-50 mt-1 max-h-60 overflow-y-auto rounded border border-stroke bg-white shadow-lg dark:border-strokedark dark:bg-boxdark hidden"></div>
                            </div>
                            <input type="hidden" name="user_id" id="selected-user-id" required>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
                    <div class="border-b border-stroke py-4 px-6.5 dark:border-strokedark">
                        <h3 class="font-medium text-black dark:text-white">Order Summary</h3>
                    </div>
                    <div class="p-6.5">
                        <div class="mb-4 flex items-center justify-between">
                            <span class="font-medium">Item:</span>
                            <span>{{ $item->name }}</span>
                        </div>
                        <div class="mb-4 flex items-center justify-between">
                            <span class="font-medium">Quantity:</span>
                            <span>{{ $quantity }}</span>
                        </div>
                        <div class="mb-4 flex items-center justify-between">
                            <span class="font-medium">BV:</span>
                            <span class="text-meta-3">{{ $item->bv * $quantity }}</span>
                        </div>
                        <div class="mt-4 border-t border-stroke pt-4 flex items-center justify-between">
                            <span class="text-lg font-bold">Total:</span>
                            <span class="text-lg font-bold text-primary">Rs.{{ number_format($item->price * $quantity, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-9">
                <!-- Payment Method -->
                <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
                    <div class="border-b border-stroke py-4 px-6.5 dark:border-strokedark">
                        <h3 class="font-medium text-black dark:text-white">Payment Method</h3>
                    </div>
                    <div class="p-6.5">
                        <div class="flex flex-col gap-4">
                            <label class="flex cursor-pointer select-none items-center gap-4 rounded border border-stroke p-4 dark:border-strokedark">
                                <input type="radio" name="payment_method" value="manual_cash" checked class="h-5 w-5">
                                <div class="flex flex-col">
                                    <span class="font-medium text-black dark:text-white">Manual Cash</span>
                                    <span class="text-sm">Payment collected manually</span>
                                </div>
                            </label>
                            
                            <label class="flex cursor-pointer select-none items-center gap-4 rounded border border-stroke p-4 dark:border-strokedark">
                                <input type="radio" name="payment_method" value="main_wallet" class="h-5 w-5">
                                <div class="flex flex-col">
                                    <span class="font-medium text-black dark:text-white">User Main Wallet</span>
                                    <span class="text-sm">Deduct from user's balance</span>
                                </div>
                            </label>

                            <label class="flex cursor-pointer select-none items-center gap-4 rounded border border-stroke p-4 dark:border-strokedark">
                                <input type="radio" name="payment_method" value="credit_wallet" class="h-5 w-5">
                                <div class="flex flex-col">
                                    <span class="font-medium text-black dark:text-white">User Credit Account</span>
                                    <span class="text-sm">Place order on EMI</span>
                                </div>
                            </label>
                        </div>

                        <button type="submit" class="mt-8 flex w-full justify-center rounded bg-primary py-3 px-6 font-medium text-gray hover:bg-opacity-90">
                            Confirm Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('user-search');
    const resultsDiv = document.getElementById('user-results');
    const selectedIdInput = document.getElementById('selected-user-id');
    
    // Detail elements
    const detDetails = document.getElementById('selected-user-details');
    const detBadge = document.getElementById('selected-user-badge');
    const detName = document.getElementById('det-name');
    const detJoin = document.getElementById('det-join');
    const detWallet = document.getElementById('det-wallet');
    const detCredit = document.getElementById('det-credit');
    const detCreditStatus = document.getElementById('det-credit-status');
    const detLastShop = document.getElementById('det-last-shop');

    let timeout = null;

    searchInput.addEventListener('input', function() {
        clearTimeout(timeout);
        const q = this.value;

        if (q.length < 2) {
            resultsDiv.classList.add('hidden');
            return;
        }

        timeout = setTimeout(() => {
            fetch(`{{ route('admin.users.search') }}?q=${encodeURIComponent(q)}`)
                .then(res => res.json())
                .then(users => {
                    resultsDiv.innerHTML = '';
                    if (users.length > 0) {
                        users.forEach(user => {
                            const div = document.createElement('div');
                            div.className = 'p-3 hover:bg-gray-2 dark:hover:bg-meta-4 cursor-pointer border-b border-stroke dark:border-strokedark';
                            div.innerHTML = `
                                <div class="font-medium text-black dark:text-white">${user.name}</div>
                                <div class="text-xs">${user.email} | ${user.phone || 'No phone'}</div>
                                <div class="text-[10px] text-gray-500">Joined: ${user.join_date}</div>
                            `;
                            div.onclick = () => {
                                // Set hidden ID
                                selectedIdInput.value = user.id;
                                
                                // Update search input
                                searchInput.value = user.name;
                                
                                // Update Details Card
                                detName.textContent = user.name;
                                detJoin.textContent = user.join_date;
                                detWallet.textContent = 'Rs.' + parseFloat(user.wallet_balance).toLocaleString(undefined, {minimumFractionDigits: 2});
                                detCredit.textContent = 'Rs.' + parseFloat(user.credit_limit).toLocaleString(undefined, {minimumFractionDigits: 2});
                                detLastShop.textContent = user.last_shopping;
                                
                                if(user.credit_approved) {
                                    detCreditStatus.textContent = 'Approved';
                                    detCreditStatus.className = 'text-[9px] italic text-success font-medium';
                                } else {
                                    detCreditStatus.textContent = 'Not Approved';
                                    detCreditStatus.className = 'text-[9px] italic text-danger font-medium';
                                }

                                // Show details and badge
                                detDetails.classList.remove('hidden');
                                detBadge.classList.remove('hidden');
                                
                                resultsDiv.classList.add('hidden');
                            };
                            resultsDiv.appendChild(div);
                        });
                        resultsDiv.classList.remove('hidden');
                    } else {
                        resultsDiv.innerHTML = '<div class="p-3 text-sm">No users found</div>';
                        resultsDiv.classList.remove('hidden');
                    }
                });
        }, 300);
    });

    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
            resultsDiv.classList.add('hidden');
        }
    });
});
</script>
@endsection
