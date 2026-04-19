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
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 mb-6">
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
    const res = await fetch('/api/payment-requests', {
        method:'POST', 
        credentials:'same-origin', 
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }, 
        body:JSON.stringify(body)
    });
    if(res.ok){ alert('Request created'); e.target.reset(); loadRequests(); }
    else { const j=await res.json(); alert(j.message||'Error'); }
});

loadRequests();
</script>
@endsection
