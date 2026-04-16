<header class="sticky top-0 z-50 w-full border-b bg-white dark:bg-gray-900">
	<div class="flex items-center justify-between px-4 py-3 lg:px-6">
		<div class="flex items-center gap-3">
			<button id="sidebarToggle" class="p-2 rounded-lg border lg:hidden">
				☰
			</button>
			<a href="/admin" class="flex items-center gap-2">
				<img src="/images/logo/logo.svg" alt="Logo" class="h-8">
				<span class="font-semibold text-lg">{{ config('app.name','MLM App') }}</span>
			</a>
		</div>

		<div class="flex items-center gap-3">
			<form class="hidden lg:block">
				<input type="text" placeholder="Search..." class="rounded-lg border px-3 py-2" />
			</form>

			<a href="#" class="p-2 rounded-full border">🔔</a>
			<a href="#" class="p-2 rounded-full border">👤</a>
		</div>
	</div>
</header>
