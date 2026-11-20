@if(session()->has('admin_impersonator_id'))
    <div class="sticky top-0 z-50 w-full bg-gradient-to-r from-amber-600 via-orange-600 to-red-600 text-white px-4 py-2 text-xs sm:text-sm font-bold flex flex-wrap items-center justify-between gap-2 shadow-lg border-b border-amber-700/50">
        <div class="flex items-center gap-2">
            <span class="flex h-2.5 w-2.5 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-yellow-200"></span>
            </span>
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span>
                Admin Impersonation Mode: Currently viewing as <strong class="underline decoration-white/70 underline-offset-2">{{ auth()->user()->name }}</strong> (ID: #{{ auth()->id() }})
            </span>
        </div>
        <form action="{{ route('impersonate.leave') }}" method="POST" class="inline m-0">
            @csrf
            <button type="submit" 
                    title="Switch back to original Admin account"
                    class="bg-white text-gray-900 hover:bg-yellow-50 active:scale-95 font-extrabold px-3 py-1 rounded-md shadow-xs text-xs flex items-center gap-1.5 transition">
                <span>Exit & Return to Admin</span>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </button>
        </form>
    </div>
@endif
