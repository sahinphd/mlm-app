@extends('layouts.admin')

@section('content')
<div class="grid grid-cols-1 gap-4 md:gap-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 text-center">
        <h2 class="mb-3 text-2xl font-semibold text-gray-800 dark:text-white/90">Referral Program</h2>
        <p class="mb-6 text-gray-500 dark:text-gray-400">Invite your friends and earn commissions when they join and make purchases.</p>
        
        @if($referralRecord)
        <div class="mb-6 p-4 bg-gray-50 dark:bg-white/5 rounded-xl inline-block">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Your Referral Link:</p>
            <div class="flex items-center gap-2">
                <input type="text" id="refLinkInput" readonly value="{{ url('/register?ref=' . $referralRecord->referral_code) }}" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded px-3 py-2 text-sm w-64">
                <button onclick="copyRefLink()" class="bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 px-4 py-2 rounded text-sm hover:bg-gray-300 dark:hover:bg-gray-700 transition">Copy</button>
                <button onclick="shareRefLink()" class="bg-brand-500 text-white px-4 py-2 rounded text-sm hover:bg-brand-600 transition flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                    Share
                </button>
            </div>
        </div>
        @endif
    </div>

    <!-- Referred Users Section -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <h3 class="mb-5 text-xl font-semibold text-gray-800 dark:text-white/90">Your Referred Users</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Name</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Email</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Status</th>
                        <th class="pb-3 text-sm font-medium text-gray-500 dark:text-gray-400 text-right">Joined Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($referredUsers as $ref)
                    <tr>
                        <td class="py-4 text-sm text-gray-800 dark:text-white/90 font-medium">{{ $ref->name }}</td>
                        <td class="py-4 text-sm text-gray-500 dark:text-gray-400">{{ $ref->email }}</td>
                        <td class="py-4">
                            @if($ref->status === 'active')
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-600">Active</span>
                            @elseif($ref->status === 'pending')
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-50 text-yellow-600">Pending</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-600">Blocked</span>
                            @endif
                        </td>
                        <td class="py-4 text-sm text-gray-500 dark:text-gray-400 text-right">{{ $ref->created_at->format('M d, Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-gray-400 italic">You haven't referred any users yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $referredUsers->links() }}
        </div>
    </div>
</div>

<script>
function copyRefLink() {
    const copyText = document.getElementById('refLinkInput');
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);
    alert("Referral link copied!");
}

async function shareRefLink() {
    const shareData = {
        title: '{{ config('app.name', 'MLM App') }}',
        text: 'Join me on {{ config('app.name', 'MLM App') }} and start earning! Register using my link:',
        url: '{{ url('/register?ref=' . ($referralRecord->referral_code ?? '')) }}'
    };

    try {
        if (navigator.share) {
            await navigator.share(shareData);
        } else {
            copyRefLink();
            alert("Share API not supported. Referral link copied to clipboard!");
        }
    } catch (err) {
        console.log('Error sharing:', err);
    }
}
</script>
@endsection
