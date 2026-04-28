@extends('layouts.auth')

@section('content')
<div class="flex flex-col flex-1 w-full lg:w-1/2 overflow-y-auto">
  <div class="flex flex-col justify-center flex-1 w-full max-w-md mx-auto py-12 px-4 sm:px-0">
    <div class="mb-5 sm:mb-8">
        <h1 class="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md">
          Select Your Account
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Multiple accounts found for this mobile number. Please select which one you want to sign in to.
        </p>
    </div>

    <form method="POST" action="{{ route('login.select_account.post') }}">
        @csrf
        <div class="space-y-4">
            @foreach($accounts as $account)
            <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-dark-800 transition">
                <input type="radio" name="user_id" value="{{ $account['id'] }}" class="w-4 h-4 text-brand-500" required>
                <div class="ml-4">
                    <div class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $account['name'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $account['email'] }}</div>
                </div>
            </label>
            @endforeach

            <button type="submit" class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                Sign In to Selected Account
            </button>
            
            <a href="{{ route('login') }}" class="block text-center text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400">
                Cancel and try again
            </a>
        </div>
    </form>
  </div>
</div>
@endsection
