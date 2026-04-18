@extends('admin.layout')

@section('content')
<div class="mx-auto max-w-270">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            Network Genealogy
        </h2>
    </div>

    <!-- User Search -->
    <div class="rounded-sm border border-stroke bg-white p-4 shadow-default dark:border-strokedark dark:bg-boxdark mb-6">
        <div class="relative max-w-md">
            <label class="mb-2.5 block text-black dark:text-white font-medium">Search User to View Tree</label>
            <div class="relative">
                <input type="text" id="user_search" placeholder="Enter name, email or ID..." autocomplete="off" 
                    class="w-full rounded border-[1.5px] border-stroke bg-transparent py-3 px-5 font-medium outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
                <div id="search_results" class="absolute z-50 w-full bg-white dark:bg-boxdark border border-stroke dark:border-strokedark rounded-b shadow-lg hidden max-h-64 overflow-y-auto">
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-sm border border-stroke bg-white p-7 shadow-default dark:border-strokedark dark:bg-boxdark min-h-[400px]">
        @if($user)
            <div class="mb-6 flex items-center justify-between border-b border-stroke pb-4 dark:border-strokedark">
                <div class="flex items-center gap-3">
                    <img src="{{ $user->avatar_url }}" alt="avatar" class="h-12 w-12 rounded-full border-2 border-primary p-0.5">
                    <div>
                        <h3 class="font-bold text-black dark:text-white text-lg">{{ $user->name }}</h3>
                        <p class="text-xs text-gray-500">ID: #{{ $user->id }} | {{ $user->email }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="inline-block rounded bg-primary bg-opacity-10 py-1 px-3 text-sm font-medium text-white">
                        Root Node
                    </span>
                </div>
            </div>
            
            <div class="genealogy-container overflow-auto p-4 bg-gray-50 dark:bg-meta-4 rounded-sm border border-stroke dark:border-strokedark">
                <ul class="genealogy-tree">
                    @include('admin.partials.genealogy_node', ['user' => $user, 'isRoot' => true])
                </ul>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-20 text-gray-400">
                <svg class="h-16 w-16 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21a6 6 0 00-9-5.197M15 21a6 6 0 006-6v-1a6 6 0 00-6-6h-1.333a6 6 0 00-11.334 0v1a6 6 0 006 6h1.333z" />
                </svg>
                <p class="text-lg">Please search for a user to visualize their network genealogy.</p>
            </div>
        @endif
    </div>
</div>

<style>
    /* Genealogy Tree Styles */
    .genealogy-tree, .genealogy-tree ul {
        list-style-type: none;
        margin: 0;
        padding: 0;
        position: relative;
    }
    .genealogy-tree ul {
        margin-left: 30px;
    }
    .genealogy-tree li {
        margin: 0;
        padding: 15px 0 15px 30px;
        position: relative;
    }
    /* Vertical line */
    .genealogy-tree li::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        border-left: 2px solid #cbd5e1;
        height: 100%;
        width: 2px;
    }
    .dark .genealogy-tree li::before {
        border-left-color: #334155;
    }
    /* Vertical line for last child */
    .genealogy-tree li:last-child::before {
        height: 25px;
    }
    /* Horizontal line */
    .genealogy-tree li::after {
        content: "";
        position: absolute;
        top: 25px;
        left: 0;
        border-top: 2px solid #cbd5e1;
        height: 2px;
        width: 30px;
    }
    .dark .genealogy-tree li::after {
        border-top-color: #334155;
    }
    .genealogy-node {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 10px 16px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        transition: all 0.2s;
        min-width: 200px;
    }
    .dark .genealogy-node {
        background: #1a222c;
        border-color: #2e3a47;
    }
    .genealogy-node:hover {
        border-color: #3c50e0;
        box-shadow: 0 10px 15px -3px rgba(60, 80, 224, 0.15);
        transform: translateY(-1px);
    }
    .genealogy-node img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e2e8f0;
    }
    .dark .genealogy-node img {
        border-color: #2e3a47;
    }
    .genealogy-info {
        display: flex;
        flex-direction: column;
        line-height: 1.4;
    }
    .genealogy-name {
        font-weight: 700;
        color: #1c2434;
        font-size: 14px;
    }
    .dark .genealogy-name {
        color: white;
    }
    .genealogy-meta {
        font-size: 11px;
        color: #64748b;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        const $input = $('#user_search');
        const $results = $('#search_results');
        let timeout = null;

        $input.on('keyup', function() {
            clearTimeout(timeout);
            const query = $(this).val();
            if (query.length < 2) {
                $results.hide();
                return;
            }

            timeout = setTimeout(function() {
                $.ajax({
                    url: "{{ route('admin.users.search') }}",
                    data: { q: query },
                    success: function(data) {
                        let html = '';
                        data.forEach(user => {
                            html += `<div class="p-3 hover:bg-gray-100 dark:hover:bg-meta-4 cursor-pointer border-b border-stroke dark:border-strokedark last:border-0" 
                                data-id="${user.id}">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center text-primary text-xs font-bold">
                                        #${user.id}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-black dark:text-white text-sm">${user.name}</span>
                                        <span class="text-xs text-gray-500">${user.email}</span>
                                    </div>
                                </div>
                            </div>`;
                        });
                        if (html) {
                            $results.html(html).show();
                        } else {
                            $results.html('<div class="p-4 text-center text-gray-500">No users found</div>').show();
                        }
                    }
                });
            }, 300);
        });

        $results.on('click', 'div[data-id]', function() {
            const id = $(this).data('id');
            window.location.href = "{{ route('admin.genealogy.genealogy') }}?user_id=" + id;
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#user_search').length && !$(e.target).closest('#search_results').length) {
                $results.hide();
            }
        });
    });
</script>
@endsection
