<script setup>
// ===============================
// IMPORT
// ===============================
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import {
  Armchair,
  CheckCircle2,
  ChevronRight,
  DoorOpen,
  Download,
  FileText,
  Plus,
  Send,
  UserCog,
  Wifi,
  Zap,
} from 'lucide-vue-next'

import api from '../services/api'
import AdminSidebar from '../components/AdminSidebar.vue'
import DashboardTopbar from '../components/DashboardTopbar.vue'

const router = useRouter()
const { t } = useI18n()

// ===============================
// STATE HALAMAN ADMIN DASHBOARD
// ===============================
const stats = ref([])
const incomingReports = ref([])
const recentReports = ref([])
const isLoadingDashboard = ref(false)
const isExporting = ref(false)
const isGeneratingReport = ref(false)

// ===============================
// COMPUTED DATA UNTUK TEMPLATE
// ===============================
const displayedIncomingReports = computed(() => incomingReports.value.slice(0, 5))
const displayedRecentReports = computed(() => recentReports.value.slice(0, 5))

// ===============================
// LIFECYCLE
// ===============================
onMounted(fetchDashboard)

// ===============================
// FITUR: FETCH DATA DASHBOARD
// ===============================

// Mengambil data dashboard admin dari backend
async function fetchDashboard() {
  isLoadingDashboard.value = true

  try {
    const response = await api.get('/admin/dashboard')
    const payload = unwrapResponse(response)
    const incomingItems = payload.incomingReports ?? payload.newReports ?? []
    const recentItems = payload.recentReports ?? payload.latestReports ?? []

    stats.value = buildStats(payload)
    incomingReports.value = Array.isArray(incomingItems)
      ? incomingItems.map(normalizeIncomingReport)
      : []
    recentReports.value = Array.isArray(recentItems)
      ? recentItems.map(normalizeRecentReport)
      : []
  } catch (error) {
    // Menampilkan state kosong jika request dashboard gagal
    stats.value = []
    incomingReports.value = []
    recentReports.value = []
  } finally {
    // Mengatur kondisi loading saat data dashboard selesai dimuat
    isLoadingDashboard.value = false
  }
}

// ===============================
// FITUR: STATISTIK LAPORAN
// ===============================

const statIconMap = {
  total: FileText,
  dikirim: Send,
  diproses: UserCog,
  selesai: CheckCircle2,
}

const statStyleMap = {
  total: 'bg-blue-100 text-blue-700',
  dikirim: 'bg-yellow-100 text-yellow-700',
  diproses: 'bg-blue-100 text-blue-700',
  selesai: 'bg-green-100 text-green-700',
}

// Mengambil key statistik agar cocok dengan konfigurasi ikon dan warna
function getStatKey(stat) {
  return String(stat.key ?? stat.status ?? stat.title ?? '')
    .toLowerCase()
    .replace(/\s+/g, '-')
}

// Menerjemahkan judul statistik yang tampil di card dashboard
function getStatTitle(title) {
  if (String(title).toUpperCase() === 'TOTAL') return t('common.total')

  return getStatusLabel(title)
}

// Menyamakan format statistik dari backend agar siap dipakai template
function normalizeStat(stat) {
  const key = getStatKey(stat)

  return {
    title: stat.title ?? stat.label ?? '',
    value: stat.value ?? stat.count ?? '',
    subtitle: stat.subtitle ?? stat.description ?? '',
    icon: statIconMap[key] || FileText,
    iconClass: stat.iconClass ?? statStyleMap[key] ?? statStyleMap.total,
  }
}

// Menghitung dan menyusun ringkasan statistik dashboard admin
function buildStats(payload) {
  if (Array.isArray(payload.stats)) {
    return payload.stats.map(normalizeStat)
  }

  const statDefinitions = [
    {
      key: 'total',
      title: 'TOTAL',
      value: payload.totalReports,
      subtitle: 'dashboard.reports_in',
    },
    {
      key: 'dikirim',
      title: 'Dikirim',
      value: payload.submittedReports,
      subtitle: 'dashboard.waiting_review',
    },
    {
      key: 'diproses',
      title: 'Diproses',
      value: payload.processedReports,
      subtitle: 'dashboard.in_progress',
    },
    {
      key: 'selesai',
      title: 'Selesai',
      value: payload.completedReports,
      subtitle: 'dashboard.repaired',
    },
  ]

  return statDefinitions
    .filter((stat) => stat.value !== undefined && stat.value !== null)
    .map(normalizeStat)
}

