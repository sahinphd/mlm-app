@extends('layouts.app')

@section('content')
<style>
/* Reveal on-scroll animation */
.reveal { opacity: 0; transform: translateY(18px); transition: opacity .6s ease, transform .6s cubic-bezier(.2,.9,.3,1); will-change: opacity, transform; }
.reveal.reveal--visible { opacity: 1; transform: none; }

/* Gentle floating accent */
@keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
.float-anim { animation: float 4s ease-in-out infinite; }

/* Slow shifting hero gradient */
@keyframes gradientShift { 0%{background-position:0% 50%} 50%{background-position:100% 50%} 100%{background-position:0% 50%} }
.hero-bg { background-size: 200% 200%; animation: gradientShift 10s ease infinite; }
</style>
<div class="bg-neutral-50 dark:bg-neutral-900">
    <!-- Hero Section – full width -->
    <section id="home" class="hero-bg w-full py-16 md:py-24 relative overflow-hidden reveal">
        <div class="absolute inset-0 -z-10 opacity-40 dark:opacity-20 pointer-events-none" aria-hidden="true">
            <svg viewBox="0 0 1440 500" class="w-full h-full" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="heroGradient" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0%" stop-color="#60A5FA" />
                        <stop offset="100%" stop-color="#3B82F6" />
                    </linearGradient>
                </defs>
                <path d="M0,120 C220,260 460,20 720,130 C980,240 1180,80 1440,170 L1440,0 L0,0 Z" fill="url(#heroGradient)"></path>
                <path d="M0,320 C280,420 480,230 760,310 C1040,390 1240,280 1440,340 L1440,500 L0,500 Z" fill="#93C5FD" fill-opacity="0.35"></path>
            </svg>
        </div>
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto grid md:grid-cols-2 gap-12 items-center align-middle">
                <div class="flex justify-evenly flex-col gap-4 md:gap-6 reveal">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold tracking-tight text-gray-900 dark:text-white">
                        Welcome to <span class="text-blue-600 bg-blue-100 dark:bg-blue-900/40 px-2 inline-block rounded-lg">Dua Re Dokandar</span>
                    </h1>
                    <p class="mt-6 text-lg text-gray-600 dark:text-gray-300">
                        Your trusted local shop for fresh goods, traditional spices, and daily essentials — now online.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        @guest
                            <a href="{{ route('register') }}" class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-full shadow-md hover:bg-blue-700 transition transform hover:-translate-y-1">
                                Get Started <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                            <a href="{{ route('login') }}" class="px-6 py-3 border-2 border-blue-600 text-blue-600 dark:text-blue-400 font-semibold rounded-full hover:bg-blue-50 dark:hover:bg-blue-900/30 transition">
                                Log in
                            </a>
                        @else
                            <a href="{{ url('/dashboard') }}" class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-full shadow-md hover:bg-blue-700 transition">
                                Dashboard →
                            </a>
                        @endguest
                    </div>
                </div>
                <div class="flex justify-center relative reveal">
                    <div class="w-72 h-72 bg-blue-200/40 dark:bg-blue-500/20 rounded-full blur-3xl absolute -z-10 float-anim"></div>
                    <i class="fas fa-store text-8xl text-blue-500/60 dark:text-blue-400/40 float-anim"></i>
                    <i class="fas fa-leaf text-7xl absolute bottom-0 right-10 text-blue-400/30 dark:text-blue-300/20 float-anim"></i>
                </div>
            </div>
        </div>

        <div class="px-4 sm:px-6 lg:px-8 mt-10 md:mt-14">
            <div class="max-w-7xl mx-auto">
                <div class="relative bg-white/80 dark:bg-gray-800/70 backdrop-blur rounded-3xl border border-blue-100 dark:border-gray-700 p-6 md:p-8 overflow-hidden">
                    <div class="absolute -right-8 -top-8 w-40 h-40 rounded-full bg-blue-200/50 dark:bg-blue-500/20 blur-2xl"></div>
                    <div class="absolute -left-6 -bottom-6 w-28 h-28 rounded-full bg-cyan-200/50 dark:bg-cyan-500/20 blur-xl"></div>

                    <div class="relative grid md:grid-cols-2 gap-8 items-center reveal">
                        <div>
                            <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">Freshness in Motion</h3>
                            <p class="mt-3 text-gray-600 dark:text-gray-300">From local market pickup to your doorstep delivery, our service moves fast and stays reliable.</p>
                        </div>

                        <div class="relative h-40 flex items-center justify-center reveal">
                            <svg viewBox="0 0 320 140" class="w-full h-full" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M20 90 C80 20, 160 20, 300 90" stroke="#60A5FA" stroke-width="4" fill="none" stroke-linecap="round" stroke-dasharray="8 8"></path>
                            </svg>
                            <div class="absolute animate-bounce bg-blue-600 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg">
                                <i class="fas fa-motorcycle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Grid – full width -->
    <section id="services" class="w-full py-20 bg-white dark:bg-gray-900">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="text-center max-w-2xl mx-auto">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Our Offerings</h2>
                    <div class="w-20 h-1 bg-blue-500 mx-auto mt-4 rounded-full"></div>
                    <p class="mt-4 text-gray-600 dark:text-gray-400">Fresh from our dokandar to your doorstep</p>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8 mt-12">
                    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-6 text-center shadow-md hover:shadow-xl transition-all hover:-translate-y-1 border border-gray-100 dark:border-gray-700 reveal">
                        <div class="w-14 h-14 bg-blue-100 dark:bg-blue-900/40 rounded-2xl flex items-center justify-center mx-auto">
                            <i class="fas fa-apple-alt text-2xl text-blue-600"></i>
                        </div>
                        <h3 class="text-xl font-semibold mt-4 dark:text-white">Fresh Grocery</h3>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">Fruits, vegetables, daily essentials sourced from local farmers.</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-6 text-center shadow-md hover:shadow-xl transition-all hover:-translate-y-1 border border-gray-100 dark:border-gray-700 reveal">
                        <div class="w-14 h-14 bg-blue-100 dark:bg-blue-900/40 rounded-2xl flex items-center justify-center mx-auto">
                            <i class="fas fa-mortar-pestle text-2xl text-blue-600"></i>
                        </div>
                        <h3 class="text-xl font-semibold mt-4 dark:text-white">Spices & Masala</h3>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">Authentic spice blends and traditional condiments.</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-6 text-center shadow-md hover:shadow-xl transition-all hover:-translate-y-1 border border-gray-100 dark:border-gray-700 reveal">
                        <div class="w-14 h-14 bg-blue-100 dark:bg-blue-900/40 rounded-2xl flex items-center justify-center mx-auto">
                            <i class="fas fa-gift text-2xl text-blue-600"></i>
                        </div>
                        <h3 class="text-xl font-semibold mt-4 dark:text-white">Gift Boxes</h3>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">Special thalis and gift packs for festivals.</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-6 text-center shadow-md hover:shadow-xl transition-all hover:-translate-y-1 border border-gray-100 dark:border-gray-700 reveal">
                        <div class="w-14 h-14 bg-blue-100 dark:bg-blue-900/40 rounded-2xl flex items-center justify-center mx-auto">
                            <i class="fas fa-truck text-2xl text-blue-600"></i>
                        </div>
                        <h3 class="text-xl font-semibold mt-4 dark:text-white">Home Delivery</h3>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">Fast and free delivery — just call or WhatsApp.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About + Stats – full width -->
    <section id="about" class="w-full py-20 bg-neutral-50 dark:bg-gray-900/50">
        <div class="px-4 sm:px-6 lg:px-8">
                <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-12 items-center">
                <div class="reveal">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Dua Re <span class="text-blue-600">Dokandar</span></h2>
                    <div class="w-20 h-1 bg-blue-500 rounded-full mt-3 mb-5"></div>
                    <p class="text-gray-600 dark:text-gray-300 text-lg leading-relaxed">“Dua” means prayer, “Dokandar” means shopkeeper — we blend tradition with trust. Since 1985, our family-run shop has been a cornerstore for quality and warmth.</p>
                    <div class="mt-8 p-5 bg-white dark:bg-gray-800 rounded-2xl border-l-4 border-blue-500 shadow-sm">
                        <p class="italic text-gray-700 dark:text-gray-200">Every grain, every spice is handpicked. Now we bring the same care to our website.</p>
                        <p class="mt-3 font-semibold text-blue-600">— MD Lalbabu, Proprietor</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 text-center shadow-lg reveal">
                        <div class="text-4xl font-black text-blue-600">39+</div>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">Years Serving</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 text-center shadow-lg reveal">
                        <div class="text-4xl font-black text-blue-600">24/7</div>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">Fresh Arrival</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 text-center shadow-lg reveal">
                        <div class="text-4xl font-black text-blue-600">100%</div>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">Swadeshi</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 text-center shadow-lg reveal">
                        <div class="text-4xl font-black text-blue-600">5k+</div>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">Happy Families</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- UPI QR SECTION – reads from env('APP_QR_CODE') or custom settings -->
    @php
        $useCustomQr = ($systemSettings['use_custom_qr'] ?? 'off') === 'on';
        if ($useCustomQr && !empty($systemSettings['payment_qr_path'])) {
            $upiId = $systemSettings['custom_upi_id'] ?? env('APP_QR_CODE', 'sahinahmed.com@ybl');
            $qrUrl = asset($systemSettings['payment_qr_path']);
        } else {
            $upiId = env('APP_QR_CODE', 'sahinahmed.com@ybl');
            $qrUrl = 'https://quickchart.io/qr?text=upi%3A%2F%2Fpay%3Fpa%3D' . urlencode($upiId) . '%26pn%3DDuare%20Dokandar%26cu%3DINR&size=160&margin=2';
        }
    @endphp

    <div class="w-full px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 md:p-8 reveal">
                <div class="flex flex-col md:flex-row items-center justify-between gap-8">
                    <div class="text-center md:text-left">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white"><i class="fas fa-rupee-sign text-blue-600 mr-2"></i> Support Application</h3>
                        <p class="text-gray-500 dark:text-gray-400 mt-2">Pay directly via UPI – fast & secure</p>
                        <div class="mt-4 inline-flex items-center gap-2 bg-gray-100 dark:bg-gray-700 px-4 py-2 rounded-full">
                            <i class="fab fa-google-pay text-xl"></i> <span class="font-mono font-bold">{{ $upiId }}</span>
                            <button onclick="copyUPI('{{ $upiId }}')" class="text-blue-600 hover:text-blue-700 text-sm ml-2"><i class="fas fa-copy"></i> Copy</button>
                        </div>
                        <p class="text-xs text-gray-400 mt-3">Scan QR or use UPI ID to pay</p>
                    </div>
                    <div class="bg-white dark:bg-gray-900 p-3 rounded-2xl shadow-md">
                        <img src="{{ $qrUrl }}" alt="UPI QR" class="w-36 h-36 rounded-xl">
                        <p class="text-center text-xs mt-2">Scan to pay</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Section – full width -->
    <section id="contact" class="w-full py-20 bg-neutral-50 dark:bg-gray-900">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="text-center max-w-2xl mx-auto">
                    <h2 class="text-3xl font-bold dark:text-white">Talk to Dokandar</h2>
                    <div class="w-20 h-1 bg-blue-500 mx-auto mt-4 rounded-full"></div>
                    <p class="mt-4 text-gray-600 dark:text-gray-400">We reply within a few hours, usually faster than chai time ☕</p>
                </div>
                <div class="grid lg:grid-cols-2 gap-12 mt-12">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-md reveal">
                        <div class="flex items-start gap-4 mb-6">
                            <i class="fas fa-map-marker-alt text-blue-500 text-xl mt-1"></i>
                            <div><h4 class="font-semibold dark:text-white">📍 duaredokandar.in</h4><p class="text-gray-500 dark:text-gray-400">Janaseba Mudi O Vandar, Goshaipur Bajar, PS Deganga, Dist North 24 Parganas, PIN 743423, West Bengal</p></div>
                        </div>
                        <div class="flex items-start gap-4 mb-6">
                            <i class="fas fa-phone-alt text-blue-500 text-xl mt-1"></i>
                            <div><h4 class="font-semibold dark:text-white">Call / WhatsApp</h4><p class="text-gray-500 dark:text-gray-400">+91 90910 90858</p></div>
                        </div>
                        <div class="flex items-start gap-4 mb-6">
                            <i class="fas fa-envelope text-blue-500 text-xl mt-1"></i>
                            <div><h4 class="font-semibold dark:text-white">Email</h4><p class="text-gray-500 dark:text-gray-400">info@duaredokandar.in</p></div>
                        </div>
                        <div class="flex gap-6 mt-8">
                            <a href="#" class="text-gray-500 hover:text-blue-500 text-2xl"><i class="fab fa-facebook"></i></a>
                            <a href="#" class="text-gray-500 hover:text-blue-500 text-2xl"><i class="fab fa-instagram"></i></a>
                            <a href="https://wa.me/919091090858?text=hiduaredokanda" class="text-gray-500 hover:text-blue-500 text-2xl"><i class="fab fa-whatsapp"></i></a>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-md reveal">
                        <h3 class="text-xl font-semibold mb-4 dark:text-white">पूछताछ करें</h3>
                        <form id="contactForm" onsubmit="handleContactSubmit(event)">
                            <div class="mb-4"><input id="contact_name" name="name" type="text" placeholder="Your name" required class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"></div>
                            <div class="mb-4"><input id="contact_email" name="email" type="email" placeholder="Email address" required class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></div>
                            <div class="mb-4"><textarea id="contact_message" name="message" rows="3" placeholder="What are you looking for?" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 resize-none"></textarea></div>
                            <button type="submit" class="w-full py-3 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition">Send Message <i class="fas fa-paper-plane ml-2"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer with Terms Modal -->
    <div x-data="{ showTerms: false }" x-cloak>
        <div x-show="showTerms" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block">
                <div x-show="showTerms" x-transition.opacity class="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true" @click="showTerms = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showTerms" x-transition.scale class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
                    <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">📜 Terms & Conditions</h3>
                        <button @click="showTerms = false" class="text-gray-400 hover:text-gray-500"><i class="fas fa-times text-xl"></i></button>
                    </div>
                    <div class="px-6 py-4 max-h-96 overflow-y-auto text-gray-700 dark:text-gray-300 space-y-4">
                        <h4 class="font-semibold">1. Introduction</h4>
                        <p>Welcome to duaredokandar.in. By accessing our website and services, you agree to comply with these Terms & Conditions.</p>
                        <h4 class="font-semibold mt-3">2. Use of Website</h4>
                        <p>You agree to use our website for lawful purposes only. All product information, pricing, and availability are subject to change without notice.</p>
                        <h4 class="font-semibold mt-3">3. Orders & Payments</h4>
                        <p>Orders placed via WhatsApp, phone, or contact form are confirmed by our team. Payments can be made via cash on delivery or UPI ({{ $upiId }}).</p>
                        <h4 class="font-semibold mt-3">4. Delivery & Returns</h4>
                        <p>We offer free local delivery within Goshaipur & nearby areas. Returns are accepted within 24 hours for damaged/fresh produce issues.</p>
                        <h4 class="font-semibold mt-3">5. Account Registration</h4>
                        <p>You may register via our app at <a href="https://app.duaredokandar.in/register" target="_blank" class="text-blue-600">app.duaredokandar.in</a>. You are responsible for your login credentials.</p>
                        <h4 class="font-semibold mt-3">6. Governing Law</h4>
                        <p>These terms are governed by the laws of West Bengal, India. Any disputes will be subject to the jurisdiction of local courts in North 24 Parganas.</p>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end">
                        <button @click="showTerms = false" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <footer class="w-full bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 py-6">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="max-w-7xl mx-auto text-center text-gray-500 dark:text-gray-400 text-sm">
                    <p>© {{ date('Y') }} duaredokandar.in — responsive, real, relatable.</p>
                    <p class="mt-2">
                        Crafted with <i class="fas fa-heart text-red-500"></i> for the neighbourhood &nbsp;|&nbsp;
                        <a href="#" @click.prevent="showTerms = true" class="hover:text-blue-600">Terms & Conditions</a>
                    </p>
                </div>
            </div>
        </footer>
    </div>
