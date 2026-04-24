<aside :class="sidebarToggle ? 'translate-x-0' : '-translate-x-full'"
	class="fixed left-0 top-0 z-50 flex h-screen w-72 flex-col overflow-y-hidden bg-white border-r dark:bg-gray-900 dark:border-gray-800 duration-300 ease-linear lg:static lg:translate-x-0 shadow-sm">
	<!-- SIDEBAR HEADER -->
	<div class="flex items-center justify-between gap-2 px-6 py-5.5 lg:py-6.5 border-b dark:border-gray-800">
		<a href="{{ auth()->user()->isAdmin() ? '/admin' : '/dashboard' }}" class="flex items-center gap-2">
			<img src="{{ asset('images/logo/logo-icon.svg') }}" alt="Logo" class="h-8">
			<span class="font-bold text-xl text-black dark:text-white tracking-tight">{{ config('app.name', 'MLM App') }}</span>
		</a>

		<button @click.prevent="sidebarToggle = !sidebarToggle" class="lg:hidden text-gray-500 hover:text-black dark:hover:text-white p-1">
			<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
				xmlns="http://www.w3.org/2000/svg">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
			</svg>
		</button>
	</div>
	<!-- SIDEBAR HEADER -->

	<div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
		<!-- Sidebar Menu -->
		<nav class="mt-2 py-4 px-4 lg:px-6">
			@php
				if (!function_exists('render_admin_menu_item')) {
					function render_admin_menu_item($href, $label, $icon, $active_routes = [], $badge = null) {
						$active = false;
						foreach((array)$active_routes as $pattern) {
							if(request()->is($pattern) || request()->routeIs($pattern)) $active = true;
						}
						$active_class = $active 
							? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 font-semibold' 
							: 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 hover:text-black dark:hover:text-white';
						
						echo '<li>
							<a href="'.$href.'"
								class="group relative flex items-center gap-3 rounded-lg py-2.5 px-4 transition-all duration-200 '.$active_class.'">
								<span class="w-5 h-5 flex items-center justify-center">'.$icon.'</span>
								<span class="text-sm">'.$label.'</span>';
						if($badge) echo $badge;
						echo '</a></li>';
					}
				}
			@endphp

			{{-- ADMIN SECTION --}}
			@if(auth()->user()->isAdmin())
				<div class="mb-4">
					<h3 class="mb-4 ml-4 text-[10px] font-bold text-brand-500 uppercase tracking-widest">Administration</h3>
					<ul class="flex flex-col gap-1">
						@php render_admin_menu_item(route('admin.dashboard'), 'Admin Dashboard', '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>', ['admin']); @endphp
						@php
							$pendingCount = \App\Models\PaymentRequest::where('status','pending')->count();
							$badge = $pendingCount > 0 ? '<span class="ml-auto inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-danger text-white">'.$pendingCount.'</span>' : null;
							render_admin_menu_item(route('admin.payments'), 'Recharge Requests', '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>', ['admin/payments*'], $badge);
							render_admin_menu_item(route('admin.products'), 'Products', '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>', ['admin/products*']);
							render_admin_menu_item(route('admin.packages'), 'Packages', '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>', ['admin/packages*']);
							render_admin_menu_item(route('admin.shop.index'), 'Order for User', '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>', ['admin/shop*']);
							render_admin_menu_item(route('admin.orders'), 'Order Management', '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>', ['admin/orders*']);
							render_admin_menu_item(route('admin.emis.index'), 'EMI Management', '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>', ['admin/emis*']);
							render_admin_menu_item(route('admin.users'), 'User Management', '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21a6 6 0 00-9-5.197M15 21a6 6 0 006-6v-1a6 6 0 00-6-6h-1.333a6 6 0 00-11.334 0v1a6 6 0 006 6h1.333z"/></svg>', ['admin/users*']);
							render_admin_menu_item(route('admin.genealogy.genealogy'), 'Platform Map', '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17l-5-5m0 0l5-5m-5 5h12m-2-10l5 5m0 0l-5 5m5-5H7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m0-16c-3.314 0-6 2.686-6 6s2.686 6 6 6 6-2.686 6-6-2.686-6-6-6z"/></svg>', ['admin/genealogy*']);
						@endphp

						{{-- Financials Dropdown --}}
						<li x-data="{ open: {{ (request()->is('admin/commissions*') || request()->is('admin/wallet*') || request()->is('admin/credit*') || request()->is('admin/penalties*')) ? 'true' : 'false' }} }">
							<a href="#" @click.prevent="open = !open" 
								class="group relative flex items-center gap-3 rounded-lg py-2.5 px-4 transition-all duration-200 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 hover:text-black dark:hover:text-white"
								:class="{ 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 font-semibold': open }">
								<span class="w-5 h-5 flex items-center justify-center">
									<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3-1.343-3-3S10.343 2 12 2s3 1.343 3 3-1.343 3-3 3zM6 20c0-3.866 3.582-7 8-7s8 3.134 8 7"/></svg>
								</span>
								<span class="text-sm">Financials</span>
								<svg class="ml-auto w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
								</svg>
							</a>
							<ul x-show="open" x-transition class="mt-1 ml-4 flex flex-col gap-1 border-l-2 border-gray-100 dark:border-gray-800 pl-4">
								@php
									render_admin_menu_item(route('admin.commissions'), 'Commissions', '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 8c-1.657 0-3-1.343-3-3S10.343 2 12 2s3 1.343 3 3-1.343 3-3 3z"/></svg>', ['admin/commissions']);
									render_admin_menu_item(route('admin.commissions.bv'), 'BV Earnings', '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M9 12l2 2 4-4"/></svg>', ['admin/commissions/bv*']);
									render_admin_menu_item(route('admin.wallet.history'), 'Wallet History', '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M3 10h18"/></svg>', ['admin/wallet/history*']);
									render_admin_menu_item(route('admin.wallet.transfer'), 'Balance Transfer', '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M8 7h12m0 0l-4-4m4 4l-4 4"/></svg>', ['admin/wallet/transfer*']);
									render_admin_menu_item(route('admin.credit.history'), 'Credit History', '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 8c-1.657 0-3-1.343-3 3"/></svg>', ['admin/credit/history*']);
									render_admin_menu_item(route('admin.penalties.history'), 'Penalty History', '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 9v2"/></svg>', ['admin/penalties/history*']);
									render_admin_menu_item(route('admin.reports.export'), 'Reports Export', '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>', ['admin/reports/export*']);
								@endphp
							</ul>
						</li>

						@php 
							render_admin_menu_item(route('admin.settings'), 'Settings', '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>', ['admin/settings*']);
							render_admin_menu_item(route('admin.help'), 'Help & Guide', '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', ['admin/help*']);
						@endphp
					</ul>
				</div>
				<div class="my-6 border-t border-gray-100 dark:border-gray-800"></div>
			@endif

			{{-- USER SECTION (Always visible) --}}
			<div class="bg-dark bg-opacity-20">
				<h3 class="mb-4 ml-0 text-[10px] font-bold bg-warning bg-opacity-10 rounded-xl p-3 text-info uppercase tracking-widest">{{ auth()->user()->isAdmin() ? 'My Personal Account (User)' : 'User Menu' }}</h3>
				<ul class="mb-6 flex flex-col gap-1">
					@include('admin.partials.user_links')
				</ul>
			</div>
		</nav>
		<!-- Sidebar Menu -->
	</div>
</aside>
