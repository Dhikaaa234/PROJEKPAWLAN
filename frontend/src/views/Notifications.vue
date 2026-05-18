<script setup>
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  BarChart3,
  Bell,
  Building2,
  Check,
  CheckCircle2,
  ChevronLeft,
  CirclePlus,
  ClipboardList,
  Filter,
  Info,
  LayoutDashboard,
  LogOut,
  RefreshCcw,
  Search,
  Settings,
  TriangleAlert,
  UserSearch,
} from 'lucide-vue-next'
import { useAuthStore } from '../stores/auth'
import DashboardSidebar from '../components/DashboardSidebar.vue'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const isMobileMenuOpen = ref(false)

const menuItems = [
  {
    label: 'Dashboard',
    icon: LayoutDashboard,
    path: '/dashboard',
  },
  {
    label: 'Semua Laporan',
    icon: BarChart3,
    path: '/semua-laporan',
  },
  {
    label: 'Laporan Saya',
    icon: UserSearch,
    path: '/laporan-saya',
  },
  {
    label: 'Buat Laporan',
    icon: CirclePlus,
    path: '/buat-laporan',
  },
  {
    label: 'Notifikasi',
    icon: Bell,
    path: '/notifikasi',
  },
]

const newNotifications = ref([
  {
    id: 1,
    title: 'Ada laporan baru: AC Ruang H3.1 Tidak Dingin',
    description:
      'Seorang mahasiswa melaporkan masalah suhu pada ruangan H3.1. Harap segera ditinjau dan diteruskan ke tim teknisi.',
    time: 'Baru saja',
    tag: 'ADMIN NOTIFICATION',
    tagClass: 'text-blue-700',
    icon: Bell,
    iconClass: 'bg-blue-100 text-blue-700',
    unread: true,
  },
  {
    id: 2,
    title: 'Laporan [Proyektor Mati Gedung B] telah diproses',
    description:
      'Status laporan Anda telah berubah menjadi "Diproses". Tim teknis sedang dalam perjalanan menuju lokasi.',
    time: '10 menit yang lalu',
    tag: 'STATUS UPDATE',
    tagClass: 'bg-amber-50 text-amber-700',
    icon: RefreshCcw,
    iconClass: 'bg-yellow-100 text-yellow-700',
    unread: true,
  },
  {
    id: 3,
    title: 'Pembaruan Sistem FilkomCare v2.1',
    description:
      'Kami telah menambahkan fitur baru untuk pelacakan laporan secara real-time. Periksa dashboard Anda untuk melihat perubahannya.',
    time: '1 jam yang lalu',
    tag: '',
    tagClass: '',
    icon: Info,
    iconClass: 'bg-blue-100 text-blue-700',
    unread: true,
  },
])

const yesterdayNotifications = ref([
  {
    id: 4,
    title: 'Laporan [Kebocoran Kran Musholla] Selesai',
    description:
      'Perbaikan telah selesai dilakukan. Terima kasih atas laporan Anda untuk membantu menjaga fasilitas kampus.',
    time: 'Kemarin, 14:20',
    icon: CheckCircle2,
    iconClass: 'bg-green-100 text-green-700',
  },
  {
    id: 5,
    title: 'Laporan baru berhasil dikirim',
    description:
      'Laporan Anda mengenai "Kursi Patah di Gazebo" telah kami terima dan sedang mengantri untuk peninjauan.',
    time: 'Kemarin, 09:15',
    icon: ClipboardList,
    iconClass: 'bg-slate-100 text-slate-500',
  },
  {
    id: 6,
    title: 'Laporan Ditolak: Detail Kurang Lengkap',
    description:
      'Laporan mengenai "Kerusakan Kabel" ditolak karena foto dan lokasi gedung tidak dilampirkan dengan jelas.',
    time: '2 hari yang lalu',
    icon: TriangleAlert,
    iconClass: 'bg-red-100 text-red-600',
  },
])

const userInitials = computed(() => {
  const name = auth.user?.name || 'Admin Filkom'

  return name
    .split(' ')
    .map((word) => word[0])
    .join('')
    .slice(0, 2)
    .toUpperCase()
})

function isActive(path) {
  return route.path === path
}

function goTo(path) {
  if (route.path !== path) {
    router.push(path)
  }

  closeMobileMenu()
}

function toggleMobileMenu() {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}

function closeMobileMenu() {
  isMobileMenuOpen.value = false
}

function logout() {
  auth.logout()
  closeMobileMenu()
  router.push('/login')
}

function markAllAsRead() {
  newNotifications.value = newNotifications.value.map((notification) => ({
    ...notification,
    unread: false,
  }))
}
</script>

