@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto p-6">
    <div class="bg-white rounded shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Complete Your Profile</h2>

        @if(session('status'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200">{{ session('status') }}</div>
        @endif

        <form method="POST" action="/profile" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm text-neutral-600 mb-1">Full name</label>
                <input name="name" type="text" required value="{{ old('name', $user->name) }}" class="mt-1 block w-full border border-neutral-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200">
            </div>

            <div>
                <label class="block text-sm text-neutral-600 mb-1">Phone</label>
                <input name="phone" type="text" required value="{{ old('phone', $user->phone) }}" class="mt-1 block w-full border border-neutral-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200">
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-blue-600 text-white rounded-md py-2.5 font-medium">Save and Continue</button>
            </div>
        </form>
    </div>
</div>

@endsection
