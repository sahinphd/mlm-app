@extends('layouts.admin')

@section('content')
<?php
    $settingsFile = 'settings.json';
    $currency = 'INR';
    if (Illuminate\Support\Facades\Storage::disk('local')->exists($settingsFile)) {
        $s = json_decode(Illuminate\Support\Facades\Storage::disk('local')->get($settingsFile), true);
        $currency = $s['currency'] ?? 'INR';
    }
?>
<div class="max-w-2xl mx-auto">
    @php
        $user = auth()->user();
        $isRestricted = ($user->role !== 'admin' && $user->kyc_status !== 'approved');
        $upiId = env('APP_QR_CODE', 'sahinahmed.com@ybl');
        $qrUrl = 'https://quickchart.io/qr?text=upi%3A%2F%2Fpay%3Fpa%3D' . urlencode($upiId) . '%26pn%3DDuare%20Dokandar%26cu%3DINR&size=160&margin=2';
    @endphp

    <div class="mb-6 p-6 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-sm">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="text-center md:text-left">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Pay via UPI QR</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Scan the QR code to pay manually, then fill the form below.</p>
                <div class="mt-4 inline-flex items-center gap-2 bg-gray-100 dark:bg-gray-700 px-4 py-2 rounded-full">
                    <span class="font-mono text-xs font-bold text-gray-700 dark:text-gray-300">{{ $upiId }}</span>
                    <button onclick="copyUPI('{{ $upiId }}')" class="text-brand-600 hover:text-brand-700 text-xs font-semibold ml-2">Copy</button>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-900 p-2 rounded-xl shadow-inner border border-gray-100 dark:border-gray-800">
                <img src="{{ $qrUrl }}" alt="UPI QR" class="w-32 h-32 rounded-lg">
                <p class="text-center text-[10px] text-gray-400 mt-1 uppercase font-bold tracking-wider">Scan to pay</p>
            </div>
        </div>
    </div>

    @if($isRestricted)
        <div class="mb-6 p-6 rounded-2xl border border-warning-200 bg-warning-50 flex flex-col items-center text-center gap-4">
            <div class="h-16 w-16 rounded-full bg-warning-100 flex items-center justify-center text-warning-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                @if($user->kyc_status === 'pending')
                    <h3 class="text-lg font-bold text-warning-800">KYC Approval Pending</h3>
                    <p class="text-sm text-warning-700 mt-1">Your profile has been submitted for KYC. Please wait for an admin to approve it before you can top-up your wallet.</p>
                @else
                    <h3 class="text-lg font-bold text-warning-800">KYC Approval Required</h3>
                    <p class="text-sm text-warning-700 mt-1">You must complete your profile and have your KYC approved by the admin before you can top-up your wallet.</p>
                @endif
            </div>
            <a href="{{ route('profile.edit') }}" class="px-5 py-2.5 rounded bg-dark-900 text-danger text-sm font-semibold hover:bg-gray-800 transition">
                {{ $user->kyc_status === 'pending' ? 'View Profile' : 'Complete Profile' }}
            </a>
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 mb-6 {{ $isRestricted ? 'opacity-50 grayscale pointer-events-none' : '' }}">
        <h2 class="mb-5 text-xl font-semibold text-gray-800 dark:text-white/90">Wallet Top-up (Manual)</h2>

        <form id="payment-form" class="space-y-5">
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Amount<span class="text-error-500">*</span>
              </label>
              <input
                type="number"
                name="amount"
                step="0.01"
                required
                placeholder="0.00"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
              />
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Method (e.g. UPI/Bank)<span class="text-error-500">*</span>
              </label>
              <input
                type="text"
                name="method"
                required
                placeholder="UPI / Bank Transfer"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
              />
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Reference (optional)
              </label>
              <input
                type="text"
                name="reference"
                placeholder="Transaction ID"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
              />
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" id="submit-btn" class="flex items-center justify-center px-5 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                    Request Top-up
                </button>
            </div>
        </form>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <h3 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white/90">Your Requests</h3>
        <div id="requests" class="space-y-4"></div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const CURRENCY = '{{ $currency }}';
async function loadRequests(){
    const res = await fetch('/api/payment-requests', {credentials:'same-origin'});
    const json = await res.json();
    const el = document.getElementById('requests');
    el.innerHTML = '';
    
    if (json.data.length === 0) {
        el.innerHTML = '<p class="text-sm text-gray-500 text-center py-4">No payment requests yet.</p>';
        return;
    }

    json.data.forEach(r=>{
        const d = document.createElement('div');
        d.className = 'p-4 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-white/[0.03] space-y-2';
        
        let statusColor = 'bg-warning-50 text-warning-600';
        if(r.status === 'approved') statusColor = 'bg-success-50 text-white-600';
        if(r.status === 'rejected') statusColor = 'bg-error-50 text-error-600';

        const date = new Date(r.created_at).toLocaleString();
        
        d.innerHTML = `
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm font-semibold text-gray-800 dark:text-white/90">Amount: ${CURRENCY}${parseFloat(r.amount).toLocaleString()}</span>
                    <p class="text-xs text-gray-500 dark:text-gray-400">${r.method || 'N/A'} ${r.reference ? ' - ' + r.reference : ''}</p>
                </div>
                <div class="px-2.5 py-0.5 rounded-full text-xs font-medium ${statusColor}">
                    ${r.status.toUpperCase()}
                </div>
            </div>
            <div class="flex items-center justify-between text-[10px] text-gray-400">
                <span>${date}</span>
                ${r.processed_at ? `<span>Processed: ${new Date(r.processed_at).toLocaleString()}</span>` : ''}
            </div>
            ${r.admin_note ? `
                <div class="mt-2 p-2 rounded bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400 italic">
                    Note: ${r.admin_note}
                </div>
            ` : ''}
        `;
        el.appendChild(d);
    });
}

document.getElementById('payment-form').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const data = new FormData(e.target);
    const body = {amount: data.get('amount'), method: data.get('method'), reference: data.get('reference')};
    
    const submitBtn = document.getElementById('submit-btn');
    const originalText = submitBtn.innerText;
    submitBtn.disabled = true;
    submitBtn.innerText = 'Processing...';

    try {
        const res = await fetch('/api/payment-requests', {
            method:'POST', 
            credentials:'same-origin', 
            headers:{
                'Content-Type':'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }, 
            body:JSON.stringify(body)
        });

        if(res.ok){ 
            Swal.fire({
                icon: 'success',
                title: 'Request Created',
                text: 'Your payment top-up request has been submitted successfully.',
                confirmButtonColor: '#3085d6',
            });
            e.target.reset(); 
            loadRequests(); 
        } else { 
            const j = await res.json(); 
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: j.message || 'Something went wrong!',
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Could not connect to the server. Please try again later.',
        });
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerText = originalText;
    }
});

function copyUPI(id) {
    navigator.clipboard.writeText(id).then(() => {
        Swal.fire({
            icon: 'success',
            title: 'Copied!',
            text: 'UPI ID copied to clipboard: ' + id,
            timer: 2000,
            showConfirmButton: false
        });
    });
}

loadRequests();
</script>
@endpush
@endsection
