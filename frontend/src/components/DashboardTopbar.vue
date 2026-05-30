<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { Bell, Settings } from 'lucide-vue-next'
import { useAuthStore } from '../stores/auth'
import api from '../services/api'

const router = useRouter()
const auth = useAuthStore()

const unreadNotificationCount = ref(0)

const displayName = computed(() => {
  return auth.user?.nama || auth.user?.name || 'User Filkom'
})

const userInitials = computed(() => {
  return displayName.value
    .split(' ')
    .filter(Boolean)
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

  return 'Mahasiswa'
})

const notificationPath = computed(() => {
  return auth.user?.role === 'admin' ? '/admin/notifikasi' : '/notifikasi'
})

const notificationEndpoint = computed(() => {
  return auth.user?.role === 'admin' ? '/admin/notifications' : '/notifications'
})

async function fetchUnreadNotifications() {
  if (!auth.isAuthenticated) return

  try {
    const response = await api.get(notificationEndpoint.value)
    const payload = response?.data?.data ?? response?.data ?? {}

    const notifications = Array.isArray(payload)
      ? payload
      : payload.notifications || payload.items || []

    unreadNotificationCount.value = notifications.filter((notification) => {
      return Boolean(notification.unread ?? !notification.read_at)
    }).length
  } catch (error) {
    unreadNotificationCount.value = 0
  }
}

function goToNotifications() {
  router.push(notificationPath.value)
}

onMounted(() => {
  fetchUnreadNotifications()
})
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
        class="relative grid size-9 place-items-center rounded-full text-slate-600 transition hover:bg-slate-100 hover:text-blue-700"
      >
        <Bell :size="20" />

        <span
          v-if="unreadNotificationCount > 0"
          class="absolute right-1.5 top-1.5 grid min-h-4 min-w-4 place-items-center rounded-full bg-red-600 px-1 text-[10px] font-extrabold leading-none text-white ring-2 ring-white"
        >
          {{ unreadNotificationCount > 99 ? '99+' : unreadNotificationCount }}
        </span>
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
            {{ displayName }}
          </p>
          <p class="text-xs text-slate-500">
            {{ userRoleLabel }}
          </p>
        </div>
      </div>
    </div>
  </header>
</template>