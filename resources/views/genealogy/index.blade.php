@extends('layouts.admin')

@section('content')
<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Genealogy Explorer</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Showing 3 levels from <strong>{{ $rootUser->name }}</strong></p>
        </div>
        
        @if($rootUser->id !== auth()->id())
            <a href="{{ route('genealogy.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-brand-600 bg-brand-50 border border-brand-200 rounded-lg hover:bg-brand-100 dark:bg-brand-500/10 dark:border-brand-500/20 dark:text-brand-400 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Back to My Root
            </a>
        @endif
    </div>

    <div class="max-w-4xl mx-auto">
        <!-- Root Card (Current View Root) -->
        <div class="relative mb-8">
            <div class="flex items-center gap-4 p-5 rounded-xl border-2 border-brand-500 bg-brand-50/50 dark:bg-brand-500/5 shadow-sm relative z-10">
                <div class="relative">
                    <img src="{{ $rootUser->avatar_url }}" alt="Avatar" class="w-14 h-14 rounded-full border-2 border-white dark:border-gray-800 shadow-md" />
                    <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 border-2 border-white dark:border-gray-900 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <h4 class="text-base font-bold text-gray-800 dark:text-white">{{ $rootUser->name }}</h4>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-brand-500 text-white">Focus Root</span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $rootUser->email }}</p>
                    <p class="text-xs text-brand-600 dark:text-brand-400 mt-1 font-medium">Referred by: {{ $rootUser->referralRecord->parent->name ?? 'None' }}</p>
                </div>
            </div>
            
            @if(count($genealogy) > 0)
                <!-- Connection line from root to first child group -->
                <div class="absolute left-[47px] top-full h-8 w-[2px] bg-gray-300 dark:bg-gray-700"></div>
            @endif
        </div>

        <!-- Genealogy Tree -->
        <div class="relative pl-12">
            @if(count($genealogy) > 0)
                <div class="space-y-6">
                    @foreach($genealogy as $item)
                        @include('genealogy.tree_node', ['node' => $item, 'isLast' => $loop->last])
                    @endforeach
                </div>
            @else
                <div class="text-center py-16 bg-gray-50 dark:bg-white/5 rounded-2xl border border-dashed border-gray-200 dark:border-gray-800">
                    <p class="text-gray-500 dark:text-gray-400 italic">No referrals found under this user.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .tree-node-connector {
        position: absolute;
        left: -48px;
        top: 27px;
        width: 48px;
        height: 2px;
        background-color: #d1d5db; /* gray-300 */
    }
    .dark .tree-node-connector {
        background-color: #374151; /* gray-700 */
    }
    .tree-vertical-line {
        position: absolute;
        left: -48px;
        top: 0;
        width: 2px;
        background-color: #d1d5db; /* gray-300 */
    }
    .dark .tree-vertical-line {
        background-color: #374151; /* gray-700 */
    }
</style>
@endsection
