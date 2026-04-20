@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-270">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between no-print">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            Digital ID Card
        </h2>
        <button onclick="window.print()" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition shadow-md">
            <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print ID Card
        </button>
    </div>

    <div class="flex justify-center py-10">
        <!-- ID Card Container -->
        <div id="id-card" class="relative bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-300 font-outfit select-none" style="width: 85.6mm; height: 54mm; min-width: 85.6mm; min-height: 54mm;">
            
            <!-- Top Decorative Bar (Brand Blue) -->
            <div class="absolute top-0 left-0 w-full h-[15mm] bg-slate-900">
                <div class="absolute top-0 right-0 w-24 h-full bg-brand-500 skew-x-[-20deg] translate-x-8 opacity-20"></div>
                <div class="absolute top-0 right-0 w-12 h-full bg-brand-500 skew-x-[-20deg] translate-x-4 opacity-40"></div>
            </div>
            
            <!-- Header Content -->
            <div class="relative z-10 p-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="bg-white p-0.5 rounded shadow-sm">
                        <img src="{{ asset('images/logo/auth-logo.svg') }}" class="h-5 w-auto" alt="Logo">
                    </div>
                    <div class="text-white">
                        <h3 class="font-bold text-[10px] tracking-widest uppercase leading-tight">{{ config('app.name', 'MLM App') }}</h3>
                        <p class="text-[7px] font-medium text-brand-300 uppercase tracking-tighter">Official Member</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-[9px] font-bold text-white leading-none">ID CARD</p>
                    <p class="text-[6px] text-gray-400 mt-0.5 uppercase tracking-widest">Digital Pass</p>
                </div>
            </div>

            <!-- Card Body -->
            <div class="relative z-10 px-4 pt-1 flex gap-4">
                <!-- Photo with Premium Frame -->
                <div class="shrink-0">
                    <div class="w-20 h-24 bg-gray-50 rounded-lg border-2 border-slate-900 p-0.5 shadow-md overflow-hidden flex items-center justify-center">
                        @if($user->avatar)
                            <img src="{{ asset($user->avatar) }}" class="w-full h-full object-cover rounded" alt="User">
                        @else
                            <div class="w-full h-full bg-slate-100 flex items-center justify-center text-slate-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Personal Info -->
                <div class="flex-1 min-w-0 py-0">
                    <div class="mb-1">
                        <p class="text-[6px] text-slate-400 font-bold uppercase tracking-widest leading-tight">Name</p>
                        <p class="text-[10px] font-black text-slate-900 truncate leading-tight">{{ strtoupper($user->name) }}</p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-x-2 gap-y-1">
                        <div>
                            <p class="text-[6px] text-slate-400 font-bold uppercase tracking-widest leading-tight">User ID</p>
                            <p class="text-[8px] font-bold text-slate-800">#{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}</p>
                        </div>
                        <div>
                            <p class="text-[6px] text-slate-400 font-bold uppercase tracking-widest leading-tight">Joined</p>
                            <p class="text-[8px] font-bold text-slate-800">{{ $user->created_at->format('M Y') }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-[6px] text-slate-400 font-bold uppercase tracking-widest leading-tight">Phone</p>
                            <p class="text-[8px] font-bold text-slate-800">{{ $user->phone ?? 'N/A' }}</p>
                        </div>
                        @if($user->aadhaar_number)
                        <div class="col-span-2">
                            <p class="text-[6px] text-slate-400 font-bold uppercase tracking-widest leading-tight">KYC / ID</p>
                            <p class="text-[8px] font-bold text-slate-800 tracking-wider">{{ $user->aadhaar_number }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Address Strip -->
            @if($user->address)
            <div class="px-4 mt-0.5">
                <div class="border-t border-gray-100 pt-0.5">
                    <p class="text-[5.5px] text-slate-400 uppercase font-bold tracking-widest leading-tight">Address</p>
                    <p class="text-[7px] text-slate-600 line-clamp-1 italic leading-tight">{{ $user->address }}</p>
                </div>
            </div>
            @endif

            <!-- Bottom Bar with Barcode -->
            <div class="absolute bottom-0 left-0 w-full bg-slate-50 border-t border-gray-100 flex items-center justify-between px-4 py-1">
                <div class="flex flex-col items-start">
                    <p class="text-[5.5px] text-slate-400 font-bold uppercase tracking-widest leading-none mb-0.5">Referral Code</p>
                    <p class="text-[8px] font-black text-brand-600 font-mono tracking-widest leading-none">{{ $user->referralRecord->referral_code ?? 'U'.$user->id }}</p>
                </div>
                <div class="bg-white px-1 py-0.5 rounded border border-gray-200 shadow-sm flex flex-col items-center">
                    <svg id="barcode" class="h-4 w-auto"></svg>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 text-center no-print">
        <div class="inline-block p-4 rounded-xl bg-blue-50 border border-blue-100 dark:bg-white/[0.03] dark:border-gray-800">
            <p class="text-sm text-blue-800 dark:text-blue-300 font-medium">This card can be scanned by the admin for quick identification at the shop.</p>
            <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">Recommended print size: 85.6mm x 54mm (Standard CR80)</p>
        </div>
    </div>
</div>

<style>
#barcode {
    max-width: 80px;
}

@media print {
    /* Hide everything first */
    body * {
        visibility: hidden;
    }
    /* Show only the ID card container */
    #id-card, #id-card * {
        visibility: visible;
    }
    /* Precise positioning for printer */
    #id-card {
        position: fixed;
        left: 0;
        top: 0;
        margin: 0;
        box-shadow: none !important;
        border: 1px solid #e5e7eb;
        /* Standard ID Card Size */
        width: 85.6mm !important;
        height: 54mm !important;
        border-radius: 3.18mm;
        transform: scale(1.0);
        overflow: hidden;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    /* Ensure colors print correctly */
    .bg-slate-900 { background-color: #0f172a !important; -webkit-print-color-adjust: exact; }
    .bg-brand-500 { background-color: #465fff !important; -webkit-print-color-adjust: exact; }
    .bg-slate-50 { background-color: #f8fafc !important; -webkit-print-color-adjust: exact; }
    .text-white { color: #ffffff !important; -webkit-print-color-adjust: exact; }
    .text-brand-300 { color: #9cb9ff !important; -webkit-print-color-adjust: exact; }
    .text-brand-600 { color: #3641f5 !important; -webkit-print-color-adjust: exact; }
    
    .no-print {
        display: none !important;
    }
    
    @page {
        size: auto;
        margin: 0.5in;
    }
}
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        JsBarcode("#barcode", "{{ $user->referralRecord->referral_code ?? 'U'.$user->id }}", {
            format: "CODE128",
            width: 2,
            height: 40,
            displayValue: false,
            margin: 0,
            background: "transparent"
        });
    });
</script>
@endpush
@endsection
