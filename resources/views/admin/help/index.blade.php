@extends('admin.layout')

@section('content')
<div class="mx-auto max-w-270">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            Admin Management Guide
        </h2>
    </div>

    <div class="grid grid-cols-1 gap-6">
        <!-- Introduction -->
        <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="border-b border-stroke py-4 px-7 dark:border-strokedark">
                <h3 class="font-medium text-black dark:text-white">Overview</h3>
            </div>
            <div class="p-7">
                <p class="text-base text-body dark:text-bodydark">
                    Welcome to the Admin Guide. This document provides clear instructions on how to manage the MLM application, financial systems, and user accounts.
                </p>
            </div>
        </div>

        <!-- Section 1: User Management -->
        <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="border-b border-stroke py-4 px-7 dark:border-strokedark bg-gray-50 dark:bg-meta-4">
                <h3 class="font-bold text-black dark:text-white">1. User & Credit Management</h3>
            </div>
            <div class="p-7 space-y-4">
                <h4 class="font-semibold text-black dark:text-white">Approving Credit Limits</h4>
                <p class="text-sm">Users cannot buy items on credit until you approve them:</p>
                <ol class="list-decimal list-inside text-sm space-y-1 pl-4">
                    <li>Go to <strong>Admin > Users</strong>.</li>
                    <li>Search for the user and click <strong>View/Edit</strong>.</li>
                    <li>Enter a <strong>Credit Limit</strong> (e.g., 5000).</li>
                    <li>Set the status to <strong>Approved</strong> and save.</li>
                </ol>
                <h4 class="font-semibold text-black dark:text-white pt-2">Managing Balances</h4>
                <p class="text-sm">You can view a user's Wallet and Credit history from their profile page to resolve balance disputes.</p>
                
                <h4 class="font-semibold text-black dark:text-white pt-2">Quick Shopping (ID Card Scan)</h4>
                <p class="text-sm">In the <strong>Admin > Shop</strong> checkout page, you can scan a user's Digital ID Card barcode directly into the search box to instantly find and select that user for a quick purchase.</p>
            </div>
        </div>

        <!-- Section 2: Financial Settings -->
        <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="border-b border-stroke py-4 px-7 dark:border-strokedark bg-gray-50 dark:bg-meta-4">
                <h3 class="font-bold text-black dark:text-white">2. Financial & EMI Settings</h3>
            </div>
            <div class="p-7 space-y-4">
                <p class="text-sm">Manage these under <strong>Admin > Settings > Advanced</strong>.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 border border-stroke rounded-md">
                        <span class="font-bold block">Default EMI Amount</span>
                        <span class="text-xs text-gray-500">Every credit purchase is split into installments of this amount. (Default: 500)</span>
                    </div>
                    <div class="p-4 border border-stroke rounded-md">
                        <span class="font-bold block">EMI Frequency</span>
                        <span class="text-xs text-gray-500">Number of days between payments (7 = Weekly, 30 = Monthly).</span>
                    </div>
                </div>
                <h4 class="font-semibold text-black dark:text-white pt-2">Late Fees</h4>
                <p class="text-sm">If a user misses their EMI due date, the system automatically applies the <strong>Late Penalty Amount</strong> (e.g., 80) at midnight.</p>
            </div>
        </div>

        <!-- Section 3: Order Management -->
        <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="border-b border-stroke py-4 px-7 dark:border-strokedark bg-gray-50 dark:bg-meta-4">
                <h3 class="font-bold text-black dark:text-white">3. Orders & Commission Reversal</h3>
            </div>
            <div class="p-7 space-y-4">
                <h4 class="font-semibold text-black dark:text-white">Status Tracking</h4>
                <p class="text-sm">Update orders from <strong>Pending</strong> to <strong>Processing</strong>, then <strong>Shipped</strong> and <strong>Delivered</strong>.</p>
                <div class="p-4 bg-red-50 dark:bg-red-900/10 border-l-4 border-red-500">
                    <h4 class="font-bold text-red-600 dark:text-red-400">Critical: Commission Reversal</h4>
                    <p class="text-sm">If an order is marked as <strong>Cancelled</strong> or <strong>Returned</strong>, the system will automatically deduct the commissions previously paid to all uplines.</p>
                </div>
            </div>
        </div>

        <!-- Section 4: Communication -->
        <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="border-b border-stroke py-4 px-7 dark:border-strokedark bg-gray-50 dark:bg-meta-4">
                <h3 class="font-bold text-black dark:text-white">4. Push Notifications</h3>
            </div>
            <div class="p-7 space-y-4">
                <p class="text-sm">The system uses Firebase to send real-time alerts to users' phones.</p>
                <ul class="list-disc list-inside text-sm space-y-1 pl-4">
                    <li><strong>EMI Reminders:</strong> Sent automatically 2 days before due date.</li>
                    <li><strong>Order Updates:</strong> Sent when you change an order status.</li>
                    <li><strong>Payment Status:</strong> Sent when you approve or reject a top-up request.</li>
                </ul>
                <p class="text-xs italic">Note: Ensure your FCM Service Account JSON is uploaded in Advanced Settings for this to work.</p>
            </div>
        </div>

        <!-- Section 5: Automated Tasks (Cron Jobs) -->
        <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="border-b border-stroke py-4 px-7 dark:border-strokedark bg-gray-50 dark:bg-meta-4">
                <h3 class="font-bold text-black dark:text-white">5. Automated Maintenance (Cron Job)</h3>
            </div>
            <div class="p-7 space-y-4">
                <p class="text-sm">The application handles several critical tasks automatically using the <strong>Laravel Task Scheduler</strong>. For these tasks to run, a "Cron Job" must be configured on your server.</p>
                
                <h4 class="font-semibold text-black dark:text-white pt-2">Scheduled Tasks:</h4>
                <ul class="list-disc list-inside text-sm space-y-1 pl-4">
                    <li><strong>Apply EMI Penalties:</strong> Runs daily at midnight to find overdue EMIs and add late fees.</li>
                    <li><strong>Send EMI Reminders:</strong> Runs daily to send notifications for EMIs due in 2 days.</li>
                </ul>

                <div class="p-4 bg-yellow-50 dark:bg-yellow-900/10 border-l-4 border-yellow-500 mt-4">
                    <h4 class="font-bold text-yellow-700 dark:text-yellow-400">Server Setup Required</h4>
                    <p class="text-sm text-yellow-800 dark:text-yellow-300">If you are on a live server (CPanel, VPS, etc.), you <strong>MUST</strong> add the following entry to your server's Cron Job settings:</p>
                    <code class="block mt-2 bg-black text-green-400 p-3 rounded text-xs overflow-x-auto">
                        * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
                    </code>
                    <p class="text-xs mt-2 italic">* Replace "/path-to-your-project" with the actual path to your application on the server.</p>
                </div>

                <h4 class="font-semibold text-black dark:text-white pt-2">How to Setup in CPanel:</h4>
                <ol class="list-decimal list-inside text-sm space-y-1 pl-4">
                    <li>Log in to <strong>CPanel</strong> and search for <strong>"Cron Jobs"</strong>.</li>
                    <li>Under "Common Settings", select <strong>"Once Per Minute (* * * * *)"</strong>.</li>
                    <li>In the "Command" box, paste the line shown above (with your correct path).</li>
                    <li>Click <strong>"Add New Cron Job"</strong>.</li>
                </ol>
            </div>
        </div>

        <!-- Section 6: Troubleshooting -->
        <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="border-b border-stroke py-4 px-7 dark:border-strokedark bg-gray-50 dark:bg-meta-4">
                <h3 class="font-bold text-black dark:text-white">6. Troubleshooting</h3>
            </div>
            <div class="p-7 space-y-4">
                <h4 class="font-semibold text-black dark:text-white">Manual Recovery</h4>
                <p class="text-sm">If a user reports a balance error, check their <strong>Wallet History</strong> vs <strong>Order History</strong>. You can manually adjust balances if necessary by creating a manual order or through the database.</p>
                
                <h4 class="font-semibold text-black dark:text-white pt-2">System Logs</h4>
                <p class="text-sm">For errors related to payments or emails, check the server logs located at: <br><code class="text-xs bg-gray-100 dark:bg-gray-800 p-1 rounded">storage/logs/laravel.log</code></p>
            </div>
        </div>
    </div>
</div>
@endsection
