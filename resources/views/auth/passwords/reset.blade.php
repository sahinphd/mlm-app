@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto mt-10 p-6 bg-white rounded shadow">
    <h2 class="text-lg font-semibold mb-4">Reset Password</h2>

    @if ($errors->any())
        <div class="mb-4 text-sm text-red-600">Please fix the errors below.</div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}" />
        <div class="mb-4">
            <label class="block text-sm mb-1">Email</label>
            <input type="email" name="email" value="{{ $email ?? old('email') }}" required class="w-full border px-3 py-2 rounded" />
            @error('email')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm mb-1">New Password</label>
            <input type="password" name="password" required class="w-full border px-3 py-2 rounded" />
            @error('password')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm mb-1">Confirm Password</label>
            <input type="password" name="password_confirmation" required class="w-full border px-3 py-2 rounded" />
        </div>

        <div>
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Reset Password</button>
        </div>
    </form>
    <div class="mt-4 text-sm">
        <a href="{{ route('login.view') }}" class="text-blue-600">Back to login</a>
    </div>
</div>
@endsection
