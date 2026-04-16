@extends('layouts.app')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center bg-neutral-50 dark:bg-neutral-900">
    <div class="w-full max-w-xl px-6">
        <div class="bg-white dark:bg-[#0b1221] rounded-xl shadow-lg p-8 text-center">
            <h1 class="text-2xl font-semibold mb-4">Welcome</h1>
            <p class="text-neutral-600 mb-6">Please sign in or create a new account to continue.</p>
            <div class="flex justify-center gap-4">
                <a href="/login" class="inline-block px-6 py-3 rounded-md border border-neutral-200">Login</a>
                <a href="/register" class="inline-block px-6 py-3 rounded-md bg-blue-600 text-white">Register</a>
            </div>
        </div>
    </div>
</div>

@endsection
