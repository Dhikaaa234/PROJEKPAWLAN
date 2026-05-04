<template>
  <div class="min-h-screen w-full grid lg:grid-cols-2" data-testid="auth-layout">
    <aside
      class="relative hidden lg:flex flex-col justify-between overflow-hidden bg-brand-deep text-white p-10 xl:p-14"
      data-testid="auth-hero"
    >
      <div class="absolute inset-0 bg-gradient-to-b from-[#0b2545] via-[#0a1f3d] to-[#06122a]"></div>
      <div class="absolute inset-0 hero-grid opacity-60"></div>

      <div class="absolute inset-0 pointer-events-none">
        <span
          v-for="(s, i) in stars"
          :key="i"
          class="absolute rounded-full bg-white twinkle"
          :style="{ top: s.top, left: s.left, width: s.size, height: s.size, animationDelay: s.delay }"
        ></span>
      </div>

      <svg class="absolute bottom-0 left-0 right-0 w-full" viewBox="0 0 800 320" preserveAspectRatio="none" aria-hidden="true">
        <defs>
          <linearGradient id="cityFade" x1="0" x2="0" y1="0" y2="1">
            <stop offset="0%" stop-color="#0b2545" stop-opacity="0"/>
            <stop offset="100%" stop-color="#06122a" stop-opacity="0.95"/>
          </linearGradient>
        </defs>
        <path d="M0 240 Q40 215 80 240 T160 240 T240 240 T320 240 L320 320 L0 320 Z" fill="#0a1d36" opacity="0.85"/>
        <path d="M480 240 Q520 215 560 240 T640 240 T720 240 T800 240 L800 320 L480 320 Z" fill="#0a1d36" opacity="0.85"/>

        <g fill="#142a4d" stroke="#1d3a66" stroke-width="1">
          <rect x="240" y="200" width="320" height="120"/>
          <rect x="260" y="220" width="10" height="100" fill="#1b3766"/>
          <rect x="290" y="220" width="10" height="100" fill="#1b3766"/>
          <rect x="320" y="220" width="10" height="100" fill="#1b3766"/>
          <rect x="470" y="220" width="10" height="100" fill="#1b3766"/>
          <rect x="500" y="220" width="10" height="100" fill="#1b3766"/>
          <rect x="530" y="220" width="10" height="100" fill="#1b3766"/>
          <polygon points="350,200 450,200 450,180 400,160 350,180"/>
          <rect x="380" y="120" width="40" height="40" fill="#142a4d"/>
          <ellipse cx="400" cy="120" rx="30" ry="24" fill="#1b3766"/>
          <rect x="397" y="80" width="6" height="20" fill="#f5c542"/>
          <circle cx="400" cy="78" r="4" fill="#f5c542"/>
          <g fill="#f5c542" opacity="0.85">
            <rect x="350" y="220" width="10" height="14"/>
            <rect x="370" y="220" width="10" height="14"/>
            <rect x="420" y="220" width="10" height="14"/>
            <rect x="440" y="220" width="10" height="14"/>
            <rect x="350" y="250" width="10" height="14"/>
            <rect x="370" y="250" width="10" height="14"/>
            <rect x="420" y="250" width="10" height="14"/>
            <rect x="440" y="250" width="10" height="14"/>
          </g>
        </g>

        <rect x="0" y="0" width="800" height="320" fill="url(#cityFade)"/>
      </svg>

      <div class="relative z-10 flex items-center gap-3">
        <div class="w-11 h-11 rounded-lg bg-brand-gold/15 ring-1 ring-brand-gold/30 grid place-items-center">
          <img
            src="/images/icon-gedung.png"
            alt="FilkomCare"
            class="w-6 h-6"
          />
        </div>
        <span class="font-display font-bold tracking-tight text-lg">FilkomCare</span>
      </div>

      <div class="relative z-10 max-w-md">
        <h1 class="font-display text-5xl xl:text-6xl font-extrabold leading-[1.05] tracking-tight">
          {{ headlineTop }}<br />
          <span class="text-brand-gold">{{ headlineAccent }}</span>
        </h1>
        <p class="mt-6 text-base xl:text-lg text-slate-300 leading-relaxed">
          {{ description }}
        </p>
      </div>

      <div class="relative z-10">
        <div class="inline-flex items-center gap-3 px-4 py-3 rounded-xl bg-white/5 backdrop-blur-md border border-white/10">
          <div class="w-9 h-9 rounded-md bg-white/10 grid place-items-center">
            <img
              src="/images/icon-shield.png"
              alt=""
              class="w-5 h-5"
            />
          </div>
          <div class="leading-tight">
            <p class="text-sm font-semibold">Official University Portal</p>
            <p class="text-xs text-slate-400">Secure &amp; Monitored Access</p>
          </div>
        </div>
      </div>
    </aside>

    <main class="flex flex-col min-h-screen bg-white">
      <div class="lg:hidden flex items-center gap-3 px-6 py-5 border-b border-slate-100">
        <div class="w-9 h-9 rounded-md bg-brand-deep grid place-items-center">
          <i class="fa-solid fa-building-columns text-brand-gold"></i>
        </div>
        <span class="font-display font-bold text-brand-deep">FilkomCare</span>
      </div>

      <div class="flex-1 flex items-center justify-center px-6 py-10 sm:px-10">
        <div class="w-full max-w-md">
          <slot />
        </div>
      </div>

      <footer class="border-t border-slate-100 px-6 py-5 sm:px-10 flex flex-wrap items-center justify-end gap-x-6 gap-y-2 text-sm text-slate-500">
        <a href="#" class="hover:text-slate-900 transition">Privacy Policy</a>
        <span class="text-slate-300">•</span>
        <a href="#" class="hover:text-slate-900 transition">Terms of Service</a>
        <span class="text-slate-300">•</span>
        <a href="#" class="hover:text-slate-900 transition">Help Center</a>
      </footer>
    </main>
  </div>
</template>

<script setup>
defineProps({
  headlineTop: { type: String, default: 'FilkomCare' },
  headlineAccent: { type: String, default: '' },
  description: {
    type: String,
    default: 'Streamlining facility management and issue reporting for the Faculty of Computer Science. Clear, efficient, and transparent.'
  }
})

const stars = Array.from({ length: 22 }).map(() => ({
  top: `${Math.random() * 55}%`,
  left: `${Math.random() * 95}%`,
  size: `${Math.random() * 2 + 1}px`,
  delay: `${Math.random() * 3}s`
}))
</script>
