@extends('layouts.app')

@section('title', $blog->seo_title)

@section('meta_tags')
    <meta name="description" content="{{ $blog->seo_description }}">
    @if($blog->meta_keywords)
        <meta name="keywords" content="{{ $blog->meta_keywords }}">
    @endif
    <link rel="canonical" href="{{ $blog->canonical_url ?? url()->current() }}">

    <!-- Open Graph (Facebook / WhatsApp / LinkedIn) -->
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="Dua Re Dokandar">
    <meta property="og:title" content="{{ $blog->seo_title }}">
    <meta property="og:description" content="{{ $blog->seo_description }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if($blog->image)
        <meta property="og:image" content="{{ $blog->image }}">
    @endif
    @if($blog->published_at)
        <meta property="article:published_time" content="{{ $blog->published_at->toIso8601String() }}">
    @endif
    <meta property="article:author" content="{{ $blog->display_author }}">
    <meta property="article:section" content="{{ $blog->category ?? 'General' }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $blog->seo_title }}">
    <meta name="twitter:description" content="{{ $blog->seo_description }}">
    @if($blog->image)
        <meta name="twitter:image" content="{{ $blog->image }}">
    @endif

    <!-- Schema.org JSON-LD Structured Data for Google Rich Results -->
    <script type="application/ld+json">
    {!! json_encode(array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => url()->current(),
        ],
        'headline' => $blog->title,
        'description' => $blog->seo_description,
        'image' => $blog->image ?: null,
        'datePublished' => $blog->published_at ? $blog->published_at->toIso8601String() : $blog->created_at->toIso8601String(),
        'dateModified' => $blog->updated_at->toIso8601String(),
        'author' => [
            '@type' => 'Person',
            'name' => $blog->display_author,
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'Dua Re Dokandar',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => asset('logo.png'),
            ],
        ],
    ]), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
@endsection

