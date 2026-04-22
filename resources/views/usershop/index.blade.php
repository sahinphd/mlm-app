@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">Shop</h2>
    </div>

    @if($packages->count() > 0)
    <div class="mb-10">
        <h3 class="mb-5 text-xl font-semibold text-black dark:text-white">Available Packages</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-4 2xl:gap-7.5">
            @foreach($packages as $package)
            <div class="rounded-sm border border-stroke bg-white p-4 shadow-default dark:border-strokedark dark:bg-boxdark">
                <div class="mb-4 h-48 w-full overflow-hidden rounded">
                    @if($package->image)
                        <img src="{{ $package->image }}" alt="{{ $package->name }}" class="h-full w-full object-cover">
                    @else
                        <div class="h-full w-full bg-gray-2 dark:bg-meta-4 flex items-center justify-center text-sm font-medium text-meta-3">
                            PKG
                        </div>
                    @endif
                </div>
                <div>
                    <h4 class="mb-1.5 font-semibold text-black dark:text-white">{{ $package->name }}</h4>
                    <p class="text-sm font-medium">Price: {{ number_format($package->price, 2) }}</p>
                    <p class="text-xs text-meta-3 mb-4">BV: {{ $package->bv }}</p>
                    <a href="{{ route('shop.checkout', ['package_id' => $package->id]) }}" class="flex w-full justify-center rounded bg-primary py-2 px-6 font-medium text-white hover:bg-opacity-90">
                        Buy Now
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div>
        <h3 class="mb-5 text-xl font-semibold text-black dark:text-white">Individual Products</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-4 2xl:gap-7.5">
            @foreach($products as $product)
            <div class="rounded-sm border border-stroke bg-white p-4 shadow-default dark:border-strokedark dark:bg-boxdark">
                <div class="mb-4 h-48 w-full overflow-hidden rounded bg-gray-2 dark:bg-meta-4 flex items-center justify-center text-sm font-medium text-meta-3">
                    PROD
                </div>
                <div>
                    <h4 class="mb-1.5 font-semibold text-black dark:text-white">{{ $product->name }}</h4>
                    <p class="text-sm font-medium">Price: {{ number_format($product->price, 2) }}</p>
                    <p class="text-xs text-meta-3 mb-4">BV: {{ $product->bv }}</p>
                    <a href="{{ route('shop.checkout', ['product_id' => $product->id]) }}" class="flex w-full justify-center rounded bg-primary py-2 px-6 font-medium text-white hover:bg-opacity-90">
                        Buy Now
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
