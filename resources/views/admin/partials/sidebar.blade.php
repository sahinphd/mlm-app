<aside :class="sidebarToggle ? 'translate-x-0' : '-translate-x-full'"
	class="fixed left-0 top-0 z-50 flex h-screen w-72 flex-col overflow-y-hidden bg-white border-r dark:bg-gray-900 dark:border-gray-800 duration-300 ease-linear lg:static lg:translate-x-0">
	<!-- SIDEBAR HEADER -->
	<div class="flex items-center justify-between gap-2 px-6 py-5.5 lg:py-6.5">
		<a href="/admin" class="flex items-center gap-2">
			<img src="{{ asset('images/logo/logo-icon.svg') }}" alt="Logo" class="h-8">
			<span class="font-semibold text-lg text-black dark:text-white">{{ config('app.name', 'MLM App') }}</span>
		</a>

		<button @click.prevent="sidebarToggle = !sidebarToggle" class="lg:hidden text-black dark:text-white">
			<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
				xmlns="http://www.w3.org/2000/svg">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
			</svg>
		</button>
	</div>
	<!-- SIDEBAR HEADER -->

	<div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
		<!-- Sidebar Menu -->
		<nav class="mt-5 py-4 px-4 lg:mt-9 lg:px-6">
			<!-- Menu Group -->
			<div>
				<h3 class="mb-4 ml-4 text-sm font-semibold text-gray-400 uppercase">ADMIN MENU</h3>

				<ul class="mb-6 flex flex-col gap-1.5">
					@php
						if (!function_exists('render_admin_menu_item')) {
							function render_admin_menu_item($href, $label, $icon, $active_routes = [], $badge = null) {
								$active = false;
								foreach((array)$active_routes as $route) {
									if(request()->is($route)) $active = true;
								}
								$active_class = $active ? 'bg-gray-100 dark:bg-white/5 text-primary dark:text-white' : 'text-gray-600 dark:text-gray-400';
								echo '<li>
									<a href="'.$href.'"
										class="group relative flex items-center gap-2.5 rounded-sm py-2.5 px-4 font-medium duration-300 ease-in-out hover:bg-gray-100 dark:hover:bg-white/5 '.$active_class.'">
										<svg class="w-5 h-5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">'.$icon.'</svg>
										'.$label;
								if($badge) echo $badge;
								echo '</a></li>';
							}
						}
					@endphp

					@php render_admin_menu_item(route('admin.dashboard'), 'Dashboard', '<path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>', ['admin']); @endphp

					@can('admin-access')
						@php
							$pendingCount = \App\Models\PaymentRequest::where('status','pending')->count();
							$badge = $pendingCount > 0 ? '<span class="ml-auto inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-danger text-white">'.$pendingCount.'</span>' : null;
							
							render_admin_menu_item(route('admin.payments'), 'Payments', '<path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>', ['admin/payments*'], $badge);
							
							render_admin_menu_item(route('admin.products'), 'Products', '<path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>', ['admin/products*']);

							render_admin_menu_item(route('admin.packages'), 'Packages', '<path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>', ['admin/packages*']);

							render_admin_menu_item(route('admin.shop.index'), 'Shop (Order for User)', '<path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>', ['admin/shop*']);

							render_admin_menu_item(route('admin.orders'), 'Orders', '<path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>', ['admin/orders*']);

							render_admin_menu_item(route('admin.emis.index'), 'EMI Schedules', '<path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>', ['admin/emis*']);
							
							render_admin_menu_item(route('admin.users'), 'Users', '<path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21a6 6 0 00-9-5.197M15 21a6 6 0 006-6v-1a6 6 0 00-6-6h-1.333a6 6 0 00-11.334 0v1a6 6 0 006 6h1.333z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>', ['admin/users*']);

							render_admin_menu_item(route('admin.genealogy.genealogy'), 'Genealogy', '<path d="M9 17l-5-5m0 0l5-5m-5 5h12m-2-10l5 5m0 0l-5 5m5-5H7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M12 4v16m0-16c-3.314 0-6 2.686-6 6s2.686 6 6 6 6-2.686 6-6-2.686-6-6-6z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>', ['admin/genealogy*']);

							render_admin_menu_item(route('admin.commissions'), 'Commissions', '<path d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zM12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zM12 20c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>', ['admin/commissions']);

							render_admin_menu_item(route('admin.commissions.bv'), 'BV Commissions', '<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>', ['admin/commissions/bv*']);

							render_admin_menu_item(route('admin.wallet.history'), 'Wallet History', '<path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>', ['admin/wallet/history*']);

							render_admin_menu_item(route('admin.credit.history'), 'Credit History', '<path d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zM12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zM12 20c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>', ['admin/credit/history*']);
							
							render_admin_menu_item(route('admin.penalties.history'), 'Penalty History', '<path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>', ['admin/penalties/history*']);

							render_admin_menu_item(route('admin.settings'), 'Settings', '<path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>', ['admin/settings*']);
						@endphp
					@else
						@php 
							render_admin_menu_item(route('payments.index'), 'Payments', '<path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>', ['payments*']);
							render_admin_menu_item(route('commissions.index'), 'Commissions', '<path d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zM12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zM12 20c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>', ['commissions']);
							
							if (($systemSettings['enable_bv_commission'] ?? 'on') === 'on') {
								render_admin_menu_item(route('commissions.bv'), 'BV Commissions', '<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>', ['commissions/bv*']);
							}

							render_admin_menu_item(route('wallet.history'), 'Wallet History', '<path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>', ['wallet/history*']);
							render_admin_menu_item(route('credit.history'), 'Credit History', '<path d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zM12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zM12 20c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>', ['credit/history*']);
							render_admin_menu_item(route('credit.penalties.history'), 'Penalty History', '<path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>', ['credit/penalties/history*']);
						@endphp
					@endcan
				</ul>
			</div>
		</nav>
		<!-- Sidebar Menu -->
	</div>
</aside>
