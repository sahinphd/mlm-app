<li>
    <div class="genealogy-node">
        <img src="{{ $user->avatar_url }}" alt="avatar">
        <div class="genealogy-info">
            <a href="{{ route('admin.users.show', $user->id) }}" class="genealogy-name hover:text-primary">
                {{ $user->name }}
            </a>
            <span class="genealogy-meta">
                ID: #{{ $user->id }} | {{ $user->referralRecord->referral_code ?? 'N/A' }} | 
                <span class="{{ $user->status === 'active' ? 'text-success' : 'text-danger' }}">{{ ucfirst($user->status) }}</span>
            </span>
        </div>
        <a href="{{ route('admin.users.genealogy', $user->id) }}" class="ml-2 text-[10px] text-gray-400 hover:text-primary" title="View this branch">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
        </a>
    </div>

    @php
        $children = $user->referredUsers()->with('referralRecord')->get();
    @endphp

    @if($children->count() > 0)
        <ul>
            @foreach($children as $child)
                @include('admin.partials.genealogy_node', ['user' => $child, 'isRoot' => false])
            @endforeach
        </ul>
    @endif
</li>
