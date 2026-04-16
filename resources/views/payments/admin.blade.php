@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-4">
    <h2 class="text-xl font-semibold mb-4">Admin - Manual Payments</h2>

    <div id="requests" class="space-y-3"></div>
</div>

<script>
async function loadAll(){
    const res = await fetch('/api/admin/payment-requests', {credentials:'same-origin'});
    const json = await res.json();
    const el = document.getElementById('requests'); el.innerHTML='';
    json.data.forEach(r=>{
        const d = document.createElement('div');
        d.className='p-3 bg-white rounded shadow flex items-start justify-between';
        d.innerHTML = `
            <div>
                <div class="font-medium">User: ${r.user_id} — ₹${r.amount}</div>
                <div class="text-sm text-gray-600">${r.method||''} • ${r.reference||''}</div>
                <div class="text-sm mt-2">Status: <strong>${r.status}</strong></div>
            </div>
            <div class="flex flex-col gap-2">
                <button onclick="approve(${r.id})" class="bg-green-600 text-white px-3 py-1 rounded">Approve</button>
                <button onclick="reject(${r.id})" class="bg-red-600 text-white px-3 py-1 rounded">Reject</button>
            </div>
        `;
        el.appendChild(d);
    });
}

async function approve(id){
    const res = await fetch('/api/admin/payment-requests/'+id+'/approve', {method:'POST', credentials:'same-origin'});
    if(res.ok){ alert('Approved'); loadAll(); } else { alert('Error'); }
}

async function reject(id){
    const res = await fetch('/api/admin/payment-requests/'+id+'/reject', {method:'POST', credentials:'same-origin'});
    if(res.ok){ alert('Rejected'); loadAll(); } else { alert('Error'); }
}

loadAll();
</script>

@endsection
