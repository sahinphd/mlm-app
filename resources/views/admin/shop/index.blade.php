@extends('admin.layout')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6"
     x-data="{ 
         viewMode: localStorage.getItem('admin_shop_view') || 'grid',
         search: '',
         matchesSearch(name) {
             return !this.search || name.toLowerCase().includes(this.search.toLowerCase());
         }
     }"
     x-init="$watch('viewMode', val => localStorage.setItem('admin_shop_view', val))">

    <!-- Top Header & Controls -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md2 font-bold text-black dark:text-white">Admin Shop (Order for User)</h2>
            <p class="text-sm text-body dark:text-bodydark mt-1">Select items to place orders on behalf of registered users</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Search bar -->
            <div class="relative min-w-[200px] sm:min-w-[240px]">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-body dark:text-bodydark">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input
                    type="text"
                    x-model="search"
                    placeholder="Search products or packages..."
                    class="w-full rounded-lg border border-stroke bg-white py-2 pl-9 pr-8 text-sm font-medium text-black outline-none focus:border-primary dark:border-strokedark dark:bg-boxdark dark:text-white dark:focus:border-primary shadow-sm"
                />
                <button
                    x-show="search"
                    @click="search = ''"
                    type="button"
                    class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-body hover:text-black dark:text-bodydark dark:hover:text-white"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- View Toggle (Grid / List) -->
            <div class="inline-flex items-center rounded-lg border border-stroke bg-gray-2 p-1 dark:border-strokedark dark:bg-meta-4 shadow-sm">
                <button
                    type="button"
                    @click="viewMode = 'grid'"
                    :class="viewMode === 'grid' 
                        ? 'bg-white text-primary shadow-sm dark:bg-boxdark dark:text-white font-semibold' 
                        : 'text-body hover:text-black dark:text-bodydark dark:hover:text-white'"
                    class="inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm transition-all duration-150"
                    title="Grid View"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span>Grid</span>
                </button>
                <button
                    type="button"
                    @click="viewMode = 'list'"
                    :class="viewMode === 'list' 
                        ? 'bg-white text-primary shadow-sm dark:bg-boxdark dark:text-white font-semibold' 
                        : 'text-body hover:text-black dark:text-bodydark dark:hover:text-white'"
                    class="inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm transition-all duration-150"
                    title="List View"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span>List</span>
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 flex w-full border-l-6 border-[#34D399] bg-[#34D399] bg-opacity-[15%] px-7 py-4 shadow-md dark:bg-[#1b1b1b] md:p-5">
            <div class="w-full">
                <p class="text-base leading-relaxed text-body">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 flex w-full border-l-6 border-[#F87171] bg-[#F87171] bg-opacity-[15%] px-7 py-4 shadow-md dark:bg-[#1b1b1b] md:p-5">
            <div class="w-full">
                <p class="text-base leading-relaxed text-body">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Empty State if no items exist -->
    @if($packages->isEmpty() && $products->isEmpty())
        <div class="rounded-sm border border-stroke bg-white p-12 text-center shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-2 dark:bg-meta-4">
                <svg class="h-8 w-8 text-body dark:text-bodydark" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-black dark:text-white">No Items Available</h3>
            <p class="mt-1 text-sm text-body dark:text-bodydark">There are currently no active products or packages to order.</p>
        </div>
    @endif

    <!-- Packages Section -->
    @if($packages->count() > 0)
    <div class="mb-10">
        <div class="mb-5 flex items-center justify-between">
            <h3 class="text-xl font-semibold text-black dark:text-white flex items-center gap-2">
                <span>Packages</span>
                <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary">
                    {{ $packages->count() }}
                </span>
            </h3>
        </div>

        <!-- Grid View -->
        <div x-show="viewMode === 'grid'" x-cloak class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6 xl:grid-cols-4 2xl:gap-7.5">
            @foreach($packages as $package)
            <div x-show="matchesSearch('{{ addslashes($package->name) }}')"
                 class="group rounded-sm border border-stroke bg-white p-4 shadow-default transition-all duration-200 hover:shadow-lg dark:border-strokedark dark:bg-boxdark flex flex-col justify-between">
                <div>
                    <div class="relative mb-4 h-48 w-full bg-gray-2 dark:bg-meta-4 flex items-center justify-center overflow-hidden rounded">
                        @if($package->image)
                            <img src="{{ $package->image }}" alt="{{ $package->name }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                        @else
                            <svg class="h-12 w-12 text-gray-400 group-hover:text-primary transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        @endif
                        <span class="absolute top-2 right-2 rounded bg-primary/90 px-2 py-0.5 text-[11px] font-semibold text-white uppercase tracking-wider">
                            Package
                        </span>
                    </div>
                    <div>
                        <h4 class="mb-1.5 font-semibold text-black dark:text-white group-hover:text-primary transition-colors line-clamp-1" title="{{ $package->name }}">
                            {{ $package->name }}
                        </h4>
                        <div class="mb-4 flex items-center justify-between">
                            <span class="text-base font-bold text-black dark:text-white">
                                ₹{{ number_format($package->price, 2) }}
                            </span>
                            <span class="inline-flex items-center rounded-full bg-meta-3/10 px-2.5 py-0.5 text-xs font-semibold text-meta-3">
                                {{ $package->bv }} BV
                            </span>
                        </div>
                    </div>
                </div>
                <div>
                    <a href="{{ route('admin.shop.checkout', ['package_id' => $package->id]) }}" 
                       class="flex w-full items-center justify-center gap-2 rounded bg-primary py-2.5 px-4 font-medium text-white shadow-sm hover:bg-opacity-90 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>Order for User</span>
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        <!-- List View -->
        <div x-show="viewMode === 'list'" x-cloak class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="max-w-full overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-2 text-left dark:bg-meta-4">
                            <th class="py-4 px-4 font-medium text-black dark:text-white xl:pl-6">Package</th>
                            <th class="py-4 px-4 font-medium text-black dark:text-white">Price</th>
                            <th class="py-4 px-4 font-medium text-black dark:text-white">BV</th>
                            <th class="py-4 px-4 font-medium text-black dark:text-white">Status</th>
                            <th class="py-4 px-4 font-medium text-black dark:text-white text-right pr-4 xl:pr-6">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stroke dark:divide-strokedark">
                        @foreach($packages as $package)
                        <tr x-show="matchesSearch('{{ addslashes($package->name) }}')" class="hover:bg-gray-1 dark:hover:bg-meta-4/30 transition">
                            <td class="py-4 px-4 xl:pl-6">
                                <div class="flex items-center gap-3">
                                    <div class="h-12 w-12 rounded border border-stroke bg-gray-2 dark:border-strokedark dark:bg-meta-4 flex items-center justify-center overflow-hidden shrink-0">
                                        @if($package->image)
                                            <img src="{{ $package->image }}" alt="{{ $package->name }}" class="h-full w-full object-cover">
                                        @else
                                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <h5 class="font-semibold text-black dark:text-white text-sm">
                                            {{ $package->name }}
                                        </h5>
                                        <span class="inline-flex items-center rounded bg-primary/10 px-2 py-0.5 text-[10px] font-semibold text-primary mt-0.5">
                                            Package
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <p class="font-bold text-black dark:text-white text-sm">
                                    ₹{{ number_format($package->price, 2) }}
                                </p>
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex rounded-full bg-meta-3/10 px-2.5 py-0.5 text-xs font-semibold text-meta-3">
                                    {{ $package->bv }} BV
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex rounded-full bg-success/10 px-2.5 py-0.5 text-xs font-medium text-success">
                                    Active
                                </span>
                            </td>
                            <td class="py-4 px-4 text-right pr-4 xl:pr-6">
                                <a href="{{ route('admin.shop.checkout', ['package_id' => $package->id]) }}" 
                                   class="inline-flex items-center gap-1.5 rounded bg-primary py-2 px-4 text-xs font-medium text-white shadow-sm hover:bg-opacity-90 transition">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span>Order for User</span>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Products Section -->
    @if($products->count() > 0)
    <div>
        <div class="mb-5 flex items-center justify-between">
            <h3 class="text-xl font-semibold text-black dark:text-white flex items-center gap-2">
                <span>Individual Products</span>
                <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary">
                    {{ $products->count() }}
                </span>
            </h3>
        </div>

        <!-- Grid View -->
        <div x-show="viewMode === 'grid'" x-cloak class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6 xl:grid-cols-4 2xl:gap-7.5">
            @foreach($products as $product)
            <div x-show="matchesSearch('{{ addslashes($product->name) }}')"
                 class="group rounded-sm border border-stroke bg-white p-4 shadow-default transition-all duration-200 hover:shadow-lg dark:border-strokedark dark:bg-boxdark flex flex-col justify-between">
                <div>
                    <div class="relative mb-4 h-48 w-full bg-gray-2 dark:bg-meta-4 flex items-center justify-center overflow-hidden rounded">
                        @if($product->image)
                            <img src="{{ $product->image }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                        @else
                            <svg class="h-12 w-12 text-gray-400 group-hover:text-primary transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        @endif
                        <span class="absolute top-2 right-2 rounded bg-blue-600/90 px-2 py-0.5 text-[11px] font-semibold text-white uppercase tracking-wider">
                            Product
                        </span>
                    </div>
                    <div>
                        <div class="mb-1 flex items-center justify-between gap-2">
                            <h4 class="font-semibold text-black dark:text-white group-hover:text-primary transition-colors line-clamp-1 flex-1" title="{{ $product->name }}">
                                {{ $product->name }}
                            </h4>
                        </div>
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-base font-bold text-black dark:text-white">
                                ₹{{ number_format($product->price, 2) }}
                            </span>
                            <span class="inline-flex items-center rounded-full bg-meta-3/10 px-2.5 py-0.5 text-xs font-semibold text-meta-3">
                                {{ $product->bv }} BV
                            </span>
                        </div>
                        <div class="mb-4">
                            @if($product->stock > 0)
                                <span class="inline-flex items-center rounded-full bg-success/10 px-2.5 py-0.5 text-xs font-medium text-success">
                                    In Stock ({{ $product->stock }})
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-danger/10 px-2.5 py-0.5 text-xs font-medium text-danger">
                                    Out of Stock
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div>
                    <a href="{{ route('admin.shop.checkout', ['product_id' => $product->id]) }}" 
                       class="flex w-full items-center justify-center gap-2 rounded bg-primary py-2.5 px-4 font-medium text-white shadow-sm hover:bg-opacity-90 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>Order for User</span>
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        <!-- List View -->
        <div x-show="viewMode === 'list'" x-cloak class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="max-w-full overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-2 text-left dark:bg-meta-4">
                            <th class="py-4 px-4 font-medium text-black dark:text-white xl:pl-6">Product</th>
                            <th class="py-4 px-4 font-medium text-black dark:text-white">Price</th>
                            <th class="py-4 px-4 font-medium text-black dark:text-white">BV</th>
                            <th class="py-4 px-4 font-medium text-black dark:text-white">Stock</th>
                            <th class="py-4 px-4 font-medium text-black dark:text-white">Status</th>
                            <th class="py-4 px-4 font-medium text-black dark:text-white text-right pr-4 xl:pr-6">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stroke dark:divide-strokedark">
                        @foreach($products as $product)
                        <tr x-show="matchesSearch('{{ addslashes($product->name) }}')" class="hover:bg-gray-1 dark:hover:bg-meta-4/30 transition">
                            <td class="py-4 px-4 xl:pl-6">
                                <div class="flex items-center gap-3">
                                    <div class="h-12 w-12 rounded border border-stroke bg-gray-2 dark:border-strokedark dark:bg-meta-4 flex items-center justify-center overflow-hidden shrink-0">
                                        @if($product->image)
                                            <img src="{{ $product->image }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                        @else
                                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <h5 class="font-semibold text-black dark:text-white text-sm">
                                            {{ $product->name }}
                                        </h5>
                                        <span class="inline-flex items-center rounded bg-blue-500/10 px-2 py-0.5 text-[10px] font-semibold text-blue-600 dark:text-blue-400 mt-0.5">
                                            Product
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <p class="font-bold text-black dark:text-white text-sm">
                                    ₹{{ number_format($product->price, 2) }}
                                </p>
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex rounded-full bg-meta-3/10 px-2.5 py-0.5 text-xs font-semibold text-meta-3">
                                    {{ $product->bv }} BV
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                @if($product->stock > 0)
                                    <span class="inline-flex rounded-full bg-success/10 px-2.5 py-0.5 text-xs font-semibold text-success">
                                        {{ $product->stock }} in stock
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-danger/10 px-2.5 py-0.5 text-xs font-semibold text-danger">
                                        Out of stock
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex rounded-full bg-success/10 px-2.5 py-0.5 text-xs font-medium text-success">
                                    Active
                                </span>
                            </td>
                            <td class="py-4 px-4 text-right pr-4 xl:pr-6">
                                <a href="{{ route('admin.shop.checkout', ['product_id' => $product->id]) }}" 
                                   class="inline-flex items-center gap-1.5 rounded bg-primary py-2 px-4 text-xs font-medium text-white shadow-sm hover:bg-opacity-90 transition">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span>Order for User</span>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
