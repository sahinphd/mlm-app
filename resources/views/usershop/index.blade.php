@extends('layouts.app')

@section('content')
<div x-data="shopApp()" x-init="initShop()" class="min-h-screen bg-gray-50/60 dark:bg-gray-900/60 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">

        <!-- Store Header Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-700 text-white p-6 sm:p-10 mb-8 shadow-xl">
            <!-- Decorative Background SVGs -->
            <div class="absolute -right-10 -bottom-10 opacity-15 pointer-events-none text-9xl">
                <i class="fas fa-shopping-basket"></i>
            </div>
            <div class="absolute right-1/4 -top-8 w-48 h-48 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>

            <div class="relative z-10 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur text-xs font-semibold uppercase tracking-wider mb-3">
                    <i class="fas fa-store"></i> Dua Re Dokandar Official Store
                </div>
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight text-white mb-3">
                    Fresh Goods, Spices & Packages
                </h1>
                <p class="text-blue-100 text-sm sm:text-base leading-relaxed mb-6">
                    Browse our full catalog of daily essentials, handpicked spices, and exclusive value packages. Add to your cart and pay seamlessly with your wallet or EMI credit limit!
                </p>

                <!-- Value Proposition Highlights -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs sm:text-sm font-medium">
                    <div class="flex items-center gap-2 bg-white/10 backdrop-blur rounded-xl px-3 py-2">
                        <i class="fas fa-truck text-cyan-300"></i>
                        <span>Doorstep Delivery</span>
                    </div>
                    <div class="flex items-center gap-2 bg-white/10 backdrop-blur rounded-xl px-3 py-2">
                        <i class="fas fa-medal text-yellow-300"></i>
                        <span>100% Authentic</span>
                    </div>
                    <div class="flex items-center gap-2 bg-white/10 backdrop-blur rounded-xl px-3 py-2">
                        <i class="fas fa-gem text-emerald-300"></i>
                        <span>Earn BV Rewards</span>
                    </div>
                    <div class="flex items-center gap-2 bg-white/10 backdrop-blur rounded-xl px-3 py-2">
                        <i class="fas fa-credit-card text-purple-300"></i>
                        <span>Wallet & EMI</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Controls Bar -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 p-4 mb-8 shadow-xs">
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
                
                <!-- Category Tabs -->
                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                    <button type="button" 
                            @click="filterType = 'all'" 
                            :class="filterType === 'all' ? 'bg-blue-600 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition">
                        All Items (<span x-text="allItems.length"></span>)
                    </button>
                    <button type="button" 
                            @click="filterType = 'product'" 
                            :class="filterType === 'product' ? 'bg-blue-600 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition">
                        Products (<span x-text="productsCount"></span>)
                    </button>
                    <button type="button" 
                            @click="filterType = 'package'" 
                            :class="filterType === 'package' ? 'bg-blue-600 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition">
                        Packages (<span x-text="packagesCount"></span>)
                    </button>
                    <label class="inline-flex items-center gap-2 cursor-pointer ml-1 sm:ml-3 text-xs sm:text-sm text-gray-600 dark:text-gray-300 select-none">
                        <input type="checkbox" x-model="inStockOnly" class="rounded text-blue-600 focus:ring-blue-500 h-4 w-4">
                        <span>In Stock Only</span>
                    </label>
                </div>

                <!-- Search Input & Sort Dropdown -->
                <div class="flex items-center gap-3">
                    <div class="relative flex-1 sm:w-64">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" 
                               x-model="searchQuery" 
                               placeholder="Search items or packages..." 
                               class="w-full pl-9 pr-4 py-2 text-xs sm:text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
                        <button type="button" 
                                x-show="searchQuery.length > 0" 
                                @click="searchQuery = ''" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>

                    <!-- Sort -->
                    <select x-model="sortBy" class="px-3 py-2 text-xs sm:text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        <option value="default">Sort: Featured</option>
                        <option value="price_asc">Price: Low to High</option>
                        <option value="price_desc">Price: High to Low</option>
                        <option value="bv_desc">Highest BV Points</option>
                        <option value="name_asc">Name: A to Z</option>
                    </select>
                </div>

            </div>
        </div>

        <!-- Products & Packages Grid -->
        <div class="mb-12">
            <!-- Server-rendered data fallback for SEO and search crawlers -->
            <div x-show="false" class="hidden">
                @foreach($packages as $pkg)
                    <div>
                        <h4>{{ $pkg->name }}</h4>
                        <span>Rs.{{ number_format($pkg->price, 2) }}</span>
                        <span>{{ $pkg->bv }} BV</span>
                    </div>
                @endforeach
                @foreach($products as $prod)
                    <div>
                        <h4>{{ $prod->name }}</h4>
                        <span>Rs.{{ number_format($prod->price, 2) }}</span>
                        <span>{{ $prod->bv }} BV</span>
                    </div>
                @endforeach
            </div>

            <!-- Loading state or empty state -->
            <div x-show="filteredItems.length === 0" x-cloak class="bg-white dark:bg-gray-800 rounded-3xl p-12 text-center border border-gray-200/80 dark:border-gray-700">
                <div class="w-16 h-16 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fas fa-box-open"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">No matching items found</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto mb-5">
                    We couldn't find any products matching "<span x-text="searchQuery"></span>". Try clearing your search or filters.
                </p>
                <button type="button" @click="searchQuery = ''; filterType = 'all'; inStockOnly = false;" class="px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition">
                    Reset Filters
                </button>
            </div>

            <!-- Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <template x-for="item in filteredItems" :key="item.type + '_' + item.id">
                    <div class="group relative flex flex-col rounded-2xl border border-gray-200/80 dark:border-gray-700/80 bg-white dark:bg-gray-800 overflow-hidden shadow-xs hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                        
                        <!-- Badges Header -->
                        <div class="absolute top-3 left-3 z-10 flex flex-col gap-1.5">
                            <span :class="item.type === 'package' ? 'bg-purple-600 text-white' : 'bg-blue-600 text-white'" 
                                  class="text-[10px] uppercase tracking-wider font-extrabold px-2.5 py-1 rounded-full shadow-xs" 
                                  x-text="item.type === 'package' ? 'Package' : 'Product'">
                            </span>
                        </div>

                        <div class="absolute top-3 right-3 z-10">
                            <!-- In Stock / Out of stock badge -->
                            <template x-if="item.type === 'product'">
                                <span :class="item.stock > 0 ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/40 dark:text-red-400'"
                                      class="border text-[11px] font-semibold px-2 py-0.5 rounded-full flex items-center gap-1 shadow-2xs">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="item.stock > 0 ? 'bg-emerald-500' : 'bg-red-500'"></span>
                                    <span x-text="item.stock > 0 ? (item.stock + ' in stock') : 'Out of stock'"></span>
                                </span>
                            </template>
                            <template x-if="item.type === 'package'">
                                <span class="bg-indigo-50 border border-indigo-200 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400 text-[11px] font-semibold px-2 py-0.5 rounded-full shadow-2xs">
                                    Combo Value
                                </span>
                            </template>
                        </div>

                        <!-- Product Thumbnail Image -->
                        <div class="relative h-52 w-full bg-gray-100 dark:bg-gray-900/60 flex items-center justify-center overflow-hidden cursor-pointer" @click="openQuickView(item)">
                            <template x-if="item.image">
                                <img :src="item.image" :alt="item.name" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500">
                            </template>
                            <template x-if="!item.image">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <i :class="item.type === 'package' ? 'fas fa-box-open' : 'fas fa-shopping-basket'" class="text-5xl text-blue-300 dark:text-blue-900 mb-2"></i>
                                    <span class="text-xs uppercase font-bold text-gray-400" x-text="item.type"></span>
                                </div>
                            </template>
                        </div>

                        <!-- Card Body -->
                        <div class="flex flex-col flex-1 p-5">
                            
                            <!-- BV Points Indicator -->
                            <div class="mb-2">
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-md">
                                    <i class="fas fa-sparkles text-[10px]"></i>
                                    <span x-text="item.bv + ' BV Points'"></span>
                                </span>
                            </div>

                            <!-- Name -->
                            <h3 class="font-bold text-base text-gray-900 dark:text-white mb-1.5 line-clamp-1 hover:text-blue-600 transition cursor-pointer" 
                                @click="openQuickView(item)" 
                                :title="item.name" 
                                x-text="item.name"></h3>

                            <!-- Optional Description Preview -->
                            <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mb-4 flex-1" x-text="item.description || 'Quality product from Dua Re Dokandar, carefully verified for customer satisfaction.'"></p>

                            <!-- Price Section -->
                            <div class="flex items-baseline justify-between mb-4 pt-2 border-t border-gray-100 dark:border-gray-700/60">
                                <span class="text-xs text-gray-400 uppercase font-bold">Price</span>
                                <span class="text-xl font-extrabold text-gray-900 dark:text-white">
                                    Rs.<span x-text="formatNumber(item.price)"></span>
                                </span>
                            </div>

                            <!-- Quantity Selector + Action Buttons -->
                            <div class="space-y-2 mt-auto">
                                <!-- Stepper Input -->
                                <div class="flex items-center justify-between border border-gray-200 dark:border-gray-700 rounded-xl px-2 py-1 bg-gray-50/50 dark:bg-gray-900/40">
                                    <span class="text-xs font-semibold text-gray-500 pl-1">Qty:</span>
                                    <div class="flex items-center gap-2">
                                        <button type="button" 
                                                @click="decrementCardQty(item)" 
                                                :disabled="getItemQty(item) <= 1"
                                                class="w-7 h-7 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 disabled:opacity-40 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                            <i class="fas fa-minus text-[10px]"></i>
                                        </button>
                                        <span class="font-bold text-sm text-gray-800 dark:text-gray-200 min-w-[20px] text-center" x-text="getItemQty(item)"></span>
                                        <button type="button" 
                                                @click="incrementCardQty(item)" 
                                                :disabled="item.type === 'product' && getItemQty(item) >= item.stock"
                                                class="w-7 h-7 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 disabled:opacity-40 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                            <i class="fas fa-plus text-[10px]"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Action Buttons: Add to Cart & Buy Now -->
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" 
                                            @click="addToCart(item)"
                                            :disabled="item.type === 'product' && item.stock <= 0"
                                            class="flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl border border-blue-600 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/40 text-xs font-bold transition disabled:opacity-50 disabled:pointer-events-none">
                                        <i class="fas fa-cart-plus text-xs"></i> Add
                                    </button>
                                    <button type="button" 
                                            @click="buyNow(item)"
                                            :disabled="item.type === 'product' && item.stock <= 0"
                                            class="flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs hover:shadow transition disabled:opacity-50 disabled:pointer-events-none">
                                        Buy Now
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>

    <!-- Floating Cart Drawer Toggle Button (Mobile & Desktop) -->
    <div class="fixed bottom-6 right-6 z-40">
        <button type="button" 
                @click="cartOpen = true" 
                class="group flex items-center gap-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-5 py-3.5 rounded-full shadow-xl hover:shadow-2xl transition transform hover:-translate-y-0.5">
            <div class="relative">
                <i class="fas fa-shopping-cart text-lg"></i>
                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-[11px] font-bold w-5 h-5 rounded-full flex items-center justify-center" 
                      x-text="totalCartCount"></span>
            </div>
            <div class="flex flex-col text-left leading-tight">
                <span class="text-xs font-medium text-blue-100">View Cart</span>
                <span class="text-sm font-extrabold">Rs.<span x-text="formatNumber(totalCartPrice)"></span></span>
            </div>
        </button>
    </div>

    <!-- SLIDE-OVER CART DRAWER -->
    <div x-show="cartOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-hidden" 
         aria-labelledby="cart-title" 
         role="dialog" 
         aria-modal="true">
        
        <!-- Backdrop -->
        <div x-show="cartOpen" 
             x-transition.opacity.duration.300ms 
             @click="cartOpen = false" 
             class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div x-show="cartOpen" 
                 x-transition:enter="transform transition ease-in-out duration-300" 
                 x-transition:enter-start="translate-x-full" 
                 x-transition:enter-end="translate-x-0" 
                 x-transition:leave="transform transition ease-in-out duration-300" 
                 x-transition:leave-start="translate-x-0" 
                 x-transition:leave-end="translate-x-full" 
                 class="w-screen max-w-md bg-white dark:bg-gray-800 shadow-2xl flex flex-col">
                
                <!-- Drawer Header -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/40">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center">
                            <i class="fas fa-shopping-cart text-sm"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white" id="cart-title">Your Cart</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                <span x-text="totalCartCount"></span> items selected
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="cartOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-2 rounded-lg">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <!-- Drawer Items List -->
                <div class="flex-1 overflow-y-auto p-6 space-y-4">
                    
                    <!-- Empty Cart Message -->
                    <template x-if="cart.length === 0">
                        <div class="py-16 text-center">
                            <div class="w-20 h-20 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center mx-auto mb-4 text-3xl">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <h3 class="text-base font-bold text-gray-800 dark:text-gray-200 mb-1">Your cart is empty</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs mx-auto mb-6">
                                Explore our listed products and packages to add them to your shopping cart.
                            </p>
                            <button type="button" @click="cartOpen = false" class="px-5 py-2.5 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition">
                                Start Shopping
                            </button>
                        </div>
                    </template>

                    <!-- Cart Item Rows -->
                    <template x-for="item in cart" :key="item.type + '_' + item.id">
                        <div class="flex items-center gap-3.5 p-3 rounded-2xl border border-gray-100 dark:border-gray-700/60 bg-gray-50/40 dark:bg-gray-900/30">
                            <!-- Image -->
                            <div class="w-16 h-16 rounded-xl overflow-hidden bg-gray-200 dark:bg-gray-700 flex-shrink-0 flex items-center justify-center">
                                <template x-if="item.image">
                                    <img :src="item.image" :alt="item.name" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!item.image">
                                    <i class="fas fa-box text-gray-400 text-xl"></i>
                                </template>
                            </div>

                            <!-- Details -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-1">
                                    <h4 class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white truncate" x-text="item.name"></h4>
                                    <button type="button" @click="removeFromCart(item)" class="text-gray-400 hover:text-red-500 transition p-1">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Rs.<span x-text="formatNumber(item.price)"></span></span>
                                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold" x-text="'+' + (item.bv * item.quantity) + ' BV'"></span>
                                </div>

                                <!-- Quantity Stepper inside Cart -->
                                <div class="flex items-center justify-between mt-2">
                                    <div class="flex items-center gap-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-0.5">
                                        <button type="button" @click="updateCartQty(item, -1)" class="text-gray-500 hover:text-black dark:hover:text-white text-xs">
                                            <i class="fas fa-minus text-[9px]"></i>
                                        </button>
                                        <span class="font-bold text-xs min-w-[16px] text-center" x-text="item.quantity"></span>
                                        <button type="button" @click="updateCartQty(item, 1)" class="text-gray-500 hover:text-black dark:hover:text-white text-xs">
                                            <i class="fas fa-plus text-[9px]"></i>
                                        </button>
                                    </div>
                                    <span class="text-xs font-bold text-blue-600 dark:text-blue-400">
                                        Rs.<span x-text="formatNumber(item.price * item.quantity)"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </template>

                </div>

                <!-- Drawer Footer Summary -->
                <div x-show="cart.length > 0" class="p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/40 space-y-4">
                    <div class="space-y-2 text-xs sm:text-sm">
                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                            <span>Total Items</span>
                            <span class="font-semibold" x-text="totalCartCount"></span>
                        </div>
                        <div class="flex justify-between text-emerald-600 dark:text-emerald-400 font-semibold">
                            <span>Total Reward BV</span>
                            <span x-text="totalCartBv + ' BV Points'"></span>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-2 flex justify-between text-base font-extrabold text-gray-900 dark:text-white">
                            <span>Subtotal</span>
                            <span class="text-blue-600 dark:text-blue-400">Rs.<span x-text="formatNumber(totalCartPrice)"></span></span>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 pt-2">
                        <button type="button" 
                                @click="proceedToCheckout()" 
                                class="w-full flex items-center justify-center gap-2 py-3.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md hover:shadow-lg transition">
                            <i class="fas fa-lock text-xs"></i>
                            <span>Proceed to Checkout</span>
                        </button>
                        <button type="button" 
                                @click="clearCart()" 
                                class="w-full py-2 text-xs text-gray-500 hover:text-red-500 transition">
                            Clear Shopping Cart
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- GUEST AUTHENTICATION PROMPT MODAL -->
    <div x-show="showAuthModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="auth-modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block">
            <div x-show="showAuthModal" 
                 x-transition.opacity 
                 @click="showAuthModal = false" 
                 class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div x-show="showAuthModal" 
                 x-transition.scale 
                 class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full p-6 sm:p-8">
                
                <div class="w-14 h-14 rounded-2xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fas fa-user-lock"></i>
                </div>

                <div class="text-center">
                    <h3 class="text-xl font-extrabold text-gray-900 dark:text-white mb-2" id="auth-modal-title">
                        Sign In to Complete Order
                    </h3>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">
                        Your <span class="font-bold text-blue-600" x-text="totalCartCount"></span> selected items have been saved safely in your cart! Please log in or register so we can process your order and credit your BV points.
                    </p>
                </div>

                <div class="space-y-3">
                    <button type="button" 
                            @click="redirectToAuth('login')" 
                            class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-xs transition">
                        <i class="fas fa-sign-in-alt"></i> Log In to Existing Account
                    </button>
                    <button type="button" 
                            @click="redirectToAuth('register')" 
                            class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 hover:bg-gray-100 font-bold text-sm transition">
                        <i class="fas fa-user-plus"></i> Create New Account
                    </button>
                    <button type="button" 
                            @click="showAuthModal = false" 
                            class="w-full py-2 text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 font-medium">
                        Continue Browsing Products
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- PRODUCT QUICK VIEW MODAL -->
    <div x-show="quickViewItem !== null" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block">
            <div x-show="quickViewItem !== null" 
                 x-transition.opacity 
                 @click="quickViewItem = null" 
                 class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div x-show="quickViewItem !== null" 
                 x-transition.scale 
                 class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full p-6 sm:p-8">
                
                <template x-if="quickViewItem">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <span :class="quickViewItem.type === 'package' ? 'bg-purple-600 text-white' : 'bg-blue-600 text-white'" 
                                  class="text-[10px] uppercase tracking-wider font-extrabold px-2.5 py-1 rounded-full" 
                                  x-text="quickViewItem.type === 'package' ? 'Package Deal' : 'Individual Product'"></span>
                            <button type="button" @click="quickViewItem = null" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-lg"></i>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-center">
                            <div class="h-60 rounded-2xl bg-gray-100 dark:bg-gray-900 overflow-hidden flex items-center justify-center">
                                <template x-if="quickViewItem.image">
                                    <img :src="quickViewItem.image" :alt="quickViewItem.name" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!quickViewItem.image">
                                    <i :class="quickViewItem.type === 'package' ? 'fas fa-box-open' : 'fas fa-shopping-basket'" class="text-6xl text-blue-300"></i>
                                </template>
                            </div>

                            <div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2" x-text="quickViewItem.name"></h3>
                                <div class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-1 rounded-md mb-3">
                                    <i class="fas fa-sparkles"></i>
                                    <span x-text="quickViewItem.bv + ' BV Points Reward'"></span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4" x-text="quickViewItem.description || 'Verified authentic stock sourced from certified local partners.'"></p>
                                
                                <div class="text-2xl font-extrabold text-blue-600 dark:text-blue-400 mb-6">
                                    Rs.<span x-text="formatNumber(quickViewItem.price)"></span>
                                </div>

                                <div class="flex gap-2">
                                    <button type="button" 
                                            @click="addToCart(quickViewItem); quickViewItem = null;" 
                                            class="flex-1 py-3 px-4 rounded-xl border border-blue-600 text-blue-600 font-bold text-xs hover:bg-blue-50 transition">
                                        Add to Cart
                                    </button>
                                    <button type="button" 
                                            @click="buyNow(quickViewItem); quickViewItem = null;" 
                                            class="flex-1 py-3 px-4 rounded-xl bg-blue-600 text-white font-bold text-xs hover:bg-blue-700 transition">
                                        Buy Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Hidden form for checkout submission -->
    <form id="checkoutPostForm" method="POST" action="{{ route('shop.checkout') }}" class="hidden">
        @csrf
        <input type="hidden" name="cart" id="checkoutCartPayload">
    </form>

