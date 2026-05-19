<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import {
  CalendarClock,
  CheckCircle2,
  ChevronDown,
  ChevronLeft,
  ChevronRight,
  ClipboardList,
  FileImage,
  FileText,
  Search,
  Settings2,
  X,
} from 'lucide-vue-next'

import api from '../services/api'
import AdminSidebar from '../components/AdminSidebar.vue'
import DashboardTopbar from '../components/DashboardTopbar.vue'

const searchKeyword = ref('')
const selectedStatus = ref('Semua Status')
const selectedCategory = ref('Semua Kategori')

const statusOptions = ref([
  'Semua Status',
  'Dikirim',
  'Diproses',
  'Selesai',
])

const categoryOptions = ref(['Semua Kategori'])

const isStatusModalOpen = ref(false)
const isLoadingReports = ref(false)
const isSavingStatus = ref(false)

const selectedReport = ref(null)
const selectedImage = ref(null)
const selectedUpdateStatus = ref('')
const adminNote = ref('')
const isImagePreviewOpen = ref(false)

const stats = ref([])
const reports = ref([])

const pagination = reactive({
  currentPage: 1,
  perPage: 5,
  total: 0,
})

const statIconMap = {
  total: FileText,
  dikirim: CalendarClock,
  pending: CalendarClock,
  diproses: Settings2,
  selesai: CheckCircle2,
}

const statStyleMap = {
  total: {
    descriptionClass: 'text-green-600',
    iconClass: 'bg-blue-50 text-blue-700',
  },
  dikirim: {
    descriptionClass: 'text-slate-400',
    iconClass: 'bg-yellow-50 text-yellow-600',
  },
  pending: {
    descriptionClass: 'text-slate-400',
    iconClass: 'bg-yellow-50 text-yellow-600',
  },
  diproses: {
    descriptionClass: 'text-slate-400',
    iconClass: 'bg-blue-50 text-blue-700',
  },
  selesai: {
    descriptionClass: 'text-green-600',
    iconClass: 'bg-green-50 text-green-700',
  },
}

watch([searchKeyword, selectedStatus, selectedCategory], () => {
  pagination.currentPage = 1
})

const filteredReports = computed(() => {
  const keyword = searchKeyword.value.toLowerCase().trim()

  return reports.value.filter((report) => {
    const matchesKeyword =
      !keyword ||
      String(report.title).toLowerCase().includes(keyword) ||
      String(report.reporter).toLowerCase().includes(keyword) ||
      String(report.category).toLowerCase().includes(keyword) ||
      String(report.location).toLowerCase().includes(keyword) ||
      String(report.status).toLowerCase().includes(keyword)

    const matchesStatus =
      selectedStatus.value === 'Semua Status' ||
      report.status === selectedStatus.value

    const matchesCategory =
      selectedCategory.value === 'Semua Kategori' ||
      report.category === selectedCategory.value

    return matchesKeyword && matchesStatus && matchesCategory
  })
})

const paginatedReports = computed(() => {
  const start = (pagination.currentPage - 1) * pagination.perPage
  const end = start + pagination.perPage

  return filteredReports.value.slice(start, end)
})

const totalReports = computed(() => filteredReports.value.length)

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

function getStatKey(stat) {
  return String(stat.key ?? stat.status ?? stat.title ?? '')
    .toLowerCase()
    .replace(/\s+/g, '-')
}

function normalizeStat(stat) {
  const key = getStatKey(stat)
  const style = statStyleMap[key] || statStyleMap.total

  return {
    title: stat.title ?? stat.label ?? '',
    value: stat.value ?? stat.count ?? '',
    description: stat.description ?? stat.subtitle ?? '',
    descriptionClass: stat.descriptionClass ?? style.descriptionClass,
    icon: statIconMap[key] || FileText,
    iconClass: stat.iconClass ?? style.iconClass,
  }
}

function getStatusClass(status) {
  if (status === 'Dikirim') return 'bg-yellow-100 text-yellow-700'
  if (status === 'Diproses') return 'bg-blue-100 text-blue-700'
  if (status === 'Selesai') return 'bg-green-100 text-green-700'

  return 'bg-slate-100 text-slate-700'
}

