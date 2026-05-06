<script setup>
import {
  Bell,
  Building2,
  LayoutDashboard,
  LogOut,
  UserCog,
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
    icon: UserCog,
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

function logout() {
  auth.logout()
  router.push('/login')
}
</script>

<template>
  <aside
    class="hidden min-h-screen w-[260px] shrink-0 border-r border-slate-200 bg-white px-4 py-6 lg:flex lg:flex-col"
  >
    <div class="mb-9 flex items-center gap-3 px-2">
      <div class="grid size-10 place-items-center rounded-lg bg-blue-600 text-white shadow-sm">
        <Building2 :size="22" />
      </div>

      <div>
        <h1 class="text-lg font-extrabold leading-tight text-slate-950">
          FilkomCare
        </h1>
        <p class="text-sm font-medium text-slate-500">
          Facility Management
        </p>
      </div>
    </div>

    <nav class="space-y-2">
      <button
        v-for="menu in menus"
        :key="menu.label"
        type="button"
        @click="goTo(menu.path)"
        class="group relative flex w-full items-center gap-3 rounded-lg px-4 py-3 text-left text-sm font-semibold transition"
        :class="
          isActive(menu.path)
            ? 'bg-blue-50 text-blue-700'
            : 'text-slate-600 hover:bg-slate-50 hover:text-blue-700'
        "
      >
        <component :is="menu.icon" :size="21" />
        <span>{{ menu.label }}</span>

        <span
          v-if="isActive(menu.path)"
          class="absolute right-0 top-1/2 h-10 w-1 -translate-y-1/2 rounded-l-full bg-blue-600"
        />
      </button>
    </nav>

    <button
      type="button"
      @click="logout"
      class="mt-auto flex items-center gap-3 px-4 py-5 text-sm font-medium text-slate-600 transition hover:text-red-600"
    >
      <LogOut :size="21" />
      <span>Logout</span>
    </button>
  </aside>
</template>