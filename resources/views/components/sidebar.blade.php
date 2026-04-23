<!-- Overlay (Mobile Only) -->
<div 
    x-show="sidebarToggle"
    @click="sidebarToggle = false"
    class="fixed inset-0 bg-black/50 z-40 lg:hidden"
></div>

<aside
  x-transition
  :class="sidebarToggle ? 'translate-x-0' : '-translate-x-full'"
  class="sidebar fixed left-0 top-0 z-50 flex h-screen w-[290px] flex-col overflow-y-auto border-r border-gray-200 bg-white px-5 dark:border-gray-800 dark:bg-black transform transition-transform duration-300 ease-in-out will-change-transform lg:static lg:translate-x-0"
>

  <!-- SIDEBAR HEADER -->
  <div class="flex items-center justify-between gap-2 pt-8 pb-7">
    <a href="{{ url('/') }}">
      <img class="dark:hidden mt-8" src="{{ asset('images/logo/logo.svg') }}" alt="Logo" />
      <img class="hidden dark:block" src="{{ asset('images/logo/logo-dark.svg') }}" alt="Logo" />
    </a>
  </div>

  <!-- Sidebar Menu -->
  <div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
    <nav>
      <div>
        <h3 class="mb-4 text-xs uppercase text-gray-400">USER MENU</h3>

        <ul class="flex flex-col gap-4 mb-6">

          <!-- Dashboard -->
          <li>
            <a href="{{ route('dashboard') }}"
               @click="sidebarToggle = false"
               class="menu-item group">
              <span>Dashboard</span>
            </a>
          </li>

          <!-- Referrals -->
          <li>
            <a href="{{ route('referrals.index') }}"
               @click="sidebarToggle = false"
               class="menu-item group">
              <span>Referrals</span>
            </a>
          </li>

          <!-- Genealogy -->
          <li>
            <a href="{{ route('genealogy.index') }}"
               @click="sidebarToggle = false"
               class="menu-item group">
              <span>Genealogy</span>
            </a>
          </li>

          <!-- Shop -->
          <li>
            <a href="{{ route('shop.index') }}"
               @click="sidebarToggle = false"
               class="menu-item group">
              <span>Shop</span>
            </a>
          </li>

          <!-- Orders -->
          <li>
            <a href="{{ route('orders.index') }}"
               @click="sidebarToggle = false"
               class="menu-item group">
              <span>My Orders</span>
            </a>
          </li>

          <!-- Commissions -->
          <li>
            <a href="{{ route('commissions.index') }}"
               @click="sidebarToggle = false"
               class="menu-item group">
              <span>Commissions</span>
            </a>
          </li>

          @if(($systemSettings['enable_bv_commission'] ?? 'on') === 'on')
          <li>
            <a href="{{ route('commissions.bv') }}"
               @click="sidebarToggle = false"
               class="menu-item group">
              <span>BV Commissions</span>
            </a>
          </li>
          @endif

          @can('admin-access')
          <li>
            <a href="{{ route('admin.products') }}"
               @click="sidebarToggle = false"
               class="menu-item group">
              <span>Products</span>
            </a>
          </li>

          <li>
            <a href="{{ route('admin.packages') }}"
               @click="sidebarToggle = false"
               class="menu-item group">
              <span>Packages</span>
            </a>
          </li>

          <li>
            <a href="{{ route('admin.shop.index') }}"
               @click="sidebarToggle = false"
               class="menu-item group">
              <span>Admin Shop</span>
            </a>
          </li>

          <li>
            <a href="{{ route('admin.emis.index') }}"
               @click="sidebarToggle = false"
               class="menu-item group">
              <span>User EMIs</span>
            </a>
          </li>
          @endcan

          <!-- Wallet -->
          <li>
            <a href="{{ route('wallet.history') }}"
               @click="sidebarToggle = false"
               class="menu-item group">
              <span>Wallet History</span>
            </a>
          </li>

          <li>
            <a href="{{ route('wallet.transfer') }}"
               @click="sidebarToggle = false"
               class="menu-item group">
              <span>Transfer Balance</span>
            </a>
          </li>

          <!-- Credit -->
          <li>
            <a href="{{ route('credit.history') }}"
               @click="sidebarToggle = false"
               class="menu-item group">
              <span>Credit History</span>
            </a>
          </li>

          <li>
            <a href="{{ route('credit.emis') }}"
               @click="sidebarToggle = false"
               class="menu-item group">
              <span>EMI Schedule</span>
            </a>
          </li>

          <li>
            <a href="{{ route('credit.penalties.history') }}"
               @click="sidebarToggle = false"
               class="menu-item group">
              <span>Penalty History</span>
            </a>
          </li>

          <!-- Profile -->
          <li>
            <a href="{{ route('profile.edit') }}"
               @click="sidebarToggle = false"
               class="menu-item group">
              <span>Profile</span>
            </a>
          </li>

          <li>
            <a href="{{ route('profile.id-card') }}"
               @click="sidebarToggle = false"
               class="menu-item group">
              <span>My ID Card</span>
            </a>
          </li>

        </ul>
      </div>
    </nav>
  </div>
</aside>