// ===============================
// FITUR: LAPORAN TERBARU
// ===============================

const recentIconMap = {
  chair: Armchair,
  door: DoorOpen,
  network: Wifi,
  wifi: Wifi,
  sent: Send,
  Dikirim: Send,
  Diproses: UserCog,
  Selesai: CheckCircle2,
  done: CheckCircle2,
}

// Menampilkan laporan baru masuk di dashboard
function normalizeIncomingReport(report) {
  const reporterName = report.author ?? report.reporter ?? report.user?.name ?? ''

  return {
    id: report.id ?? report.code ?? report.reportId,
    title: report.title ?? '',
    location: report.location ?? '',
    description: report.description ?? '',
    priority: report.priority ?? '',
    priorityClass: report.priorityClass ?? 'bg-blue-700 text-white',
    imageUrl: report.imageUrl ?? report.image_url ?? null,
    imageClass: report.imageClass ?? 'bg-gradient-to-br from-slate-200 via-slate-100 to-slate-300',
    author: reporterName,
    avatar: report.avatar ?? report.reporterInitial ?? getInitials(reporterName),
    time: report.time ?? report.date ?? report.createdAt ?? report.created_at ?? '',
  }
}

// Menampilkan laporan terbaru di dashboard
function normalizeRecentReport(report) {
  const type = report.iconType ?? report.type ?? ''

  return {
    id: report.id ?? report.code ?? report.reportId,
    title: report.title ?? '',
    location: report.location ?? '',
    time: report.time ?? report.date ?? report.createdAt ?? report.created_at ?? '',
    status: report.status ?? '',
    icon: report.icon ?? recentIconMap[type] ?? recentIconMap[report.status] ?? Send,
    iconClass: report.iconClass ?? 'bg-slate-100 text-blue-700',
    statusClass: report.statusClass ?? getStatusClass(report.status),
  }
}

// ===============================
// FITUR: NAVIGASI ADMIN
// ===============================

// Mengekspor data laporan dari dashboard admin
async function exportData() {
  isExporting.value = true

  try {
    const response = await api.get('/admin/reports/export', {
      responseType: 'blob',
    })
    const url = URL.createObjectURL(response.data)
    const link = document.createElement('a')
    link.href = url
    link.download = 'filkomcare-reports'
    link.click()
    URL.revokeObjectURL(url)
  } finally {
    isExporting.value = false
  }
}

// Menjalankan aksi generate report lalu refresh data dashboard
async function generateReport() {
  isGeneratingReport.value = true

  try {
    await api.post('/admin/reports/generate')
    await fetchDashboard()
  } finally {
    isGeneratingReport.value = false
  }
}

// Navigasi menuju halaman management laporan
function goToReportManagement() {
  router.push('/admin/management-laporan')
}

// Membuka detail laporan melalui halaman management laporan
function openReport() {
  goToReportManagement()
}

// ===============================
// HELPER FUNCTION
// ===============================

// Mengambil payload data dari response API Laravel
function unwrapResponse(response) {
  return response?.data?.data ?? response?.data ?? {}
}

// Menentukan warna badge status laporan
function getStatusClass(status) {
  if (status === 'Dikirim') return 'bg-yellow-100 text-yellow-700'
  if (status === 'Diproses') return 'bg-blue-100 text-blue-700'
  if (status === 'Selesai') return 'bg-green-100 text-green-700'
  if (status === 'Dibatalkan') return 'bg-red-100 text-red-700'
  return 'bg-slate-100 text-slate-700'
}

// Menerjemahkan status laporan sesuai bahasa aktif
function getStatusLabel(status) {
  const labels = {
    Dikirim: t('reports.status_sent'),
    Diproses: t('reports.status_processed'),
    Selesai: t('reports.status_completed'),
    Dibatalkan: t('reports.status_cancelled'),
  }

  return labels[status] || status
}

// Membuat inisial nama pelapor untuk avatar kecil
function getInitials(name = '') {
  return String(name)
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase()
}
</script>

