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
          Sign In
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Enter your email and password to sign in!
        </p>
      </div>
      <div>
        <form method="POST" action="{{ route('login') }}">
          @csrf
          <div class="space-y-4">
            <!-- Email -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Email<span class="text-error-500">*</span>
              </label>
              <input
                type="email"
                name="email"
                value="{{ old('email') }}"
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
              <div class="flex items-center justify-between mb-1.5">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">
                  Password<span class="text-error-500">*</span>
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400">
                      Forgot password?
                    </a>
                @endif
              </div>
              <input
                type="password"
                name="password"
                required
                placeholder="Enter your password"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
              />
              @error('password')
                <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
              @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center">
              <div x-data="{ checkboxToggle: false }">
                <label for="remember" class="flex items-center text-sm font-normal text-gray-700 cursor-pointer select-none dark:text-gray-400">
                  <div class="relative">
                    <input type="checkbox" id="remember" name="remember" class="sr-only" @change="checkboxToggle = !checkboxToggle" />
                    <div :class="checkboxToggle ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'" class="mr-3 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px]">
                      <span :class="checkboxToggle ? '' : 'opacity-0'">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                      </span>
                    </div>
                  </div>
                  Keep me logged in
                </label>
              </div>
            </div>

            <!-- Button -->
            <div>
              <button type="submit" class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                Sign In
              </button>
            </div>
          </div>
        </form>
        <div class="mt-5 text-center">
          <p class="text-sm font-normal text-gray-700 dark:text-gray-400">
            Don't have an account?
            <a href="{{ route('register') }}" class="text-brand-500 hover:text-brand-600 dark:text-brand-400">Sign Up</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
