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
          Enter your email/mobile and password to sign in!
        </p>
      </div>

      @if(session('warning'))
      <div class="mb-4 p-4 text-sm text-amber-700 bg-amber-50 rounded-lg border border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800" role="alert">
        {{ session('warning') }}
      </div>
      @endif

      @if(config('services.truecaller.client_id') && ($systemSettings['truecaller_login'] ?? 'off') === 'on')
      <div class="mb-6">
        <button type="button" onclick="initTruecaller()" class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-[#21b3ff] shadow-theme-xs hover:bg-[#00a3f5]">
            <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.5 14H7.5V8h9v8z"/>
            </svg>
            Login with Truecaller
        </button>
        <div class="relative flex items-center justify-center mt-6">
            <div class="flex-grow border-t border-gray-300 dark:border-gray-700"></div>
            <span class="px-3 text-xs text-gray-500 uppercase bg-white dark:bg-dark-900">Or continue with</span>
            <div class="flex-grow border-t border-gray-300 dark:border-gray-700"></div>
        </div>
      </div>
      @endif

      <div>
        <form method="POST" action="{{ route('login') }}">
          @csrf
          <div class="space-y-4">
            <!-- Email/Phone -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Email or Mobile Number<span class="text-error-500">*</span>
              </label>
              <input
                type="text"
                name="email"
                value="{{ old('email') }}"
                required
                placeholder="info@gmail.com or 9876543210"
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
@if(config('services.truecaller.client_id') && ($systemSettings['truecaller_login'] ?? 'off') === 'on')
<script>
    function initTruecaller() {
        // Generate a random nonce for security
        const nonce = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
        const appKey = "{{ config('services.truecaller.client_id') }}";
        const appName = "{{ config('app.name') }}";
        
        // Deep link for mobile web flow
        const truecallerDeepLink = "truecallersdk://truesdk/web_verify?" +
            "type=btmsheet" +
            "&requestNonce=" + nonce +
            "&partnerKey=" + appKey +
            "&partnerName=" + appName +
            "&lang=en" +
            "&loginPrefix=signin" +
            "&loginSuffix=toconfirm" +
            "&ctaPrefix=continue" +
            "&ctaColor=%230087ff" +
            "&ctaTextColor=%23ffffff" +
            "&btnShape=round" +
            "&skipOption=useanothermethod" +
            "&ttl=60000";

        // Try to open the Truecaller app
        window.location.href = truecallerDeepLink;

        // Fallback: If the user stays on this page for more than 2 seconds, 
        // it means the Truecaller app is likely not installed or didn't open.
        setTimeout(function() {
            if (document.hasFocus()) {
                console.log("Truecaller app not detected or failed to open.");
                // Optionally alert the user or show a fallback message
                // alert("Please ensure Truecaller app is installed for 1-tap login.");
            }
        }, 2000);
    }
</script>
@endif
@endsection
