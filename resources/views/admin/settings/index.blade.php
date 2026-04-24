@extends('admin.layout')

@section('content')
<div class="mx-auto max-w-270">
    <!-- Breadcrumb -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            System Settings
        </h2>
    </div>

    @if(session('success'))
        <div class="mb-6 flex w-full border-l-6 border-[#34D399] bg-[#34D399] bg-opacity-[15%] px-7 py-4 shadow-md dark:bg-[#1b1b1b] md:p-5">
            <div class="mr-5 flex h-9 w-full max-w-[36px] items-center justify-center rounded-lg bg-[#34D399]">
                <svg width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15.2984 0.826822L15.2868 0.811827L15.2741 0.797751C14.9173 0.401837 14.3238 0.400754 13.9657 0.794406L5.91888 9.53233L2.02771 5.41173C1.6655 5.02795 1.07402 5.02206 0.704179 5.39847C0.334339 5.77488 0.328449 6.37722 0.69066 6.761L5.24211 11.5956L5.24355 11.5972C5.60196 11.9751 6.18434 11.9744 6.54194 11.5953L15.3035 1.95661C15.6669 1.56494 15.6644 0.957512 15.2984 0.826822Z" fill="white" stroke="white"></path>
                </svg>
            </div>
            <div class="w-full">
                <h5 class="text-lg font-semibold text-black dark:text-[#34D399]">
                    Success
                </h5>
                <p class="text-base leading-relaxed text-body">
                    {{ session('success') }}
                </p>
            </div>
        </div>
    @endif

    <div x-data="{ activeTab: 'general' }">
        <!-- Tabs -->
        <div class="mb-6 flex flex-wrap gap-5 border-b border-stroke dark:border-strokedark sm:gap-10">
            <button @click="activeTab = 'general'" :class="activeTab === 'general' ? 'border-primary text-primary' : 'border-transparent text-black dark:text-white'" class="border-b-2 py-4 text-sm font-medium hover:text-primary md:text-base">
                General
            </button>
            <button @click="activeTab = 'branding'" :class="activeTab === 'branding' ? 'border-primary text-primary' : 'border-transparent text-black dark:text-white'" class="border-b-2 py-4 text-sm font-medium hover:text-primary md:text-base">
                Branding
            </button>
            <button @click="activeTab = 'financial'" :class="activeTab === 'financial' ? 'border-primary text-primary' : 'border-transparent text-black dark:text-white'" class="border-b-2 py-4 text-sm font-medium hover:text-primary md:text-base">
                Financial
            </button>
            <button @click="activeTab = 'commission'" :class="activeTab === 'commission' ? 'border-primary text-primary' : 'border-transparent text-black dark:text-white'" class="border-b-2 py-4 text-sm font-medium hover:text-primary md:text-base">
                Commission
            </button>
            <button @click="activeTab = 'advanced'" :class="activeTab === 'advanced' ? 'border-primary text-primary' : 'border-transparent text-black dark:text-white'" class="border-b-2 py-4 text-sm font-medium hover:text-primary md:text-base">
                Advanced
            </button>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <!-- General Settings Tab -->
            <div x-show="activeTab === 'general'" class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
                <div class="border-b border-stroke py-4 px-7 dark:border-strokedark">
                    <h3 class="font-medium text-black dark:text-white">General Information</h3>
                </div>
                <div class="p-7">
                    <div class="mb-5.5 flex flex-col gap-5.5 sm:flex-row">
                        <div class="w-full sm:w-1/2">
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Site Name</label>
                            <input type="text" name="site_name" value="{{ $settings['site_name'] ?? '' }}" placeholder="MLM App" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        </div>
                        <div class="w-full sm:w-1/2">
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Contact Email</label>
                            <input type="email" name="contact_email" value="{{ $settings['contact_email'] ?? '' }}" placeholder="admin@example.com" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        </div>
                    </div>

                    <div class="mb-5.5">
                        <label class="mb-3 block text-sm font-medium text-black dark:text-white">Support Status</label>
                        <div class="flex items-center gap-3">
                            <label for="reg_on" class="flex cursor-pointer select-none items-center">
                                <div class="relative">
                                    <input type="radio" id="reg_on" name="registration_enabled" value="on" class="sr-only" {{ ($settings['registration_enabled'] ?? 'on') === 'on' ? 'checked' : '' }}>
                                    <div class="mr-4 flex h-5 w-5 items-center justify-center rounded-full border border-primary {{ ($settings['registration_enabled'] ?? 'on') === 'on' ? 'bg-primary' : '' }}">
                                        <span class="h-2.5 w-2.5 rounded-full bg-white {{ ($settings['registration_enabled'] ?? 'on') === 'on' ? '' : 'hidden' }}"></span>
                                    </div>
                                </div>
                                Registrations Open
                            </label>
                            <label for="reg_off" class="flex cursor-pointer select-none items-center">
                                <div class="relative">
                                    <input type="radio" id="reg_off" name="registration_enabled" value="off" class="sr-only" {{ ($settings['registration_enabled'] ?? 'on') === 'off' ? 'checked' : '' }}>
                                    <div class="mr-4 flex h-5 w-5 items-center justify-center rounded-full border border-primary {{ ($settings['registration_enabled'] ?? 'on') === 'off' ? 'bg-primary' : '' }}">
                                        <span class="h-2.5 w-2.5 rounded-full bg-white {{ ($settings['registration_enabled'] ?? 'on') === 'off' ? '' : 'hidden' }}"></span>
                                    </div>
                                </div>
                                Closed
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Branding Tab -->
            <div x-show="activeTab === 'branding'" class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
                <div class="border-b border-stroke py-4 px-7 dark:border-strokedark">
                    <h3 class="font-medium text-black dark:text-white">Logo & Branding</h3>
                </div>
                <div class="p-7">
                    <div class="grid grid-cols-1 gap-5.5 sm:grid-cols-2">
                        <!-- Main Logo -->
                        <div class="rounded-sm border border-stroke p-4 dark:border-strokedark">
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Main Logo (logo.svg)</label>
                            <div class="mb-4 flex items-center justify-center bg-gray-2 p-4 dark:bg-meta-4">
                                <img src="{{ asset('images/logo/logo.svg') }}?v={{ time() }}" class="h-10" alt="Logo">
                            </div>
                            <input type="file" name="logo" class="w-full cursor-pointer rounded-lg border-[1.5px] border-stroke bg-transparent font-medium outline-none transition file:mr-5 file:border-collapse file:cursor-pointer file:border-0 file:border-r file:border-solid file:border-stroke file:bg-whiter file:py-3 file:px-5 file:hover:bg-primary file:hover:bg-opacity-10 focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:file:border-form-strokedark dark:file:bg-white/5 dark:file:text-white dark:focus:border-primary">
                        </div>

                        <!-- Logo Dark -->
                        <div class="rounded-sm border border-stroke p-4 dark:border-strokedark">
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Logo Dark (logo-dark.svg)</label>
                            <div class="mb-4 flex items-center justify-center bg-black p-4">
                                <img src="{{ asset('images/logo/logo-dark.svg') }}?v={{ time() }}" class="h-10" alt="Logo Dark">
                            </div>
                            <input type="file" name="logo_dark" class="w-full cursor-pointer rounded-lg border-[1.5px] border-stroke bg-transparent font-medium outline-none transition file:mr-5 file:border-collapse file:cursor-pointer file:border-0 file:border-r file:border-solid file:border-stroke file:bg-whiter file:py-3 file:px-5 file:hover:bg-primary file:hover:bg-opacity-10 focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:file:border-form-strokedark dark:file:bg-white/5 dark:file:text-white dark:focus:border-primary">
                        </div>

                        <!-- Logo Icon -->
                        <div class="rounded-sm border border-stroke p-4 dark:border-strokedark">
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Logo Icon (logo-icon.svg)</label>
                            <div class="mb-4 flex items-center justify-center bg-gray-2 p-4 dark:bg-meta-4">
                                <img src="{{ asset('images/logo/logo-icon.svg') }}?v={{ time() }}" class="h-10" alt="Logo Icon">
                            </div>
                            <input type="file" name="logo_icon" class="w-full cursor-pointer rounded-lg border-[1.5px] border-stroke bg-transparent font-medium outline-none transition file:mr-5 file:border-collapse file:cursor-pointer file:border-0 file:border-r file:border-solid file:border-stroke file:bg-whiter file:py-3 file:px-5 file:hover:bg-primary file:hover:bg-opacity-10 focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:file:border-form-strokedark dark:file:bg-white/5 dark:file:text-white dark:focus:border-primary">
                        </div>

                        <!-- Auth Logo -->
                        <div class="rounded-sm border border-stroke p-4 dark:border-strokedark">
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Auth Logo (auth-logo.svg)</label>
                            <div class="mb-4 flex items-center justify-center bg-brand-950 p-4">
                                <img src="{{ asset('images/logo/auth-logo.svg') }}?v={{ time() }}" class="h-10" alt="Auth Logo">
                            </div>
                            <input type="file" name="auth_logo" class="w-full cursor-pointer rounded-lg border-[1.5px] border-stroke bg-transparent font-medium outline-none transition file:mr-5 file:border-collapse file:cursor-pointer file:border-0 file:border-r file:border-solid file:border-stroke file:bg-whiter file:py-3 file:px-5 file:hover:bg-primary file:hover:bg-opacity-10 focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:file:border-form-strokedark dark:file:bg-white/5 dark:file:text-white dark:focus:border-primary">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Tab -->
            <div x-show="activeTab === 'financial'" class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
                <div class="border-b border-stroke py-4 px-7 dark:border-strokedark">
                    <h3 class="font-medium text-black dark:text-white">Financial Configuration</h3>
                </div>
                <div class="p-7">
                    <div class="mb-5.5 flex flex-col gap-5.5 sm:flex-row">
                        <div class="w-full sm:w-1/2">
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Currency Symbol</label>
                            <input type="text" name="currency" value="{{ $settings['currency'] ?? 'INR' }}" placeholder="INR / $ / $" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        </div>
                        <div class="w-full sm:w-1/2">
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Min. Withdrawal</label>
                            <input type="number" name="min_withdrawal" value="{{ $settings['min_withdrawal'] ?? 500 }}" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        </div>
                    </div>

                    <div class="border-b border-stroke py-4 dark:border-strokedark mb-5.5">
                        <h3 class="font-medium text-black dark:text-white">Payment QR Settings</h3>
                    </div>

                    <div class="mb-5.5">
                        <label class="mb-3 block text-sm font-medium text-black dark:text-white">Use Custom QR for Payments</label>
                        <select name="use_custom_qr" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                            <option value="off" {{ ($settings['use_custom_qr'] ?? 'off') === 'off' ? 'selected' : '' }}>Disabled (Use env APP_QR_CODE)</option>
                            <option value="on" {{ ($settings['use_custom_qr'] ?? 'off') === 'on' ? 'selected' : '' }}>Enabled (Use Uploaded QR)</option>
                        </select>
                    </div>

                    <div class="mb-5.5 flex flex-col gap-5.5 sm:flex-row">
                        <div class="w-full sm:w-1/2">
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Custom UPI ID (Text to display)</label>
                            <input type="text" name="custom_upi_id" value="{{ $settings['custom_upi_id'] ?? '' }}" placeholder="upi-id@bank" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        </div>
                        <div class="w-full sm:w-1/2">
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Upload Custom QR Image</label>
                            <input type="file" name="payment_qr_code" class="w-full cursor-pointer rounded-lg border-[1.5px] border-stroke bg-transparent font-medium outline-none transition file:mr-5 file:border-collapse file:cursor-pointer file:border-0 file:border-r file:border-solid file:border-stroke file:bg-whiter file:py-3 file:px-5 file:hover:bg-primary file:hover:bg-opacity-10 focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:file:border-form-strokedark dark:file:bg-white/5 dark:file:text-white dark:focus:border-primary">
                            @if(!empty($settings['payment_qr_path']))
                                <div class="mt-4">
                                    <p class="mb-2 text-sm">Current QR Image:</p>
                                    <img src="{{ asset($settings['payment_qr_path']) }}" class="h-32 rounded border dark:border-strokedark" alt="Custom QR">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Commission Tab -->
            <div x-show="activeTab === 'commission'" class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
                <div class="border-b border-stroke py-4 px-7 dark:border-strokedark">
                    <h3 class="font-medium text-black dark:text-white">Joining Commission Rates (Flat Amount)</h3>
                </div>
                <div class="p-7">
                    <div class="mb-5.5 grid grid-cols-1 gap-5.5 sm:grid-cols-5">
                        <div>
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Level 1</label>
                            <input type="number" name="joining_commission_level_1" value="{{ $settings['joining_commission_level_1'] ?? 100 }}" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        </div>
                        <div>
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Level 2</label>
                            <input type="number" name="joining_commission_level_2" value="{{ $settings['joining_commission_level_2'] ?? 50 }}" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        </div>
                        <div>
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Level 3</label>
                            <input type="number" name="joining_commission_level_3" value="{{ $settings['joining_commission_level_3'] ?? 30 }}" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        </div>
                        <div>
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Level 4</label>
                            <input type="number" name="joining_commission_level_4" value="{{ $settings['joining_commission_level_4'] ?? 20 }}" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        </div>
                        <div>
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Level 5</label>
                            <input type="number" name="joining_commission_level_5" value="{{ $settings['joining_commission_level_5'] ?? 10 }}" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        </div>
                    </div>

                    <div class="border-b border-stroke py-4 dark:border-strokedark">
                        <h3 class="font-medium text-black dark:text-white">Repurchase Commission Rates (%)</h3>
                    </div>
                    <div class="mt-5.5 grid grid-cols-1 gap-5.5 sm:grid-cols-5">
                        <div>
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Level 1 (%)</label>
                            <input type="number" step="0.01" name="repurchase_commission_level_1" value="{{ $settings['repurchase_commission_level_1'] ?? 20 }}" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        </div>
                        <div>
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Level 2 (%)</label>
                            <input type="number" step="0.01" name="repurchase_commission_level_2" value="{{ $settings['repurchase_commission_level_2'] ?? 10 }}" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        </div>
                        <div>
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Level 3 (%)</label>
                            <input type="number" step="0.01" name="repurchase_commission_level_3" value="{{ $settings['repurchase_commission_level_3'] ?? 5 }}" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        </div>
                        <div>
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Level 4 (%)</label>
                            <input type="number" step="0.01" name="repurchase_commission_level_4" value="{{ $settings['repurchase_commission_level_4'] ?? 3 }}" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        </div>
                        <div>
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Level 5 (%)</label>
                            <input type="number" step="0.01" name="repurchase_commission_level_5" value="{{ $settings['repurchase_commission_level_5'] ?? 2 }}" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        </div>
                    </div>

                    <div class="border-b border-stroke py-4 dark:border-strokedark">
                        <h3 class="font-medium text-black dark:text-white">Order Commission Rates (Per BV Point)</h3>
                    </div>

                    <div class="mt-5.5 grid grid-cols-1 gap-5.5 sm:grid-cols-5">
                        <div>
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Level 1 (per BV)</label>
                            <input type="number" step="0.01" name="order_commission_level_1" value="{{ $settings['order_commission_level_1'] ?? 2.0 }}" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        </div>
                        <div>
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Level 2 (per BV)</label>
                            <input type="number" step="0.01" name="order_commission_level_2" value="{{ $settings['order_commission_level_2'] ?? 1.0 }}" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        </div>
                        <div>
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Level 3 (per BV)</label>
                            <input type="number" step="0.01" name="order_commission_level_3" value="{{ $settings['order_commission_level_3'] ?? 0.3 }}" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        </div>
                        <div>
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Level 4 (per BV)</label>
                            <input type="number" step="0.01" name="order_commission_level_4" value="{{ $settings['order_commission_level_4'] ?? 0.2 }}" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        </div>
                        <div>
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Level 5 (per BV)</label>
                            <input type="number" step="0.01" name="order_commission_level_5" value="{{ $settings['order_commission_level_5'] ?? 0.1 }}" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advanced Tab -->
            <div x-show="activeTab === 'advanced'" class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
                <div class="border-b border-stroke py-4 px-7 dark:border-strokedark">
                    <h3 class="font-medium text-black dark:text-white">Advanced Settings</h3>
                </div>
                <div class="p-7">
                    <div class="mb-5.5">
                        <label class="mb-3 block text-sm font-medium text-black dark:text-white">Maintenance Mode</label>
                        <select name="maintenance_mode" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                            <option value="off" {{ ($settings['maintenance_mode'] ?? 'off') === 'off' ? 'selected' : '' }}>Disabled (Site Live)</option>
                            <option value="on" {{ ($settings['maintenance_mode'] ?? 'off') === 'on' ? 'selected' : '' }}>Enabled (Under Maintenance)</option>
                        </select>
                        <p class="mt-2 text-xs text-meta-1">Warning: Site will be inaccessible to non-admin users if enabled.</p>
                    </div>

                    <div class="mb-5.5">
                        <label class="mb-3 block text-sm font-medium text-black dark:text-white">Enable BV Commissions Page for Users</label>
                        <select name="enable_bv_commission" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                            <option value="on" {{ ($settings['enable_bv_commission'] ?? 'on') === 'on' ? 'selected' : '' }}>Enabled (Show page to users)</option>
                            <option value="off" {{ ($settings['enable_bv_commission'] ?? 'on') === 'off' ? 'selected' : '' }}>Disabled (Hide page from users)</option>
                        </select>
                    </div>

                    <div class="border-b border-stroke py-4 dark:border-strokedark">
                        <h3 class="font-medium text-black dark:text-white">EMI & Credit Settings</h3>
                    </div>

                    <div class="mt-5.5 grid grid-cols-1 gap-5.5 sm:grid-cols-3">
                        <div>
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Default EMI Amount ({{ $settings['currency'] ?? 'INR' }})</label>
                            <input type="number" name="default_emi_amount" value="{{ $settings['default_emi_amount'] ?? 500 }}" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        </div>
                        <div>
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">EMI Frequency (Days)</label>
                            <input type="number" name="emi_frequency" value="{{ $settings['emi_frequency'] ?? 7 }}" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                            <p class="mt-1 text-xs">7 = Weekly, 30 = Monthly</p>
                        </div>
                        <div>
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">Late Penalty Amount ({{ $settings['currency'] ?? 'INR' }})</label>
                            <input type="number" name="late_penalty_amount" value="{{ $settings['late_penalty_amount'] ?? 80 }}" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        </div>
                    </div>

                    <div class="border-b border-stroke py-4 dark:border-strokedark">
                        <h3 class="font-medium text-black dark:text-white">Push Notifications (FCM)</h3>
                    </div>

                    <div class="mt-5.5">
                        <label class="mb-3 block text-sm font-medium text-black dark:text-white">Enable Push Notifications</label>
                        <select name="enable_push_notifications" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                            <option value="on" {{ ($settings['enable_push_notifications'] ?? 'off') === 'on' ? 'selected' : '' }}>Enabled</option>
                            <option value="off" {{ ($settings['enable_push_notifications'] ?? 'off') === 'off' ? 'selected' : '' }}>Disabled</option>
                        </select>
                    </div>

                    <div class="mt-5.5 grid grid-cols-1 gap-5.5 sm:grid-cols-2">
                        <div>
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">FCM Project ID</label>
                            <input type="text" name="fcm_project_id" value="{{ $settings['fcm_project_id'] ?? '' }}" placeholder="my-project-123" class="w-full rounded border border-stroke bg-gray py-3 px-4.5 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary">
                        </div>
                        <div>
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">FCM Service Account JSON</label>
                            <input type="file" name="fcm_service_account" class="w-full cursor-pointer rounded-lg border-[1.5px] border-stroke bg-transparent font-medium outline-none transition file:mr-5 file:border-collapse file:cursor-pointer file:border-0 file:border-r file:border-solid file:border-stroke file:bg-whiter file:py-3 file:px-5 file:hover:bg-primary file:hover:bg-opacity-10 focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:file:border-form-strokedark dark:file:bg-white/5 dark:file:text-white dark:focus:border-primary">
                            @if(\Illuminate\Support\Facades\Storage::disk('local')->exists('certs/fcm-service-account.json'))
                                <p class="mt-1 text-xs text-green-500">Service account file exists.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-4.5">
                <button type="reset" class="flex justify-center rounded border border-stroke py-2 px-6 font-medium text-black hover:shadow-1 dark:border-strokedark dark:text-white">
                    Cancel
                </button>
                <button type="submit" class="flex justify-center rounded bg-primary py-2 px-6 font-medium text-gray hover:bg-opacity-90">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
