@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <h2 class="mb-5 text-xl font-semibold text-gray-800 dark:text-white/90">Complete Your Profile</h2>

        @if(session('status'))
            <div class="mb-4 p-3 rounded bg-success-50 text-success-600 border border-success-100">{{ session('status') }}</div>
        @endif

        <form method="POST" action="/profile" class="space-y-5">
            @csrf
            <!-- Name -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Full Name<span class="text-error-500">*</span>
              </label>
              <input
                type="text"
                name="name"
                value="{{ old('name', $user->name) }}"
                required
                placeholder="Your full name"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
              />
            </div>

            <!-- Phone -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Phone Number<span class="text-error-500">*</span>
              </label>
              <input
                type="text"
                name="phone"
                value="{{ old('phone', $user->phone) }}"
                required
                placeholder="Phone number"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
              />
            </div>

            <div class="pt-2">
                <button type="submit" class="flex items-center justify-center w-full px-5 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                    Save and Continue
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