<template>
  <div class="min-h-screen bg-[#faf8ff] text-slate-900">
    <div class="flex min-h-screen">
      <DashboardSidebar />

      <div class="min-w-0 flex-1">
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
              class="grid size-9 place-items-center rounded-full text-blue-700 transition hover:bg-slate-100"
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
                  {{ auth.user?.roleLabel || 'Super Admin' }}
                </p>
              </div>
            </div>
          </div>
        </header>

        <button
          type="button"
          class="fixed bottom-5 right-5 z-50 grid size-12 place-items-center rounded-full bg-blue-700 text-white shadow-lg lg:hidden"
          aria-label="Toggle menu"
          @click="toggleMobileMenu"
        >
          <Bell v-if="!isMobileMenuOpen" :size="24" />
          <ChevronLeft v-else :size="24" />
        </button>

        <div
          v-if="isMobileMenuOpen"
          class="fixed inset-0 z-40 bg-slate-950/40 lg:hidden"
          @click="closeMobileMenu"
        ></div>

        <aside
          class="fixed bottom-0 left-0 top-0 z-50 flex w-[280px] transform flex-col bg-white p-5 shadow-2xl transition lg:hidden"
          :class="isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'"
        >
          <div class="mb-8 flex items-center gap-3">
            <div class="grid size-10 place-items-center rounded-lg bg-blue-600 text-white">
              <Building2 :size="22" />
            </div>

            <div>
              <h1 class="text-xl font-extrabold text-slate-950">
                FilkomCare
              </h1>
              <p class="text-xs font-extrabold uppercase tracking-wide text-blue-700">
                Facility Management
              </p>
            </div>
          </div>

          <nav class="space-y-2">
            <button
              v-for="item in menuItems"
              :key="item.label"
              type="button"
              @click="goTo(item.path)"
              class="flex w-full items-center gap-3 rounded-lg px-4 py-3 text-left text-sm font-medium transition"
              :class="
                isActive(item.path)
                  ? 'bg-blue-50 text-blue-700'
                  : 'text-slate-600 hover:bg-slate-50 hover:text-blue-700'
              "
            >
              <component :is="item.icon" :size="21" />
              <span>{{ item.label }}</span>
            </button>
          </nav>

          <button
            type="button"
            @click="logout"
            class="mt-auto flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-red-600"
          >
            <LogOut :size="21" />
            <span>Logout</span>
          </button>
        </aside>

        <main class="px-5 py-5 md:px-8 lg:px-10">
          <section class="mx-auto max-w-[1280px]">
            <div class="mb-8 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
              <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-950 md:text-4xl">
                  Notifikasi
                </h1>
                <p class="mt-3 text-base text-slate-600">
                  Kelola dan pantau semua pembaruan laporan fasilitas Anda.
                </p>
              </div>

              <div class="flex flex-col gap-3 sm:flex-row">
                <button
                  type="button"
                  @click="markAllAsRead"
                  class="inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-5 text-sm font-extrabold text-blue-700 shadow-sm transition hover:bg-blue-50"
                >
                  <Check :size="17" />
                  Tandai Semua Dibaca
                </button>

                <button
                  type="button"
                  class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-blue-700 px-5 text-sm font-extrabold text-white shadow-sm transition hover:bg-blue-800"
                >
                  <Filter :size="17" />
                  Filter
                </button>
              </div>
            </div>

            <section class="overflow-hidden rounded-xl border border-slate-300 bg-white shadow-sm">
              <div class="border-b border-slate-300 bg-[#f1f0fb] px-6 py-3">
                <h2 class="text-sm font-extrabold uppercase tracking-wide text-slate-600">
                  Terbaru (3)
                </h2>
              </div>

              <div class="relative border-l-4 border-blue-700 bg-blue-50/40">
                <article
                  v-for="notification in newNotifications"
                  :key="notification.id"
                  class="grid gap-4 border-b border-slate-300 px-6 py-6 last:border-b-0 md:grid-cols-[48px_1fr_170px]"
                  :class="notification.unread ? 'bg-blue-50/40' : 'bg-white'"
                >
                  <div
                    class="grid size-12 place-items-center rounded-full"
                    :class="notification.iconClass"
                  >
                    <component :is="notification.icon" :size="22" />
                  </div>

                  <div>
                    <h3 class="text-base font-extrabold text-slate-950">
                      {{ notification.title }}
                    </h3>

                    <p class="mt-1 max-w-[780px] text-base leading-relaxed text-slate-600">
                      {{ notification.description }}
                    </p>

                    <p
                      v-if="notification.tag"
                      class="mt-4 inline-flex rounded-full px-3 py-1 text-xs font-extrabold"
                      :class="notification.tagClass"
                    >
                      {{ notification.tag }}
                    </p>
                  </div>

                  <p class="text-left text-sm font-bold text-blue-700 md:text-right">
                    {{ notification.time }}
                  </p>
                </article>
              </div>

              <div class="border-y border-slate-300 bg-[#f1f0fb] px-6 py-3">
                <h2 class="text-sm font-extrabold uppercase tracking-wide text-slate-600">
                  Kemarin
                </h2>
              </div>

              <div>
                <article
                  v-for="notification in yesterdayNotifications"
                  :key="notification.id"
                  class="grid gap-4 border-b border-slate-200 px-6 py-6 last:border-b-0 md:grid-cols-[48px_1fr_170px]"
                >
                  <div
                    class="grid size-12 place-items-center rounded-full"
                    :class="notification.iconClass"
                  >
                    <component :is="notification.icon" :size="22" />
                  </div>

                  <div>
                    <h3 class="text-base font-extrabold text-slate-700">
                      {{ notification.title }}
                    </h3>

                    <p class="mt-1 max-w-[780px] text-base leading-relaxed text-slate-500">
                      {{ notification.description }}
                    </p>
                  </div>

                  <p class="text-left text-sm font-medium text-slate-500 md:text-right">
                    {{ notification.time }}
                  </p>
                </article>
              </div>

              <div class="border-t border-slate-200 bg-white px-6 py-6 text-center">
                <button
                  type="button"
                  class="text-base font-extrabold text-blue-700 transition hover:text-blue-900"
                >
                  Muat Notifikasi Sebelumnya
                </button>
              </div>
            </section>
          </section>
        </main>
      </div>
    </div>
  </div>
</template>