<template>
  <AuthLayout
    headline-top="Reset your"
    headline-accent="Access"
    description="We'll email you a secure link to reset your password and regain access to FilkomCare."
  >
    <div data-testid="forgot-page">
      <router-link
        to="/login"
        class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-slate-900 transition mb-6"
      >
        <i class="fa-solid fa-arrow-left"></i> Back to sign in
      </router-link>

      <header class="mb-8">
        <h2 class="font-display text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">
          Forgot Password?
        </h2>
        <p class="mt-2 text-base text-slate-500">
          Enter your faculty email and we'll send you instructions to reset your password.
        </p>
      </header>

      <form @submit.prevent="onSubmit" class="space-y-5">
        <div>
          <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">Email Address</label>
          <div class="relative">
            <i class="field-icon fa-regular fa-envelope"></i>
            <input id="email" v-model="email" type="email" required placeholder="student@filkom.edu" class="field-input" />
          </div>
        </div>

        <button type="submit" class="btn-primary" :disabled="auth.loading">
          <span v-if="!auth.loading">Send reset link</span>
          <span v-else class="inline-flex items-center gap-2">
            <i class="fa-solid fa-circle-notch fa-spin"></i> Sending…
          </span>
        </button>

        <div v-if="sent" class="rounded-lg border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
          <p class="font-semibold flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i> Check your inbox
          </p>
          <p class="mt-1 text-emerald-700/80">
            If an account exists for <strong>{{ email }}</strong>, a reset link will arrive shortly.
          </p>
        </div>

        <div class="rounded-lg bg-slate-50 border border-slate-100 px-4 py-3 text-sm text-slate-600">
          <p class="font-semibold text-slate-800 mb-1 flex items-center gap-2">
            <i class="fa-solid fa-circle-info text-brand-blue"></i> Need more help?
          </p>
          Reach out to the IT helpdesk at
          <a href="mailto:helpdesk@filkom.edu" class="text-brand-blue font-semibold hover:underline">helpdesk@filkom.edu</a>.
        </div>

        <p class="text-center text-sm text-slate-600">
          Remembered it?
          <router-link to="/login" class="font-semibold text-brand-blue hover:text-brand-blueHover">
            Sign in instead
          </router-link>
        </p>
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
const sent = ref(false)

async function onSubmit() {
  await auth.sendResetLink(email.value)
  sent.value = true
}
</script>
