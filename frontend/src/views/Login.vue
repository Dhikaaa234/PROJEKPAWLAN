<template>
  <AuthLayout
    headline-top="FilkomCare"
    description="Streamlining facility management and issue reporting for the Faculty of Computer Science. Clear, efficient, and transparent."
  >
    <div data-testid="login-page">
      <header class="mb-8">
        <h2 class="font-display text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">
          Welcome Back
        </h2>
        <p class="mt-2 text-base text-slate-500">
          Please sign in to access the facility management portal.
        </p>
      </header>

      <form @submit.prevent="onSubmit" class="space-y-5" data-testid="login-form">
        <div>
          <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">Email Address</label>
          <div class="relative">
            <img
              src="/images/icon-email.png"
              alt=""
              class="field-icon"
            />
            <input
              id="email"
              v-model="email"
              type="email"
              required
              placeholder="student@filkom.edu"
              class="field-input"
            />
          </div>
        </div>

        <div>
          <div class="flex items-center justify-between mb-2">
            <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
            <router-link
              to="/forgot-password"
              class="text-sm font-semibold text-brand-blue hover:text-brand-blueHover"
            >
              Forgot password?
            </router-link>
          </div>

          <div class="relative">
            <img
              src="/images/icon-password.png"
              alt=""
              class="field-icon"
            />
            <input
              id="password"
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              required
              minlength="6"
              placeholder="••••••••"
              class="field-input pr-11"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition"
              :aria-label="showPassword ? 'Hide password' : 'Show password'"
            >
              <i :class="showPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
            </button>
          </div>
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer select-none">
          <input
            v-model="remember"
            type="checkbox"
            class="w-4 h-4 rounded border-slate-300 text-brand-blue focus:ring-brand-blue"
          />
          Remember me for 30 days
        </label>

        <button type="submit" class="btn-primary" :disabled="auth.loading">
          <span v-if="!auth.loading">Sign in to FilkomCare</span>
          <span v-else class="inline-flex items-center gap-2">
            <i class="fa-solid fa-circle-notch fa-spin"></i> Signing in…
          </span>
        </button>

        <p
          v-if="auth.lastMessage"
          class="text-sm text-emerald-600 bg-emerald-50 border border-emerald-100 rounded-lg px-3 py-2"
        >
          <i class="fa-solid fa-circle-check mr-1"></i> {{ auth.lastMessage }}
        </p>

        <p class="text-center text-sm text-slate-600">
          Don't have an account?
          <router-link to="/register" class="font-semibold text-brand-blue hover:text-brand-blueHover">
            Register here
          </router-link>
        </p>

        <div class="flex items-center gap-3 pt-2">
          <span class="h-px bg-slate-200 flex-1"></span>
          <span class="text-xs tracking-[0.2em] text-slate-400 font-semibold">OR SIGN IN WITH</span>
          <span class="h-px bg-slate-200 flex-1"></span>
        </div>

        <button type="button" class="btn-secondary">
          <img
            src="/images/icon-edu.png"
            alt=""
            class="w-5 h-5"
          />
          University SSO
        </button>
      </form>
    </div>
  </AuthLayout>
</template>

<script setup>
import { ref } from 'vue'
import AuthLayout from '../components/AuthLayout.vue'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const email = ref('')
const password = ref('')
const remember = ref(false)
const showPassword = ref(false)

async function onSubmit() {
  await auth.login({ email: email.value, password: password.value, remember: remember.value })
}
</script>
