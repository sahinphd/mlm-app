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
                @include('admin.partials.genealogy_node', ['user' => $user, 'isRoot' => true])
            </ul>
        </div>
    </div>
</div>

<style>
    .genealogy-tree, .genealogy-tree ul {
        list-style-type: none;
        margin: 0;
        padding: 0;
        position: relative;
    }
    .genealogy-tree ul {
        margin-left: 20px;
    }
    .genealogy-tree li {
        margin: 0;
        padding: 10px 0 10px 20px;
        position: relative;
    }
    .genealogy-tree li::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        border-left: 1px solid #ccc;
        height: 100%;
        width: 1px;
    }
    .genealogy-tree li:last-child::before {
        height: 20px;
    }
    .genealogy-tree li::after {
        content: "";
        position: absolute;
        top: 20px;
        left: 0;
        border-top: 1px solid #ccc;
        height: 1px;
        width: 20px;
    }
    .genealogy-node {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        background: white;
        border: 1px solid #eee;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        transition: all 0.2s;
    }
    .dark .genealogy-node {
        background: #1a222c;
        border-color: #2e3a47;
    }
    .genealogy-node:hover {
        border-color: #3c50e0;
        box-shadow: 0 4px 6px rgba(60, 80, 224, 0.1);
    }
    .genealogy-node img {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
    }
    .genealogy-info {
        display: flex;
        flex-col: column;
        line-height: 1.2;
    }
    .genealogy-name {
        font-weight: 600;
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
@endsection
