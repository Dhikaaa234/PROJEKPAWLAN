<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import {
  BarChart3,
  Bell,
  Building2,
  Check,
  CheckCircle2,
  ChevronLeft,
  CirclePlus,
  ClipboardList,
  Info,
  LayoutDashboard,
  LogOut,
  RefreshCcw,
  TriangleAlert,
  UserSearch,
  X,
} from 'lucide-vue-next'
import { useAuthStore } from '../stores/auth'
import api from '../services/api'
import DashboardSidebar from '../components/DashboardSidebar.vue'
import DashboardTopbar from '../components/DashboardTopbar.vue'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const { t } = useI18n()

const isMobileMenuOpen = ref(false)
const selectedNotification = ref(null)
const isDetailModalOpen = ref(false)
const isLoadingNotifications = ref(false)
const notifications = ref([])

watch(isDetailModalOpen, (isOpen) => {
  document.body.style.overflow = isOpen ? 'hidden' : ''
})

onUnmounted(() => {
  document.body.style.overflow = ''
})

const menuItems = computed(() => [
  {
    label: t('sidebar.dashboard'),
    icon: LayoutDashboard,
    path: '/dashboard',
  },
  {
    label: t('sidebar.all_reports'),
    icon: BarChart3,
    path: '/semua-laporan',
  },
  {
    label: t('sidebar.my_reports'),
    icon: UserSearch,
    path: '/laporan-saya',
  },
  {
    label: t('sidebar.create_report'),
    icon: CirclePlus,
    path: '/buat-laporan',
  },
  {
    label: t('sidebar.notifications'),
    icon: Bell,
    path: '/notifikasi',
  },
])

const allNotifications = computed(() => notifications.value)

function unwrapResponse(response) {
  return response?.data?.data ?? response?.data ?? {}
}

function extractNotifications(payload) {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload.notifications)) return payload.notifications
  if (Array.isArray(payload.items)) return payload.items
  return []
}

function getNotificationIcon(type) {
  const icons = {
    report_created: ClipboardList,
    report_updated: RefreshCcw,
    report_done: CheckCircle2,
    warning: TriangleAlert,
    info: Info,
  }

  return icons[type] || Bell
}

function getNotificationIconClass(type) {
  const classes = {
    report_created: 'bg-slate-100 text-slate-500',
    report_updated: 'bg-yellow-100 text-yellow-700',
    report_done: 'bg-green-100 text-green-700',
    warning: 'bg-red-100 text-red-600',
    info: 'bg-blue-100 text-blue-700',
  }

  return classes[type] || 'bg-blue-100 text-blue-700'
}

function normalizeNotification(notification) {
  const type = notification.type ?? notification.category ?? 'info'

  return {
    id: notification.id,
    title: notification.title ?? '',
    description: notification.description ?? notification.message ?? '',
    time: notification.time ?? notification.createdAt ?? notification.created_at ?? '',
    tag: notification.tag ?? '',
    tagClass: notification.tagClass ?? '',
    icon: notification.icon ?? getNotificationIcon(type),
    iconClass: notification.iconClass ?? getNotificationIconClass(type),
    unread: Boolean(notification.unread ?? !notification.read_at),
  }
}

async function fetchNotifications() {
  isLoadingNotifications.value = true

  try {
    const response = await api.get('/notifications')
    const payload = unwrapResponse(response)
    notifications.value = extractNotifications(payload).map(normalizeNotification)
  } catch (error) {
    notifications.value = []
  } finally {
    isLoadingNotifications.value = false
  }
}

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

async function markAllAsRead() {
  notifications.value = notifications.value.map((notification) => ({
    ...notification,
    unread: false,
  }))

  try {
    await api.patch('/notifications/read-all')
  } catch (error) {
    await fetchNotifications()
  }
}

async function openNotificationDetail(notification) {
  selectedNotification.value = notification
  isDetailModalOpen.value = true

  notifications.value = notifications.value.map((item) =>
    item.id === notification.id ? { ...item, unread: false } : item
  )

  try {
    await api.patch(`/notifications/${notification.id}/read`)
  } catch (error) {
    notifications.value = notifications.value.map((item) =>
      item.id === notification.id ? { ...item, unread: notification.unread } : item
    )
  }
}

function closeNotificationDetail() {
  selectedNotification.value = null
  isDetailModalOpen.value = false
}

onMounted(fetchNotifications)
</script>

