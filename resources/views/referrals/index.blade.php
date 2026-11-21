@extends('layouts.admin')

@section('content')
<div class="grid grid-cols-1 gap-4 md:gap-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 text-center">
        <h2 class="mb-3 text-2xl font-semibold text-gray-800 dark:text-white/90">Referral Program</h2>
        <p class="mb-6 text-gray-500 dark:text-gray-400">Invite your friends and earn commissions when they join and make purchases.</p>
        
       @if($referralRecord)
        <div class="mb-6 mx-auto w-full max-w-xl text-left">
            <div class="relative overflow-hidden rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50/70 via-white to-indigo-50/40 p-4 sm:p-5 shadow-xs dark:border-gray-800 dark:from-gray-900/80 dark:via-gray-900 dark:to-blue-950/20">
                <!-- Top Row: Icon, Title & Referral Code Badge -->
                <div class="flex flex-wrap items-center justify-between gap-2.5 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#1555a8]/10 text-[#1555a8] dark:bg-[#1555a8]/25 dark:text-blue-400">
                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white">Your Referral Link</span>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">Invite friends & earn direct rewards when they join</p>
                        </div>
                    </div>
                    
                    <button type="button" onclick="copyRefCode('{{ $referralRecord->referral_code }}')" 
                            class="group inline-flex items-center gap-1.5 rounded-lg border border-blue-200/80 bg-white/90 px-2.5 py-1 text-xs font-semibold text-gray-700 shadow-2xs transition-all hover:bg-blue-50 hover:border-blue-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700" 
                            title="Click to copy Referral Code">
                        <span class="text-[10px] uppercase tracking-wider text-gray-400 dark:text-gray-400">Code:</span>
                        <span class="font-mono font-bold text-[#1555a8] dark:text-blue-400">{{ $referralRecord->referral_code }}</span>
                        <svg class="h-3.5 w-3.5 text-gray-400 group-hover:text-[#1555a8] dark:group-hover:text-blue-400 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </button>
                </div>

                <!-- Input & Action Controls -->
                <div class="mt-1 flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                    <div class="relative flex-1 min-w-0">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 dark:text-gray-500">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                        </div>
                        <input type="text" 
                               id="refLinkInput" 
                               readonly 
                               value="{{ url('/register?ref=' . $referralRecord->referral_code) }}" 
                               onclick="this.select()"
                               class="w-full rounded-xl border border-gray-200 bg-white py-2 pl-9 pr-3 text-xs sm:text-sm font-medium text-gray-800 shadow-2xs focus:border-[#1555a8] focus:outline-none focus:ring-2 focus:ring-[#1555a8]/20 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100" />
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" 
                                id="btnCopyRef"
                                onclick="copyRefLink()" 
                                class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs sm:text-sm font-semibold text-gray-700 shadow-2xs transition-all hover:bg-gray-50 hover:text-black dark:border-gray-800 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                            <svg id="copyIconRef" class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <span id="copyTextRef">Copy</span>
                        </button>

                        <a href="https://api.whatsapp.com/send?text={{ rawurlencode('Join me on ' . config('app.name', 'MLM App') . '! Register using my referral link: ' . url('/register?ref=' . $referralRecord->referral_code)) }}" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#25D366] hover:bg-[#20ba59] px-3.5 py-2 text-xs sm:text-sm font-semibold text-white shadow-2xs transition-all"
                           title="Share on WhatsApp">
                            <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                            </svg>
                            <span class="hidden xs:inline">WhatsApp</span>
                        </a>

                        <button type="button" 
                                onclick="shareRefLink()" 
                                class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#1555a8] hover:bg-[#12468d] px-3.5 py-2 text-xs sm:text-sm font-semibold text-white shadow-2xs transition-all"
                                title="Share Referral Link">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                            <span>Share</span>
                        </button>
                    </div>
                </div>
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
    if (!copyText) return;
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(copyText.value);
    } else {
        document.execCommand('copy');
    }

    const btnText = document.getElementById('copyTextRef');
    const btnIcon = document.getElementById('copyIconRef');
    if (btnText) {
        const orig = btnText.textContent;
        btnText.textContent = 'Copied!';
        if (btnIcon) btnIcon.classList.add('text-green-600', 'dark:text-green-400');
        setTimeout(() => {
            btnText.textContent = orig;
            if (btnIcon) btnIcon.classList.remove('text-green-600', 'dark:text-green-400');
        }, 2000);
    }
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Copied!',
            text: 'Referral link copied to clipboard.',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
        });
    }
}

function copyRefCode(code) {
    if (!code) return;
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(code);
    } else {
        const temp = document.createElement('textarea');
        temp.value = code;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
    }
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Code Copied!',
            text: 'Referral code ' + code + ' copied to clipboard.',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
        });
    }
}

async function shareRefLink() {
    const shareData = {
        title: @json(config('app.name', 'MLM App')),
        text: 'Join me on ' + @json(config('app.name', 'MLM App')) + ' and start earning! Register using my link:',
        url: '{{ url('/register?ref=' . ($referralRecord->referral_code ?? '')) }}'
    };

    try {
        if (navigator.share) {
            await navigator.share(shareData);
        } else {
            copyRefLink();
        }
    } catch (err) {
        console.log('Error sharing:', err);
    }
}
</script>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
@endsection
