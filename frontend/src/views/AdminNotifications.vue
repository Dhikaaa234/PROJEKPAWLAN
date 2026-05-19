<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue'
import {
  Bell,
  Check,
  CheckCircle2,
  ClipboardList,
  Info,
  RefreshCcw,
  TriangleAlert,
  X,
} from 'lucide-vue-next'

import api from '../services/api'
import AdminSidebar from '../components/AdminSidebar.vue'
import DashboardTopbar from '../components/DashboardTopbar.vue'
import { useLocale } from '../composables/useLocale'

const { t } = useLocale()

const notifications = ref([])
const selectedNotification = ref(null)
const isDetailModalOpen = ref(false)
const isLoadingNotifications = ref(false)

watch(isDetailModalOpen, (isOpen) => {
  document.body.style.overflow = isOpen ? 'hidden' : ''
})

onUnmounted(() => {
  document.body.style.overflow = ''
})

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
    const response = await api.get('/admin/notifications')
    const payload = unwrapResponse(response)
    notifications.value = extractNotifications(payload).map(normalizeNotification)
  } catch (error) {
    notifications.value = []
  } finally {
    isLoadingNotifications.value = false
  }
}

async function markAllAsRead() {
  const previousNotifications = [...notifications.value]
  notifications.value = notifications.value.map(n => ({ ...n, unread: false }))
  try {
    await api.patch('/admin/notifications/read-all')
  } catch (error) {
    notifications.value = previousNotifications
  }
}

async function openNotificationDetail(notification) {
  selectedNotification.value = notification
  isDetailModalOpen.value = true

  notifications.value = notifications.value.map(item =>
    item.id === notification.id ? { ...item, unread: false } : item
  )

  try {
    await api.patch(`/admin/notifications/${notification.id}/read`)
  } catch (error) {
    notifications.value = notifications.value.map(item =>
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
  <div class="min-h-screen bg-[#faf8ff] dark:bg-slate-950">
    <div class="flex min-h-screen">
      <AdminSidebar />
      <div class="min-w-0 flex-1">
        <DashboardTopbar />
        <main class="px-5 py-8 md:px-8 lg:px-10">
          <section class="mx-auto max-w-[1280px]">
            <div class="mb-8 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
              <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-950 md:text-4xl">
                  {{ t.notifications }}
                </h1>
                <p class="mt-3 text-base text-slate-600">
                  {{ t.notifications_desc }}
                </p>
              </div>
              <div class="flex flex-col gap-3 sm:flex-row">
                <button
                  type="button"
                  @click="markAllAsRead"
                  class="inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-5 text-sm font-extrabold text-blue-700 shadow-sm transition hover:bg-blue-50"
                >
                  <Check :size="17" />
                  {{ t.mark_all_read }}
                </button>
              </div>
            </div>

            <section class="overflow-hidden rounded-xl border border-slate-300 bg-white shadow-sm">
              <div v-if="isLoadingNotifications && notifications.length === 0" class="px-6 py-12 text-center">
                <p class="text-base font-bold text-slate-700">Memuat notifikasi...</p>
              </div>

              <article
                v-for="notification in notifications"
                v-else
                :key="notification.id"
                role="button"
                tabindex="0"
                class="grid cursor-pointer gap-4 border-b border-slate-200 px-6 py-6 transition last:border-b-0 hover:bg-blue-50/50 md:grid-cols-[48px_1fr_170px]"
                :class="notification.unread ? 'bg-blue-50/40' : 'bg-white'"
                @click="openNotificationDetail(notification)"
                @keydown.enter="openNotificationDetail(notification)"
              >
                <div class="grid size-12 place-items-center rounded-full" :class="notification.iconClass">
                  <component :is="notification.icon" :size="22" />
                </div>
                <div class="min-w-0">
                  <div class="flex flex-wrap items-center gap-2">
                    <h3 class="truncate text-base font-extrabold" :class="notification.unread ? 'text-slate-950' : 'text-slate-700'" :title="notification.title">
                      {{ notification.title }}
                    </h3>
                    <span v-if="notification.unread" class="size-2 shrink-0 rounded-full bg-blue-700"></span>
                  </div>
                  <p class="mt-1 max-w-[780px] line-clamp-2 text-base leading-relaxed text-slate-600">
                    {{ notification.description }}
                  </p>
                  <p v-if="notification.tag" class="mt-4 inline-flex rounded-full px-3 py-1 text-xs font-extrabold" :class="notification.tagClass">
                    {{ notification.tag }}
                  </p>
                </div>
                <p class="text-left text-sm font-bold md:text-right" :class="notification.unread ? 'text-blue-700' : 'text-slate-500'">
                  {{ notification.time }}
                </p>
              </article>

              <div v-if="notifications.length === 0 && !isLoadingNotifications" class="px-6 py-12 text-center">
                <p class="text-base font-bold text-slate-700">{{ t.no_notifications }}</p>
                <p class="mt-1 text-sm text-slate-500">Semua pembaruan laporan admin akan muncul di halaman ini.</p>
              </div>
            </section>
          </section>
        </main>
      </div>
    </div>

    <!-- Modal Detail -->
    <div v-if="isDetailModalOpen && selectedNotification" class="fixed inset-0 z-[70] overflow-y-auto bg-slate-950/40 px-4 py-10 backdrop-blur-sm" @click.self="closeNotificationDetail">
      <div class="mx-auto flex min-h-full w-full max-w-[620px] items-start justify-center">
        <div class="w-full overflow-hidden rounded-2xl bg-white shadow-[0_20px_70px_rgba(15,23,42,0.28)]">
          <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5">
            <div class="flex items-start gap-4">
              <div class="grid size-12 shrink-0 place-items-center rounded-full" :class="selectedNotification.iconClass">
                <component :is="selectedNotification.icon" :size="22" />
              </div>
              <div>
                <h2 class="text-xl font-extrabold leading-tight text-slate-950">Detail Notifikasi</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">{{ selectedNotification.time }}</p>
              </div>
            </div>
            <button type="button" class="grid size-9 shrink-0 place-items-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" aria-label="Tutup detail notifikasi" @click="closeNotificationDetail">
              <X :size="20" />
            </button>
          </div>
          <div class="px-6 py-6">
            <div class="mb-4 flex flex-wrap items-center gap-2">
              <p v-if="selectedNotification.tag" class="inline-flex rounded-full px-3 py-1 text-xs font-extrabold" :class="selectedNotification.tagClass">
                {{ selectedNotification.tag }}
              </p>
              <p v-if="selectedNotification.unread" class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-extrabold text-blue-700">
                BELUM DIBACA
              </p>
            </div>
            <h3 class="text-2xl font-extrabold leading-tight text-slate-950">{{ selectedNotification.title }}</h3>
            <p class="mt-4 text-base leading-relaxed text-slate-600">{{ selectedNotification.description }}</p>
          </div>
          <div class="flex justify-end border-t border-slate-100 bg-slate-50 px-6 py-4">
            <button type="button" class="inline-flex h-11 items-center justify-center rounded-lg bg-blue-700 px-5 text-sm font-extrabold text-white transition hover:bg-blue-800" @click="closeNotificationDetail">
              Tutup
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>