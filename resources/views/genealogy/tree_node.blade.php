<div class="relative" x-data="{ expanded: true, showExplore: false }">
    <!-- Vertical line segment -->
    @if(!$isLast)
        <div class="tree-vertical-line h-full"></div>
    @else
        <div class="tree-vertical-line h-[27px]"></div>
    @endif

    <!-- Horizontal connector line -->
    <div class="tree-node-connector"></div>

    <div class="flex items-center gap-3">
        <!-- Node Card -->
        <div 
            @mouseenter="showExplore = true" 
            @mouseleave="showExplore = false"
            class="group flex items-center flex-1 min-w-0 p-3.5 rounded-xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-white/[0.03] shadow-theme-xs transition-all hover:border-brand-400 dark:hover:border-brand-500/50 hover:shadow-md"
        >
            <div class="relative flex-shrink-0">
                <img src="{{ $node['user']->avatar_url }}" alt="Avatar" class="w-10 h-10 rounded-full" />
                <div class="absolute -top-1 -right-1 flex items-center justify-center w-5 h-5 rounded-full bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm text-[10px] font-bold text-gray-500">
                    L{{ $node['level'] }}
                </div>
            </div>
            
            <div class="ml-4 flex-1 min-w-0">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-bold text-gray-800 dark:text-white truncate">{{ $node['user']->name }}</span>
                    
                    <div x-show="showExplore" x-cloak x-transition class="flex items-center">
                        <a 
                            href="{{ route('genealogy.index', ['root_id' => $node['user']->id]) }}" 
                            class="text-[10px] font-bold uppercase text-brand-500 hover:text-brand-600 dark:text-brand-400 flex items-center gap-1"
                        >
                            Explore Tree
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $node['user']->email }}</p>
            </div>

            @if(count($node['children']) > 0)
                <button @click="expanded = !expanded" class="ml-3 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 text-gray-400 transition-colors">
                    <svg :class="expanded ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            @endif
        </div>
    </div>

    <!-- Recursive Children -->
    @if(count($node['children']) > 0)
        <div 
            x-show="expanded" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="relative pl-12 mt-6 space-y-6"
        >
            @foreach($node['children'] as $child)
                @include('genealogy.tree_node', ['node' => $child, 'isLast' => $loop->last])
            @endforeach
        </div>
    @endif
</div>
