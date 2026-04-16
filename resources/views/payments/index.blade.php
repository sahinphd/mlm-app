@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto p-4">
    <h2 class="text-xl font-semibold mb-4">Wallet Top-up (Manual)</h2>

    <form id="payment-form" class="space-y-4 bg-white p-4 rounded shadow">
        <div>
            <label class="block text-sm font-medium">Amount</label>
            <input name="amount" type="number" step="0.01" class="mt-1 block w-full border p-2 rounded" required>
        </div>
        <div>
            <label class="block text-sm font-medium">Method (e.g. UPI/Bank)</label>
            <input name="method" class="mt-1 block w-full border p-2 rounded">
        </div>
        <div>
            <label class="block text-sm font-medium">Reference (optional)</label>
            <input name="reference" class="mt-1 block w-full border p-2 rounded">
        </div>
        <div class="flex justify-end">
            <button id="submit-btn" class="bg-blue-600 text-white px-4 py-2 rounded">Request Top-up</button>
        </div>
    </form>

    <h3 class="mt-6 text-lg font-medium">Your Requests</h3>
    <div id="requests" class="mt-2 space-y-2"></div>
</div>

<script>
async function loadRequests(){
    const res = await fetch('/api/payment-requests', {credentials:'same-origin'});
    const json = await res.json();
    const el = document.getElementById('requests');
    el.innerHTML = '';
    json.data.forEach(r=>{
        const d = document.createElement('div');
        d.className = 'p-3 bg-white rounded shadow';
        d.innerHTML = `<div class=\"flex items-center justify-between\"><div>Amount: ₹${r.amount}</div><div class=\"text-sm text-gray-600\">${r.status}</div></div>`;
        el.appendChild(d);
    });
}

document.getElementById('payment-form').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const data = new FormData(e.target);
    const body = {amount: data.get('amount'), method: data.get('method'), reference: data.get('reference')};
    const res = await fetch('/api/payment-requests', {method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body)});
    if(res.ok){ alert('Request created'); e.target.reset(); loadRequests(); }
    else { const j=await res.json(); alert(j.message||'Error'); }
});

loadRequests();
</script>

@endsection
