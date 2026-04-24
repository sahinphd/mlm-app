<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>{{ config('app.name', 'MLM App') }} – Grow Together</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Font Awesome 6 (free) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        .hero-bg {
            background: linear-gradient(135deg, #fef9f0 0%, #fff3e5 100%);
        }
        .dark .hero-bg {
            background: linear-gradient(135deg, #111827 0%, #1f2937 100%);
        }
        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 25px -12px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body 
    x-data="{ darkMode: false, mobileMenuOpen: false, showTerms: false }"
    x-init="
        darkMode = JSON.parse(localStorage.getItem('darkMode') || 'false');
        $watch('darkMode', val => localStorage.setItem('darkMode', JSON.stringify(val)))"
    :class="{ 'dark bg-gray-900': darkMode }"
    class="bg-gray-50 text-gray-900 font-sans antialiased"
>
    <!-- Sticky Header -->
    <header class="sticky top-0 z-50 bg-white/90 dark:bg-gray-900/90 backdrop-blur-md border-b border-gray-200 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 md:h-20">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ url('/') }}" class="flex items-center gap-2">
                        <img src="{{ asset('images/logo/logo.svg') }}" class="dark:hidden h-9 w-auto" alt="Logo">
                        <img src="{{ asset('images/logo/logo-dark.svg') }}" class="hidden dark:block h-9 w-auto" alt="Logo">
                        <span class="text-xl font-bold bg-gradient-to-r from-brand-600 to-brand-800 bg-clip-text text-transparent">MLM Nexus</span>
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center gap-6">
                    <a href="#home" class="text-gray-700 hover:text-brand-600 dark:text-gray-300 dark:hover:text-brand-400 font-medium transition">Home</a>
                    <a href="#services" class="text-gray-700 hover:text-brand-600 dark:text-gray-300 dark:hover:text-brand-400 transition">Services</a>
                    <a href="#about" class="text-gray-700 hover:text-brand-600 dark:text-gray-300 dark:hover:text-brand-400 transition">About</a>
                    <a href="#contact" class="text-gray-700 hover:text-brand-600 dark:text-gray-300 dark:hover:text-brand-400 transition">Contact</a>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-4 py-2 rounded-full bg-brand-600 text-white text-sm font-semibold shadow-md hover:bg-brand-700 transition">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 rounded-full border border-brand-600 text-brand-600 dark:text-brand-400 font-medium hover:bg-brand-50 dark:hover:bg-brand-900/30 transition">Log in</a>
                        <a href="{{ route('register') }}" class="px-4 py-2 rounded-full bg-brand-600 text-white font-semibold shadow-md hover:bg-brand-700 transition">Register</a>
                    @endauth
                </div>

                <!-- Mobile controls -->
                <div class="flex items-center gap-2 md:hidden">
                    <button @click="darkMode = !darkMode" class="p-2 rounded-full text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
                        <i x-show="!darkMode" class="fas fa-sun text-lg"></i>
                        <i x-show="darkMode" class="fas fa-moon text-lg"></i>
                    </button>
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 rounded-full text-gray-600 dark:text-gray-300 hover:bg-gray-100">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
                <!-- Desktop dark mode toggle -->
                <div class="hidden md:block">
                    <button @click="darkMode = !darkMode" class="p-2 rounded-full text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">
                        <i x-show="!darkMode" class="fas fa-sun text-lg"></i>
                        <i x-show="darkMode" class="fas fa-moon text-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile menu -->
            <div x-show="mobileMenuOpen" x-cloak x-transition.duration.200ms class="md:hidden py-4 border-t border-gray-200 dark:border-gray-800">
                <div class="flex flex-col space-y-3">
                    <a href="#home" @click="mobileMenuOpen=false" class="px-3 py-2 text-gray-700 dark:text-gray-300 font-medium hover:bg-brand-50 dark:hover:bg-gray-800 rounded-lg">Home</a>
                    <a href="#services" @click="mobileMenuOpen=false" class="px-3 py-2 text-gray-700 dark:text-gray-300 hover:bg-brand-50 dark:hover:bg-gray-800 rounded-lg">Services</a>
                    <a href="#about" @click="mobileMenuOpen=false" class="px-3 py-2 text-gray-700 dark:text-gray-300 hover:bg-brand-50 dark:hover:bg-gray-800 rounded-lg">About</a>
                    <a href="#contact" @click="mobileMenuOpen=false" class="px-3 py-2 text-gray-700 dark:text-gray-300 hover:bg-brand-50 dark:hover:bg-gray-800 rounded-lg">Contact</a>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-3 py-2 text-center bg-brand-600 text-white rounded-lg">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="px-3 py-2 text-center border border-brand-600 text-brand-600 rounded-lg">Log in</a>
                        <a href="{{ route('register') }}" class="px-3 py-2 text-center bg-brand-600 text-white rounded-lg">Register</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <main>
        <!-- Hero Section (duaredokandar inspired) -->
        <section id="home" class="hero-bg py-16 md:py-24 transition-all">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div>
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold tracking-tight text-gray-900 dark:text-white">
                            Build Your <span class="text-brand-600 bg-brand-100 dark:bg-brand-900/40 px-2 inline-block rounded-lg">Network Empire</span>
                        </h1>
                        <p class="mt-6 text-lg text-gray-600 dark:text-gray-300">
                            Join the most transparent, fast‑growing MLM platform. Smart commissions, real‑time analytics, and a community that rises together.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-4">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="px-6 py-3 bg-brand-600 text-white font-semibold rounded-full shadow-md hover:bg-brand-700 transition transform hover:-translate-y-1">Dashboard →</a>
                            @else
                                <a href="{{ route('register') }}" class="px-6 py-3 bg-brand-600 text-white font-semibold rounded-full shadow-md hover:bg-brand-700 transition transform hover:-translate-y-1">Get Started <i class="fas fa-arrow-right ml-2"></i></a>
                                <a href="{{ route('login') }}" class="px-6 py-3 border-2 border-brand-600 text-brand-600 dark:text-brand-400 font-semibold rounded-full hover:bg-brand-50 dark:hover:bg-brand-900/30 transition">Log in</a>
                            @endauth
                        </div>
                    </div>
                    <div class="flex justify-center relative">
                        <div class="w-72 h-72 bg-brand-200/40 dark:bg-brand-500/20 rounded-full blur-3xl absolute -z-10"></div>
                        <i class="fas fa-chart-line text-8xl text-brand-500/60 dark:text-brand-400/40"></i>
                        <i class="fas fa-users text-7xl absolute bottom-0 right-10 text-brand-400/30 dark:text-brand-300/20"></i>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Grid -->
        <section id="services" class="py-20 bg-white dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Powerful Tools for Growth</h2>
                    <div class="w-20 h-1 bg-brand-500 mx-auto mt-4 rounded-full"></div>
                    <p class="mt-4 text-gray-600 dark:text-gray-400">Everything you need to scale your downline and earnings</p>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8 mt-12">
                    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-6 text-center shadow-md card-hover border border-gray-100 dark:border-gray-700">
                        <div class="w-14 h-14 bg-brand-100 dark:bg-brand-900/40 rounded-2xl flex items-center justify-center mx-auto"><i class="fas fa-chalkboard-user text-2xl text-brand-600"></i></div>
                        <h3 class="text-xl font-semibold mt-4 dark:text-white">Smart Dashboard</h3>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">Real‑time tree view, commission tracking, performance analytics.</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-6 text-center shadow-md card-hover border border-gray-100 dark:border-gray-700">
                        <div class="w-14 h-14 bg-brand-100 dark:bg-brand-900/40 rounded-2xl flex items-center justify-center mx-auto"><i class="fas fa-coins text-2xl text-brand-600"></i></div>
                        <h3 class="text-xl font-semibold mt-4 dark:text-white">Fast Payouts</h3>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">Daily, weekly settlements via UPI, Bank, Crypto – zero delays.</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-6 text-center shadow-md card-hover border border-gray-100 dark:border-gray-700">
                        <div class="w-14 h-14 bg-brand-100 dark:bg-brand-900/40 rounded-2xl flex items-center justify-center mx-auto"><i class="fas fa-people-arrows text-2xl text-brand-600"></i></div>
                        <h3 class="text-xl font-semibold mt-4 dark:text-white">Team Building</h3>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">Genealogy viewer, sponsor links, team messaging tools.</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-6 text-center shadow-md card-hover border border-gray-100 dark:border-gray-700">
                        <div class="w-14 h-14 bg-brand-100 dark:bg-brand-900/40 rounded-2xl flex items-center justify-center mx-auto"><i class="fas fa-headset text-2xl text-brand-600"></i></div>
                        <h3 class="text-xl font-semibold mt-4 dark:text-white">24/7 Support</h3>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">Dedicated relationship managers & priority ticket system.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- About + Stats (duaredokandar style) -->
        <section id="about" class="py-20 bg-gray-50 dark:bg-gray-800/30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Our <span class="text-brand-600">Journey</span> & Mission</h2>
                        <div class="w-20 h-1 bg-brand-500 rounded-full mt-3 mb-5"></div>
                        <p class="text-gray-600 dark:text-gray-300 text-lg leading-relaxed">Founded in 2021 with a vision to democratize wealth through ethical network marketing, we have empowered over 25,000+ entrepreneurs across India. Our proprietary technology ensures transparency, instant payouts, and a community‑first approach.</p>
                        <div class="mt-8 p-5 bg-white dark:bg-gray-800 rounded-2xl border-l-4 border-brand-500 shadow-sm">
                            <p class="italic text-gray-700 dark:text-gray-200">“We don’t just build networks; we build futures. Every member is a partner in our growth.”</p>
                            <p class="mt-3 font-semibold text-brand-600">— Rajesh Mehta, Founder & CEO</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 text-center shadow-lg">
                            <div class="text-4xl font-black text-brand-600">25k+</div>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">Active Members</p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 text-center shadow-lg">
                            <div class="text-4xl font-black text-brand-600">₹85Cr</div>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">Commissions Paid</p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 text-center shadow-lg">
                            <div class="text-4xl font-black text-brand-600">100%</div>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">Legal Compliant</p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 text-center shadow-lg">
                            <div class="text-4xl font-black text-brand-600">24/7</div>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">Dedicated Support</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- UPI QR Section (exactly as duaredokandar: sahin@ybl) -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 md:p-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-8">
                    <div class="text-center md:text-left">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white"><i class="fas fa-rupee-sign text-brand-600 mr-2"></i> Lightning Payouts</h3>
                        <p class="text-gray-500 dark:text-gray-400 mt-2">Withdraw your earnings instantly to UPI or Bank</p>
                        <div class="mt-4 inline-flex items-center gap-2 bg-gray-100 dark:bg-gray-700 px-4 py-2 rounded-full">
                            <i class="fab fa-google-pay text-xl"></i> <span class="font-mono font-bold">sahin@ybl</span>
                            <button onclick="copyUPI()" class="text-brand-600 hover:text-brand-700 text-sm ml-2"><i class="fas fa-copy"></i> Copy</button>
                        </div>
                        <p class="text-xs text-gray-400 mt-3">UPI ID / QR code for fast settlements</p>
                    </div>
                    <div class="bg-white dark:bg-gray-900 p-3 rounded-2xl shadow-md">
                        <img src="https://quickchart.io/qr?text=upi%3A%2F%2Fpay%3Fpa%3Dsahin%40ybl%26pn%3DMLM%20Nexus%26cu%3DINR&size=150&margin=2" alt="UPI QR" class="w-36 h-36 rounded-xl">
                        <p class="text-center text-xs mt-2">Scan to pay</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Section (with actual address from duaredokandar) -->
        <section id="contact" class="py-20 bg-gray-50 dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto">
                    <h2 class="text-3xl font-bold dark:text-white">Get in Touch</h2>
                    <div class="w-20 h-1 bg-brand-500 mx-auto mt-4 rounded-full"></div>
                    <p class="mt-4 text-gray-600 dark:text-gray-400">We're just a message away</p>
                </div>
                <div class="grid lg:grid-cols-2 gap-12 mt-12">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-md">
                        <div class="flex items-start gap-4 mb-6">
                            <i class="fas fa-map-marker-alt text-brand-500 text-xl mt-1"></i>
                            <div><h4 class="font-semibold dark:text-white">Registered Office</h4><p class="text-gray-500 dark:text-gray-400">Janaseba Mudi O Vandar, Goshaipur Bajar, PS Deganga, Dist North 24 Parganas, PIN 743423, West Bengal</p></div>
                        </div>
                        <div class="flex items-start gap-4 mb-6">
                            <i class="fas fa-phone-alt text-brand-500 text-xl mt-1"></i>
                            <div><h4 class="font-semibold dark:text-white">Call / WhatsApp</h4><p class="text-gray-500 dark:text-gray-400">+91 90910 90858</p></div>
                        </div>
                        <div class="flex items-start gap-4 mb-6">
                            <i class="fas fa-envelope text-brand-500 text-xl mt-1"></i>
                            <div><h4 class="font-semibold dark:text-white">Email</h4><p class="text-gray-500 dark:text-gray-400">info@duaredokandar.in</p></div>
                        </div>
                        <div class="flex gap-6 mt-8">
                            <a href="#" class="text-gray-500 hover:text-brand-500 text-2xl"><i class="fab fa-facebook"></i></a>
                            <a href="#" class="text-gray-500 hover:text-brand-500 text-2xl"><i class="fab fa-instagram"></i></a>
                            <a href="https://wa.me/919091090858?text=hiduaredokanda" class="text-gray-500 hover:text-brand-500 text-2xl"><i class="fab fa-whatsapp"></i></a>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-md">
                        <form id="contactForm" onsubmit="handleContactSubmit(event)">
                            <div class="mb-4"><input id="contact_name" type="text" placeholder="Your name" required class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500"></div>
                            <div class="mb-4"><input id="contact_email" type="email" placeholder="Email address" required class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></div>
                            <div class="mb-4"><textarea id="contact_message" rows="3" placeholder="Your message" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 resize-none"></textarea></div>
                            <button class="w-full py-3 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition">Send Message <i class="fas fa-paper-plane ml-2"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer with Terms link (modal) -->
    <footer class="bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 py-10">
        <div class="max-w-7xl mx-auto px-4 text-center text-gray-500 dark:text-gray-400">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'MLM App') }}. All rights reserved.</p>
            <div class="mt-3 space-x-4">
                <a href="#" @click.prevent="showTerms = true" class="hover:text-brand-600">Terms & Conditions</a>
                <a href="#contact" class="hover:text-brand-600">Privacy Policy</a>
            </div>
        </div>
    </footer>

    <!-- Terms Modal -->
    <div x-show="showTerms" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block">
            <div x-show="showTerms" x-transition.opacity class="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true" @click="showTerms = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showTerms" x-transition.scale class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Terms & Conditions</h3>
                    <button @click="showTerms = false" class="text-gray-400 hover:text-gray-500"><i class="fas fa-times text-xl"></i></button>
                </div>
                <div class="px-6 py-4 max-h-96 overflow-y-auto text-gray-700 dark:text-gray-300 space-y-4">
                    <h4 class="font-semibold">1. Introduction</h4>
                    <p>By using {{ config('app.name') }} platform, you agree to these Terms. Our MLM model is 100% legal and transparent.</p>
                    <h4 class="font-semibold mt-3">2. Membership & Commissions</h4>
                    <p>Members must be 18+ years. Commissions are calculated based on active sales volumes and team performance.</p>
                    <h4 class="font-semibold mt-3">3. Payouts</h4>
                    <p>Payments are processed within 48 hours after request. Minimum withdrawal: ₹500. UPI/Bank transfer.</p>
                    <h4 class="font-semibold mt-3">4. Prohibited Activities</h4>
                    <p>Spamming, misleading promotions, or fraudulent activity leads to immediate termination.</p>
                    <h4 class="font-semibold mt-3">5. Governing Law</h4>
                    <p>These terms are governed by the laws of India, jurisdiction at Kolkata, West Bengal.</p>
                </div>
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end">
                    <button @click="showTerms = false" class="px-4 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function copyUPI() {
            navigator.clipboard.writeText("sahin@ybl");
            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'UPI ID sahin@ybl copied to clipboard.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }

        function handleContactSubmit(e) {
            e.preventDefault();
            const name = document.getElementById('contact_name').value.trim();
            const email = document.getElementById('contact_email').value.trim();
            const message = document.getElementById('contact_message').value.trim();
            
            if (!name || !email || !message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please fill name, email and message.',
                });
                return;
            }

            Swal.fire({
                icon: 'success',
                title: 'Thank you!',
                text: 'Our team will contact you shortly.',
                confirmButtonColor: '#3085d6',
            }).then(() => {
                e.target.reset();
            });
        }
    </script>
</body>
</html>