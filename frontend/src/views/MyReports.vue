<script setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import {
  AlertTriangle,
  CalendarDays,
  CheckCircle2,
  ChevronLeft,
  ChevronRight,
  ClipboardList,
  MapPin,
  MessageSquare,
  Plus,
  RefreshCcw,
  Search,
  Send,
  SlidersHorizontal,
  X,
} from 'lucide-vue-next'
import { useRouter } from 'vue-router'

import api from '../services/api'
import DashboardSidebar from '../components/DashboardSidebar.vue'
import DashboardTopbar from '../components/DashboardTopbar.vue'

const router = useRouter()
const { t } = useI18n()

const searchKeyword = ref('')
const selectedStatus = ref('Semua Status')
const selectedSort = ref('Terbaru')
const selectedReport = ref(null)
const reportToCancel = ref(null)
const isDetailModalOpen = ref(false)
const isCancelModalOpen = ref(false)
const isLoadingReports = ref(false)
const isCancellingReport = ref(false)
const reports = ref([])
const summaries = ref([])

const pagination = reactive({
  currentPage: 1,
  perPage: 5,
  total: 0,
})

const statusOptions = ref([
  'Semua Status',
  'Dikirim',
  'Diproses',
  'Selesai',
  'Dibatalkan',
])

watch([isDetailModalOpen, isCancelModalOpen], ([isDetailOpen, isCancelOpen]) => {
  document.body.style.overflow = isDetailOpen || isCancelOpen ? 'hidden' : ''
})

watch([searchKeyword, selectedStatus, selectedSort], () => {
  pagination.currentPage = 1
})

onUnmounted(() => {
  document.body.style.overflow = ''
})

const summaryConfig = [
  {
    key: 'Dikirim',
    label: 'Dikirim',
    icon: Send,
    iconClass: 'bg-yellow-100 text-yellow-700',
  },
  {
    key: 'Diproses',
    label: 'Diproses',
    icon: RefreshCcw,
    iconClass: 'bg-blue-100 text-blue-700',
  },
  {
    key: 'Selesai',
    label: 'Selesai',
    icon: CheckCircle2,
    iconClass: 'bg-green-100 text-green-700',
  },
]

const displaySummaries = computed(() => {
  if (summaries.value.length > 0) {
    return summaries.value
  }

  return summaryConfig.map((summary) => ({
    ...summary,
    value: reports.value.filter((report) => report.status === summary.key).length,
  }))
})

const filteredReports = computed(() => {
  const keyword = searchKeyword.value.toLowerCase().trim()

  const result = reports.value.filter((report) => {
    const matchesKeyword =
      !keyword ||
      String(report.title).toLowerCase().includes(keyword) ||
      String(report.description).toLowerCase().includes(keyword) ||
      String(report.reportId).toLowerCase().includes(keyword) ||
      String(report.location).toLowerCase().includes(keyword) ||
      String(report.status).toLowerCase().includes(keyword) ||
      String(report.category).toLowerCase().includes(keyword)

    const matchesStatus =
      selectedStatus.value === 'Semua Status' ||
      report.status === selectedStatus.value

    return matchesKeyword && matchesStatus
  })

  return [...result].sort((a, b) => {
    if (selectedSort.value === 'Terlama') {
      return Number(a.id ?? 0) - Number(b.id ?? 0)
    }

    return Number(b.id ?? 0) - Number(a.id ?? 0)
  })
})

const paginatedReports = computed(() => {
  const start = (pagination.currentPage - 1) * pagination.perPage
  const end = start + pagination.perPage

  return filteredReports.value.slice(start, end)
})

const filteredTotalReports = computed(() => filteredReports.value.length)
const allReportsTotal = computed(() => reports.value.length)

const pageCount = computed(() => {
  if (filteredReports.value.length === 0) return 0

  return Math.ceil(filteredReports.value.length / pagination.perPage)
})

const visiblePages = computed(() =>
  Array.from({ length: pageCount.value }, (_, index) => index + 1)
)

function unwrapResponse(response) {
  return response?.data?.data ?? response?.data ?? {}
}

function extractReports(payload) {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload.reports)) return payload.reports
  if (Array.isArray(payload.items)) return payload.items
  return []
}

