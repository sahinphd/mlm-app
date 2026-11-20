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
          Sign Up
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Create an account to get started!
        </p>
      </div>
      <div>
        <form method="POST" action="{{ route('register') }}" id="signupForm">
          @csrf
          <div class="space-y-4">
            <!-- Full Name -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Full Name<span class="text-error-500">*</span>
              </label>
              <input
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                placeholder="Your full name"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
              />
              @error('name')
                <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
              @enderror
            </div>

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

            <!-- Phone -->
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                  Phone Number<span class="text-error-500">*</span>
                </label>
                <div class="flex gap-2">
                    <select name="country" id="country-select" required class="dark:bg-dark-900 h-11 w-24 rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="+91" {{ old('country', '+91') == '+91' ? 'selected' : '' }}>India (+91)</option>
                        <option value="+880" {{ old('country') == '+880' ? 'selected' : '' }}>BD (+880)</option>
                        <option value="+44" {{ old('country') == '+44' ? 'selected' : '' }}>UK (+44)</option>
                        <option value="+61" {{ old('country') == '+61' ? 'selected' : '' }}>AUS (+61)</option>
                        <option value="+1" {{ old('country') == '+1' ? 'selected' : '' }}>USA (+1)</option>
                        <option value="+971" {{ old('country') == '+971' ? 'selected' : '' }}>UAE (+971)</option>
                        <option value="+92" {{ old('country') == '+92' ? 'selected' : '' }}>PK (+92)</option>
                        <option value="+977" {{ old('country') == '+977' ? 'selected' : '' }}>NP (+977)</option>
                        <option value="+966" {{ old('country') == '+966' ? 'selected' : '' }}>KW (+966)</option>
                    </select>
                    <input                    
                      type="tel"
                      id="phone-input"
                      name="phone"
                      value="{{ old('phone') }}"
                      required
                      placeholder="10-digit mobile number"
                      inputmode="numeric"
                      class="dark:bg-dark-900 h-11 flex-1 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                    />
                </div>
                @error('phone')
                  <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                @enderror
            </div>

           

            <!-- Password -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Password<span class="text-error-500">*</span>
              </label>
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

            <!-- Confirm Password -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Confirm Password<span class="text-error-500">*</span>
              </label>
              <input
                type="password"
                name="password_confirmation"
                required
                placeholder="Re-enter your password"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
              />
            </div>

             <!-- Referral -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Referral Code (Optional)
              </label>
              <input
                type="text"
                name="referral"
                value="{{ request()->query('ref', old('referral', 'NFWUPJ')) }}"
                placeholder="Enter referral code"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
              />
            </div>

            <!-- Checkbox -->
            <div class="flex items-center">
              <div x-data="{ checkboxToggle: false }">
                <label for="agree" class="flex items-center text-sm font-normal text-gray-700 cursor-pointer select-none dark:text-gray-400">
                  <div class="relative">
                    <input type="checkbox" id="agree" name="agree" required class="sr-only" @change="checkboxToggle = !checkboxToggle" />
                    <div :class="checkboxToggle ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'" class="mr-3 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px]">
                      <span :class="checkboxToggle ? '' : 'opacity-0'">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                      </span>
                    </div>
                  </div>
                  I accept the <a href="#terms" class="text-brand-500 ml-1">Terms & Conditions</a>
                </label>
              </div>
            </div>

            <!-- Button -->
            <div>
              <button type="submit" class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                Sign Up
              </button>
            </div>
          </div>
        </form>
        <div class="mt-5 text-center">
          <p class="text-sm font-normal text-gray-700 dark:text-gray-400">
            Already have an account?
            <a href="{{ route('login') }}" class="text-brand-500 hover:text-brand-600 dark:text-brand-400">Sign In</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const countrySelect = document.getElementById('country-select');
    const phoneInput = document.getElementById('phone-input');

    if (!countrySelect || !phoneInput) return;

    function cleanPhone() {
        let val = phoneInput.value;
        const country = countrySelect.value;
        
        // Remove spaces always
        val = val.replace(/\s+/g, '');

        if (country === '+91') {
            // Strip any non-digits
            val = val.replace(/\D/g, '');

            // If user typed/pasted with leading +91 or 91
            if (val.length === 12 && val.startsWith('91')) {
                val = val.slice(2);
            } else if (val.length === 11 && val.startsWith('0')) {
                val = val.slice(1);
            }

            // Cap at 10 digits
            if (val.length > 10) {
                val = val.slice(0, 10);
            }
            phoneInput.placeholder = "10-digit mobile number";
            phoneInput.maxLength = 10;
        } else {
            phoneInput.placeholder = "Mobile number";
            phoneInput.removeAttribute('maxLength');
        }

        phoneInput.value = val;
    }

    phoneInput.addEventListener('input', cleanPhone);
    phoneInput.addEventListener('paste', function() { setTimeout(cleanPhone, 0); });
    countrySelect.addEventListener('change', cleanPhone);
    
    cleanPhone();
});
</script>
@endsection
