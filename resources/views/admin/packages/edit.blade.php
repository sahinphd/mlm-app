@extends('admin.layout')

@section('content')
<div class="mx-auto max-w-270">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            Edit Package: {{ $package->name }}
        </h2>
    </div>

    <form action="{{ route('admin.packages.update', $package->id) }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 gap-9 sm:grid-cols-2">
            <div class="flex flex-col gap-9">
                <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
                    <div class="border-b border-stroke py-4 px-6.5 dark:border-strokedark">
                        <h3 class="font-medium text-black dark:text-white">Package Info</h3>
                    </div>
                    <div class="p-6.5">
                        <div class="mb-4.5">
                            <label class="mb-2.5 block text-black dark:text-white">Package Name</label>
                            <input type="text" name="name" value="{{ $package->name }}" required placeholder="Starter Pack" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-3 px-5 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary" />
                        </div>

                        <div class="mb-4.5">
                            <label class="mb-2.5 block text-black dark:text-white">Image URL (Cloudinary or any URL)</label>
                            <input type="text" name="image" value="{{ $package->image }}" placeholder="https://res.cloudinary.com/..." class="w-full rounded border-[1.5px] border-stroke bg-transparent py-3 px-5 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary" />
                            @if($package->image)
                                <div class="mt-2">
                                    <img src="{{ $package->image }}" alt="Preview" class="h-20 w-20 object-cover rounded border">
                                </div>
                            @endif
                        </div>

                        <div class="mb-4.5">
                            <label class="mb-2.5 block text-black dark:text-white">Description</label>
                            <textarea name="description" rows="3" placeholder="Enter package description" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-3 px-5 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">{{ $package->description }}</textarea>
                        </div>

                        <div class="mb-4.5 flex flex-col gap-6 xl:flex-row">
                            <div class="w-full xl:w-1/2">
                                <label class="mb-2.5 block text-black dark:text-white">Price (₹)</label>
                                <input type="number" step="0.01" name="price" value="{{ $package->price }}" required placeholder="0.00" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-3 px-5 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary" />
                            </div>
                            <div class="w-full xl:w-1/2">
                                <label class="mb-2.5 block text-black dark:text-white">BV Points</label>
                                <input type="number" step="0.01" name="bv" value="{{ $package->bv }}" required placeholder="0.00" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-3 px-5 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary" />
                            </div>
                        </div>

                        <div class="mb-4.5">
                            <label class="mb-2.5 block text-black dark:text-white">Status</label>
                            <select name="status" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-3 px-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input">
                                <option value="active" {{ $package->status === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $package->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-9">
                <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
                    <div class="border-b border-stroke py-4 px-6.5 dark:border-strokedark flex justify-between items-center">
                        <h3 class="font-medium text-black dark:text-white">Package Products</h3>
                        <button type="button" onclick="addProductRow()" class="text-sm text-primary font-medium hover:underline">+ Add Product</button>
                    </div>
                    <div class="p-6.5" id="product-list">
                        @foreach($package->products as $index => $p)
                        <div class="mb-4 flex gap-4 product-row">
                            <div class="w-2/3">
                                <select name="products[{{ $index }}][id]" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-3 px-5 font-medium outline-none transition focus:border-primary dark:border-form-strokedark dark:bg-form-input">
                                    <option value="">Select Product</option>
                                    @foreach($products as $prod)
                                        <option value="{{ $prod->id }}" {{ $p->id == $prod->id ? 'selected' : '' }}>{{ $prod->name }} (₹{{ $prod->price }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-1/4">
                                <input type="number" name="products[{{ $index }}][quantity]" min="1" value="{{ $p->pivot->quantity }}" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-3 px-5 font-medium outline-none transition focus:border-primary dark:border-form-strokedark dark:bg-form-input" />
                            </div>
                            <button type="button" onclick="this.parentElement.remove()" class="text-danger hover:underline">Remove</button>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-4.5">
            <a href="{{ route('admin.packages') }}" class="flex justify-center rounded border border-stroke py-2 px-6 font-medium text-black hover:shadow-1 dark:border-strokedark dark:text-white">
                Cancel
            </a>
            <button type="submit" class="flex justify-center rounded bg-primary py-2 px-6 font-medium text-gray hover:bg-opacity-90">
                Update Package
            </button>
        </div>
    </form>
</div>

<script>
let rowIdx = {{ $package->products->count() }};
function addProductRow() {
    const list = document.getElementById('product-list');
    const div = document.createElement('div');
    div.className = 'mb-4 flex gap-4 product-row';
    div.innerHTML = `
        <div class="w-2/3">
            <select name="products[${rowIdx}][id]" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-3 px-5 font-medium outline-none transition focus:border-primary dark:border-form-strokedark dark:bg-form-input">
                <option value="">Select Product</option>
                @foreach($products as $prod)
                    <option value="{{ $prod->id }}">{{ $prod->name }} (₹{{ $prod->price }})</option>
                @endforeach
            </select>
        </div>
        <div class="w-1/4">
            <input type="number" name="products[${rowIdx}][quantity]" min="1" value="1" class="w-full rounded border-[1.5px] border-stroke bg-transparent py-3 px-5 font-medium outline-none transition focus:border-primary dark:border-form-strokedark dark:bg-form-input" />
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-danger hover:underline">Remove</button>
    `;
    list.appendChild(div);
    rowIdx++;
}
</script>
@endsection
