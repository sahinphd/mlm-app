@extends('admin.layout')

@section('content')
<div class="mx-auto max-w-270">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-blue-100 sm:text-title-md">
            Products
        </h2>
        <a href="{{ route('admin.products.create') }}" class="inline-flex items-center justify-center rounded-md bg-primary py-2 px-6 text-center font-medium text-white hover:bg-opacity-90 lg:px-8 xl:px-10">
            + Add New Product
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 flex w-full border-l-6 border-[#34D399] bg-[#34D399] bg-opacity-[15%] px-7 py-4 shadow-md dark:bg-[#1b1b1b] md:p-5">
            <div class="w-full">
                <p class="text-base leading-relaxed text-body">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="border-b border-stroke py-4 px-6.5 dark:border-strokedark flex justify-between items-center">
            <h3 class="font-medium text-black dark:text-white">Product List</h3>
                   </div>
        <div class="max-w-full overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-2 text-left dark:bg-meta-4">
                        <th class="py-4 px-4 font-medium text-black dark:text-white">Image</th>
                        <th class="min-w-[150px] py-4 px-4 font-medium text-black dark:text-white">Product Name</th>
                        <th class="min-w-[100px] py-4 px-4 font-medium text-black dark:text-white">Price</th>
                        <th class="min-w-[100px] py-4 px-4 font-medium text-black dark:text-white">BV</th>
                        <th class="min-w-[100px] py-4 px-4 font-medium text-black dark:text-white">Stock</th>
                        <th class="min-w-[100px] py-4 px-4 font-medium text-black dark:text-white">Status</th>
                        <th class="py-4 px-4 font-medium text-black dark:text-white">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            @if($product->image)
                                <img src="{{ $product->image }}" alt="{{ $product->name }}" class="h-12 w-12 object-cover rounded">
                            @else
                                <div class="h-12 w-12 bg-gray-200 flex items-center justify-center rounded">
                                    <span class="text-[10px] text-gray-400">No Image</span>
                                </div>
                            @endif
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            <p class="text-black dark:text-white">{{ $product->name }}</p>
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            <p class="text-black dark:text-white">{{ number_format($product->price, 2) }}</p>
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            <p class="text-black dark:text-white">{{ $product->bv }}</p>
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            <p class="text-black dark:text-white">{{ $product->stock }}</p>
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            <span class="inline-flex rounded-full bg-opacity-10 py-1 px-3 text-sm font-medium {{ $product->status === 'active' ? 'bg-success text-white' : 'bg-danger text-white' }}">
                                {{ ucfirst($product->status) }}
                            </span>
                        </td>
                        <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                            <div class="flex items-center space-x-3.5">
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="hover:text-primary">Edit</a>
                                <button onclick="deleteProduct({{ $product->id }})" class="hover:text-danger">Delete</button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                        <tr class="bg-gray-2 text-left dark:bg-meta-4">
                            <th class="py-4 px-4 font-medium text-black dark:text-white">Image</th>
                            <th class="min-w-[150px] py-4 px-4 font-medium text-black dark:text-white">Product Name</th>
                            <th class="min-w-[100px] py-4 px-4 font-medium text-black dark:text-white">Price</th>
                            <th class="min-w-[100px] py-4 px-4 font-medium text-black dark:text-white">BV</th>
                            <th class="min-w-[100px] py-4 px-4 font-medium text-black dark:text-white">Stock</th>
                            <th class="min-w-[100px] py-4 px-4 font-medium text-black dark:text-white">Status</th>
                            <th class="py-4 px-4 font-medium text-black dark:text-white">       <a href="{{ route('admin.products.create') }}" class="inline-flex items-center justify-center rounded bg-primary py-2 px-6 text-center font-medium text-white hover:bg-opacity-90">
                + Add Product
            </a></th>
                        </tr>
              
                </tfoot>
            </table>
        </div>
        <div class="p-4">
            {{ $products->links() }}
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function deleteProduct(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/products/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', 'Product has been deleted.', 'success')
                    .then(() => window.location.reload());
                }
            });
        }
    });
}
</script>
@endsection
