<script setup>
import { computed, ref, onMounted, watch } from 'vue'
import { useAuthStore } from '../stores/auth'
import api from '../services/api'
import AdminSidebar from '../components/AdminSidebar.vue'
import DashboardSidebar from '../components/DashboardSidebar.vue'
import DashboardTopbar from '../components/DashboardTopbar.vue'
import { User } from 'lucide-vue-next'
import { useLocale } from '../composables/useLocale'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const { t } = useLocale()
const SidebarComponent = computed(() => (auth.isAdmin ? AdminSidebar : DashboardSidebar))

const router = useRouter()

const isEditing = ref(false)
const isLoading = ref(false)
const message = ref({ type: '', text: '' })

const form = ref({
  name: '',
  email: '',
  nim: '',
  phone: ''
})

const originalForm = ref({})

function updateFormFromUser() {
  form.value.name = auth.user?.name || auth.user?.nama || ''
  form.value.email = auth.user?.email || ''
  form.value.nim = auth.user?.nim || ''
  form.value.phone = auth.user?.phone || auth.user?.no_telepon || ''
  originalForm.value = { ...form.value }
}

onMounted(async () => {
  // Jika belum ter-auth tapi token ada, coba ambil user dari backend.
  try {
    if (!auth.isAuthenticated && auth.token) {
      await auth.fetchCurrentUser()
    }
  } catch (err) {
    // Jika fetch gagal (mis. token invalid), arahkan ke login.
    auth.clearAuth()
    router.push('/login')
    return
  }

  updateFormFromUser()
})

// Jika auth.user berubah (mis. setelah fetchCurrentUser), update form
watch(
  () => auth.user,
  () => {
    updateFormFromUser()
  }
)

function startEdit() {
  originalForm.value = { ...form.value }
  isEditing.value = true
  message.value = { type: '', text: '' }
}

function cancelEdit() {
  form.value = { ...originalForm.value }
  isEditing.value = false
  message.value = { type: '', text: '' }
}

async function saveProfile() {
  isLoading.value = true
  message.value = { type: '', text: '' }
  
  try {
    const response = await api.put('/profile', {
      name: form.value.name,
      nim: form.value.nim,
      phone: form.value.phone,
      no_telepon: form.value.phone,
    })

    let updatedUser = response.data?.user || response.data?.data?.user || response.data
    if (updatedUser && updatedUser.id) {
      auth.setAuth({
        token: auth.token,
        user: { ...auth.user, ...updatedUser },
      })
      updateFormFromUser()
      message.value = { type: 'success', text: t.value['profile.updated'] }
      isEditing.value = false
    } else {
      message.value = { type: 'success', text: t.value['profile.updated'] }
      isEditing.value = false
      await auth.fetchCurrentUser()
      updateFormFromUser()
    }
  } catch (error) {
    console.error('Save profile error:', error)
    let errorMsg = t.value['profile.update_failed']
    if (error.response) {
      console.error('Response data:', error.response.data)
      errorMsg = error.response.data?.message || error.response.data?.errors || JSON.stringify(error.response.data)
    } else if (error.request) {
      errorMsg = t.value['profile.server_unavailable']
    }
    message.value = { type: 'error', text: errorMsg }
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-[#faf8ff]">
    <div class="flex min-h-screen">
      <component :is="SidebarComponent" />

      <div class="min-w-0 flex-1">
        <DashboardTopbar />

        <main class="px-5 py-8 md:px-8 lg:px-10">
          <div class="mx-auto max-w-3xl">
            <h1 class="text-3xl font-extrabold text-slate-950">{{ $t('profile.title') }}</h1>
            <p class="mt-2 text-slate-600">{{ $t('profile.description') }}</p>

            <div class="mt-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
              <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                  <div class="grid size-14 place-items-center rounded-full bg-blue-100 text-blue-700">
                    <User :size="28" />
                  </div>
                  <div>
                    <h2 class="text-xl font-bold">{{ auth.user?.name || auth.user?.nama }}</h2>
                    <p class="text-sm text-slate-500">{{ auth.user?.role === 'admin' ? $t('topbar.admin') : $t('topbar.student') }}</p>
                  </div>
                </div>
                <button v-if="!isEditing" @click="startEdit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-50">
                  {{ $t('profile.edit') }}
                </button>
              </div>

              <div class="mt-6 space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div>
                    <label class="block text-sm font-medium text-slate-700">{{ $t('profile.full_name') }}</label>
                    <input v-model="form.name" :disabled="!isEditing" type="text" class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2" :class="{ 'bg-white': isEditing }" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-700">{{ $t('profile.email') }}</label>
                    <input v-model="form.email" disabled type="email" class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-100 px-4 py-2 text-slate-500" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-700">{{ $t('profile.nim') }}</label>
                    <input v-model="form.nim" :disabled="!isEditing" type="text" class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2" :class="{ 'bg-white': isEditing }" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-700">{{ $t('profile.phone') }}</label>
                    <input v-model="form.phone" :disabled="!isEditing" type="tel" class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2" :class="{ 'bg-white': isEditing }" />
                  </div>
                </div>

                <div v-if="message.text" class="rounded-lg p-3" :class="message.type === 'success' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'">
                  {{ message.text }}
                </div>

                <div v-if="isEditing" class="flex justify-end gap-3 pt-4">
                  <button @click="cancelEdit" class="rounded-lg border border-slate-300 px-5 py-2 text-sm font-medium">{{ $t('common.cancel') }}</button>
                  <button @click="saveProfile" :disabled="isLoading" class="rounded-lg bg-blue-700 px-5 py-2 text-sm font-medium text-white hover:bg-blue-800 disabled:opacity-70">
                    {{ isLoading ? $t('common.saving') : $t('admin.save_changes') }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>
  </div>
</template>