function getStatusClass(status) {
  if (status === 'Dikirim') return 'bg-yellow-100 text-yellow-700'
  if (status === 'Diproses') return 'bg-blue-100 text-blue-700'
  if (status === 'Selesai') return 'bg-green-100 text-green-700'
  if (status === 'Dibatalkan') return 'bg-red-100 text-red-700'

  return 'bg-slate-100 text-slate-700'
}

function getStatusLabel(status) {
  const labels = {
    Dikirim: t('reports.status_sent'),
    Diproses: t('reports.status_processed'),
    Selesai: t('reports.status_completed'),
    Dibatalkan: t('reports.status_cancelled'),
  }

  return labels[status] || status
}

function getStatusOptionLabel(status) {
  return status === 'Semua Status' ? t('reports.all_statuses') : getStatusLabel(status)
}

function getSortLabel(sort) {
  return sort === 'Terlama' ? t('reports.oldest') : t('reports.newest')
}

function normalizeReport(report) {
  const status = report.status ?? report.statusName ?? report.status?.name ?? ''
  const isHistory = ['Selesai', 'Dibatalkan'].includes(status)

  return {
    id: report.id ?? report.reportId ?? report.code,
    reportId: report.reportId ?? report.code ?? report.report_code ?? report.id ?? '',
    title: report.title ?? '',
    description: report.description ?? '',
    status,
    statusClass: report.statusClass ?? getStatusClass(status),
    date: report.date ?? report.createdAt ?? report.created_at ?? '',
    location: report.location ?? '',
    category: report.category ?? report.categoryName ?? report.category?.name ?? '',
    responses: report.responses ?? report.responseSummary ?? '',
    adminResponse: report.adminResponse ?? report.admin_response ?? '',
    action: report.action ?? (isHistory ? 'history' : 'active'),
    imagePath: report.imagePath ?? report.image_path ?? null,
    imageUrl: report.imageUrl ?? report.image_url ?? null,
    imageType: report.imageType ?? report.image_type ?? '',
    titleClass:
      report.titleClass ??
      (isHistory ? 'text-slate-600 line-through decoration-2' : 'text-slate-950'),
  }
}

function normalizeSummary(summary) {
  const config = summaryConfig.find(
    (item) =>
      item.key === summary.key ||
      item.key === summary.status ||
      item.label === summary.label
  )

  return {
    key: config?.key ?? summary.key ?? summary.status ?? summary.label ?? '',
    label: summary.label ?? summary.status ?? config?.label ?? '',
    value: summary.value ?? summary.count ?? 0,
    icon: config?.icon ?? ClipboardList,
    iconClass: config?.iconClass ?? 'bg-slate-100 text-slate-700',
  }
}

async function fetchMyReports() {
  isLoadingReports.value = true

  try {
    const response = await api.get('/reports/my')
    const payload = unwrapResponse(response)

    reports.value = extractReports(payload).map(normalizeReport)
    summaries.value = Array.isArray(payload.summaries)
      ? payload.summaries.map(normalizeSummary)
      : []

    pagination.total = reports.value.length
    pagination.currentPage = 1
  } catch (error) {
    reports.value = []
    summaries.value = []
    pagination.total = 0
  } finally {
    isLoadingReports.value = false
  }
}

function goToCreateReport() {
  router.push('/buat-laporan')
}

function getReportImageClass(type) {
  const classes = {
    ac: 'from-slate-950 via-slate-800 to-stone-400',
    chair: 'from-emerald-950 via-slate-900 to-slate-500',
    lamp: 'from-yellow-900 via-amber-300 to-yellow-50',
  }

  return classes[type] || 'from-slate-800 to-slate-400'
}

function setPage(page) {
  if (page < 1 || page > pageCount.value) return

  pagination.currentPage = page
}

function openDetailModal(report) {
  selectedReport.value = report
  isDetailModalOpen.value = true
}

function closeDetailModal() {
  selectedReport.value = null
  isDetailModalOpen.value = false
}

function openCancelModal(report) {
  if (report.status !== 'Dikirim') return

  reportToCancel.value = report
  isCancelModalOpen.value = true
}

function closeCancelModal() {
  reportToCancel.value = null
  isCancelModalOpen.value = false
}

