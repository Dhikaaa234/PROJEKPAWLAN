<script setup>
import {
  BarChart3,
  Bell,
  Building2,
  CirclePlus,
  LayoutDashboard,
  LogOut,
  UserSearch,
  User,
  Settings,
} from 'lucide-vue-next'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useLocale } from '../composables/useLocale'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const { t } = useLocale()

const menuItems = [
  { label: t.value.dashboard, icon: LayoutDashboard, path: '/dashboard' },
  { label: t.value.all_reports_menu, icon: BarChart3, path: '/semua-laporan' },
  { label: t.value.my_reports_menu, icon: UserSearch, path: '/laporan-saya' },
  { label: t.value.create_report_menu, icon: CirclePlus, path: '/buat-laporan' },
  { label: t.value.notifications_menu, icon: Bell, path: '/notifikasi' },
  { label: t.value.profile_menu, icon: User, path: '/profile' },
  { label: t.value.settings_menu, icon: Settings, path: '/settings' },
]

function isActive(path) {
  return route.path === path
}

function goTo(path) {
  if (route.path !== path) router.push(path)
}

async function logout() {
  await auth.logout()
  await router.replace('/login')
}
</script>

<template>
  <aside class="hidden h-screen w-[260px] shrink-0 border-r border-slate-200 bg-white px-4 py-6 lg:sticky lg:top-0 lg:flex lg:flex-col">
    <div class="mb-9 flex items-center gap-3 px-2">
      <div class="grid size-10 place-items-center rounded-lg bg-blue-600 text-white shadow-sm">
        <Building2 :size="22" />
      </div>
      <div>
        <h1 class="text-lg font-extrabold leading-tight text-slate-950">{{ t.app_name }}</h1>
        <p class="text-sm font-medium text-slate-500">{{ t.facility_management }}</p>
      </div>
    </div>

    <nav class="flex-1 space-y-2 overflow-y-auto">
      <button
        v-for="item in menuItems"
        :key="item.label"
        type="button"
        @click="goTo(item.path)"
        class="group relative flex w-full items-center gap-3 rounded-lg px-4 py-3 text-left text-sm font-semibold transition"
        :class="isActive(item.path) ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-700'"
      >
        <component :is="item.icon" :size="21" />
        <span>{{ item.label }}</span>
        <span v-if="isActive(item.path)" class="absolute right-0 top-1/2 h-10 w-1 -translate-y-1/2 rounded-l-full bg-blue-600" />
      </button>
    </nav>

    <button
      type="button"
      :disabled="auth.loading"
      @click="logout"
      class="mt-4 flex items-center gap-3 rounded-lg px-4 py-5 text-sm font-medium text-red-600 transition hover:bg-red-50 hover:text-red-700 disabled:cursor-not-allowed disabled:opacity-60"
    >
      <LogOut :size="21" />
      <span>{{ auth.loading ? 'Logout...' : t.logout }}</span>
    </button>
  </aside>
</template>