</div>

@push('scripts')
<script>
function shopApp() {
    return {
        // Raw Catalog Data
        products: @json($products),
        packages: @json($packages),

        // Filter / Search State
        searchQuery: '',
        filterType: 'all', // 'all', 'product', 'package'
        inStockOnly: false,
        sortBy: 'default',

        // Card Quantity State
        cardQuantities: {},

        // Cart State
        cart: [],
        cartOpen: false,
        showAuthModal: false,
        quickViewItem: null,

        // Auth flag
        isAuthenticated: {{ Auth::check() ? 'true' : 'false' }},

        initShop() {
            // Flatten raw items
            this.loadCart();

            // Listen to global open-cart trigger from navbar
            window.addEventListener('open-cart', () => {
                this.cartOpen = true;
            });
        },

        get allItems() {
            const prodList = (this.products || []).map(p => ({
                id: p.id,
                name: p.name,
                description: p.description || '',
                price: parseFloat(p.price) || 0,
                bv: parseFloat(p.bv) || 0,
                image: p.image || null,
                stock: parseInt(p.stock) || 0,
                type: 'product'
            }));

            const pkgList = (this.packages || []).map(pkg => ({
                id: pkg.id,
                name: pkg.name,
                description: pkg.description || '',
                price: parseFloat(pkg.price) || 0,
                bv: parseFloat(pkg.bv) || 0,
                image: pkg.image || null,
                stock: 999,
                type: 'package'
            }));

            return [...pkgList, ...prodList];
        },

        get productsCount() {
            return (this.products || []).length;
        },

        get packagesCount() {
            return (this.packages || []).length;
        },

        get filteredItems() {
            let list = this.allItems;

            // 1. Filter by Type
            if (this.filterType !== 'all') {
                list = list.filter(item => item.type === this.filterType);
            }

            // 2. Filter by Stock
            if (this.inStockOnly) {
                list = list.filter(item => item.type === 'package' || item.stock > 0);
            }

            // 3. Filter by Search Query
            if (this.searchQuery.trim().length > 0) {
                const q = this.searchQuery.toLowerCase();
                list = list.filter(item => 
                    item.name.toLowerCase().includes(q) || 
                    (item.description && item.description.toLowerCase().includes(q))
                );
            }

            // 4. Sort
            if (this.sortBy === 'price_asc') {
                list.sort((a, b) => a.price - b.price);
            } else if (this.sortBy === 'price_desc') {
                list.sort((a, b) => b.price - a.price);
            } else if (this.sortBy === 'bv_desc') {
                list.sort((a, b) => b.bv - a.bv);
            } else if (this.sortBy === 'name_asc') {
                list.sort((a, b) => a.name.localeCompare(b.name));
            }

            return list;
        },

        getItemQty(item) {
            const key = item.type + '_' + item.id;
            return this.cardQuantities[key] || 1;
        },

        incrementCardQty(item) {
            const key = item.type + '_' + item.id;
            const current = this.getItemQty(item);
            if (item.type === 'product' && current >= item.stock) return;
            this.cardQuantities[key] = current + 1;
        },

        decrementCardQty(item) {
            const key = item.type + '_' + item.id;
            const current = this.getItemQty(item);
            if (current > 1) {
                this.cardQuantities[key] = current - 1;
            }
        },

        // CART MANAGEMENT
        loadCart() {
            try {
                const stored = localStorage.getItem('dua_cart');
                this.cart = stored ? JSON.parse(stored) : [];
                if (!Array.isArray(this.cart)) this.cart = [];
            } catch(e) {
                this.cart = [];
            }
            this.notifyCartUpdated();
        },

        saveCart() {
            localStorage.setItem('dua_cart', JSON.stringify(this.cart));
            this.notifyCartUpdated();
            this.syncCartToServer();
        },

        notifyCartUpdated() {
            window.dispatchEvent(new CustomEvent('cart-updated'));
        },

        syncCartToServer() {
            fetch("{{ route('shop.cart.sync') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ cart: this.cart })
            }).catch(() => {});
        },

        addToCart(item) {
            const qty = this.getItemQty(item);
            const existingIndex = this.cart.findIndex(c => c.id === item.id && c.type === item.type);

            if (existingIndex > -1) {
                let newQty = this.cart[existingIndex].quantity + qty;
                if (item.type === 'product' && newQty > item.stock) {
                    newQty = item.stock;
                }
                this.cart[existingIndex].quantity = newQty;
            } else {
                this.cart.push({
                    id: item.id,
                    type: item.type,
                    name: item.name,
                    price: item.price,
                    bv: item.bv,
                    image: item.image,
                    quantity: qty
                });
            }

            this.saveCart();
            this.cartOpen = true;
        },

        updateCartQty(item, delta) {
            const idx = this.cart.findIndex(c => c.id === item.id && c.type === item.type);
            if (idx === -1) return;

            const newQty = this.cart[idx].quantity + delta;
            if (newQty <= 0) {
                this.cart.splice(idx, 1);
            } else {
                this.cart[idx].quantity = newQty;
            }
            this.saveCart();
        },

        removeFromCart(item) {
            this.cart = this.cart.filter(c => !(c.id === item.id && c.type === item.type));
            this.saveCart();
        },

        clearCart() {
            this.cart = [];
            this.saveCart();
        },

        get totalCartCount() {
            return this.cart.reduce((sum, it) => sum + (parseInt(it.quantity) || 1), 0);
        },

        get totalCartPrice() {
            return this.cart.reduce((sum, it) => sum + ((parseFloat(it.price) || 0) * (parseInt(it.quantity) || 1)), 0);
        },

        get totalCartBv() {
            return this.cart.reduce((sum, it) => sum + ((parseFloat(it.bv) || 0) * (parseInt(it.quantity) || 1)), 0);
        },

        buyNow(item) {
            this.addToCart(item);
            this.proceedToCheckout();
        },

        proceedToCheckout() {
            if (this.cart.length === 0) {
                alert('Your cart is empty. Please choose products first.');
                return;
            }

            if (!this.isAuthenticated) {
                // Show guest auth prompt modal
                this.syncCartToServer();
                this.showAuthModal = true;
                return;
            }

            // Authenticated user - submit cart via POST form to checkout
            const form = document.getElementById('checkoutPostForm');
            const payload = document.getElementById('checkoutCartPayload');
            payload.value = JSON.stringify(this.cart);
            form.submit();
        },

        redirectToAuth(mode) {
            this.syncCartToServer();
            const targetUrl = mode === 'register' ? "{{ route('register') }}" : "{{ route('login') }}";
            window.location.href = targetUrl;
        },

        openQuickView(item) {
            this.quickViewItem = item;
        },

        formatNumber(val) {
            return Number(val || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    };
}
</script>
@endpush
@endsection