async function confirmCancelReport() {
  if (!reportToCancel.value) return

  isCancellingReport.value = true

  try {
    const response = await api.patch(`/reports/${reportToCancel.value.id}/cancel`)
    const payload = unwrapResponse(response)
    const updatedReport = normalizeReport(payload.report ?? payload)

    reports.value = reports.value.map((report) =>
      report.id === reportToCancel.value.id
        ? {
            ...report,
            ...updatedReport,
            status: updatedReport.status || 'Dibatalkan',
            statusClass: updatedReport.status
              ? updatedReport.statusClass
              : getStatusClass('Dibatalkan'),
            action: updatedReport.action || 'history',
            titleClass:
              updatedReport.titleClass || 'text-slate-600 line-through decoration-2',
          }
        : report
    )

    closeCancelModal()
  } finally {
    isCancellingReport.value = false
  }
}

onMounted(fetchMyReports)
</script>

<template>
  <div class="min-h-screen bg-[#faf8ff] text-slate-900">
    <div class="flex min-h-screen">
      <DashboardSidebar />

      <div class="min-w-0 flex-1">
        <DashboardTopbar />

        <main class="px-5 py-5 md:px-8 lg:px-10">
          <section class="mx-auto max-w-[1240px]">
            <div class="mb-8 flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
              <div>
                <h1 class="text-4xl font-extrabold tracking-tight text-slate-950 md:text-5xl">
                  {{ $t('reports.my_title') }}
                </h1>
                <p class="mt-3 text-base text-slate-600 md:text-lg">
                  {{ $t('reports.my_description') }}
                </p>
              </div>

              <button
                type="button"
                @click="goToCreateReport"
                class="inline-flex h-12 items-center justify-center gap-3 rounded-lg bg-blue-700 px-6 text-sm font-bold text-white shadow-[0_8px_18px_rgba(29,78,216,0.28)] transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-200"
              >
                <Plus :size="21" />
                {{ $t('reports.create_new') }}
              </button>
            </div>

            <div class="mb-8 grid gap-6 xl:grid-cols-[1fr_300px]">
              <div class="grid gap-6 md:grid-cols-3">
                <article
                  v-for="summary in displaySummaries"
                  :key="summary.label"
                  class="flex items-center gap-4 rounded-xl border border-slate-300 bg-white p-6 shadow-sm"
                >
                  <div
                    class="grid size-14 shrink-0 place-items-center rounded-full"
                    :class="summary.iconClass"
                  >
                    <component :is="summary.icon" :size="25" />
                  </div>

                  <div>
                    <p class="text-sm font-medium text-slate-600">
                      {{ getStatusLabel(summary.key || summary.label) }}
                    </p>
                    <h2 class="text-3xl font-extrabold leading-none tracking-tight text-slate-950">
                      {{ summary.value }}
                    </h2>
                  </div>
                </article>
              </div>

              <aside class="rounded-xl border border-dashed border-slate-300 bg-white/60 p-5">
                <div class="mb-4 flex items-center justify-between">
                  <h3 class="text-sm font-extrabold text-slate-700">
                    {{ $t('reports.quick_filters') }}
                  </h3>

                  <SlidersHorizontal :size="17" class="text-slate-500" />
                </div>

                <div class="grid grid-cols-2 gap-2">
                  <button
                    type="button"
                    @click="selectedSort = 'Terbaru'"
                    class="h-9 rounded-lg px-4 text-xs font-extrabold shadow-sm transition"
                    :class="
                      selectedSort === 'Terbaru'
                        ? 'bg-blue-700 text-white hover:bg-blue-800'
                        : 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50'
                    "
                  >
                    {{ getSortLabel('Terbaru') }}
                  </button>

                  <button
                    type="button"
                    @click="selectedSort = 'Terlama'"
                    class="h-9 rounded-lg px-4 text-xs font-extrabold shadow-sm transition"
                    :class="
                      selectedSort === 'Terlama'
                        ? 'bg-blue-700 text-white hover:bg-blue-800'
                        : 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50'
                    "
                  >
                    {{ getSortLabel('Terlama') }}
                  </button>
                </div>
              </aside>
            </div>

            <section class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
              <div class="grid gap-3 md:grid-cols-[1fr_180px]">
                <label class="relative">
                  <Search
                    :size="19"
                    class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"
                  />

                  <input
                    v-model="searchKeyword"
                    type="search"
                    :placeholder="$t('reports.search_placeholder')"
                    class="h-11 w-full rounded-lg border border-slate-200 bg-slate-50 pl-11 pr-4 text-sm font-medium text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                  />
                </label>

                <select
                  v-model="selectedStatus"
                  class="h-11 rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                >
                  <option
                    v-for="status in statusOptions"
                    :key="status"
                  >
                    {{ getStatusOptionLabel(status) }}
                  </option>
                </select>
              </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-slate-300 bg-white shadow-sm">
              <div class="flex border-b border-slate-300 px-5 py-4">
                <div class="flex items-center gap-3">
                  <h2 class="text-2xl font-extrabold text-slate-950">
                    {{ $t('reports.report_list') }}
                  </h2>

                  <span class="rounded-md bg-blue-100 px-3 py-1 text-xs font-extrabold text-blue-700">
                    {{ allReportsTotal }} {{ $t('common.total') }}
                  </span>
                </div>
              </div>

              <div class="divide-y divide-slate-200">
                <div
                  v-if="isLoadingReports"
                  class="px-6 py-12 text-center"
                >
                  <p class="text-base font-bold text-slate-700">
                    {{ $t('common.loading_reports') }}
                  </p>
                  <p class="mt-1 text-sm text-slate-500">
                    {{ $t('common.please_wait') }}
                  </p>
                </div>

                <article
                  v-for="report in paginatedReports"
                  v-else
                  :key="report.id"
                  class="p-5"
                >
                  <div class="grid gap-5 xl:grid-cols-[160px_1fr_190px]">
                    <div
                      class="relative h-[130px] overflow-hidden rounded-lg bg-slate-100"
                    >
                      <img
                        v-if="report.imageUrl"
                        :src="report.imageUrl"
                        :alt="report.title"
                        class="h-full w-full object-cover"
                      />

                      <div
                        v-else
                        class="h-full w-full bg-gradient-to-br"
                        :class="getReportImageClass(report.imageType)"
                      ></div>
                    </div>

                    <div class="min-w-0">
                      <div class="mb-3 flex flex-wrap items-center gap-3">
                        <span
                          class="rounded-full px-3 py-1 text-xs font-extrabold"
                          :class="report.statusClass"
                        >
                          {{ getStatusLabel(report.status) }}
                        </span>
                      </div>

                      <h3
                        class="truncate text-xl font-extrabold leading-tight"
                        :class="report.titleClass"
                        :title="report.title"
                      >
                        {{ report.title }}
                      </h3>

                      <p
                        class="mt-3 line-clamp-2 text-sm leading-relaxed text-slate-600"
                        :title="report.description"
                      >
                        {{ report.description }}
                      </p>

                      <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm font-semibold text-slate-500">
                        <span class="inline-flex items-center gap-2">
                          <CalendarDays :size="16" />
                          {{ report.date }}
                        </span>

                        <span class="inline-flex min-w-0 items-center gap-2">
                          <MapPin :size="16" class="shrink-0" />
                          <span class="max-w-[210px] truncate">
                            {{ report.location }}
                          </span>
                        </span>

                        <span
                          class="inline-flex items-center gap-2"
                          :class="report.action === 'history' ? 'text-green-600' : 'text-slate-500'"
                        >
                          <CheckCircle2 v-if="report.action === 'history'" :size="16" />
                          <MessageSquare v-else :size="16" />
                          {{ report.responses }}
                        </span>
                      </div>
                    </div>

                    <div class="flex flex-col items-start justify-between gap-4 xl:items-end">
                      <p class="text-sm font-medium text-slate-700">
                        ID: {{ report.reportId }}
                      </p>

                      <div class="flex gap-3 xl:flex-col xl:items-end">
                        <button
                          v-if="report.action === 'active'"
                          type="button"
                          @click="openDetailModal(report)"
                          class="h-10 rounded-lg border border-slate-300 bg-white px-7 text-sm font-extrabold text-slate-800 transition hover:bg-slate-50"
                        >
                          {{ $t('common.details') }}
                        </button>

                        <button
                          v-if="report.status === 'Dikirim'"
                          type="button"
                          @click="openCancelModal(report)"
                          class="h-10 px-3 text-sm font-extrabold text-red-600 transition hover:text-red-700"
                        >
                          {{ $t('reports.cancel_report') }}
                        </button>

                        <button
                          v-if="report.action === 'history'"
                          type="button"
                          @click="openDetailModal(report)"
                          class="h-10 rounded-lg border border-slate-300 bg-white px-6 text-sm font-extrabold text-slate-800 transition hover:bg-slate-50"
                        >
                          {{ $t('reports.history') }}
                        </button>
                      </div>
                    </div>
                  </div>
                </article>

                <div
                  v-if="filteredReports.length === 0 && !isLoadingReports"
                  class="px-6 py-12 text-center"
                >
                  <p class="text-base font-bold text-slate-700">
                  {{ $t('reports.no_reports') }}
                  </p>
                  <p class="mt-1 text-sm text-slate-500">
                  {{ $t('reports.try_again') }}
                  </p>
                </div>
              </div>

              <div class="flex flex-col gap-5 border-t border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
                <p class="text-sm font-medium text-slate-600">
                  {{ $t('common.showing') }} {{ paginatedReports.length }} {{ $t('common.of') }} {{ filteredTotalReports }} {{ $t('common.reports') }}
                </p>

                <div
                  v-if="pageCount > 1"
                  class="flex items-center gap-2"
                >
                  <button
                    type="button"
                    :disabled="pagination.currentPage === 1"
                    @click="setPage(pagination.currentPage - 1)"
                    class="grid size-10 place-items-center rounded-lg border border-slate-300 bg-white text-slate-400 transition hover:bg-slate-50"
                    :class="pagination.currentPage === 1 ? 'cursor-not-allowed opacity-50' : ''"
                  >
                    <ChevronLeft :size="18" />
                  </button>

                  <button
                    v-for="page in visiblePages"
                    :key="page"
                    type="button"
                    @click="setPage(page)"
                    class="grid size-10 place-items-center rounded-lg text-sm font-extrabold transition"
                    :class="
                      pagination.currentPage === page
                        ? 'bg-blue-700 text-white'
                        : 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50'
                    "
                  >
                    {{ page }}
                  </button>

                  <button
                    type="button"
                    :disabled="pagination.currentPage === pageCount"
                    @click="setPage(pagination.currentPage + 1)"
                    class="grid size-10 place-items-center rounded-lg border border-slate-300 bg-white text-slate-700 transition hover:bg-slate-50"
                    :class="pagination.currentPage === pageCount ? 'cursor-not-allowed opacity-50' : ''"
                  >
                    <ChevronRight :size="18" />
                  </button>
                </div>
              </div>
            </section>
          </section>
        </main>
      </div>
    </div>

    <div
      v-if="isDetailModalOpen && selectedReport"
      class="fixed inset-0 z-[70] overflow-y-auto bg-slate-950/40 px-4 py-10 backdrop-blur-sm"
      @click.self="closeDetailModal"
    >
      <div class="mx-auto flex min-h-full w-full max-w-[680px] items-start justify-center">
        <div class="w-full overflow-hidden rounded-2xl bg-white shadow-[0_20px_70px_rgba(15,23,42,0.28)]">
          <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5">
            <div>
              <p class="text-sm font-extrabold text-blue-700">
                {{ selectedReport.reportId }}
              </p>
              <h2 class="mt-1 text-2xl font-extrabold leading-tight text-slate-950">
                {{ $t('reports.detail_title') }}
              </h2>
            </div>

            <button
              type="button"
              class="grid size-9 shrink-0 place-items-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
              :aria-label="$t('common.close')"
              @click="closeDetailModal"
            >
              <X :size="20" />
            </button>
          </div>

          <div class="px-6 py-6">
            <div
              class="mb-5 h-[190px] overflow-hidden rounded-xl bg-slate-100"
            >
              <img
                v-if="selectedReport.imageUrl"
                :src="selectedReport.imageUrl"
                :alt="selectedReport.title"
                class="h-full w-full object-cover"
              />

              <div
                v-else
                class="h-full w-full bg-gradient-to-br"
                :class="getReportImageClass(selectedReport.imageType)"
              ></div>
            </div>

            <div class="mb-4">
              <span
                class="inline-flex rounded-full px-3 py-1 text-xs font-extrabold"
                :class="selectedReport.statusClass"
              >
                {{ getStatusLabel(selectedReport.status) }}
              </span>
            </div>

            <h3 class="text-2xl font-extrabold leading-tight text-slate-950">
              {{ selectedReport.title }}
            </h3>

            <p class="mt-4 text-base leading-relaxed text-slate-600">
              {{ selectedReport.description }}
            </p>

            <div class="mt-6 grid gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-2">
              <div>
                <p class="text-xs font-extrabold uppercase tracking-wide text-slate-400">
                  {{ $t('reports.category') }}
                </p>
                <p class="mt-1 text-sm font-bold text-slate-700">
                  {{ selectedReport.category }}
                </p>
              </div>

              <div>
                <p class="text-xs font-extrabold uppercase tracking-wide text-slate-400">
                  {{ $t('reports.location') }}
                </p>
                <p class="mt-1 text-sm font-bold text-slate-700">
                  {{ selectedReport.location }}
                </p>
              </div>

              <div>
                <p class="text-xs font-extrabold uppercase tracking-wide text-slate-400">
                  {{ $t('common.time') }}
                </p>
                <p class="mt-1 text-sm font-bold text-slate-700">
                  {{ selectedReport.date }}
                </p>
              </div>

              <div>
                <p class="text-xs font-extrabold uppercase tracking-wide text-slate-400">
                  {{ $t('reports.admin_response') }}
                </p>
                <p class="mt-1 text-sm font-bold leading-relaxed text-slate-700">
                  {{ selectedReport.adminResponse || $t('reports.no_admin_response') }}
                </p>
              </div>
            </div>
          </div>

          <div class="flex justify-end border-t border-slate-100 bg-slate-50 px-6 py-4">
            <button
              type="button"
              class="inline-flex h-11 items-center justify-center rounded-lg bg-blue-700 px-5 text-sm font-extrabold text-white transition hover:bg-blue-800"
              @click="closeDetailModal"
            >
              {{ $t('common.close') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="isCancelModalOpen && reportToCancel"
      class="fixed inset-0 z-[70] overflow-y-auto bg-slate-950/40 px-4 py-10 backdrop-blur-sm"
      @click.self="closeCancelModal"
    >
      <div class="mx-auto flex min-h-full w-full max-w-[520px] items-start justify-center">
        <div class="w-full overflow-hidden rounded-2xl bg-white shadow-[0_20px_70px_rgba(15,23,42,0.28)]">
          <div class="flex items-start gap-4 border-b border-slate-200 px-6 py-5">
            <div class="grid size-11 shrink-0 place-items-center rounded-full bg-red-100 text-red-700">
              <AlertTriangle :size="22" />
            </div>

            <div class="min-w-0">
              <h2 class="text-2xl font-extrabold leading-tight text-slate-950">
                {{ $t('reports.cancel_title') }}
              </h2>
              <p class="mt-1 text-sm font-medium text-slate-500">
                {{ $t('reports.cancel_subtitle') }}
              </p>
            </div>
          </div>

          <div class="px-6 py-6">
            <p class="text-base leading-relaxed text-slate-600">
              {{ $t('reports.cancel_confirm_named', { title: reportToCancel.title, id: reportToCancel.reportId }) }}
            </p>

            <p class="mt-3 rounded-lg bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
              {{ $t('reports.cancel_warning_active') }}
            </p>
          </div>

          <div class="flex flex-col gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end">
            <button
              type="button"
              class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-300 bg-white px-5 text-sm font-extrabold text-slate-700 transition hover:bg-slate-100"
              @click="closeCancelModal"
            >
              {{ $t('common.no') }}
            </button>

            <button
              type="button"
              :disabled="isCancellingReport"
              class="inline-flex h-11 items-center justify-center rounded-lg bg-red-600 px-5 text-sm font-extrabold text-white transition hover:bg-red-700"
              :class="isCancellingReport ? 'cursor-not-allowed opacity-70' : ''"
              @click="confirmCancelReport"
            >
              {{ isCancellingReport ? $t('common.cancelling') : $t('common.yes_cancel') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