function getReporterInitial(name) {
  return String(name || '')
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase()
}

function normalizeReport(report) {
  const reporter =
    report.reporter ??
    report.reporterName ??
    report.user?.name ??
    report.user?.nama ??
    ''

  const status =
    report.status ??
    report.statusName ??
    report.status?.name ??
    ''

  return {
    id: report.id ?? report.code ?? report.reportId,
    code: report.code ?? report.reportId ?? report.report_code ?? report.id ?? '',
    title: report.title ?? '',
    description: report.description ?? '',
    reporter,
    reporterInitial: report.reporterInitial ?? getReporterInitial(reporter),
    category: report.category ?? report.categoryName ?? report.category?.name ?? '',
    location: report.location ?? '',
    status,
    statusClass: report.statusClass ?? getStatusClass(status),
    date: report.date ?? report.createdAt ?? report.created_at ?? '',
    imagePath: report.imagePath ?? report.image_path ?? null,
    imageUrl: report.imageUrl ?? report.image_url ?? null,
    adminResponse: report.adminResponse ?? report.admin_response ?? '',
  }
}

function syncCategoryOptions(payload, normalizedReports) {
  const categories = Array.isArray(payload.categories)
    ? payload.categories
    : [...new Set(normalizedReports.map((report) => report.category).filter(Boolean))]

  categoryOptions.value = ['Semua Kategori', ...categories]
}

async function fetchStats() {
  try {
    const response = await api.get('/admin/reports/stats')
    const payload = unwrapResponse(response)

    stats.value = Array.isArray(payload.stats)
      ? payload.stats.map(normalizeStat)
      : []
  } catch (error) {
    stats.value = []
  }
}

async function fetchReports() {
  isLoadingReports.value = true

  try {
    const response = await api.get('/admin/reports')
    const payload = unwrapResponse(response)
    const normalizedReports = extractReports(payload).map(normalizeReport)

    reports.value = normalizedReports
    pagination.total = normalizedReports.length
    pagination.currentPage = 1
    syncCategoryOptions(payload, normalizedReports)
  } catch (error) {
    reports.value = []
    pagination.total = 0
    categoryOptions.value = ['Semua Kategori']
  } finally {
    isLoadingReports.value = false
  }
}

function openStatusModal(report) {
  selectedReport.value = { ...report }
  selectedUpdateStatus.value = report.status
  adminNote.value = report.adminResponse || ''
  isStatusModalOpen.value = true
}

function closeStatusModal() {
  isStatusModalOpen.value = false
  selectedReport.value = null
  selectedUpdateStatus.value = ''
  adminNote.value = ''
}

function openImagePreview(report) {
  if (!report.imageUrl) return

  selectedImage.value = { ...report }
  isImagePreviewOpen.value = true
}

function closeImagePreview() {
  isImagePreviewOpen.value = false
  selectedImage.value = null
}

async function saveStatusUpdate() {
  if (!selectedReport.value || !selectedUpdateStatus.value) return

  isSavingStatus.value = true

  try {
    const response = await api.patch(`/admin/reports/${selectedReport.value.id}/status`, {
      status: selectedUpdateStatus.value,
      note: adminNote.value,
      admin_response: adminNote.value,
    })

    const payload = unwrapResponse(response)
    const updatedReport = normalizeReport(payload.report ?? payload)

    reports.value = reports.value.map((report) =>
      report.id === selectedReport.value.id
        ? {
            ...report,
            ...updatedReport,
            status: updatedReport.status || selectedUpdateStatus.value,
            statusClass: updatedReport.status
              ? updatedReport.statusClass
              : getStatusClass(selectedUpdateStatus.value),
          }
        : report
    )

    await fetchStats()
    closeStatusModal()
  } finally {
    isSavingStatus.value = false
  }
}

function setPage(page) {
  if (page < 1 || page > pageCount.value) return

  pagination.currentPage = page
}

