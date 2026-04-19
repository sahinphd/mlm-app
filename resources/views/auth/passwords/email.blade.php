@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto mt-10 p-6 bg-white rounded shadow">
    <h2 class="text-lg font-semibold mb-4">Forgot your password?</h2>

    @if (session('status'))
        <div class="mb-4 text-sm text-green-600">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-4">
            <label class="block text-sm mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="w-full border px-3 py-2 rounded" />
            @error('email')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Send reset link</button>
        </div>
    </form>
    <div class="mt-4 text-sm">
        <a href="{{ route('login.view') }}" class="text-blue-600">Back to login</a>
    </div>
</div>
@endsection