</div>

<script>
    function copyUPI(upiId) {
        navigator.clipboard.writeText(upiId);
        Swal.fire({
            icon: 'success',
            title: 'Copied!',
            text: 'UPI ID ' + upiId + ' copied to clipboard.',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const reveals = Array.from(document.querySelectorAll('.reveal'));
        if ('IntersectionObserver' in window) {
            const io = new IntersectionObserver((entries, obs) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('reveal--visible');
                        obs.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12 });
            reveals.forEach(el => io.observe(el));
        } else {
            // Fallback for older browsers
            reveals.forEach((el, i) => setTimeout(() => el.classList.add('reveal--visible'), i * 80));
        }
    });

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

        // Admin WhatsApp number (international format, no + or leading zeros)
        const adminPhone = '919091090858';
        const text = `New contact from ${name} (${email})%0A%0A${message}`;
        const waUrl = `https://wa.me/${adminPhone}?text=${encodeURIComponent(text)}`;

        // Open WhatsApp in new tab/window
        window.open(waUrl, '_blank');

        // Optional UX: show confirmation to user
        Swal.fire({
            icon: 'info',
            title: 'Redirecting...',
            text: 'Opening WhatsApp to send your message to the admin.',
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false
        });
        
        // Clear form
        e.target.reset();
    }
</script>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
@endsection