<template>
  <div class="min-h-screen bg-[#f5f4ff] text-slate-900">
    <div class="flex min-h-screen">
      <AdminSidebar />

      <div class="min-w-0 flex-1">
        <DashboardTopbar />

        <main class="px-5 py-9 md:px-8 lg:px-10">
          <section class="mx-auto max-w-[1340px]">
            <div class="mb-8 flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
              <div>
                <h1 class="text-4xl font-extrabold tracking-tight text-slate-950 md:text-5xl">
                  {{ $t('dashboard.admin_title') }}
                </h1>
                <p class="mt-3 text-base text-slate-600 md:text-lg">
                  {{ $t('dashboard.admin_description') }}
                </p>
              </div>

              <div class="flex flex-col gap-3 sm:flex-row">
                <button
                  type="button"
                  :disabled="isExporting"
                  @click="exportData"
                  class="inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-5 text-sm font-bold text-slate-800 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-70"
                >
                  <Download :size="17" />
                  {{ isExporting ? $t('dashboard.exporting') : $t('dashboard.export_data') }}
                </button>

                <button
                  type="button"
                  :disabled="isGeneratingReport"
                  @click="generateReport"
                  class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-blue-700 px-5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-70"
                >
                  {{ isGeneratingReport ? $t('dashboard.generating') : $t('dashboard.generate_report') }}
                </button>
              </div>
            </div>

            <div class="mb-9 grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
              <template v-if="isLoadingDashboard && stats.length === 0">
                <article
                  v-for="index in 4"
                  :key="`stat-loading-${index}`"
                  class="rounded-xl border border-slate-300/80 bg-white p-6 shadow-sm"
                >
                  <div class="mb-5 flex items-start justify-between">
                    <div class="size-10 rounded-lg bg-slate-100"></div>
                    <div class="h-3 w-14 rounded bg-slate-100"></div>
                  </div>

                  <div class="h-9 w-20 rounded bg-slate-100"></div>
                  <div class="mt-3 h-4 w-28 rounded bg-slate-100"></div>
                </article>
              </template>

              <template v-else>
                <article
                  v-for="stat in stats"
                  :key="stat.title"
                  class="rounded-xl border border-slate-300/80 bg-white p-6 shadow-sm"
                >
                  <div class="mb-5 flex items-start justify-between">
                    <div class="grid size-10 place-items-center rounded-lg" :class="stat.iconClass">
                      <component :is="stat.icon" :size="23" />
                    </div>

                    <p class="text-xs font-extrabold text-slate-400">
                      {{ getStatTitle(stat.title) }}
                    </p>
                  </div>

                  <h3 class="text-4xl font-extrabold leading-none tracking-tight text-slate-950">
                    {{ stat.value }}
                  </h3>
                  <p class="mt-2 text-sm text-slate-600">
                  {{ stat.subtitle?.includes?.('.') ? $t(stat.subtitle) : stat.subtitle }}
                  </p>
                </article>
              </template>
            </div>

            <div class="grid gap-7 xl:grid-cols-[1fr_430px]">
              <section>
                <div class="mb-5 flex items-center justify-between">
                  <h2 class="flex items-center gap-2 text-2xl font-extrabold text-slate-950">
                    <Zap :size="24" class="text-blue-700" />
                    {{ $t('dashboard.new_reports') }}
                  </h2>

                  <button
                    type="button"
                    @click="goToReportManagement"
                    class="text-sm font-extrabold text-blue-700 transition hover:text-blue-900"
                  >
                    {{ $t('common.view_all') }}
                  </button>
                </div>

                <div class="flex h-[520px] min-h-[520px] flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                  <div
                    v-if="isLoadingDashboard"
                    class="grid min-h-[520px] flex-1 place-items-center px-6 py-8 text-center"
                  >
                    <p class="text-sm font-bold text-slate-500">
                      {{ $t('common.loading_reports') }}
                    </p>
                  </div>

                  <div
                    v-else-if="displayedIncomingReports.length === 0"
                    class="grid min-h-[520px] flex-1 place-items-center px-6 py-8 text-center"
                  >
                    <p class="text-base font-bold text-slate-700">
                      {{ $t('dashboard.no_new_reports') }}
                    </p>
                  </div>

                  <div
                    v-else
                    class="min-h-0 flex-1 divide-y divide-slate-100 overflow-y-auto"
                  >
                    <article
                      v-for="report in displayedIncomingReports"
                      :key="report.id"
                      class="grid gap-4 px-5 py-4 transition hover:bg-slate-50 md:grid-cols-[132px_1fr]"
                    >
                      <div class="relative h-[112px] overflow-hidden rounded-lg bg-slate-100">
                        <img
                          v-if="report.imageUrl"
                          :src="report.imageUrl"
                          :alt="report.title"
                          class="h-full w-full object-cover"
                        />

                        <div
                          v-else
                          class="h-full w-full"
                          :class="report.imageClass"
                        ></div>

                        <span
                          v-if="report.priority"
                          class="absolute left-3 top-3 rounded px-2.5 py-1 text-[10px] font-extrabold tracking-[0.14em]"
                          :class="report.priorityClass"
                        >
                          {{ report.priority }}
                        </span>
                      </div>

                      <div class="min-w-0">
                        <p class="mb-1 truncate text-xs font-extrabold text-slate-500" :title="report.location">
                          {{ report.location }}
                        </p>

                        <h3 class="truncate text-lg font-extrabold leading-tight text-slate-950" :title="report.title">
                          {{ report.title }}
                        </h3>

                        <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-slate-600">
                          {{ report.description }}
                        </p>

                        <div class="mt-4 flex items-center justify-between gap-3">
                          <div class="flex min-w-0 items-center gap-3">
                            <div
                              class="grid size-8 shrink-0 place-items-center rounded-full bg-slate-800 text-[10px] font-extrabold text-white"
                            >
                              {{ report.avatar }}
                            </div>
                            <p class="truncate text-sm font-medium text-slate-900" :title="report.author">
                              {{ report.author }}
                            </p>
                          </div>

                          <p class="shrink-0 text-xs text-slate-400">
                            {{ report.time }}
                          </p>
                        </div>
                      </div>
                    </article>
                  </div>
                </div>
              </section>

              <section>
                <h2 class="mb-5 text-2xl font-extrabold text-slate-950">
                  {{ $t('dashboard.recent_reports') }}
                </h2>

                <div class="flex h-[520px] min-h-[520px] flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                  <div
                    v-if="isLoadingDashboard"
                    class="grid min-h-[520px] flex-1 place-items-center px-5 py-8 text-center"
                  >
                    <p class="text-sm font-bold text-slate-500">
                      {{ $t('common.loading_reports') }}
                    </p>
                  </div>

                  <div
                    v-else-if="displayedRecentReports.length === 0"
                    class="grid min-h-[520px] flex-1 place-items-center px-5 py-8 text-center"
                  >
                    <p class="text-sm font-bold text-slate-700">
                      {{ $t('dashboard.no_recent_reports') }}
                    </p>
                  </div>

                  <div
                    v-else
                    class="min-h-0 flex-1 divide-y divide-slate-100 overflow-y-auto"
                  >
                    <article
                      v-for="report in displayedRecentReports"
                      :key="report.id"
                      class="group flex items-center gap-4 px-5 py-4 hover:bg-slate-50"
                    >
                      <div
                        class="grid size-13 shrink-0 place-items-center rounded-lg"
                        :class="report.iconClass"
                      >
                        <component :is="report.icon" :size="22" />
                      </div>

                      <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-3">
                          <h3 class="truncate text-base font-extrabold text-slate-950" :title="report.title">
                            {{ report.title }}
                          </h3>

                          <span
                            class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-extrabold"
                            :class="report.statusClass"
                          >
                            {{ getStatusLabel(report.status) }}
                          </span>
                        </div>

                        <p class="mt-1 truncate text-sm text-slate-500" :title="report.location">
                          {{ report.location }}
                        </p>
                        <p class="mt-2 text-xs text-slate-400">
                          {{ report.time }}
                        </p>
                      </div>

                      <button
                        type="button"
                        @click="openReport(report)"
                        class="grid size-8 place-items-center rounded-full text-slate-300 transition group-hover:bg-blue-50 group-hover:text-blue-700"
                        :aria-label="$t('common.open_report', { title: report.title })"
                      >
                        <ChevronRight :size="19" />
                      </button>
                    </article>
                  </div>

                  <button
                    v-if="displayedRecentReports.length > 0"
                    type="button"
                    @click="goToReportManagement"
                    class="w-full border-t border-slate-100 px-5 py-4 text-sm font-extrabold text-blue-700 transition hover:bg-blue-50"
                  >
                    {{ $t('common.show_more') }}
                  </button>
                </div>
              </section>
            </div>
          </section>
        </main>
      </div>
    </div>
  </div>
</template>
