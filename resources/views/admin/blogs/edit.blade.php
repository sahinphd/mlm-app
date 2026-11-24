@extends('admin.layout')

@section('content')
<div class="mx-auto max-w-270" x-data="blogEditor()">
    <!-- Header -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md2 font-bold text-black dark:text-white">
                Edit Article: {{ $blog->title }}
            </h2>
            <p class="text-sm text-body dark:text-bodydark mt-1">
                Update article contents, category, and SEO metadata.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('blog.show', $blog->slug) }}" target="_blank" class="inline-flex items-center justify-center rounded border border-primary text-primary py-2 px-4 text-sm font-medium hover:bg-primary hover:text-white transition">
                View Live
            </a>
            <a href="{{ route('admin.blogs.index') }}" class="inline-flex items-center justify-center rounded border border-stroke py-2 px-4 text-sm font-medium text-black hover:shadow-1 dark:border-strokedark dark:text-white">
                &larr; Back
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

    @if($errors->any())
        <div class="mb-6 flex w-full border-l-6 border-[#F87171] bg-[#F87171] bg-opacity-[15%] px-7 py-4 shadow-md dark:bg-[#1b1b1b] md:p-5">
            <div class="w-full">
                <ul class="list-disc pl-5 text-sm text-danger space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.blogs.update', $blog->id) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Content Form (Left 2 cols) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Title & Slug -->
                <div class="rounded-sm border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-black dark:text-white">
                            Article Title <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               name="title" 
                               x-model="title" 
                               value="{{ old('title', $blog->title) }}" 
                               required 
                               placeholder="e.g. 10 Health Benefits of Authentic Mustard Oil from Bengal" 
                               class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black font-medium focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white">
                        @error('title') <p class="text-danger text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            SEO URL Slug
                        </label>
                        <div class="flex items-center rounded border border-stroke bg-gray-100 dark:bg-meta-4/40 px-3">
                            <span class="text-xs text-gray-400 font-mono">/blog/</span>
                            <input type="text" 
                                   name="slug" 
                                   x-model="slug" 
                                   value="{{ old('slug', $blog->slug) }}" 
                                   class="w-full bg-transparent py-2.5 px-1 text-xs text-black dark:text-white font-mono focus:outline-none">
                        </div>
                        @error('slug') <p class="text-danger text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Excerpt / Summary -->
                <div class="rounded-sm border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
                    <label class="mb-2 block text-sm font-semibold text-black dark:text-white">
                        Article Excerpt / Short Summary
                    </label>
                    <textarea name="excerpt" 
                              x-model="excerpt"
                              rows="3" 
                              placeholder="A brief overview of the post..." 
                              class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white text-sm">{{ old('excerpt', $blog->excerpt) }}</textarea>
                    <p class="text-[11px] text-gray-400 mt-1">Recommended length: 120 - 160 characters.</p>
                    @error('excerpt') <p class="text-danger text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Main Content Body -->
                <div class="rounded-sm border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-semibold text-black dark:text-white">
                            Article Content Body <span class="text-danger">*</span>
                        </label>
                        <span class="text-xs text-gray-400">Supports HTML formatting</span>
                    </div>
                    <textarea name="content" 
                              rows="16" 
                              required 
                              class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black font-sans leading-relaxed focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white text-sm">{{ old('content', $blog->content) }}</textarea>
                    @error('content') <p class="text-danger text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- SEO Meta Settings Card -->
                <div class="rounded-sm border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark space-y-4">
                    <div class="border-b border-stroke pb-3 dark:border-strokedark flex items-center justify-between">
                        <h3 class="font-bold text-base text-black dark:text-white flex items-center gap-2">
                            <i class="fas fa-search text-primary"></i>
                            <span>Search Engine Optimization (SEO) Metadata</span>
                        </h3>
                        <span class="text-xs bg-primary/10 text-primary font-bold px-2 py-0.5 rounded">Google &amp; Social</span>
                    </div>

                    <!-- Live Google Search Snippet Preview -->
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 dark:bg-meta-4/20 dark:border-strokedark">
                        <p class="text-xs text-gray-400 mb-1 font-semibold uppercase">Google Search Preview</p>
                        <div class="space-y-0.5 font-sans">
                            <p class="text-xs text-[#202124] dark:text-gray-300 truncate">
                                {{ url('/blog') }}/<span x-text="slug || '{{ $blog->slug }}'"></span>
                            </p>
                            <h4 class="text-base text-[#1a0dab] dark:text-blue-400 font-medium hover:underline truncate" 
                                x-text="metaTitle || title || '{{ $blog->title }}'"></h4>
                            <p class="text-xs text-[#4d5156] dark:text-gray-400 line-clamp-2" 
                               x-text="metaDesc || excerpt || '{{ Str::limit(strip_tags($blog->excerpt ?? $blog->content), 140) }}'"></p>
                        </div>
                    </div>

                    <!-- Meta Title -->
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-xs font-semibold text-black dark:text-white">Custom Meta Title</label>
                            <span class="text-[11px] text-gray-400" x-text="(metaTitle ? metaTitle.length : (title ? title.length : 0)) + ' / 60 chars'"></span>
                        </div>
                        <input type="text" 
                               name="meta_title" 
                               x-model="metaTitle"
                               value="{{ old('meta_title', $blog->meta_title) }}" 
                               class="w-full rounded border border-stroke bg-gray py-2.5 px-4 text-black text-sm focus:border-primary dark:border-strokedark dark:bg-meta-4 dark:text-white">
                    </div>

                    <!-- Meta Description -->
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-xs font-semibold text-black dark:text-white">Custom Meta Description</label>
                            <span class="text-[11px] text-gray-400" x-text="(metaDesc ? metaDesc.length : (excerpt ? excerpt.length : 0)) + ' / 160 chars'"></span>
                        </div>
                        <textarea name="meta_description" 
                                  x-model="metaDesc"
                                  rows="2" 
                                  class="w-full rounded border border-stroke bg-gray py-2.5 px-4 text-black text-sm focus:border-primary dark:border-strokedark dark:bg-meta-4 dark:text-white">{{ old('meta_description', $blog->meta_description) }}</textarea>
                    </div>

                    <!-- Meta Keywords -->
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-black dark:text-white">SEO Keywords / Tags</label>
                        <input type="text" 
                               name="meta_keywords" 
                               value="{{ old('meta_keywords', $blog->meta_keywords) }}" 
                               class="w-full rounded border border-stroke bg-gray py-2.5 px-4 text-black text-sm focus:border-primary dark:border-strokedark dark:bg-meta-4 dark:text-white">
                    </div>

                    <!-- Canonical URL -->
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-black dark:text-white">Canonical URL (Optional)</label>
                        <input type="url" 
                               name="canonical_url" 
                               value="{{ old('canonical_url', $blog->canonical_url) }}" 
                               class="w-full rounded border border-stroke bg-gray py-2.5 px-4 text-black text-sm focus:border-primary dark:border-strokedark dark:bg-meta-4 dark:text-white">
                    </div>

                </div>

            </div>

            <!-- Sidebar Controls (Right 1 col) -->
            <div class="space-y-6">
                
                <!-- Publishing Status Card -->
                <div class="rounded-sm border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark space-y-4">
                    <h3 class="font-bold text-sm text-black dark:text-white border-b border-stroke pb-3 dark:border-strokedark">
                        Publishing Status
                    </h3>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            Status <span class="text-danger">*</span>
                        </label>
                        <select name="status" class="w-full rounded border border-stroke bg-gray py-2.5 px-4 text-sm text-black focus:border-primary dark:border-strokedark dark:bg-meta-4 dark:text-white">
                            <option value="published" {{ old('status', $blog->status) === 'published' ? 'selected' : '' }}>Published (Live)</option>
                            <option value="draft" {{ old('status', $blog->status) === 'draft' ? 'selected' : '' }}>Draft (Private)</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            Category
                        </label>
                        <input type="text" 
                               name="category" 
                               value="{{ old('category', $blog->category) }}" 
                               class="w-full rounded border border-stroke bg-gray py-2.5 px-4 text-sm text-black focus:border-primary dark:border-strokedark dark:bg-meta-4 dark:text-white">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            Publish Date
                        </label>
                        <input type="datetime-local" 
                               name="published_at" 
                               value="{{ old('published_at', $blog->published_at ? $blog->published_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" 
                               class="w-full rounded border border-stroke bg-gray py-2 px-3 text-xs text-black focus:border-primary dark:border-strokedark dark:bg-meta-4 dark:text-white">
                    </div>

                    <div class="pt-2 flex flex-col gap-2">
                        <button type="submit" class="w-full rounded bg-primary py-3 px-4 font-semibold text-sm text-white hover:bg-opacity-90 shadow-md transition">
                            Save Changes
                        </button>
                    </div>
                </div>

                <!-- Featured Image Card -->
                <div class="rounded-sm border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark space-y-4">
                    <h3 class="font-bold text-sm text-black dark:text-white border-b border-stroke pb-3 dark:border-strokedark">
                        Featured Image
                    </h3>

                    <!-- Image Preview -->
                    <div class="h-44 w-full rounded-xl bg-gray-100 dark:bg-meta-4/30 overflow-hidden flex items-center justify-center border border-dashed border-gray-300 dark:border-strokedark">
                        <img :src="imageUrlPreview" x-show="imageUrlPreview" class="w-full h-full object-cover">
                        <div x-show="!imageUrlPreview" class="text-center text-gray-400 p-4">
                            <i class="fas fa-image text-3xl mb-1"></i>
                            <p class="text-xs">No image</p>
                        </div>
                    </div>

                    <!-- File Upload -->
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">
                            Replace Image File
                        </label>
                        <input type="file" 
                               name="image_file" 
                               accept="image/*" 
                               @change="previewFile($event)" 
                               class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                    </div>

                    <div class="text-center text-xs text-gray-400 font-semibold uppercase">— OR —</div>

                    <!-- Direct URL -->
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">
                            Image Web URL
                        </label>
                        <input type="url" 
                               name="image_url" 
                               x-model="imageUrl" 
                               @input="imageUrlPreview = imageUrl" 
                               value="{{ old('image_url', $blog->getRawOriginal('image')) }}" 
                               class="w-full rounded border border-stroke bg-gray py-2 px-3 text-xs text-black focus:border-primary dark:border-strokedark dark:bg-meta-4 dark:text-white">
                    </div>
                </div>

            </div>

        </div>
    </form>
</div>

<script>
function blogEditor() {
    return {
        title: @json(old('title', $blog->title)),
        slug: @json(old('slug', $blog->slug)),
        excerpt: @json(old('excerpt', $blog->excerpt)),
        metaTitle: @json(old('meta_title', $blog->meta_title)),
        metaDesc: @json(old('meta_description', $blog->meta_description)),
        imageUrl: @json(old('image_url', $blog->getRawOriginal('image') ?? '')),
        imageUrlPreview: @json($blog->image ?? ''),

        previewFile(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imageUrlPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }
    };
}
</script>
@endsection
