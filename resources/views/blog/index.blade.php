@extends('layouts.app')

@section('title', 'Dokandar Blog & Articles | Dua Re Dokandar')

@section('meta_tags')
    <meta name="description" content="Discover grocery buying guides, authentic spice recipes, healthy living tips, and rural market updates from Dua Re Dokandar.">
    <meta name="keywords" content="grocery blog, bengali spices, authentic mustard oil, fresh farm produce, dua re dokandar articles">
    <meta property="og:title" content="Dokandar Blog & Articles | Dua Re Dokandar">
    <meta property="og:description" content="Discover grocery buying guides, authentic spice recipes, healthy living tips, and rural market updates from Dua Re Dokandar.">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
@endsection

@section('content')
<div class="min-h-screen bg-gray-50/60 dark:bg-gray-900/60 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">

        <!-- Blog Header Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-700 via-indigo-700 to-blue-600 text-white p-6 sm:p-10 mb-10 shadow-xl">
            <div class="absolute -right-8 -bottom-8 opacity-15 pointer-events-none text-9xl">
                <i class="fas fa-newspaper"></i>
            </div>
            <div class="relative z-10 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur text-xs font-semibold uppercase tracking-wider mb-3">
                    <i class="fas fa-feather-alt"></i> Stories, Recipes &amp; Insights
                </div>
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight mb-3">
                    The Dokandar Blog
                </h1>
                <p class="text-blue-100 text-sm sm:text-base leading-relaxed mb-6">
                    Authentic guides on traditional spices, healthy cooking, local farming in Bengal, and smart shopping tips for your everyday essentials.
                </p>

                <!-- Search Input inside Header -->
                <form method="GET" action="{{ route('blog.index') }}" class="relative max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-search text-sm text-blue-600"></i>
                    </span>
                    <input type="text" 
                           name="q" 
                           value="{{ request('q') }}" 
                           placeholder="Search recipes, spices, grocery tips..." 
                           class="w-full pl-10 pr-24 py-3 rounded-2xl bg-white text-gray-900 placeholder-gray-400 text-sm focus:outline-none focus:ring-4 focus:ring-blue-500/30 shadow-lg font-medium">
                    <button type="submit" class="absolute inset-y-1.5 right-1.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition">
                        Search
                    </button>
                </form>
            </div>
        </div>

        <!-- Category Pills Filter Bar -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-8 bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200/80 dark:border-gray-700 shadow-xs">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('blog.index') }}" 
                   class="px-4 py-1.5 rounded-xl text-xs sm:text-sm font-semibold transition {{ !request('category') ? 'bg-blue-600 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                    All Stories
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('blog.index', ['category' => $cat->category]) }}" 
                       class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-semibold transition {{ request('category') === $cat->category ? 'bg-blue-600 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                        {{ $cat->category }} ({{ $cat->count }})
                    </a>
                @endforeach
            </div>

            @if(request()->hasAny(['q', 'category']))
                <a href="{{ route('blog.index') }}" class="text-xs font-semibold text-blue-600 hover:underline">
                    Clear Filters
                </a>
            @endif
        </div>

        <!-- Featured Post Hero Card (Only on clean first page) -->
        @if($featured && !request()->hasAny(['q', 'category', 'page']))
        <div class="mb-12">
            <div class="group relative rounded-3xl overflow-hidden bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700 shadow-md hover:shadow-xl transition-all duration-300 grid grid-cols-1 lg:grid-cols-12 gap-0">
                <div class="lg:col-span-7 h-64 sm:h-80 lg:h-96 relative overflow-hidden bg-gray-100 dark:bg-gray-900">
                    @if($featured->image)
                        <img src="{{ $featured->image }}" alt="{{ $featured->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-blue-400 dark:text-blue-800 text-7xl">
                            <i class="fas fa-newspaper"></i>
                        </div>
                    @endif
                    <div class="absolute top-4 left-4">
                        <span class="bg-blue-600 text-white text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-full shadow-md">
                            Featured Story
                        </span>
                    </div>
                </div>

                <div class="lg:col-span-5 p-6 sm:p-8 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2.5 text-xs text-gray-500 dark:text-gray-400 mb-3">
                            <span class="font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider text-[11px]">{{ $featured->category }}</span>
                            <span>&bull;</span>
                            <span>{{ $featured->published_at?->format('M d, Y') }}</span>
                            <span>&bull;</span>
                            <span><i class="far fa-clock"></i> {{ $featured->reading_time }} min read</span>
                        </div>

                        <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white mb-3 group-hover:text-blue-600 transition leading-tight">
                            <a href="{{ route('blog.show', $featured->slug) }}">
                                {{ $featured->title }}
                            </a>
                        </h2>

                        <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed line-clamp-3 mb-6">
                            {{ $featured->excerpt ?? Str::limit(strip_tags($featured->content), 180) }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs">
                                <i class="fas fa-user-edit"></i>
                            </div>
                            <span class="text-xs font-semibold text-gray-800 dark:text-gray-200">{{ $featured->display_author }}</span>
                        </div>
                        <a href="{{ route('blog.show', $featured->slug) }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-bold text-blue-600 dark:text-blue-400 hover:translate-x-1 transition-transform">
                            Read Story <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Articles Grid -->
        <div class="mb-12">
            @if($blogs->count() === 0)
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-12 text-center border border-gray-200/80 dark:border-gray-700">
                    <div class="w-16 h-16 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center mx-auto mb-4 text-2xl">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">No articles found</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto mb-5">
                        We couldn't find any articles matching your search or category filter. Check back soon for fresh updates!
                    </p>
                    <a href="{{ route('blog.index') }}" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition">
                        View All Articles
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($blogs as $blog)
                    <article class="group relative flex flex-col rounded-3xl bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/80 overflow-hidden shadow-xs hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                        
                        <!-- Thumbnail Image -->
                        <a href="{{ route('blog.show', $blog->slug) }}" class="block h-52 w-full relative overflow-hidden bg-gray-100 dark:bg-gray-900">
                            @if($blog->image)
                                <img src="{{ $blog->image }}" alt="{{ $blog->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <i class="fas fa-newspaper text-5xl text-blue-300 dark:text-blue-950"></i>
                                </div>
                            @endif

                            <div class="absolute top-3 left-3">
                                <span class="bg-blue-600 text-white text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full shadow-xs">
                                    {{ $blog->category ?? 'General' }}
                                </span>
                            </div>

                            <div class="absolute top-3 right-3">
                                <span class="bg-gray-900/70 backdrop-blur text-white text-[11px] font-medium px-2 py-0.5 rounded-full flex items-center gap-1">
                                    <i class="far fa-clock text-[10px]"></i>
                                    <span>{{ $blog->reading_time }} min</span>
                                </span>
                            </div>
                        </a>

                        <!-- Body -->
                        <div class="p-6 flex flex-col flex-1">
                            <div class="flex items-center gap-2 text-xs text-gray-400 dark:text-gray-500 mb-2">
                                <span>{{ $blog->published_at ? $blog->published_at->format('M d, Y') : 'Recent' }}</span>
                                <span>&bull;</span>
                                <span><i class="far fa-eye"></i> {{ number_format($blog->views) }} views</span>
                            </div>

                            <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-2 leading-snug group-hover:text-blue-600 transition line-clamp-2">
                                <a href="{{ route('blog.show', $blog->slug) }}">
                                    {{ $blog->title }}
                                </a>
                            </h3>

                            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 leading-relaxed line-clamp-3 mb-6 flex-1">
                                {{ $blog->excerpt ?? Str::limit(strip_tags($blog->content), 120) }}
                            </p>

                            <div class="pt-4 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between mt-auto">
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 truncate max-w-[150px]">
                                    <i class="fas fa-user-circle text-blue-500 mr-1"></i> {{ $blog->display_author }}
                                </span>

                                <a href="{{ route('blog.show', $blog->slug) }}" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 dark:text-blue-400 hover:text-blue-700">
                                    Read Article <i class="fas fa-arrow-right text-[10px]"></i>
                                </a>
                            </div>
                        </div>

                    </article>
                    @endforeach
                </div>

                @if($blogs->hasPages())
                <div class="mt-10">
                    {{ $blogs->links() }}
                </div>
                @endif
            @endif
        </div>

        <!-- Shop CTA Promo Banner at Bottom of Blog -->
        <div class="rounded-3xl bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 text-white p-8 md:p-10 shadow-lg flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="max-w-xl text-center md:text-left">
                <span class="inline-block bg-white/20 text-white text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-full mb-3">Fresh Local Delivery</span>
                <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Support Local Farmers &amp; Dokandars</h3>
                <p class="mt-2 text-blue-100 text-sm">
                    All fresh goods mentioned in our blog are available directly in our online shop with doorstep delivery and reward BV points.
                </p>
            </div>
            <div class="flex-shrink-0">
                <a href="{{ route('shop.index') }}" class="px-8 py-4 bg-white text-blue-700 font-extrabold rounded-full shadow-lg hover:bg-blue-50 transition transform hover:scale-105 inline-flex items-center gap-2.5 text-base">
                    <i class="fas fa-shopping-bag"></i> Browse Dokandar Store →
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
