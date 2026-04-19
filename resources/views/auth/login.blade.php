@extends('layouts.auth')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center bg-neutral-50 dark:bg-neutral-900">
    <div class="w-full max-w-md px-6">
        <div class="bg-white dark:bg-[#0b1221] rounded-xl shadow-lg p-8">
            <h2 class="text-center text-2xl font-semibold mb-2">Sign in</h2>
            <p class="text-center text-sm text-neutral-500 mb-4">Don't have an account? <a href="/register" class="text-blue-600">Register</a></p>

            <form method="POST" action="/login" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm text-neutral-600 mb-1">Email</label>
                    <input name="email" type="email" required placeholder="email@email.com" class="mt-1 block w-full border border-neutral-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm text-neutral-600">Password</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs text-blue-600 hover:underline dark:text-blue-400">Forgot password?</a>
                        @endif
                    </div>
                    <input name="password" type="password" required placeholder="Enter Password" class="mt-1 block w-full border border-neutral-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-blue-600 text-white rounded-md py-2.5 font-medium">Sign in</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