<template>
  <div class="min-h-screen bg-[#faf8ff] text-slate-900">
    <div class="flex min-h-screen">
      <DashboardSidebar />

      <div class="min-w-0 flex-1">
        <DashboardTopbar />

        <button
          type="button"
          class="fixed bottom-5 right-5 z-50 grid size-12 place-items-center rounded-full bg-blue-700 text-white shadow-lg lg:hidden"
          :aria-label="$t('common.toggle_menu')"
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
                {{ $t('common.facility_management') }}
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
            <span>{{ $t('common.logout') }}</span>
          </button>
        </aside>

        <main class="px-5 py-5 md:px-8 lg:px-10">
          <section class="mx-auto max-w-[1280px]">
            <div class="mb-8 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
              <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-950 md:text-4xl">
                  {{ $t('notifications.title') }}
                </h1>
                <p class="mt-3 text-base text-slate-600">
                  {{ $t('notifications.description') }}
                </p>
              </div>

              <div class="flex flex-col gap-3 sm:flex-row">
                <button
                  type="button"
                  @click="markAllAsRead"
                  class="inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-5 text-sm font-extrabold text-blue-700 shadow-sm transition hover:bg-blue-50"
                >
                  <Check :size="17" />
                  {{ $t('common.mark_all_read') }}
                </button>
              </div>
            </div>

            <section class="overflow-hidden rounded-xl border border-slate-300 bg-white shadow-sm">
              <article
                v-for="notification in allNotifications"
                :key="notification.id"
                role="button"
                tabindex="0"
                class="grid cursor-pointer gap-4 border-b border-slate-200 px-6 py-6 transition last:border-b-0 hover:bg-blue-50/50 md:grid-cols-[48px_1fr_170px]"
                :class="notification.unread ? 'bg-blue-50/40' : 'bg-white'"
                @click="openNotificationDetail(notification)"
                @keydown.enter="openNotificationDetail(notification)"
              >
                <div
                  class="grid size-12 place-items-center rounded-full"
                  :class="notification.iconClass"
                >
                  <component :is="notification.icon" :size="22" />
                </div>

                <div>
                  <div class="flex flex-wrap items-center gap-2">
                    <h3
                      class="text-base font-extrabold"
                      :class="notification.unread ? 'text-slate-950' : 'text-slate-700'"
                    >
                      {{ notification.title }}
                    </h3>

                    <span
                      v-if="notification.unread"
                      class="size-2 rounded-full bg-blue-700"
                    ></span>
                  </div>

                  <p class="mt-1 max-w-[780px] line-clamp-2 text-base leading-relaxed text-slate-600">
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

              <div
                v-if="allNotifications.length === 0 && !isLoadingNotifications"
                class="px-6 py-12 text-center"
              >
                <p class="text-base font-bold text-slate-700">
                  {{ $t('notifications.no_notifications') }}
                </p>
              </div>
            </section>
          </section>
        </main>
      </div>
    </div>

    <div
      v-if="isDetailModalOpen && selectedNotification"
      class="fixed inset-0 z-[70] overflow-y-auto bg-slate-950/40 px-4 py-10 backdrop-blur-sm"
      @click.self="closeNotificationDetail"
    >
      <div class="mx-auto flex min-h-full w-full max-w-[620px] items-start justify-center">
        <div class="w-full overflow-hidden rounded-2xl bg-white shadow-[0_20px_70px_rgba(15,23,42,0.28)]">
          <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5">
            <div class="flex items-start gap-4">
              <div
                class="grid size-12 shrink-0 place-items-center rounded-full"
                :class="selectedNotification.iconClass"
              >
                <component :is="selectedNotification.icon" :size="22" />
              </div>

              <div>
                <h2 class="text-xl font-extrabold leading-tight text-slate-950">
                  {{ $t('notifications.detail_title') }}
                </h2>
                <p class="mt-1 text-sm font-medium text-slate-500">
                  {{ selectedNotification.time }}
                </p>
              </div>
            </div>

            <button
              type="button"
              class="grid size-9 shrink-0 place-items-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
              :aria-label="$t('common.close_notification_detail')"
              @click="closeNotificationDetail"
            >
              <X :size="20" />
            </button>
          </div>

          <div class="px-6 py-6">
            <div class="mb-4 flex flex-wrap items-center gap-2">
              <p
                v-if="selectedNotification.tag"
                class="inline-flex rounded-full px-3 py-1 text-xs font-extrabold"
                :class="selectedNotification.tagClass"
              >
                {{ selectedNotification.tag }}
              </p>
            </div>

            <h3 class="text-2xl font-extrabold leading-tight text-slate-950">
              {{ selectedNotification.title }}
            </h3>

            <p class="mt-4 text-base leading-relaxed text-slate-600">
              {{ selectedNotification.description }}
            </p>
          </div>

          <div class="flex justify-end border-t border-slate-100 bg-slate-50 px-6 py-4">
            <button
              type="button"
              class="inline-flex h-11 items-center justify-center rounded-lg bg-blue-700 px-5 text-sm font-extrabold text-white transition hover:bg-blue-800"
              @click="closeNotificationDetail"
            >
              {{ $t('common.close') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
