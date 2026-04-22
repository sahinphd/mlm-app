@php
    $depth = $depth ?? 1;
    // Use pre-calculated count if available, otherwise fetch
    $hasChildren = ($user->referred_users_count ?? $user->referredUsers()->count()) > 0;
@endphp
<li data-user-id="{{ $user->id }}" class="genealogy-item {{ $hasChildren ? 'has-children' : '' }}">
    <div class="genealogy-node flex items-center gap-3 p-3 bg-white dark:bg-boxdark border border-stroke dark:border-strokedark rounded-lg shadow-sm">
        <img src="{{ $user->avatar_url }}" alt="avatar" class="w-10 h-10 rounded-full object-cover border border-stroke dark:border-strokedark">
        <div class="genealogy-info flex-grow">
            <a href="{{ route('admin.users.show', $user->id) }}" class="genealogy-name font-bold text-black dark:text-white hover:text-primary transition-colors">
                {{ $user->name }}
            </a>
            <div class="genealogy-meta text-xs text-gray-500">
                ID: #{{ $user->id }} | {{ $user->referralRecord->referral_code ?? 'N/A' }} | 
                <span class="{{ $user->status === 'active' ? 'text-success' : 'text-danger' }}">{{ ucfirst($user->status) }}</span>
            </div>
        </div>
        
        @if($hasChildren)
            <button class="genealogy-toggle ml-2 p-1 text-gray-400 hover:text-primary transition-colors" 
                    title="{{ $depth >= 3 ? 'Load Children' : 'Toggle Children' }}"
                    data-loaded="{{ $depth < 3 ? 'true' : 'false' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transform transition-transform {{ $depth < 3 ? 'rotate-90' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
        @endif

        <a href="{{ route('admin.users.genealogy', $user->id) }}" class="ml-1 p-1 text-gray-400 hover:text-success transition-colors" title="Focus on this branch">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
        </a>
    </div>

    @if($hasChildren)
        <ul class="children-container {{ $depth >= 3 ? 'hidden' : '' }}">
            @if($depth < 3)
                @php
                    $children = $user->referredUsers()->with(['referralRecord'])->withCount('referredUsers')->get();
                @endphp
                @foreach($children as $child)
                    @include('admin.partials.genealogy_node', ['user' => $child, 'isRoot' => false, 'depth' => $depth + 1])
                @endforeach
            @endif
        </ul>
    @endif
</li>
