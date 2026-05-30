<template>
  <AuthLayout
    headline-top="Create new"
    headline-accent="Password"
    description="Set a secure new password for your FilkomCare account and continue reporting facility issues safely."
  >
    <div data-testid="reset-password-page">
      <router-link
        to="/login"
        class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-slate-900"
      >
        <i class="fa-solid fa-arrow-left"></i>
        Back to sign in
      </router-link>

      <header class="mb-8">
        <h2 class="font-display text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
          Reset Password
        </h2>
        <p class="mt-2 text-base text-slate-500">
          Enter your new password below to regain access to your account.
        </p>
      </header>

      <form @submit.prevent="onSubmit" class="space-y-5">
        <div>
          <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">
            Email Address
          </label>
          <div class="relative">
            <i class="field-icon fa-regular fa-envelope"></i>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              :readonly="hasEmailFromQuery"
              placeholder="student@filkom.edu"
              class="field-input"
              :class="hasEmailFromQuery ? 'bg-slate-50 text-slate-500' : ''"
            />
          </div>
        </div>

        <div>
          <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">
            New Password
          </label>
          <div class="relative">
            <i class="field-icon fa-solid fa-lock"></i>
            <input
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              required
              minlength="6"
              placeholder="••••••••"
              class="field-input pr-11"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-700"
              :aria-label="showPassword ? 'Hide password' : 'Show password'"
            >
              <i :class="showPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
            </button>
          </div>
        </div>

        <div>
          <label for="password-confirmation" class="mb-2 block text-sm font-semibold text-slate-700">
            Confirm New Password
          </label>
          <div class="relative">
            <i class="field-icon fa-solid fa-shield-halved"></i>
            <input
              id="password-confirmation"
              v-model="form.password_confirmation"
              :type="showConfirmation ? 'text' : 'password'"
              required
              minlength="6"
              placeholder="••••••••"
              class="field-input pr-11"
            />
            <button
              type="button"
              @click="showConfirmation = !showConfirmation"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-700"
              :aria-label="showConfirmation ? 'Hide password confirmation' : 'Show password confirmation'"
            >
              <i :class="showConfirmation ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn-primary" :disabled="isLoading || !form.token">
          <span v-if="!isLoading">Reset Password</span>
          <span v-else class="inline-flex items-center gap-2">
            <i class="fa-solid fa-circle-notch fa-spin"></i>
            Resetting...
          </span>
        </button>

        <div
          v-if="errorMessage"
          class="rounded-lg border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700"
        >
          <p class="flex items-center gap-2 font-semibold">
            <i class="fa-solid fa-circle-xmark"></i>
            Error
          </p>
          <p class="mt-1">{{ errorMessage }}</p>
        </div>

        <div
          v-if="successMessage"
          class="rounded-lg border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
        >
          <p class="flex items-center gap-2 font-semibold">
            <i class="fa-solid fa-circle-check"></i>
            Password updated
          </p>
          <p class="mt-1 text-emerald-700/80">{{ successMessage }}</p>
        </div>

        <div
          v-if="!form.token"
          class="rounded-lg border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-700"
        >
          <p class="flex items-center gap-2 font-semibold">
            <i class="fa-solid fa-triangle-exclamation"></i>
            Invalid reset link
          </p>
          <p class="mt-1">Please request a new password reset link from the forgot password page.</p>
        </div>

        <p class="text-center text-sm text-slate-600">
          Need a new link?
          <router-link to="/forgot-password" class="font-semibold text-brand-blue hover:text-brand-blueHover">
            Request reset link
          </router-link>
        </p>
      </form>
    </div>
  </AuthLayout>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AuthLayout from '../components/AuthLayout.vue'
import api from '../services/api'

const route = useRoute()
const router = useRouter()

const isLoading = ref(false)
const showPassword = ref(false)
const showConfirmation = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const form = reactive({
  // Token dan email berasal dari link email: /reset-password?token=...&email=...
  token: String(route.query.token ?? ''),
  email: String(route.query.email ?? ''),
  password: '',
  password_confirmation: '',
})

const hasEmailFromQuery = computed(() => Boolean(route.query.email))

function getErrorMessage(error) {
  const errors = error.response?.data?.errors

  if (errors) {
    return Object.values(errors).flat().join(' ')
  }

  return error.response?.data?.message || error.message || 'Failed to reset password. Please try again.'
}

async function onSubmit() {
  errorMessage.value = ''
  successMessage.value = ''

  // Validasi ringan di frontend sebelum dikirim ke backend.
  if (form.password !== form.password_confirmation) {
    errorMessage.value = 'Password confirmation does not match.'
    return
  }

  isLoading.value = true

  try {
    // Backend memvalidasi token reset password dan menyimpan password baru.
    await api.post('/reset-password', {
      token: form.token,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation,
    })

    successMessage.value = 'Your password has been reset. Redirecting to sign in...'

    setTimeout(() => {
      router.push('/login')
    }, 1500)
  } catch (error) {
    errorMessage.value = getErrorMessage(error)
  } finally {
    isLoading.value = false
  }
}
</script>
