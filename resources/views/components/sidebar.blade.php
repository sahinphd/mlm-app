<!-- Overlay (Mobile Only) -->
<div 
    x-show="sidebarToggle"
    x-cloak
    @click="sidebarToggle = false"
    class="fixed inset-0 bg-black/60 z-40 lg:hidden transition-opacity duration-300"
></div>

<aside
  x-cloak
  :class="sidebarToggle ? 'translate-x-0' : '-translate-x-full'"
  class="sidebar fixed left-0 top-0 z-50 flex h-screen w-[290px] flex-col overflow-y-auto border-r border-gray-200 bg-white px-5 dark:border-gray-800 dark:bg-black transition-transform duration-300 ease-in-out lg:static lg:translate-x-0"
>

  <!-- SIDEBAR HEADER -->
  <div class="flex items-center justify-between gap-2 pt-8 pb-10 sidebar-header">
    <a href="{{ url('/') }}">
      <span class="logo">
        <img class="dark:hidden mt-8" src="{{ asset('images/logo/logo.svg') }}" alt="Logo" />
        <img class="hidden dark:block mt-8" src="{{ asset('images/logo/logo-dark.svg') }}" alt="Logo" />
      </span>
    </a>
  </div>

  <!-- Sidebar Menu -->
  <div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
    <nav>
      <div class="mt-4">
        <h3 class="mb-4 text-xs uppercase text-gray-400">USER MENU</h3>

        <ul class="flex flex-col gap-4 mb-6">

          <!-- Dashboard -->
          <li>
            <a href="{{ route('dashboard') }}"
               class="menu-item group {{ request()->routeIs('dashboard') ? 'menu-item-active' : 'menu-item-inactive' }}">
               <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="{{ request()->routeIs('dashboard') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z" fill="currentColor"></path>
              </svg>
              <span>Dashboard</span>
            </a>
          </li>

          <!-- Referrals -->
          <li>
            <a href="{{ route('referrals.index') }}"
               class="menu-item group {{ request()->routeIs('referrals.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ request()->routeIs('referrals.*') ? 'text-brand-500' : 'text-gray-500 group-hover:text-gray-700' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21a6 6 0 00-9-5.197M15 21a6 6 0 006-6v-1a6 6 0 00-6-6h-1.333a6 6 0 00-11.334 0v1a6 6 0 006 6h1.333z" />
              </svg>
              <span>Referrals</span>
            </a>
          </li>

          <!-- Genealogy -->
          <li>
            <a href="{{ route('genealogy.index') }}"
               class="menu-item group {{ request()->routeIs('genealogy.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ request()->routeIs('genealogy.*') ? 'text-brand-500' : 'text-gray-500 group-hover:text-gray-700' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              <span>Genealogy</span>
            </a>
          </li>

          <!-- Shop -->
          <li>
            <a href="{{ route('shop.index') }}"
               class="menu-item group {{ request()->routeIs('shop.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ request()->routeIs('shop.*') ? 'text-brand-500' : 'text-gray-500 group-hover:text-gray-700' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <span>Shop</span>
            </a>
          </li>

          <!-- Orders -->
          <li>
            <a href="{{ route('orders.index') }}"
               class="menu-item group {{ request()->routeIs('orders.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ request()->routeIs('orders.*') ? 'text-brand-500' : 'text-gray-500 group-hover:text-gray-700' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
              <span>My Orders</span>
            </a>
          </li>

          <!-- Commissions -->
          <li>
            <a href="{{ route('commissions.index') }}"
               class="menu-item group {{ request()->routeIs('commissions.index') ? 'menu-item-active' : 'menu-item-inactive' }}">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ request()->routeIs('commissions.index') ? 'text-brand-500' : 'text-gray-500 group-hover:text-gray-700' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3-1.343-3-3S10.343 2 12 2s3 1.343 3 3-1.343 3-3 3zM6 20c0-3.866 3.582-7 8-7s8 3.134 8 7" />
              </svg>
              <span>Commissions</span>
            </a>
          </li>

          @if(($systemSettings['enable_bv_commission'] ?? 'on') === 'on')
          <li>
            <a href="{{ route('commissions.bv') }}"
               class="menu-item group {{ request()->routeIs('commissions.bv') ? 'menu-item-active' : 'menu-item-inactive' }}">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ request()->routeIs('commissions.bv') ? 'text-brand-500' : 'text-gray-500 group-hover:text-gray-700' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>BV Commissions</span>
            </a>
          </li>
          @endif

          @can('admin-access')
          <li>
            <a href="{{ route('admin.products') }}"
               class="menu-item group {{ request()->routeIs('admin.products*') ? 'menu-item-active' : 'menu-item-inactive' }}">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ request()->routeIs('admin.products*') ? 'text-brand-500' : 'text-gray-500 group-hover:text-gray-700' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
              <span>Products</span>
            </a>
          </li>

          <li>
            <a href="{{ route('admin.packages') }}"
               class="menu-item group {{ request()->routeIs('admin.packages*') ? 'menu-item-active' : 'menu-item-inactive' }}">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ request()->routeIs('admin.packages*') ? 'text-brand-500' : 'text-gray-500 group-hover:text-gray-700' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
              </svg>
              <span>Packages</span>
            </a>
          </li>

          <li>
            <a href="{{ route('admin.shop.index') }}"
               class="menu-item group {{ request()->routeIs('admin.shop.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ request()->routeIs('admin.shop.*') ? 'text-brand-500' : 'text-gray-500 group-hover:text-gray-700' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <span>Admin Shop</span>
            </a>
          </li>

          <li>
            <a href="{{ route('admin.emis.index') }}"
               class="menu-item group {{ request()->routeIs('admin.emis.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ request()->routeIs('admin.emis.*') ? 'text-brand-500' : 'text-gray-500 group-hover:text-gray-700' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
              </svg>
              <span>User EMIs</span>
            </a>
          </li>
          @endcan

          <!-- Wallet -->
          <li>
            <a href="{{ route('wallet.history') }}"
               class="menu-item group {{ request()->routeIs('wallet.history') ? 'menu-item-active' : 'menu-item-inactive' }}">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ request()->routeIs('wallet.history') ? 'text-brand-500' : 'text-gray-500 group-hover:text-gray-700' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
              </svg>
              <span>Wallet History</span>
            </a>
          </li>

          <li>
            <a href="{{ route('wallet.transfer') }}"
               class="menu-item group {{ request()->routeIs('wallet.transfer') ? 'menu-item-active' : 'menu-item-inactive' }}">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ request()->routeIs('wallet.transfer') ? 'text-brand-500' : 'text-gray-500 group-hover:text-gray-700' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
              </svg>
              <span>Transfer Balance</span>
            </a>
          </li>

          <!-- Credit -->
          <li>
            <a href="{{ route('credit.history') }}"
               class="menu-item group {{ request()->routeIs('credit.history') ? 'menu-item-active' : 'menu-item-inactive' }}">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ request()->routeIs('credit.history') ? 'text-brand-500' : 'text-gray-500 group-hover:text-gray-700' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zM12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zM12 20c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z" />
              </svg>
              <span>Credit History</span>
            </a>
          </li>

          <li>
            <a href="{{ route('credit.emis') }}"
               class="menu-item group {{ request()->routeIs('credit.emis') ? 'menu-item-active' : 'menu-item-inactive' }}">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ request()->routeIs('credit.emis') ? 'text-brand-500' : 'text-gray-500 group-hover:text-gray-700' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span>EMI Schedule</span>
            </a>
          </li>

          <li>
            <a href="{{ route('credit.penalties.history') }}"
               class="menu-item group {{ request()->routeIs('credit.penalties.history') ? 'menu-item-active' : 'menu-item-inactive' }}">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ request()->routeIs('credit.penalties.history') ? 'text-brand-500' : 'text-gray-500 group-hover:text-gray-700' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
              <span>Penalty History</span>
            </a>
          </li>

          <!-- Profile -->
          <li>
            <a href="{{ route('profile.edit') }}"
               class="menu-item group {{ request()->routeIs('profile.edit') ? 'menu-item-active' : 'menu-item-inactive' }}">
               <svg class="fill-current {{ request()->routeIs('profile.edit') ? 'text-brand-500' : 'text-gray-500 group-hover:text-gray-700' }}" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 3.5C7.30558 3.5 3.5 7.30558 3.5 12C3.5 14.1526 4.3002 16.1184 5.61936 17.616C6.17279 15.3096 8.24852 13.5955 10.7246 13.5955H13.2746C15.7509 13.5955 17.8268 15.31 18.38 17.6167C19.6996 16.119 20.5 14.153 20.5 12C20.5 7.30558 16.6944 3.5 12 3.5ZM17.0246 18.8566V18.8455C17.0246 16.7744 15.3457 15.0955 13.2746 15.0955H10.7246C8.65354 15.0955 6.97461 16.7744 6.97461 18.8455V18.856C8.38223 19.8895 10.1198 20.5 12 20.5C13.8798 20.5 15.6171 19.8898 17.0246 18.8566ZM2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12ZM11.9991 7.25C10.8847 7.25 9.98126 8.15342 9.98126 9.26784C9.98126 10.3823 10.8847 11.2857 11.9991 11.2857C13.1135 11.2857 14.0169 10.3823 14.0169 9.26784C14.0169 8.15342 13.1135 7.25 11.9991 7.25ZM8.48126 9.26784C8.48126 7.32499 10.0563 5.75 11.9991 5.75C13.9419 5.75 15.5169 7.32499 15.5169 9.26784C15.5169 11.2107 13.9419 12.7857 11.9991 12.7857C10.0563 12.7857 8.48126 11.2107 8.48126 9.26784Z" fill="currentColor"></path>
              </svg>
              <span>Profile</span>
            </a>
          </li>

          <li>
            <a href="{{ route('profile.id-card') }}"
               class="menu-item group {{ request()->routeIs('profile.id-card') ? 'menu-item-active' : 'menu-item-inactive' }}">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ request()->routeIs('profile.id-card') ? 'text-brand-500' : 'text-gray-500 group-hover:text-gray-700' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
              </svg>
              <span>My ID Card</span>
            </a>
          </li>

        </ul>
      </div>
    </nav>
  </div>
</aside>