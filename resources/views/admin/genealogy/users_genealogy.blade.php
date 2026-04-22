@extends('admin.layout')

@section('content')
<div class="mx-auto max-w-270">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            Genealogy Tree: {{ $user->name }}
        </h2>
        <a href="{{ route('admin.users') }}" class="inline-flex items-center justify-center rounded-md bg-gray py-2 px-6 text-center font-medium text-black hover:bg-opacity-90 dark:bg-meta-4 dark:text-white">
            Back to List
        </a>
    </div>

    <div class="rounded-sm border border-stroke bg-white p-7 shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="genealogy-container overflow-auto p-4 bg-gray-50 dark:bg-meta-4 rounded-sm border border-stroke dark:border-strokedark">
            <ul class="genealogy-tree">
                @include('admin.partials.genealogy_node', ['user' => $user, 'isRoot' => true, 'depth' => 1])
            </ul>
        </div>
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
        height: 31px;
    }
    /* Horizontal line */
    .genealogy-tree li::after {
        content: "";
        position: absolute;
        top: 31px;
        left: 0;
        border-top: 2px solid #cbd5e1;
        height: 2px;
        width: 30px;
    }
    .dark .genealogy-tree li::after {
        border-top-color: #334155;
    }
    .genealogy-node {
        min-width: 250px;
    }
    .genealogy-toggle svg {
        transition: transform 0.2s;
    }
    .rotate-90 {
        transform: rotate(90deg);
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Genealogy Toggle/Load Logic
        $(document).on('click', '.genealogy-toggle', function() {
            const $btn = $(this);
            const $item = $btn.closest('.genealogy-item');
            const $container = $item.find('> .children-container');
            const userId = $item.data('user-id');
            const isLoaded = $btn.attr('data-loaded') === 'true';

            if (!isLoaded) {
                // Load via AJAX
                $btn.find('svg').addClass('animate-spin');
                $.ajax({
                    url: `/admin/users/${userId}/genealogy-children`,
                    method: 'GET',
                    success: function(html) {
                        $container.html(html).removeClass('hidden');
                        $btn.attr('data-loaded', 'true');
                        $btn.find('svg').removeClass('animate-spin').addClass('rotate-90');
                    },
                    error: function() {
                        alert('Failed to load children.');
                        $btn.find('svg').removeClass('animate-spin');
                    }
                });
            } else {
                // Just toggle visibility
                $container.toggleClass('hidden');
                $btn.find('svg').toggleClass('rotate-90');
            }
        });
    });
</script>
@endsection
