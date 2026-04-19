@extends('layouts.auth')

@section('content')
<!-- Form -->
<div class="flex flex-col flex-1 w-full lg:w-1/2 overflow-y-auto">
  <div class="w-full max-w-md pt-10 mx-auto lg:hidden px-4">
    <a href="{{ url('/') }}" class="block mb-4">
        <img src="{{ asset('images/logo/logo.svg') }}" class="dark:hidden" alt="Logo" />
        <img src="{{ asset('images/logo/logo-dark.svg') }}" class="hidden dark:block" alt="Logo" />
    </a>
  </div>
  <div class="flex flex-col justify-center flex-1 w-full max-w-md mx-auto py-12 px-4 sm:px-0">
    <div>
      <div class="mb-5 sm:mb-8">
        <h1 class="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md">
          Reset Password
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Enter your new password to reset it!
        </p>
      </div>
      <div>
        <form method="POST" action="{{ route('password.update') }}">
          @csrf
          <input type="hidden" name="token" value="{{ $token }}" />
          <div class="space-y-4">
            <!-- Email -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Email Address<span class="text-error-500">*</span>
              </label>
              <input
                type="email"
                name="email"
                value="{{ $email ?? old('email') }}"
                required
                placeholder="info@gmail.com"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
              />
              @error('email')
                <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
              @enderror
            </div>

            <!-- Password -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                New Password<span class="text-error-500">*</span>
              </label>
              <input
                type="password"
                name="password"
                required
                placeholder="Enter your new password"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
              />
              @error('password')
                <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
              @enderror
            </div>

            <!-- Confirm Password -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Confirm Password<span class="text-error-500">*</span>
              </label>
              <input
                type="password"
                name="password_confirmation"
                required
                placeholder="Confirm your new password"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
              />
            </div>

            <!-- Button -->
            <div>
              <button type="submit" class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                Reset Password
              </button>
            </div>
          </div>
        </form>
        <div class="mt-5 text-center">
          <p class="text-sm font-normal text-gray-700 dark:text-gray-400">
            Back to
            <a href="{{ route('login') }}" class="text-brand-500 hover:text-brand-600 dark:text-brand-400">Sign In</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
