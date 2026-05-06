<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { Bell, Settings } from 'lucide-vue-next'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth = useAuthStore()

const userInitials = computed(() => {
  const name = auth.user?.name || 'Admin Filkom'

  return name
    .split(' ')
    .map((word) => word[0])
    .join('')
    .slice(0, 2)
    .toUpperCase()
})

const userRoleLabel = computed(() => {
  if (auth.user?.roleLabel) {
    return auth.user.roleLabel
  }

  if (auth.user?.role === 'admin') {
    return 'Super Admin'
  }

  if (auth.user?.role === 'user') {
    return 'Mahasiswa'
  }

  return 'Super Admin'
})

function goToNotifications() {
  if (auth.user?.role === 'admin') {
    router.push('/admin/notifikasi')
    return
  }

  router.push('/notifikasi')
}
</script>

<template>
  <header
    class="sticky top-0 z-30 flex h-[66px] items-center justify-between border-b border-slate-200 bg-white/95 px-5 backdrop-blur md:px-8"
  >
    <h2 class="text-xl font-extrabold tracking-tight text-blue-700">
      FilkomCare
    </h2>

    <div class="flex items-center gap-4">
      <button
        type="button"
        aria-label="Notifikasi"
        @click="goToNotifications"
        class="grid size-9 place-items-center rounded-full text-slate-600 transition hover:bg-slate-100 hover:text-blue-700"
      >
        <Bell :size="20" />
      </button>

      <button
        type="button"
        aria-label="Pengaturan"
        class="grid size-9 place-items-center rounded-full text-slate-600 transition hover:bg-slate-100 hover:text-blue-700"
      >
        <Settings :size="20" />
      </button>

      <div class="hidden h-8 w-px bg-slate-200 md:block"></div>

      <div class="flex items-center gap-3">
        <div
          class="grid size-10 place-items-center rounded-full bg-orange-100 text-sm font-extrabold text-orange-700 ring-2 ring-orange-50"
        >
          {{ userInitials }}
        </div>

        <div class="hidden leading-tight sm:block">
          <p class="text-sm font-bold text-slate-950">
            {{ auth.user?.name || 'Admin Filkom' }}
          </p>
          <p class="text-xs text-slate-500">
            {{ userRoleLabel }}
          </p>
        </div>
      </div>
    </div>
  </header>
</template>