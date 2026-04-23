<header class="sticky top-0 z-40 w-full border-b bg-white dark:bg-gray-900 shadow-sm">
	<div class="flex items-center justify-between px-4 py-3 lg:px-6">
		<div class="flex items-center gap-3">
			<button @click.stop="sidebarToggle = !sidebarToggle" class="p-2 rounded-lg border border-gray-200 dark:border-gray-800 lg:hidden text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5">
				<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
				</svg>
			</button>
			<a href="{{ auth()->user()->isAdmin() ? '/admin' : '/dashboard' }}" class="flex items-center gap-2">
				<img src="{{ asset('images/logo/logo.svg') }}" alt="Logo" class="h-8">
			</a>
		</div>

		<div class="flex items-center gap-3">
			<div class="hidden lg:block relative">
				<span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
					<svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
				</span>
				<input type="text" placeholder="Search..." class="rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-white/5 pl-10 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 w-64 transition-all" />
			</div>

			<!-- Dark Mode Toggler -->
			<button
				class="relative flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 text-gray-500 transition-colors hover:bg-gray-50 dark:hover:bg-white/5"
				@click.prevent="darkMode = !darkMode"
			>
				<svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/></svg>
				<svg x-show="!darkMode" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
			</button>

			@auth
			<!-- Notifications -->
			<div class="relative" @click.outside="menuToggle = false">
				<button @click.stop="menuToggle = !menuToggle" class="p-2 rounded-full border border-gray-200 dark:border-gray-800 flex items-center justify-center hover:bg-gray-50 dark:hover:bg-white/5 transition-colors relative">
					<svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
					@if(auth()->user()->unreadNotifications->count() > 0)
					<span class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold leading-none text-white bg-red-600 rounded-full transform translate-x-1/3 -translate-y-1/3">{{ auth()->user()->unreadNotifications->count() }}</span>
					@endif
				</button>
				<div x-show="menuToggle" x-cloak class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl z-50 overflow-hidden">
					<div class="p-4 border-b dark:border-gray-700 bg-gray-50 dark:bg-white/5">
						<p class="font-bold text-sm text-gray-800 dark:text-white">Notifications</p>
					</div>
					<div class="max-h-72 overflow-y-auto">
						@forelse(auth()->user()->unreadNotifications->take(10) as $n)
							<a href="{{ $n->data['link'] ?? '/notifications' }}" class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-white/5 border-b last:border-b-0 dark:border-gray-700 transition-colors">
								<p class="text-sm text-gray-700 dark:text-gray-300">{{ $n->data['message'] ?? $n->type }}</p>
								<p class="text-[10px] text-gray-400 mt-1">{{ $n->created_at->diffForHumans() }}</p>
							</a>
						@empty
							<div class="px-4 py-8 text-center">
								<svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
								<p class="text-xs text-gray-500">No new notifications</p>
							</div>
						@endforelse
					</div>
					<div class="p-2 text-center border-t dark:border-gray-700 bg-gray-50 dark:bg-white/5">
						<a href="/notifications" class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:underline">View all notifications</a>
					</div>
				</div>
			</div>

			<!-- Profile -->
			<div class="relative" @click.outside="dropdownOpen = false">
				<button @click.stop="dropdownOpen = !dropdownOpen" class="flex items-center gap-2 p-1 pr-3 rounded-full border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
					<div class="h-8 w-8 rounded-full overflow-hidden bg-brand-100 flex items-center justify-center text-brand-600 font-bold text-xs border border-white dark:border-gray-700">
						@if(auth()->user()->avatar_url)
							<img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="h-full w-full object-cover">
						@else
							{{ substr(auth()->user()->name, 0, 1) }}
						@endif
					</div>
					<span class="hidden sm:block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">{{ explode(' ', auth()->user()->name)[0] }}</span>
					<svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
				</button>
				<div x-show="dropdownOpen" x-cloak class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl z-50 overflow-hidden">
					<div class="p-4 border-b dark:border-gray-700 bg-gray-50 dark:bg-white/5">
						<p class="font-bold text-sm text-gray-800 dark:text-white truncate">{{ auth()->user()->name }}</p>
						<p class="text-[10px] text-gray-500 truncate mt-0.5">{{ auth()->user()->email }}</p>
					</div>
					<ul class="py-1">
						<li>
							<a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
								<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
								My Profile
							</a>
						</li>
						<li>
							<a href="/notifications" class="flex items-center gap-3 px-4 py-2.5 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors lg:hidden">
								<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
								Notifications
							</a>
						</li>
						<li class="border-t dark:border-gray-700 mt-1">
							<form id="logout-form" action="{{ route('logout') }}" method="POST">
								@csrf
								<button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-red-600 hover:bg-red-50 dark:hover:bg-red-900/10 transition-colors">
									<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
									Logout
								</button>
							</form>
						</li>
					</ul>
				</div>
			</div>
			@endauth
		</div>
	</div>
</header>
