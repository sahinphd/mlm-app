@extends('admin.layout')

@section('content')
<div class="mx-auto max-w-270">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            Add New Product
        </h2>
    </div>

    <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="p-7">
            <form action="{{ route('admin.products.store') }}" method="POST">
                @csrf
                <div class="mb-5.5">
                    <label class="mb-3 block text-sm font-medium text-black dark:text-white">Product Name</label>
                    <input type="text" name="name" required class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                </div>

                <div class="mb-5.5">
                    <label class="mb-3 block text-sm font-medium text-black dark:text-white">Image URL (Cloudinary or any URL)</label>
                    <input type="text" name="image" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary" placeholder="https://res.cloudinary.com/...">
                </div>

                <div class="mb-5.5 grid grid-cols-1 gap-5.5 sm:grid-cols-3">
                    <div>
                        <label class="mb-3 block text-sm font-medium text-black dark:text-white">Price</label>
                        <input type="number" step="0.01" name="price" required class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                    </div>
                    <div>
                        <label class="mb-3 block text-sm font-medium text-black dark:text-white">BV (Business Volume)</label>
                        <input type="number" step="0.01" name="bv" required class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                    </div>
                    <div>
                        <label class="mb-3 block text-sm font-medium text-black dark:text-white">Stock</label>
                        <input type="number" name="stock" required class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                    </div>
                </div>

                <div class="mb-5.5">
                    <label class="mb-3 block text-sm font-medium text-black dark:text-white">Status</label>
                    <select name="status" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="flex justify-end gap-4.5">
                    <a href="{{ route('admin.products') }}" class="flex justify-center rounded border border-stroke py-2 px-6 font-medium text-black hover:shadow-1 dark:border-strokedark dark:text-white">Cancel</a>
                    <button type="submit" class="flex justify-center rounded bg-primary py-2 px-6 font-medium text-gray hover:bg-opacity-90">Create Product</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
