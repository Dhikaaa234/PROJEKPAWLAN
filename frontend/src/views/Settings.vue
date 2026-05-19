<template>
  <div class="min-h-screen bg-[#faf8ff]">
    <div class="flex min-h-screen">
      <component :is="SidebarComponent" />

      <div class="min-w-0 flex-1">
        <DashboardTopbar />

        <main class="p-8">
          <div class="mx-auto max-w-3xl">
            <h1 class="text-3xl font-extrabold text-slate-950">
              {{ $t('settings.title') }}
            </h1>
            <p class="mt-2 text-slate-600">
              {{ $t('settings.description') }}
            </p>

            <div class="mt-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
              <h2 class="text-xl font-bold text-slate-950">
                {{ $t('settings.preferences') }}
              </h2>

              <div class="mt-4">
                <label class="block text-sm font-medium text-slate-700">
                  {{ $t('settings.language') }}
                </label>
                <select
                  v-model="selectedLocale"
                  @change="changeLocale"
                  class="mt-1 w-full rounded-lg border border-slate-300 bg-white p-2 text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                >
                  <option value="id">
                    {{ $t('settings.indonesian') }}
                  </option>
                  <option value="en">
                    {{ $t('settings.english') }}
                  </option>
                </select>
              </div>

              <button
                type="button"
                @click="savePreferences"
                class="mt-6 rounded-lg bg-blue-700 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                {{ $t('settings.save_preferences') }}
              </button>
              <div
                v-if="prefMessage"
                class="mt-3 text-sm text-green-600"
              >
                {{ prefMessage }}
              </div>
            </div>

            <div class="mt-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
              <h2 class="text-xl font-bold text-slate-950">
                {{ $t('settings.change_password') }}
              </h2>

              <div class="mt-4 space-y-4">
                <div>
                  <label class="block text-sm font-medium text-slate-700">
                    {{ $t('settings.current_password') }}
                  </label>
                  <input
                    v-model="passwordForm.old_password"
                    type="password"
                    class="mt-1 w-full rounded-lg border border-slate-300 bg-white p-2 text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-slate-700">
                    {{ $t('settings.new_password') }}
                  </label>
                  <input
                    v-model="passwordForm.new_password"
                    type="password"
                    class="mt-1 w-full rounded-lg border border-slate-300 bg-white p-2 text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-slate-700">
                    {{ $t('settings.confirm_password') }}
                  </label>
                  <input
                    v-model="passwordForm.new_password_confirmation"
                    type="password"
                    class="mt-1 w-full rounded-lg border border-slate-300 bg-white p-2 text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                  />
                </div>
              </div>

              <button
                type="button"
                @click="changePassword"
                :disabled="isChanging"
                class="mt-6 rounded-lg bg-blue-700 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
              >
                {{ isChanging ? $t('common.saving') : $t('settings.update_password') }}
              </button>
              <div
                v-if="passwordMessage"
                class="mt-3 text-sm"
                :class="passwordMessageType === 'error' ? 'text-red-600' : 'text-green-600'"
              >
                {{ passwordMessage }}
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '../stores/auth'
import { loadUserLocale, setLocaleForCurrentUser } from '../i18n'
import AdminSidebar from '../components/AdminSidebar.vue'
import DashboardSidebar from '../components/DashboardSidebar.vue'
import DashboardTopbar from '../components/DashboardTopbar.vue'
import api from '../services/api'

const auth = useAuthStore()
const { t, locale } = useI18n()
const SidebarComponent = computed(() => (auth.isAdmin ? AdminSidebar : DashboardSidebar))

const selectedLocale = ref(locale.value)
const prefMessage = ref('')

const isChanging = ref(false)
const passwordMessage = ref('')
const passwordMessageType = ref('success')
const passwordForm = ref({
  old_password: '',
  new_password: '',
  new_password_confirmation: '',
})

function savePreferences() {
  setLocaleForCurrentUser(selectedLocale.value, auth.user)
  prefMessage.value = t('settings.preferences_saved')
  setTimeout(() => (prefMessage.value = ''), 2000)
}

function changeLocale() {
  setLocaleForCurrentUser(selectedLocale.value, auth.user)
}

async function changePassword() {
  if (passwordForm.value.new_password !== passwordForm.value.new_password_confirmation) {
    passwordMessage.value = t('settings.password_mismatch')
    passwordMessageType.value = 'error'
    return
  }

  if (passwordForm.value.new_password.length < 6) {
    passwordMessage.value = t('settings.password_min')
    passwordMessageType.value = 'error'
    return
  }

  isChanging.value = true
  passwordMessage.value = ''

  try {
    await api.post('/change-password', {
      old_password: passwordForm.value.old_password,
      new_password: passwordForm.value.new_password,
      new_password_confirmation: passwordForm.value.new_password_confirmation,
    })
    passwordMessage.value = t('settings.password_changed')
    passwordMessageType.value = 'success'
    passwordForm.value = {
      old_password: '',
      new_password: '',
      new_password_confirmation: '',
    }
    setTimeout(() => (passwordMessage.value = ''), 3000)
  } catch (err) {
    console.error('Change password error:', err)
    const errorMsg = err.response?.data?.message || err.message || t('settings.password_update_failed')
    passwordMessage.value = errorMsg
    passwordMessageType.value = 'error'
  } finally {
    isChanging.value = false
  }
}

onMounted(() => {
  selectedLocale.value = loadUserLocale(auth.user)
})
</script>