onMounted(() => {
  fetchStats()
  fetchReports()
})
</script>

<template>
  <div class="min-h-screen bg-[#faf8ff] text-slate-900">
    <div class="flex min-h-screen">
      <AdminSidebar />

      <div class="min-w-0 flex-1">
        <DashboardTopbar />

        <main class="px-5 py-8 md:px-8 lg:px-10">
          <section class="mx-auto max-w-[1280px]">
            <div class="mb-8">
              <h1 class="text-4xl font-extrabold tracking-tight text-slate-950 md:text-5xl">
                Management Laporan
              </h1>
              <p class="mt-3 text-base text-slate-600 md:text-lg">
                Kelola dan pantau seluruh laporan fasilitas di lingkungan Filkom.
              </p>
            </div>

            <div
              v-if="stats.length > 0"
              class="mb-8 grid gap-6 sm:grid-cols-2 xl:grid-cols-4"
            >
              <article
                v-for="stat in stats"
                :key="stat.title"
                class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
              >
                <div class="mb-8 flex items-start justify-between">
                  <p class="text-xs font-extrabold uppercase tracking-wide text-slate-400">
                    {{ stat.title }}
                  </p>

                  <div
                    class="grid size-10 place-items-center rounded-lg"
                    :class="stat.iconClass"
                  >
                    <component :is="stat.icon" :size="22" />
                  </div>
                </div>

                <h2 class="text-4xl font-extrabold leading-none tracking-tight text-slate-950">
                  {{ stat.value }}
                </h2>

                <p
                  class="mt-2 text-sm font-medium"
                  :class="stat.descriptionClass"
                >
                  {{ stat.description }}
                </p>
              </article>
            </div>

            <section class="mb-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
              <div class="mb-4 flex items-center justify-between gap-4">
                <div>
                  <h2 class="text-lg font-extrabold text-slate-950">
                    Filter Laporan
                  </h2>
                </div>
              </div>

              <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_180px_240px]">
                <label class="relative">
                  <Search
                    :size="20"
                    class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"
                  />

                  <input
                    v-model="searchKeyword"
                    type="search"
                    placeholder="Cari berdasarkan judul, pelapor, kategori, lokasi..."
                    class="h-12 w-full rounded-lg border border-slate-200 bg-slate-50 pl-12 pr-4 text-sm font-medium text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                  />
                </label>

                <div class="relative">
                  <select
                    v-model="selectedStatus"
                    class="h-12 w-full appearance-none rounded-lg border border-slate-200 bg-slate-50 px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                  >
                    <option
                      v-for="status in statusOptions"
                      :key="status"
                    >
                      {{ status }}
                    </option>
                  </select>

                  <ChevronDown
                    :size="18"
                    class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-slate-500"
                  />
                </div>

                <div class="relative">
                  <select
                    v-model="selectedCategory"
                    class="h-12 w-full appearance-none rounded-lg border border-slate-200 bg-slate-50 px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                  >
                    <option
                      v-for="category in categoryOptions"
                      :key="category"
                    >
                      {{ category }}
                    </option>
                  </select>

                  <ChevronDown
                    :size="18"
                    class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-slate-500"
                  />
                </div>
              </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
              <div class="overflow-x-auto">
                <table class="min-w-[1380px] table-fixed border-collapse">
                  <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-left">
                      <th class="w-[130px] px-7 py-5 text-sm font-extrabold text-slate-500">
                        Foto
                      </th>
                      <th class="w-[250px] px-7 py-5 text-sm font-extrabold text-slate-500">
                        Judul
                      </th>
                      <th class="w-[210px] px-7 py-5 text-sm font-extrabold text-slate-500">
                        Pelapor
                      </th>
                      <th class="w-[190px] px-7 py-5 text-sm font-extrabold text-slate-500">
                        Kategori
                      </th>
                      <th class="w-[240px] px-7 py-5 text-sm font-extrabold text-slate-500">
                        Lokasi
                      </th>
                      <th class="w-[160px] px-7 py-5 text-sm font-extrabold text-slate-500">
                        Status
                      </th>
                      <th class="w-[170px] px-7 py-5 text-sm font-extrabold text-slate-500">
                        Tanggal
                      </th>
                      <th class="w-[140px] px-7 py-5 text-center text-sm font-extrabold text-slate-500">
                        Aksi
                      </th>
                    </tr>
                  </thead>

                  <tbody>
                    <tr v-if="isLoadingReports">
                      <td colspan="8" class="px-7 py-12 text-center">
                        <p class="text-base font-bold text-slate-700">
                          Memuat laporan...
                        </p>
                      </td>
                    </tr>

                    <tr
                      v-for="report in paginatedReports"
                      v-else
                      :key="report.id"
                      class="border-b border-slate-100 last:border-b-0 hover:bg-slate-50"
                    >
                      <td class="px-7 py-6">
                        <button
                          type="button"
                          :disabled="!report.imageUrl"
                          @click="openImagePreview(report)"
                          class="relative h-16 w-20 overflow-hidden rounded-lg border border-slate-200 bg-slate-100 transition"
                          :class="
                            report.imageUrl
                              ? 'cursor-pointer hover:border-blue-400 hover:ring-4 hover:ring-blue-100'
                              : 'cursor-not-allowed opacity-70'
                          "
                          :aria-label="
                            report.imageUrl
                              ? `Preview foto ${report.title}`
                              : `Tidak ada foto untuk ${report.title}`
                          "
                        >
                          <img
                            v-if="report.imageUrl"
                            :src="report.imageUrl"
                            :alt="report.title"
                            class="h-full w-full object-cover"
                          />

                          <div
                            v-else
                            class="grid h-full w-full place-items-center text-slate-400"
                          >
                            <FileImage :size="22" />
                          </div>
                        </button>
                      </td>

                      <td class="px-7 py-6">
                        <p
                          class="truncate text-base font-extrabold text-slate-900"
                          :title="report.title"
                        >
                          {{ report.title }}
                        </p>
                      </td>

                      <td class="px-7 py-6">
                        <div class="flex items-center gap-3">
                          <div
                            class="grid size-7 shrink-0 place-items-center rounded-full bg-slate-800 text-[9px] font-extrabold text-white"
                          >
                            {{ report.reporterInitial }}
                          </div>

                          <p
                            class="truncate text-sm font-medium text-slate-700"
                            :title="report.reporter"
                          >
                            {{ report.reporter }}
                          </p>
                        </div>
                      </td>

                      <td class="px-7 py-6">
                        <span class="inline-block max-w-full truncate rounded bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-700">
                          {{ report.category }}
                        </span>
                      </td>

                      <td class="px-7 py-6">
                        <p
                          class="truncate text-sm font-medium text-slate-500"
                          :title="report.location"
                        >
                          {{ report.location }}
                        </p>
                      </td>

                      <td class="px-7 py-6">
                        <span
                          class="rounded-full px-3 py-1 text-xs font-extrabold"
                          :class="report.statusClass"
                        >
                          {{ report.status }}
                        </span>
                      </td>

                      <td class="px-7 py-6">
                        <p class="text-sm font-medium text-slate-500">
                          {{ report.date }}
                        </p>
                      </td>

                      <td class="px-7 py-6">
                        <div class="flex items-center justify-center">
                          <button
                            type="button"
                            @click="openStatusModal(report)"
                            class="grid size-9 place-items-center rounded-lg text-slate-400 transition hover:bg-blue-50 hover:text-blue-700"
                            :aria-label="`Ubah status ${report.title}`"
                          >
                            <ClipboardList :size="18" />
                          </button>
                        </div>
                      </td>
                    </tr>

                    <tr v-if="filteredReports.length === 0 && !isLoadingReports">
                      <td colspan="8" class="px-7 py-12 text-center">
                        <p class="text-base font-bold text-slate-700">
                          Tidak ada laporan ditemukan.
                        </p>
                        <p class="mt-1 text-sm text-slate-500">
                          Coba gunakan kata kunci atau filter yang berbeda.
                        </p>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div
                class="flex flex-col gap-4 border-t border-slate-100 px-7 py-5 md:flex-row md:items-center md:justify-between"
              >
                <p class="text-sm font-medium text-slate-500">
                  Menampilkan {{ paginatedReports.length }} dari {{ totalReports }} laporan
                </p>

                <div
                  v-if="pageCount > 1"
                  class="flex items-center gap-2"
                >
                  <button
                    type="button"
                    :disabled="pagination.currentPage === 1"
                    @click="setPage(pagination.currentPage - 1)"
                    class="grid size-9 place-items-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50"
                    :class="pagination.currentPage === 1 ? 'cursor-not-allowed opacity-50' : ''"
                  >
                    <ChevronLeft :size="18" />
                  </button>

                  <button
                    v-for="page in visiblePages"
                    :key="page"
                    type="button"
                    @click="setPage(page)"
                    class="grid size-9 place-items-center rounded-lg text-sm font-extrabold transition"
                    :class="
                      pagination.currentPage === page
                        ? 'bg-blue-700 text-white'
                        : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                    "
                  >
                    {{ page }}
                  </button>

                  <button
                    type="button"
                    :disabled="pagination.currentPage === pageCount"
                    @click="setPage(pagination.currentPage + 1)"
                    class="grid size-9 place-items-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-50"
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
      v-if="isStatusModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/30 px-4 backdrop-blur-sm"
    >
      <div class="w-full max-w-[620px] rounded-2xl bg-white shadow-[0_20px_60px_rgba(15,23,42,0.22)]">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
          <h2 class="text-[28px] font-extrabold tracking-tight text-slate-950">
            Update Status Laporan
          </h2>

          <button
            type="button"
            @click="closeStatusModal"
            class="grid size-9 place-items-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
            aria-label="Tutup popup"
          >
            <X :size="20" />
          </button>
        </div>

        <div class="space-y-5 px-6 py-6">
          <div
            v-if="selectedReport"
            class="overflow-hidden rounded-xl border border-blue-100 bg-blue-50"
          >
            <div class="grid gap-4 p-4 sm:grid-cols-[120px_1fr]">
              <div
                class="h-24 overflow-hidden rounded-lg border border-blue-100 bg-white"
              >
                <img
                  v-if="selectedReport.imageUrl"
                  :src="selectedReport.imageUrl"
                  :alt="selectedReport.title"
                  class="h-full w-full object-cover"
                />

                <div
                  v-else
                  class="grid h-full w-full place-items-center text-slate-400"
                >
                  <FileImage :size="28" />
                </div>
              </div>

              <div class="min-w-0">
                <p
                  class="truncate text-base font-extrabold text-blue-700"
                  :title="selectedReport.title"
                >
                  {{ selectedReport.title }}
                </p>

                <p
                  class="mt-1 truncate text-sm text-blue-600"
                  :title="selectedReport.location"
                >
                  Dilaporkan oleh {{ selectedReport.reporter }} / {{ selectedReport.location }}
                </p>

                <p class="mt-3 text-xs font-extrabold uppercase tracking-wide text-blue-700">
                  {{ selectedReport.category }}
                </p>
              </div>
            </div>
          </div>

          <div>
            <p class="mb-3 text-sm font-semibold text-slate-700">
              Ubah Status Menjadi:
            </p>

            <div class="space-y-3">
              <button
                type="button"
                @click="selectedUpdateStatus = 'Dikirim'"
                class="flex w-full items-center justify-between rounded-xl border px-4 py-4 text-left transition"
                :class="
                  selectedUpdateStatus === 'Dikirim'
                    ? 'border-yellow-400 bg-yellow-50'
                    : 'border-slate-200 bg-white hover:border-slate-300'
                "
              >
                <div class="flex items-center gap-3">
                  <span class="size-3 rounded-full bg-yellow-400"></span>
                  <span class="text-base font-semibold text-slate-800">
                    Dikirim
                  </span>
                </div>

                <CheckCircle2
                  :size="18"
                  :class="
                    selectedUpdateStatus === 'Dikirim'
                      ? 'text-yellow-500'
                      : 'text-transparent'
                  "
                />
              </button>

              <button
                type="button"
                @click="selectedUpdateStatus = 'Diproses'"
                class="flex w-full items-center justify-between rounded-xl border px-4 py-4 text-left transition"
                :class="
                  selectedUpdateStatus === 'Diproses'
                    ? 'border-blue-600 bg-blue-50'
                    : 'border-slate-200 bg-white hover:border-slate-300'
                "
              >
                <div class="flex items-center gap-3">
                  <span class="size-3 rounded-full bg-blue-500"></span>
                  <span class="text-base font-semibold text-slate-800">
                    Diproses
                  </span>
                </div>

                <CheckCircle2
                  :size="18"
                  :class="
                    selectedUpdateStatus === 'Diproses'
                      ? 'text-blue-600'
                      : 'text-transparent'
                  "
                />
              </button>

              <button
                type="button"
                @click="selectedUpdateStatus = 'Selesai'"
                class="flex w-full items-center justify-between rounded-xl border px-4 py-4 text-left transition"
                :class="
                  selectedUpdateStatus === 'Selesai'
                    ? 'border-green-500 bg-green-50'
                    : 'border-slate-200 bg-white hover:border-slate-300'
                "
              >
                <div class="flex items-center gap-3">
                  <span class="size-3 rounded-full bg-green-500"></span>
                  <span class="text-base font-semibold text-slate-800">
                    Selesai
                  </span>
                </div>

                <CheckCircle2
                  :size="18"
                  :class="
                    selectedUpdateStatus === 'Selesai'
                      ? 'text-green-600'
                      : 'text-transparent'
                  "
                />
              </button>
            </div>
          </div>

          <div>
            <label
              for="admin-note"
              class="mb-2 block text-sm font-semibold text-slate-700"
            >
              Catatan Admin (Opsional):
            </label>

            <textarea
              id="admin-note"
              v-model="adminNote"
              rows="4"
              placeholder="Tambahkan progres perbaikan atau instruksi..."
              class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
            ></textarea>
          </div>
        </div>

        <div class="flex flex-col gap-3 border-t border-slate-200 px-6 py-5 sm:flex-row sm:justify-end">
          <button
            type="button"
            @click="closeStatusModal"
            class="inline-flex h-12 items-center justify-center rounded-xl border border-slate-300 bg-white px-6 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
          >
            Batal
          </button>

          <button
            type="button"
            :disabled="isSavingStatus"
            @click="saveStatusUpdate"
            class="inline-flex h-12 items-center justify-center rounded-xl bg-blue-700 px-6 text-sm font-bold text-white shadow-sm transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-70"
          >
            {{ isSavingStatus ? 'Menyimpan...' : 'Simpan Perubahan' }}
          </button>
        </div>
      </div>
    </div>

    <div
      v-if="isImagePreviewOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4 backdrop-blur-sm"
      @click.self="closeImagePreview"
    >
      <div class="w-full max-w-[900px] overflow-hidden rounded-2xl bg-white shadow-[0_20px_70px_rgba(15,23,42,0.32)]">
        <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-6 py-5">
          <div class="min-w-0">
            <p class="text-xs font-extrabold uppercase tracking-wide text-blue-700">
              Preview Foto Laporan
            </p>
            <h2
              class="mt-1 truncate text-2xl font-extrabold text-slate-950"
              :title="selectedImage?.title"
            >
              {{ selectedImage?.title }}
            </h2>
          </div>

          <button
            type="button"
            @click="closeImagePreview"
            class="grid size-10 shrink-0 place-items-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
            aria-label="Tutup preview foto"
          >
            <X :size="21" />
          </button>
        </div>

        <div class="bg-slate-950/5 p-4 sm:p-6">
          <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
            <img
              v-if="selectedImage?.imageUrl"
              :src="selectedImage.imageUrl"
              :alt="selectedImage.title"
              class="max-h-[72vh] w-full object-contain"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
