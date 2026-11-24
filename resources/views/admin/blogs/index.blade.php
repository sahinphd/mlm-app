@extends('admin.layout')

@section('content')
<div class="mx-auto max-w-270">
    <!-- Header -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md2 font-bold text-black dark:text-white sm:text-title-md">
                Blog Posts & SEO Content
            </h2>
            <p class="text-sm text-body dark:text-bodydark mt-1">
                Publish articles, recipes, and SEO-rich content for visitors and customers.
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('blog.index') }}" target="_blank" class="inline-flex items-center justify-center rounded-md border border-stroke bg-white py-2 px-4 text-center font-medium text-black hover:shadow-1 dark:border-strokedark dark:bg-meta-4 dark:text-white">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                View Public Blog
            </a>
            <a href="{{ route('admin.blogs.create') }}" class="inline-flex items-center justify-center rounded-md bg-primary py-2 px-6 text-center font-medium text-white hover:bg-opacity-90">
                + Write New Article
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 flex w-full border-l-6 border-[#34D399] bg-[#34D399] bg-opacity-[15%] px-7 py-4 shadow-md dark:bg-[#1b1b1b] md:p-5">
            <div class="w-full">
                <p class="text-base leading-relaxed text-body">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <!-- Filters Bar -->
    <div class="rounded-sm border border-stroke bg-white p-4 shadow-default dark:border-strokedark dark:bg-boxdark mb-6">
        <form method="GET" action="{{ route('admin.blogs.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search articles or keywords..." class="w-full rounded border border-stroke bg-gray py-2.5 px-4 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white text-sm">
            </div>
            <div>
                <select name="status" class="w-full rounded border border-stroke bg-gray py-2.5 px-4 text-black focus:border-primary dark:border-strokedark dark:bg-meta-4 dark:text-white text-sm">
                    <option value="">All Statuses</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published Only</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft Only</option>
                </select>
            </div>
            <div>
                <select name="category" class="w-full rounded border border-stroke bg-gray py-2.5 px-4 text-black focus:border-primary dark:border-strokedark dark:bg-meta-4 dark:text-white text-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 rounded bg-primary py-2.5 px-4 text-sm font-medium text-white hover:bg-opacity-90">
                    Filter
                </button>
                @if(request()->hasAny(['q', 'status', 'category']))
                    <a href="{{ route('admin.blogs.index') }}" class="rounded border border-stroke py-2.5 px-3 text-sm font-medium text-black hover:bg-gray-100 dark:border-strokedark dark:text-white dark:hover:bg-meta-4">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Articles Table -->
    <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="border-b border-stroke py-4 px-6.5 dark:border-strokedark flex justify-between items-center">
            <h3 class="font-medium text-black dark:text-white">All Published &amp; Draft Articles ({{ $blogs->total() }})</h3>
        </div>
        <div class="max-w-full overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-2 text-left dark:bg-meta-4">
                        <th class="py-4 px-4 font-medium text-black dark:text-white">Image</th>
                        <th class="min-w-[200px] py-4 px-4 font-medium text-black dark:text-white">Title &amp; URL</th>
                        <th class="min-w-[120px] py-4 px-4 font-medium text-black dark:text-white">Category</th>
                        <th class="min-w-[100px] py-4 px-4 font-medium text-black dark:text-white">Status</th>
                        <th class="min-w-[80px] py-4 px-4 font-medium text-black dark:text-white">Views</th>
                        <th class="min-w-[120px] py-4 px-4 font-medium text-black dark:text-white">Published Date</th>
                        <th class="py-4 px-4 font-medium text-black dark:text-white text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($blogs as $blog)
                    <tr>
                        <td class="border-b border-[#eee] py-4 px-4 dark:border-strokedark">
                            @if($blog->image)
                                <img src="{{ $blog->image }}" alt="{{ $blog->title }}" class="h-12 w-16 object-cover rounded shadow-2xs">
                            @else
                                <div class="h-12 w-16 bg-gray-200 dark:bg-meta-4 flex items-center justify-center rounded text-gray-400">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                    </svg>
                                </div>
                            @endif
                        </td>
                        <td class="border-b border-[#eee] py-4 px-4 dark:border-strokedark">
                            <h4 class="font-bold text-sm text-black dark:text-white hover:text-primary transition line-clamp-1">
                                <a href="{{ route('admin.blogs.edit', $blog->id) }}">{{ $blog->title }}</a>
                            </h4>
                            <p class="text-xs text-gray-400 mt-0.5 font-mono">/blog/{{ $blog->slug }}</p>
                        </td>
                        <td class="border-b border-[#eee] py-4 px-4 dark:border-strokedark">
                            <span class="inline-flex rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 py-0.5 px-2.5 text-xs font-semibold">
                                {{ $blog->category ?? 'General' }}
                            </span>
                        </td>
                        <td class="border-b border-[#eee] py-4 px-4 dark:border-strokedark">
                            <span class="inline-flex rounded-full py-1 px-3 text-xs font-semibold {{ $blog->status === 'published' ? 'bg-success/15 text-success' : 'bg-warning/15 text-warning' }}">
                                {{ ucfirst($blog->status) }}
                            </span>
                        </td>
                        <td class="border-b border-[#eee] py-4 px-4 dark:border-strokedark">
                            <span class="text-sm font-semibold text-black dark:text-white">
                                {{ number_format($blog->views) }}
                            </span>
                        </td>
                        <td class="border-b border-[#eee] py-4 px-4 dark:border-strokedark">
                            <p class="text-xs text-black dark:text-white">{{ $blog->published_at ? $blog->published_at->format('M d, Y') : 'Draft' }}</p>
                            <p class="text-[10px] text-gray-400">{{ $blog->display_author }}</p>
                        </td>
                        <td class="border-b border-[#eee] py-4 px-4 dark:border-strokedark text-right">
                            <div class="flex items-center justify-end space-x-2.5">
                                <a href="{{ route('blog.show', $blog->slug) }}" target="_blank" class="hover:text-primary text-xs font-semibold text-blue-600 px-2 py-1 rounded hover:bg-blue-50 dark:hover:bg-meta-4" title="View live post">
                                    View
                                </a>
                                <a href="{{ route('admin.blogs.edit', $blog->id) }}" class="hover:text-primary text-xs font-semibold text-gray-700 dark:text-gray-300 px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-meta-4">
                                    Edit
                                </a>
                                <form action="{{ route('admin.blogs.destroy', $blog->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this article?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-danger hover:underline text-xs font-semibold px-2 py-1">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-gray-400">
                            No blog posts found. Click "+ Write New Article" to publish your first content!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($blogs->hasPages())
        <div class="p-4 border-t border-stroke dark:border-strokedark">
            {{ $blogs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
