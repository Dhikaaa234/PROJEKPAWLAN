<script setup>
import {
  Bell,
  Building2,
  ClipboardList,
  LayoutDashboard,
  LogOut,
} from 'lucide-vue-next'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const menus = [
  {
    label: 'Dashboard',
    icon: LayoutDashboard,
    path: '/admin/dashboard',
  },
  {
    label: 'Management Laporan',
    icon: ClipboardList,
    path: '/admin/management-laporan',
  },
  {
    label: 'Notifikasi',
    icon: Bell,
    path: '/admin/notifikasi',
  },
]

function isActive(path) {
  return route.path === path
}

function goTo(path) {
  if (route.path !== path) {
    router.push(path)
  }
}

async function logout() {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <aside
    class="sticky top-0 hidden h-screen w-[280px] shrink-0 flex-col border-r border-slate-200 bg-white p-5 lg:flex"
  >
    <div class="mb-8 flex items-center gap-3">
      <div class="grid size-10 place-items-center rounded-lg bg-blue-600 text-white">
        <Building2 :size="22" />
      </div>

      <div class="min-w-0">
        <h1 class="truncate text-xl font-extrabold text-slate-950">
          FilkomCare
        </h1>
        <p class="text-xs font-extrabold uppercase tracking-wide text-blue-700">
          Admin Panel
        </p>
      </div>
    </div>

    <nav class="space-y-2">
      <button
        v-for="menu in menus"
        :key="menu.label"
        type="button"
        @click="goTo(menu.path)"
        class="flex w-full items-center gap-3 rounded-lg px-4 py-3 text-left text-sm font-medium transition"
        :class="
          isActive(menu.path)
            ? 'bg-blue-50 text-blue-700'
            : 'text-slate-600 hover:bg-slate-50 hover:text-blue-700'
        "
      >
        <component :is="menu.icon" :size="21" class="shrink-0" />
        <span class="truncate">{{ menu.label }}</span>
      </button>
    </nav>

    <button
      type="button"
      @click="logout"
      class="mt-auto flex w-full items-center gap-3 rounded-lg px-4 py-3 text-left text-sm font-medium text-red-600 transition hover:bg-red-50 hover:text-red-700"
    >
      <LogOut :size="21" class="shrink-0" />
      <span>Logout</span>
    </button>
  </aside>
</template>