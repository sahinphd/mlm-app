@extends('layouts.app')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center bg-neutral-50 dark:bg-neutral-900">
    <div class="w-full max-w-md px-6">
        <div class="bg-white dark:bg-[#0b1221] rounded-xl shadow-lg p-8">
            <h2 class="text-center text-2xl font-semibold mb-2">Create an account</h2>
            <p class="text-center text-sm text-neutral-500 mb-4">Already have an Account ? <a href="/login" class="text-blue-600">Sign In</a></p>

            <!-- Social signups removed per request -->

            <form method="POST" action="/register" class="space-y-4" id="signupForm">
                @csrf
                <div>
                    <label class="block text-sm text-neutral-600 mb-1">Full name</label>
                    <input name="name" type="text" required placeholder="Your full name" class="mt-1 block w-full border border-neutral-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200">
                </div>

                <div>
                    <label class="block text-sm text-neutral-600 mb-1">Email</label>
                    <input name="email" type="email" required placeholder="email@email.com" class="mt-1 block w-full border border-neutral-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200">
                </div>

                <div>
                    <label class="block text-sm text-neutral-600 mb-1">Phone</label>
                    <div class="flex gap-2">
                        <select name="country" required class="mt-1 block w-36 border border-neutral-200 rounded-md px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-200">
                            <option value="+91" selected>India (+91)</option>
                            <option value="+1">United States (+1)</option>
                            <option value="+44">United Kingdom (+44)</option>
                            <option value="+61">Australia (+61)</option>
                            <option value="+92">Pakistan (+92)</option>
                        </select>
                        <input name="phone" type="tel" required placeholder="8123456789" class="mt-1 block w-full border border-neutral-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-neutral-600 mb-1">Password</label>
                    <div class="relative">
                        <input name="password" type="password" required placeholder="Enter Password" class="mt-1 block w-full border border-neutral-200 rounded-md px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-neutral-400">👁️</button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-neutral-600 mb-1">Confirm Password</label>
                    <div class="relative">
                        <input name="password_confirmation" type="password" required placeholder="Re-enter Password" class="mt-1 block w-full border border-neutral-200 rounded-md px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-neutral-400">👁️</button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-neutral-600 mb-1">Referral code (optional)</label>
                    <input name="referral" type="text" placeholder="Referral code" class="mt-1 block w-full border border-neutral-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200">
                </div>

                <div class="flex items-center gap-2">
                    <input id="agree" type="checkbox" required class="w-4 h-4">
                    <label for="agree" class="text-sm">I accept <a href="#" class="text-blue-600">Terms & Conditions</a></label>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-blue-600 text-white rounded-md py-2.5 font-medium">Sign up</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(()=>{
  document.querySelectorAll('button[type="button"]').forEach(b=>{
    b.addEventListener('click', e=>{
      const inp = b.previousElementSibling;
      if (!inp) return;
      inp.type = inp.type === 'password' ? 'text' : 'password';
      b.textContent = inp.type === 'password' ? '👁️' : '🙈';
    });
  });

  const form = document.getElementById('signupForm');
  if (form) {
    form.addEventListener('submit', function(e){
      const agree = document.getElementById('agree');
      if (!agree.checked) {
        e.preventDefault();
        alert('Please accept Terms & Conditions to continue.');
        return false;
      }
    });
  }
})();
</script>
@endpush
