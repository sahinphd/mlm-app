<header class="sticky top-0 w-full border-b bg-white dark:bg-gray-900" :class="sidebarToggle ? 'z-40' : 'z-50'">
	<div class="flex items-center justify-between px-4 py-3 lg:px-6">
		<div class="flex items-center gap-3">
			<button @click.prevent="sidebarToggle = !sidebarToggle" class="p-2 rounded-lg border lg:hidden">
				<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
				</svg>
			</button>
			<a href="/admin" class="flex items-center gap-2">
				<img src="/images/logo/logo.svg" alt="Logo" class="h-8">
				{{-- <span class="font-semibold text-lg">{{ config('app.name','MLM App') }}</span> --}}
			</a>
		</div>

		<div class="flex items-center gap-3">
			<form class="hidden lg:block">
				<input type="text" placeholder="Search..." class="rounded-lg border px-3 py-2" />
			</form>

			@auth
			<!-- Notifications -->
			<div class="relative">
				<button id="notifToggle" class="p-2 rounded-full border flex items-center gap-2" aria-expanded="false">
					🔔
					@if(auth()->user()->unreadNotifications->count() > 0)
					<span class="ml-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full">{{ auth()->user()->unreadNotifications->count() }}</span>
					@endif
				</button>
				<div id="notifMenu" class="hidden absolute right-0 mt-2 w-80 bg-white border rounded shadow-lg z-50">
					<div class="p-3">
						<p class="font-semibold">Notifications</p>
					</div>
					<div class="max-h-56 overflow-auto">
						@foreach(auth()->user()->unreadNotifications->take(10) as $n)
							<a href="{{ $n->data['link'] ?? '/notifications' }}" class="block px-3 py-2 hover:bg-gray-100">{{ $n->data['message'] ?? $n->type }}</a>
						@endforeach
						@if(auth()->user()->unreadNotifications->isEmpty())
							<div class="px-3 py-2 text-sm text-gray-500">No new notifications</div>
						@endif
					</div>
					<div class="p-2 text-center border-t">
						<a href="/notifications" class="text-sm text-blue-600">View all</a>
					</div>
				</div>
			</div>

			<!-- Profile -->
			<div class="relative">
				<button id="profileToggle" class="p-2 rounded-full border flex items-center gap-2" aria-expanded="false">👤</button>
				<div id="profileMenu" class="hidden absolute right-0 mt-2 w-56 bg-white border rounded shadow-lg z-50">
					<div class="p-3 border-b">
						<div class="font-medium">{{ auth()->user()->name }}</div>
						<div class="text-xs text-gray-500">{{ auth()->user()->email }}</div>
					</div>
					<ul class="py-1">
						<li><a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-gray-100">View Profile</a></li>
						<li><a href="/profile/password" class="block px-4 py-2 hover:bg-gray-100">Change Password</a></li>
						<li><a href="/admin/settings" class="block px-4 py-2 hover:bg-gray-100">Account Settings</a></li>
						<li><a href="/notifications" class="block px-4 py-2 hover:bg-gray-100">Notifications</a></li>
						<li>
							<form id="logout-form" action="{{ route('logout') }}" method="POST">@csrf
								<button type="submit" class="w-full text-left px-4 py-2 hover:bg-gray-100">Logout</button>
							</form>
						</li>
					</ul>
				</div>
			</div>
			@endauth
		</div>
	</div>
</header>
<script>
document.addEventListener('DOMContentLoaded', function(){
	function toggle(buttonId, menuId){
		const btn = document.getElementById(buttonId);
		const menu = document.getElementById(menuId);
		if(!btn || !menu) return;
		btn.addEventListener('click', function(e){
			e.preventDefault();
			const shown = !menu.classList.contains('hidden');
			document.querySelectorAll('#notifMenu, #profileMenu').forEach(m=>m.classList.add('hidden'));
			if(!shown) menu.classList.remove('hidden');
		});
	}
	toggle('notifToggle','notifMenu');
	toggle('profileToggle','profileMenu');

	document.addEventListener('click', function(e){
		const notif = document.getElementById('notifMenu');
		const prof = document.getElementById('profileMenu');
		const nt = document.getElementById('notifToggle');
		const pt = document.getElementById('profileToggle');
		if(notif && nt && !notif.contains(e.target) && !nt.contains(e.target)) notif.classList.add('hidden');
		if(prof && pt && !prof.contains(e.target) && !pt.contains(e.target)) prof.classList.add('hidden');
	});
});
</script>
