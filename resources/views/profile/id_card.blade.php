@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-270">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            Digital ID Card
        </h2>
        <button onclick="window.print()" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition shadow-md no-print">
            <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print ID Card
        </button>
    </div>

    <div class="flex justify-center py-10">
        <!-- ID Card Container -->
        <div id="id-card" class="relative w-[400px] h-[250px] bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-200 font-outfit text-gray-800">
            <!-- Background Decoration -->
            <div class="absolute top-0 right-0 w-32 h-32 bg-brand-500 rounded-bl-full opacity-10"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-brand-500 rounded-tr-full opacity-10"></div>
            
            <!-- Header -->
            <div class="bg-brand-500 p-4 flex items-center gap-3">
                <img src="{{ asset('images/logo/auth-logo.svg') }}" class="h-8 w-auto" alt="Logo">
                <div class="text-white">
                    <h3 class="font-bold text-sm tracking-wider uppercase">{{ config('app.name', 'MLM App') }}</h3>
                    <p class="text-[10px] opacity-90">Official Member Card</p>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6 flex gap-6">
                <!-- Photo Placeholder -->
                <div class="w-24 h-28 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center overflow-hidden shrink-0">
                    @if(auth()->user()->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" class="w-full h-full object-cover" alt="User">
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    @endif
                </div>

                <!-- Info -->
                <div class="flex-1 space-y-2">
                    <div>
                        <p class="text-[9px] text-gray-500 uppercase font-bold tracking-tight">Full Name</p>
                        <p class="text-sm font-bold text-gray-900">{{ $user->name }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <p class="text-[9px] text-gray-500 uppercase font-bold tracking-tight">User ID</p>
                            <p class="text-[11px] font-bold">#{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}</p>
                        </div>
                        <div>
                            <p class="text-[9px] text-gray-500 uppercase font-bold tracking-tight">Joined</p>
                            <p class="text-[11px] font-bold">{{ $user->created_at->format('M Y') }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-[9px] text-gray-500 uppercase font-bold tracking-tight">Phone Number</p>
                        <p class="text-[11px] font-bold">{{ $user->phone ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Footer / Barcode -->
            <div class="absolute bottom-0 w-full bg-gray-50 border-t border-gray-100 p-2 flex flex-col items-center">
                <svg id="barcode"></svg>
                <p class="text-[8px] text-gray-400 font-mono mt-0.5 tracking-[0.2em]">{{ $user->referralRecord->referral_code ?? $user->id }}</p>
            </div>
        </div>
    </div>

    <div class="mt-6 text-center text-sm text-gray-500 no-print">
        <p>Tip: You can print this card or show the digital version to an admin for quick identification at the shop.</p>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #id-card, #id-card * {
        visibility: visible;
    }
    #id-card {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%) scale(1.5);
        box-shadow: none;
        border: 1px solid #ddd;
    }
    .no-print {
        display: none !important;
    }
}
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        JsBarcode("#barcode", "{{ $user->referralRecord->referral_code ?? $user->id }}", {
            format: "CODE128",
            width: 1.5,
            height: 30,
            displayValue: false,
            margin: 0,
            background: "transparent"
        });
    });
</script>
@endpush
@endsection