@section('content')
<div class="min-h-screen bg-gray-50/60 dark:bg-gray-900/60 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        
        <!-- Breadcrumbs Navigation -->
        <nav class="flex items-center gap-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400 mb-6 font-medium">
            <a href="{{ url('/') }}" class="hover:text-blue-600 transition">Home</a>
            <span>/</span>
            <a href="{{ route('blog.index') }}" class="hover:text-blue-600 transition">Blog</a>
            <span>/</span>
            <a href="{{ route('blog.index', ['category' => $blog->category]) }}" class="hover:text-blue-600 transition">{{ $blog->category ?? 'General' }}</a>
            <span>/</span>
            <span class="text-gray-800 dark:text-gray-200 truncate max-w-[200px]">{{ $blog->title }}</span>
        </nav>

        <!-- Article Card Wrapper -->
        <article class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 overflow-hidden shadow-sm p-6 sm:p-10 mb-12">
            
            <!-- Meta Bar -->
            <div class="flex flex-wrap items-center justify-between gap-3 pb-6 border-b border-gray-100 dark:border-gray-700/60">
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="bg-blue-600 text-white text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-full">
                        {{ $blog->category ?? 'General' }}
                    </span>
                    <span class="text-xs text-gray-400">&bull;</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        <i class="far fa-calendar-alt mr-1"></i>
                        {{ $blog->published_at ? $blog->published_at->format('F d, Y') : 'Published recently' }}
                    </span>
                    <span class="text-xs text-gray-400">&bull;</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        <i class="far fa-clock mr-1"></i> {{ $blog->reading_time }} min read
                    </span>
                </div>

                <div class="flex items-center gap-2 text-xs text-gray-400">
                    <i class="far fa-eye"></i>
                    <span>{{ number_format($blog->views) }} views</span>
                </div>
            </div>

            <!-- Title (H1) -->
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-gray-900 dark:text-white my-6 leading-tight tracking-tight">
                {{ $blog->title }}
            </h1>

            <!-- Author byline -->
            <div class="flex items-center gap-3 mb-8">
                <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-sm">
                    <i class="fas fa-feather-alt"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ $blog->display_author }}</h4>
                    <p class="text-[11px] text-gray-400">Verified Contributor &bull; Dua Re Dokandar</p>
                </div>
            </div>

            <!-- Featured Image -->
            @if($blog->image)
            <div class="mb-8 rounded-2xl overflow-hidden bg-gray-100 dark:bg-gray-900 max-h-[460px] shadow-sm">
                <img src="{{ $blog->image }}" alt="{{ $blog->title }}" class="w-full h-full object-cover">
            </div>
            @endif

            <!-- Excerpt Callout (if available) -->
            @if($blog->excerpt)
            <div class="p-5 rounded-2xl bg-blue-50/70 border-l-4 border-blue-600 text-blue-950 dark:bg-blue-950/30 dark:text-blue-200 mb-8 italic text-base leading-relaxed">
                {{ $blog->excerpt }}
            </div>
            @endif

            <!-- Article Main Content Body -->
            <div class="article-content text-gray-800 dark:text-gray-200 leading-relaxed text-base sm:text-lg space-y-6">
                {!! nl2br(e($blog->content)) !!}
            </div>

            <!-- Tags / Keywords Section -->
            @if($blog->meta_keywords)
            <div class="mt-10 pt-6 border-t border-gray-100 dark:border-gray-700/60">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-2">Topics &amp; Tags:</span>
                <div class="flex flex-wrap gap-2">
                    @foreach(explode(',', $blog->meta_keywords) as $tag)
                        @if(trim($tag))
                            <span class="inline-flex items-center text-xs font-medium bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 px-3 py-1 rounded-lg">
                                #{{ trim($tag) }}
                            </span>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Social Share Bar -->
            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700/60 flex flex-col sm:flex-row items-center justify-between gap-4">
                <span class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300">Share this article:</span>
                <div class="flex items-center gap-2.5">
                    <!-- WhatsApp -->
                    <a href="https://api.whatsapp.com/send?text={{ rawurlencode($blog->title . ' - ' . url()->current()) }}" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#25D366] hover:bg-[#20ba59] text-white text-xs font-bold transition shadow-xs">
                        <i class="fab fa-whatsapp text-sm"></i> WhatsApp
                    </a>
                    <!-- Facebook -->
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#1877F2] hover:bg-[#166fe5] text-white text-xs font-bold transition shadow-xs">
                        <i class="fab fa-facebook text-sm"></i> Share
                    </a>
                    <!-- Twitter/X -->
                    <a href="https://twitter.com/intent/tweet?text={{ rawurlencode($blog->title) }}&url={{ urlencode(url()->current()) }}" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold transition shadow-xs">
                        <i class="fab fa-x-twitter text-sm"></i> Post
                    </a>
                    <!-- Copy Link Button -->
                    <button type="button" 
                            onclick="copyArticleLink()" 
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:text-blue-600 text-xs font-bold transition">
                        <i class="fas fa-link text-xs"></i> <span id="copyLinkBtnText">Copy</span>
                    </button>
                </div>
            </div>

        </article>

        <!-- Related Articles Grid -->
        @if($relatedPosts->count() > 0)
        <div class="mb-12">
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                <i class="fas fa-book-reader text-blue-600"></i>
                <span>You might also like</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                @foreach($relatedPosts as $rel)
                <a href="{{ route('blog.show', $rel->slug) }}" class="group block rounded-2xl bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700 p-4 shadow-xs hover:shadow-md transition hover:-translate-y-1">
                    <div class="h-36 w-full rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-900 mb-3">
                        @if($rel->image)
                            <img src="{{ $rel->image }}" alt="{{ $rel->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                <i class="fas fa-newspaper text-3xl text-blue-300 dark:text-blue-900"></i>
                            </div>
                        @endif
                    </div>
                    <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider block mb-1">{{ $rel->category }}</span>
                    <h4 class="font-bold text-sm text-gray-900 dark:text-white group-hover:text-blue-600 transition line-clamp-2 leading-snug">
                        {{ $rel->title }}
                    </h4>
                    <p class="text-[11px] text-gray-400 mt-2">{{ $rel->published_at?->format('M d, Y') }} &bull; {{ $rel->reading_time }} min</p>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Back to Blog & Shop CTA Banner -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-6 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700 shadow-xs">
            <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-blue-600 hover:underline">
                <i class="fas fa-arrow-left"></i> Back to All Articles
            </a>
            <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white font-bold text-xs hover:bg-blue-700 transition shadow-xs">
                <i class="fas fa-shopping-bag"></i> Shop Related Grocery Products
            </a>
        </div>

    </div>
</div>

<script>
function copyArticleLink() {
    navigator.clipboard.writeText(window.location.href);
    const btn = document.getElementById('copyLinkBtnText');
    if (btn) {
        btn.textContent = 'Copied!';
        setTimeout(() => { btn.textContent = 'Copy'; }, 2500);
    }
}
</script>
@endsection
