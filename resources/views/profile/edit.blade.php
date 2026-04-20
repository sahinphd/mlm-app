@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            Profile Settings
        </h2>
        <a href="{{ route('profile.id-card') }}" class="inline-flex items-center justify-center rounded-lg bg-white border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition shadow-sm dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
            </svg>
            Print Digital ID Card
        </a>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 shadow-sm">
        <h3 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white/90">Complete Your KYC & Profile Details</h3>

        @if(session('status'))
            <div class="mb-6 p-4 rounded-lg bg-success-50 text-success-600 border border-success-100 flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 rounded-lg bg-red-50 text-red-600 border border-red-100">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            
            <!-- Avatar Section -->
            <div class="flex flex-col sm:flex-row items-center gap-6 pb-6 border-b border-gray-100 dark:border-gray-800">
                <div class="relative">
                    <div class="h-24 w-24 rounded-full border-2 border-brand-500 p-1">
                        @if($user->avatar)
                            <img src="{{ asset($user->avatar) }}" id="avatar-preview" class="h-full w-full rounded-full object-cover" alt="Profile">
                        @else
                            <div class="h-full w-full rounded-full bg-gray-100 flex items-center justify-center text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="flex-1 text-center sm:text-left">
                    <h4 class="font-medium text-gray-800 dark:text-white">Profile Photo</h4>
                    <p class="text-xs text-gray-500 mb-3">Update your profile picture. Max 1MB (JPG, PNG).</p>
                    <input type="file" name="avatar" accept="image/*" class="text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer" onchange="previewImage(event)"/>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Info -->
                <div class="space-y-5">
                    <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Personal Information</h4>
                    
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Full Name<span class="text-error-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90" />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Phone Number<span class="text-error-500">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90" />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Full Address</label>
                        <textarea name="address" rows="3" class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90">{{ old('address', $user->address) }}</textarea>
                    </div>
                </div>

                <!-- KYC Info -->
                <div class="space-y-5">
                    <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wider">KYC & Nominee Details</h4>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Aadhaar Number</label>
                        <input type="text" name="aadhaar_number" value="{{ old('aadhaar_number', $user->aadhaar_number) }}" placeholder="12-digit Aadhaar" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90" />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">PAN Number</label>
                        <input type="text" name="pan_number" value="{{ old('pan_number', $user->pan_number) }}" placeholder="PAN Number" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nominee Name</label>
                            <input type="text" name="nominee_name" value="{{ old('nominee_name', $user->nominee_name) }}" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Relation</label>
                            <input type="text" name="nominee_relation" value="{{ old('nominee_relation', $user->nominee_relation) }}" placeholder="e.g. Spouse, Son" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="flex items-center justify-center w-full px-5 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                    Update Profile & KYC
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(event) {
    var reader = new FileReader();
    reader.onload = function() {
        var output = document.getElementById('avatar-preview');
        if (output) {
            output.src = reader.result;
        } else {
            // If preview img doesn't exist, replace the placeholder div
            location.reload(); // Simplest way to show new image if logic is complex
        }
    }
    reader.readAsDataURL(event.target.files[0]);
}
</script>
@endsection
