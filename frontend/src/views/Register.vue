<template>
  <AuthLayout
    headline-top="Join the"
    headline-accent="Faculty Network"
    description="Create your FilkomCare account to report facility issues, track requests, and stay informed about campus services."
  >
    <div data-testid="register-page">
      <header class="mb-8">
        <h2 class="font-display text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">
          Create Account
        </h2>
        <p class="mt-2 text-base text-slate-500">
          Use your faculty email to register and start submitting reports.
        </p>
      </header>

      <form @submit.prevent="onSubmit" class="space-y-5">
        <div>
          <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Full Name</label>
          <div class="relative">
            <i class="field-icon fa-regular fa-user"></i>
            <input id="name" v-model="form.name" type="text" required placeholder="Budi Santoso" class="field-input" />
          </div>
        </div>

        <div>
          <label for="nim" class="block text-sm font-semibold text-slate-700 mb-2">Student ID (NIM)</label>
          <div class="relative">
            <i class="field-icon fa-solid fa-id-card"></i>
            <input id="nim" v-model="form.nim" type="text" required placeholder="225150200111000" class="field-input" />
          </div>
        </div>

        <div>
          <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">Email Address</label>
          <div class="relative">
            <i class="field-icon fa-regular fa-envelope"></i>
            <input id="email" v-model="form.email" type="email" required placeholder="student@filkom.edu" class="field-input" />
          </div>
        </div>

        <div>
          <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
          <div class="relative">
            <i class="field-icon fa-solid fa-lock"></i>
            <input
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              required
              minlength="6"
              placeholder="At least 6 characters"
              class="field-input pr-11"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition"
            >
              <i :class="showPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
            </button>
          </div>
          <PasswordStrength :value="form.password" class="mt-2" />
        </div>

        <label class="flex items-start gap-2 text-sm text-slate-600 cursor-pointer select-none">
          <input
            v-model="agree"
            type="checkbox"
            required
            class="mt-0.5 w-4 h-4 rounded border-slate-300 text-brand-blue focus:ring-brand-blue"
          />
          <span>
            I agree to the
            <a href="#" class="font-semibold text-brand-blue hover:underline">Terms of Service</a>
            and
            <a href="#" class="font-semibold text-brand-blue hover:underline">Privacy Policy</a>.
          </span>
        </label>

        <button type="submit" class="btn-primary" :disabled="auth.loading || !agree">
          <span v-if="!auth.loading">Create my account</span>
          <span v-else class="inline-flex items-center gap-2">
            <i class="fa-solid fa-circle-notch fa-spin"></i> Creating account…
          </span>
        </button>

        <p
          v-if="auth.lastMessage"
          class="text-sm text-emerald-600 bg-emerald-50 border border-emerald-100 rounded-lg px-3 py-2"
        >
          <i class="fa-solid fa-circle-check mr-1"></i> {{ auth.lastMessage }}
        </p>

        <p class="text-center text-sm text-slate-600">
          Already have an account?
          <router-link to="/login" class="font-semibold text-brand-blue hover:text-brand-blueHover">
            Sign in here
          </router-link>
        </p>
      </form>
    </div>
  </AuthLayout>
</template>

<script setup>
import { reactive, ref } from 'vue'
import AuthLayout from '../components/AuthLayout.vue'
import PasswordStrength from '../components/PasswordStrength.vue'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const form = reactive({ name: '', nim: '', email: '', password: '' })
const agree = ref(false)
const showPassword = ref(false)

async function onSubmit() {
  await auth.register({ ...form })
}
</script>
