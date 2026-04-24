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
                <label for="user_search" class="mb-2.5 block font-medium text-gray-700 dark:text-gray-300">
                    Recipient (Search by Name, ID, Phone or Email)
                </label>
                <div class="relative">
                    <input
                        type="text"
                        id="user_search"
                        placeholder="Type to search recipient..."
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-5 py-3 text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        autocomplete="off"
                    />
                    <input type="hidden" name="user_id" id="user_id_hidden">
                    <input type="hidden" name="email" id="email_hidden">
                    
                    <div id="search-loader" class="absolute right-4 top-1/2 -translate-y-1/2 hidden">
                        <svg class="animate-spin h-5 w-5 text-brand-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>

                    <div id="search-results" class="absolute z-50 w-full bg-white dark:bg-boxdark border border-stroke dark:border-strokedark rounded-b shadow-lg hidden max-h-60 overflow-y-auto mt-1">
                    </div>
                </div>
                
                <div id="recipient-info-wrapper" class="mt-3 p-4 rounded-xl bg-brand-50/50 dark:bg-white/5 border border-brand-100 dark:border-gray-800 hidden">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-brand-500 flex items-center justify-center text-white font-bold" id="recipient-initial">
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800 dark:text-white" id="recipient-display-name"></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400" id="recipient-display-meta"></p>
                        </div>
                    </div>
                </div>

                @error('email')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
                @error('user_id')
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
                    type="button"
                    onclick="handleTransferSubmit()"
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('user_search');
        const resultsContainer = document.getElementById('search-results');
        const loader = document.getElementById('search-loader');
        const userIdHidden = document.getElementById('user_id_hidden');
        const emailHidden = document.getElementById('email_hidden');
        const infoWrapper = document.getElementById('recipient-info-wrapper');
        const displayName = document.getElementById('recipient-display-name');
        const displayMeta = document.getElementById('recipient-display-meta');
        const displayInitial = document.getElementById('recipient-initial');
        
        let timeout = null;

        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                resultsContainer.classList.add('hidden');
                return;
            }

            timeout = setTimeout(() => {
                loader.classList.remove('hidden');
                fetch(`{{ route('wallet.user.search') }}?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        loader.classList.add('hidden');
                        let html = '';
                        if (data.length > 0) {
                            data.forEach(user => {
                                // escape single quotes in name for the onclick attribute
                                const escapedName = user.name.replace(/'/g, "\\'");
                                html += `
                                    <div class="p-3 hover:bg-gray-100 dark:hover:bg-meta-4 cursor-pointer border-b border-stroke dark:border-strokedark last:border-0" 
                                         onclick="selectRecipient(${user.id}, '${escapedName}', '${user.email}', '${user.phone || ''}')">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="font-bold text-black dark:text-white text-sm">${user.name}</span>
                                            <span class="text-[10px] bg-gray-200 dark:bg-meta-4 px-1.5 rounded text-gray-600 dark:text-gray-400">ID: ${user.id}</span>
                                        </div>
                                        <div class="text-xs text-gray-500 flex flex-col">
                                            <span>${user.email}</span>
                                            ${user.phone ? `<span class="text-gray-400">${user.phone}</span>` : ''}
                                        </div>
                                    </div>`;
                            });
                            resultsContainer.innerHTML = html;
                            resultsContainer.classList.remove('hidden');
                        } else {
                            resultsContainer.innerHTML = '<div class="p-4 text-center text-sm text-gray-500">No users found</div>';
                            resultsContainer.classList.remove('hidden');
                        }
                    })
                    .catch(error => {
                        loader.classList.add('hidden');
                        console.error('Search error:', error);
                    });
            }, 300);
        });

        // Close results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
                resultsContainer.classList.add('hidden');
            }
        });

        window.selectRecipient = function(id, name, email, phone) {
            userIdHidden.value = id;
            emailHidden.value = email;
            searchInput.value = '';
            resultsContainer.classList.add('hidden');
            
            displayName.textContent = name;
            displayMeta.textContent = `${email} ${phone ? '| ' + phone : ''}`;
            displayInitial.textContent = name.charAt(0).toUpperCase();
            infoWrapper.classList.remove('hidden');
            
            // Scroll to recipient info
            infoWrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        };

        // Show success message if present
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Transfer Complete',
                text: "{{ session('success') }}",
                confirmButtonColor: '#3085d6',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('wallet.transfer') }}";
                }
            });
        @endif
    });

    function handleTransferSubmit() {
        const amount = document.getElementById('amount').value;
        const userId = document.getElementById('user_id_hidden').value;
        const recipientName = document.getElementById('recipient-display-name').textContent;
        const recipientMeta = document.getElementById('recipient-display-meta').textContent;
        
        if (!amount || !userId) {
            Swal.fire({
                icon: 'error',
                title: 'Recipient Required',
                text: 'Please search and select a recipient before proceeding.',
            });
            return;
        }

        if (amount < 1) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Amount',
                text: 'Minimum transfer amount is ₹1.00',
            });
            return;
        }

        Swal.fire({
            title: 'Confirm Transfer',
            html: `
                <div class="text-center p-2">
                    <p class="mb-4">Are you sure you want to transfer balance?</p>
                    <div class="bg-gray-50 dark:bg-meta-4 p-4 rounded-lg border border-stroke dark:border-strokedark mb-4">
                        <p class="text-2xl font-bold text-brand-600 mb-1">₹${parseFloat(amount).toFixed(2)}</p>
                        <p class="text-sm text-gray-500">Amount</p>
                    </div>
                    <div class="flex items-center gap-3 text-left bg-brand-50/30 dark:bg-white/5 p-3 rounded-lg border border-brand-100 dark:border-gray-800">
                        <div class="h-10 w-10 rounded-full bg-brand-500 flex items-center justify-center text-white font-bold">
                            ${recipientName.charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800 dark:text-white">${recipientName}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">${recipientMeta}</p>
                        </div>
                    </div>
                    <p class="mt-4 text-xs text-red-500 italic">This action cannot be undone.</p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, transfer now!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.querySelector('form').submit();
            }
        });
    }
</script>
@endpush
@endsection
