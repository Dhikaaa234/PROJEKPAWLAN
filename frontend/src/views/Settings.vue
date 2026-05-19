<template>
  <div class="min-h-screen bg-[#faf8ff]">
    <div class="flex">
      <DashboardSidebar />
      <div class="flex-1">
        <DashboardTopbar />
        <main class="p-8">
          <div class="mx-auto max-w-3xl">
            <h1 class="text-3xl font-extrabold text-slate-950">{{ t.settings }}</h1>
            <p class="mt-2 text-slate-600">{{ t.preferences_desc }}</p>

            <!-- Preferensi Bahasa -->
            <div class="mt-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
              <h2 class="text-xl font-bold text-slate-950">{{ t.preferences }}</h2>

              <!-- Language Selector -->
              <div class="mt-4">
                <label class="block text-sm font-medium text-slate-700">{{ t.language }}</label>
                <select
                  v-model="selectedLocale"
                  @change="changeLocale"
                  class="mt-1 w-full rounded-lg border border-slate-300 bg-white p-2 text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                >
                  <option value="id">{{ t.indonesian }}</option>
                  <option value="en">{{ t.english }}</option>
                </select>
              </div>

              <button
                @click="savePreferences"
                class="mt-6 rounded-lg bg-blue-700 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                {{ t.save_preferences }}
              </button>
              <div v-if="prefMessage" class="mt-3 text-sm text-green-600">{{ prefMessage }}</div>
            </div>

            <!-- Ganti Password -->
            <div class="mt-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
              <h2 class="text-xl font-bold text-slate-950">{{ t.change_password }}</h2>
              <div class="mt-4 space-y-4">
                <div>
                  <label class="block text-sm font-medium text-slate-700">{{ t.current_password }}</label>
                  <input
                    v-model="passwordForm.old_password"
                    type="password"
                    class="mt-1 w-full rounded-lg border border-slate-300 bg-white p-2 text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-slate-700">{{ t.new_password }}</label>
                  <input
                    v-model="passwordForm.new_password"
                    type="password"
                    class="mt-1 w-full rounded-lg border border-slate-300 bg-white p-2 text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-slate-700">{{ t.confirm_password }}</label>
                  <input
                    v-model="passwordForm.new_password_confirmation"
                    type="password"
                    class="mt-1 w-full rounded-lg border border-slate-300 bg-white p-2 text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                  />
                </div>
              </div>
              <button
                @click="changePassword"
                :disabled="isChanging"
                class="mt-6 rounded-lg bg-blue-700 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
              >
                {{ isChanging ? t.saving : t.update_password }}
              </button>
              <div v-if="passwordMessage" class="mt-3 text-sm" :class="passwordMessageType === 'error' ? 'text-red-600' : 'text-green-600'">
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
import { ref, onMounted } from 'vue'
import DashboardSidebar from '../components/DashboardSidebar.vue'
import DashboardTopbar from '../components/DashboardTopbar.vue'
import { useLocale } from '../composables/useLocale'
import api from '../services/api'

const { t, setLocale, locale } = useLocale()

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
  localStorage.setItem('locale', selectedLocale.value)
  prefMessage.value = t.value.save_preferences + ' ✓'
  setTimeout(() => (prefMessage.value = ''), 2000)
}

function changeLocale() {
  setLocale(selectedLocale.value)
  window.location.reload()
}

async function changePassword() {
  if (passwordForm.value.new_password !== passwordForm.value.new_password_confirmation) {
    passwordMessage.value = t.value.password_mismatch || 'Konfirmasi password baru tidak cocok'
    passwordMessageType.value = 'error'
    return
  }
  if (passwordForm.value.new_password.length < 6) {
    passwordMessage.value = 'Password baru minimal 6 karakter'
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
    passwordMessage.value = t.value.password_changed || 'Password berhasil diubah'
    passwordMessageType.value = 'success'
    passwordForm.value = { old_password: '', new_password: '', new_password_confirmation: '' }
    setTimeout(() => (passwordMessage.value = ''), 3000)
  } catch (err) {
    console.error('Change password error:', err)
    const errorMsg = err.response?.data?.message || err.message || 'Gagal mengubah password'
    passwordMessage.value = errorMsg
    passwordMessageType.value = 'error'
  } finally {
    isChanging.value = false
  }
}

onMounted(() => {
  selectedLocale.value = locale.value
})
</script>