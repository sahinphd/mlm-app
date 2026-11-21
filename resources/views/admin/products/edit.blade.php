@extends('admin.layout')

@section('content')
<div class="mx-auto max-w-270">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md2 font-bold text-black dark:text-white">
                Edit Product: {{ $product->name }}
            </h2>
            <p class="text-sm text-body dark:text-bodydark mt-1">Update product details, pricing, and photo (&lt; 30 KB)</p>
        </div>
        <a href="{{ route('admin.products') }}" class="inline-flex items-center justify-center rounded border border-stroke py-2 px-5 text-sm font-medium text-black hover:shadow-1 dark:border-strokedark dark:text-white">
            &larr; Back to Products
        </a>
    </div>

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

    <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="p-7">
            <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Product Name -->
                <div class="mb-5.5">
                    <label class="mb-3 block text-sm font-medium text-black dark:text-white">
                        Product Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                    @error('name') <p class="text-danger text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Product Photo Section with Interactive Cropper (Max 30 KB) -->
                <div class="mb-5.5 rounded-lg border border-stroke bg-gray-50/70 p-4.5 dark:border-strokedark dark:bg-meta-4/20">
                    <div class="mb-3 flex items-center justify-between">
                        <div>
                            <label class="block text-sm font-semibold text-black dark:text-white">
                                Product Photo <span class="text-xs font-semibold text-meta-3">(Max 30 KB &bull; Auto-crop &amp; compress)</span>
                            </label>
                            <p class="text-xs text-body dark:text-bodydark mt-0.5">
                                Select an image to crop, rotate, adjust aspect ratio, and compress to &lt; 30 KB on the spot.
                            </p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary">
                            Max 30 KB
                        </span>
                    </div>

                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
                        <!-- Thumbnail / Preview -->
                        <div class="relative shrink-0">
                            <div class="h-28 w-28 rounded-xl overflow-hidden border-2 border-primary/30 bg-white dark:bg-boxdark shadow-sm flex items-center justify-center p-0.5">
                                @if($product->image)
                                    <img src="{{ $product->image }}" id="product-image-preview" alt="{{ $product->name }}" class="h-full w-full object-cover rounded-lg">
                                    <div id="product-image-placeholder" class="hidden h-full w-full flex-col items-center justify-center text-gray-400">
                                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="text-[10px] mt-1 font-medium">No Photo</span>
                                    </div>
                                @else
                                    <img src="" id="product-image-preview" alt="Preview" class="hidden h-full w-full object-cover rounded-lg">
                                    <div id="product-image-placeholder" class="h-full w-full flex flex-col items-center justify-center text-gray-400">
                                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="text-[10px] mt-1 font-medium">No Photo</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- File Input & Action Buttons -->
                        <div class="flex-1 w-full space-y-2.5">
                            <input type="file" id="product-image-file" name="image_file" accept="image/*"
                                   class="w-full cursor-pointer rounded-lg border-[1.5px] border-stroke bg-white font-medium outline-none transition file:mr-4 file:border-0 file:border-r file:border-stroke file:bg-gray-2 file:py-2.5 file:px-4 file:text-xs file:font-semibold file:text-black hover:file:bg-primary hover:file:text-white focus:border-primary dark:border-strokedark dark:bg-boxdark dark:text-white dark:file:border-strokedark dark:file:bg-meta-4 dark:file:text-white">

                            <!-- Action Buttons -->
                            <div class="flex flex-wrap items-center gap-3">
                                @if($product->image)
                                    <button type="button" onclick="window.openCropperFromUrl('{{ $product->image }}', 'product_{{ $product->id }}.jpg')" class="inline-flex items-center gap-1.5 rounded-lg border border-primary/40 bg-primary/10 px-3 py-1.5 text-xs font-semibold text-primary hover:bg-primary hover:text-white transition">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        <span>Edit / Crop Current Photo</span>
                                    </button>

                                    <label class="inline-flex items-center gap-1.5 text-xs text-danger cursor-pointer">
                                        <input type="checkbox" name="remove_image" value="1" class="rounded border-stroke text-danger focus:ring-danger">
                                        <span>Remove Photo</span>
                                    </label>
                                @endif
                            </div>

                            <!-- Dynamic Status badge -->
                            <div id="product-image-status-badge" class="hidden"></div>
                            @error('image_file') <p class="text-danger text-xs mt-1">{{ $message }}</p> @enderror
                            @error('image') <p class="text-danger text-xs mt-1">{{ $message }}</p> @enderror

                            <!-- Optional fallback URL -->
                            <div class="pt-1.5" x-data="{ showUrl: false }">
                                <button type="button" @click="showUrl = !showUrl" class="text-[11px] text-body dark:text-bodydark hover:text-primary underline flex items-center gap-1">
                                    <span x-text="showUrl ? 'Hide image URL input' : 'Or use external image URL (Cloudinary / CDN)'"></span>
                                </button>
                                <div x-show="showUrl" x-cloak class="mt-2">
                                    <input type="text" name="image_url" value="{{ str_starts_with($product->getRawOriginal('image') ?? '', 'http') ? $product->getRawOriginal('image') : '' }}" class="w-full rounded border border-stroke bg-white py-2 px-3 text-xs text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white" placeholder="https://res.cloudinary.com/...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Price, BV, Stock -->
                <div class="mb-5.5 grid grid-cols-1 gap-5.5 sm:grid-cols-3">
                    <div>
                        <label class="mb-3 block text-sm font-medium text-black dark:text-white">
                            Price (₹) <span class="text-danger">*</span>
                        </label>
                        <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" required class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        @error('price') <p class="text-danger text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-3 block text-sm font-medium text-black dark:text-white">
                            BV (Business Volume) <span class="text-danger">*</span>
                        </label>
                        <input type="number" step="0.01" name="bv" value="{{ old('bv', $product->bv) }}" required class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        @error('bv') <p class="text-danger text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-3 block text-sm font-medium text-black dark:text-white">
                            Stock Quantity <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" required class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        @error('stock') <p class="text-danger text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Status -->
                <div class="mb-5.5">
                    <label class="mb-3 block text-sm font-medium text-black dark:text-white">
                        Status <span class="text-danger">*</span>
                    </label>
                    <select name="status" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status') <p class="text-danger text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-4.5">
                    <a href="{{ route('admin.products') }}" class="flex justify-center rounded border border-stroke py-2.5 px-6 font-medium text-black hover:shadow-1 dark:border-strokedark dark:text-white">
                        Cancel
                    </a>
                    <button type="submit" class="flex justify-center rounded bg-primary py-2.5 px-6 font-medium text-white hover:bg-opacity-90 shadow-sm transition">
                        Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@include('partials.avatar_cropper')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.initAvatarCropper({
        inputId: 'product-image-file',
        previewId: 'product-image-preview',
        placeholderId: 'product-image-placeholder',
        statusBadgeId: 'product-image-status-badge',
        defaultTargetKB: 28, // Guarantees < 30 KB
        title: 'Edit & Compress Product Photo',
        subtitle: 'Crop, rotate, and optimize photo to < 30 KB',
        aspectRatio: 1
    });
});
</script>
@endpush
