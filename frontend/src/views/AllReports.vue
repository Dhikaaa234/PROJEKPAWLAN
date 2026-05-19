<script setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue'
import {
  ChevronLeft,
  ChevronRight,
  MapPin,
  Search,
  Wrench,
  X,
} from 'lucide-vue-next'

import api from '../services/api'
import DashboardSidebar from '../components/DashboardSidebar.vue'
import DashboardTopbar from '../components/DashboardTopbar.vue'

const searchKeyword = ref('')
const selectedStatus = ref('Semua Status')
const selectedCategory = ref('Semua Kategori')
const selectedReport = ref(null)
const isDetailModalOpen = ref(false)
const isLoadingReports = ref(false)
const reports = ref([])

const pagination = reactive({
  currentPage: 1,
  perPage: 5,
  total: 0,
})

const statusOptions = ref([
  'Semua Status',
  'Diproses',
  'Dikirim',
  'Selesai',
])

const categoryOptions = ref(['Semua Kategori'])

watch(isDetailModalOpen, (isOpen) => {
  document.body.style.overflow = isOpen ? 'hidden' : ''
})

watch([searchKeyword, selectedStatus, selectedCategory], () => {
  pagination.currentPage = 1
})

onUnmounted(() => {
  document.body.style.overflow = ''
})

const filteredReports = computed(() => {
  const keyword = searchKeyword.value.toLowerCase().trim()

  return reports.value.filter((report) => {
    const matchesKeyword =
      !keyword ||
      String(report.title).toLowerCase().includes(keyword) ||
      String(report.description).toLowerCase().includes(keyword) ||
      String(report.code).toLowerCase().includes(keyword) ||
      String(report.category).toLowerCase().includes(keyword) ||
      String(report.location).toLowerCase().includes(keyword) ||
      String(report.reporter).toLowerCase().includes(keyword)

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

function getStatusClass(status) {
  if (status === 'Dikirim') return 'bg-amber-100 text-amber-700'
  if (status === 'Diproses') return 'bg-blue-100 text-blue-700'
  if (status === 'Selesai') return 'bg-green-100 text-green-700'
  if (status === 'Dibatalkan') return 'bg-red-100 text-red-700'

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

  const status = report.status ?? report.statusName ?? report.status?.name ?? ''

  return {
    id: report.id ?? report.code ?? report.reportId,
    code: report.code ?? report.reportId ?? report.report_code ?? report.id ?? '',
    status,
    statusClass: report.statusClass ?? getStatusClass(status),
    title: report.title ?? '',
    description: report.description ?? '',
    category: report.category ?? report.categoryName ?? report.category?.name ?? '',
    location: report.location ?? '',
    reporter,
    reporterInitial: report.reporterInitial ?? getReporterInitial(reporter),
    date: report.date ?? report.createdAt ?? report.created_at ?? '',
    imagePath: report.imagePath ?? report.image_path ?? null,
    imageUrl: report.imageUrl ?? report.image_url ?? null,
    imageType: report.imageType ?? report.image_type ?? '',
    adminResponse: report.adminResponse ?? report.admin_response ?? '',
  }
}

function syncCategoryOptions(payload, normalizedReports) {
  const categories = Array.isArray(payload.categories)
    ? payload.categories
    : [...new Set(normalizedReports.map((report) => report.category).filter(Boolean))]

  categoryOptions.value = ['Semua Kategori', ...categories]
}

async function fetchReports() {
  isLoadingReports.value = true

  try {
    const response = await api.get('/reports')
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

function getImageClass(type) {
  const classes = {
    ac: 'from-slate-100 via-stone-200 to-slate-500',
    toilet: 'from-yellow-950 via-amber-800 to-yellow-400',
    computer: 'from-slate-950 via-cyan-950 to-amber-700',
    corridor: 'from-stone-100 via-amber-100 to-stone-500',
  }

  return classes[type] || 'from-slate-100 to-slate-400'
}

function getImageLabel(type) {
  const labels = {
    ac: 'AC',
    toilet: 'TOILET',
    computer: 'LAN',
    corridor: 'LAMP',
  }

  return labels[type] || 'IMG'
}

function openDetailModal(report) {
  selectedReport.value = report
  isDetailModalOpen.value = true
}

function closeDetailModal() {
  selectedReport.value = null
  isDetailModalOpen.value = false
}

function setPage(page) {
  if (page < 1 || page > pageCount.value) return

  pagination.currentPage = page
}

onMounted(fetchReports)
</script>

<template>
  <div class="min-h-screen bg-[#faf8ff] text-slate-900">
    <div class="flex min-h-screen">
      <DashboardSidebar />

      <div class="min-w-0 flex-1">
        <DashboardTopbar />

        <main class="px-5 py-5 md:px-8 lg:px-10">
          <section class="mx-auto max-w-[1240px]">
            <div class="mb-6">
              <h1 class="text-4xl font-extrabold tracking-tight text-slate-950 md:text-5xl">
                Semua Laporan
              </h1>
              <p class="mt-3 text-base text-slate-600 md:text-lg">
                Pantau dan kelola seluruh keluhan fasilitas di lingkungan Filkom.
              </p>
            </div>

            <section class="mb-8 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
              <div class="grid gap-3 lg:grid-cols-[1fr_180px_240px]">
                <label class="relative">
                  <Search
                    :size="19"
                    class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"
                  />

                  <input
                    v-model="searchKeyword"
                    type="search"
                    placeholder="Cari laporan berdasarkan judul, lokasi, pelapor..."
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
                    {{ status }}
                  </option>
                </select>

                <select
                  v-model="selectedCategory"
                  class="h-11 rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                >
                  <option
                    v-for="category in categoryOptions"
                    :key="category"
                  >
                    {{ category }}
                  </option>
                </select>
              </div>
            </section>

            <div class="space-y-5">
              <div
                v-if="isLoadingReports"
                class="rounded-xl border border-slate-200 bg-white px-6 py-12 text-center shadow-sm"
              >
                <p class="text-base font-bold text-slate-700">
                  Memuat laporan...
                </p>
                <p class="mt-1 text-sm text-slate-500">
                  Mohon tunggu sebentar.
                </p>
              </div>

              <article
                v-for="report in paginatedReports"
                v-else
                :key="report.id"
                class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
              >
                <div class="grid gap-5 lg:grid-cols-[150px_1fr_150px]">
                  <div
                    class="relative h-[130px] w-full shrink-0 overflow-hidden rounded-lg bg-slate-100"
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
                      :class="getImageClass(report.imageType)"
                    >
                      <div
                        class="absolute inset-0 bg-[linear-gradient(135deg,rgba(255,255,255,0.25),transparent_45%,rgba(0,0,0,0.25))]"
                      ></div>

                      <span
                        class="absolute bottom-3 right-3 rounded bg-white/85 px-2 py-1 text-[10px] font-extrabold text-slate-600"
                      >
                        {{ getImageLabel(report.imageType) }}
                      </span>
                    </div>
                  </div>

                  <div class="min-w-0">
                    <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                      <div class="flex flex-wrap items-center gap-3">
                        <span
                          class="rounded-full px-3 py-1 text-xs font-extrabold"
                          :class="report.statusClass"
                        >
                          {{ report.status }}
                        </span>

                        <span class="text-sm font-bold text-slate-400">
                          {{ report.code }}
                        </span>
                      </div>

                      <p class="text-sm font-medium text-slate-500">
                        {{ report.date }}
                      </p>
                    </div>

                    <h2
                      class="truncate text-xl font-extrabold leading-tight text-slate-950"
                      :title="report.title"
                    >
                      {{ report.title }}
                    </h2>

                    <p
                      class="mt-3 line-clamp-2 text-sm leading-relaxed text-slate-600"
                      :title="report.description"
                    >
                      {{ report.description }}
                    </p>

                    <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm font-semibold text-slate-500">
                      <span class="inline-flex items-center gap-2">
                        <Wrench :size="16" />
                        {{ report.category }}
                      </span>

                      <span class="inline-flex min-w-0 items-center gap-2">
                        <MapPin :size="16" class="shrink-0" />
                        <span class="max-w-[220px] truncate">
                          {{ report.location }}
                        </span>
                      </span>

                      <span class="inline-flex items-center gap-2">
                        <span
                          class="grid size-6 place-items-center rounded-full bg-slate-800 text-[9px] font-extrabold text-white"
                        >
                          {{ report.reporterInitial }}
                        </span>
                        {{ report.reporter }}
                      </span>
                    </div>
                  </div>

                  <div class="flex items-start justify-start lg:justify-end">
                    <button
                      type="button"
                      @click="openDetailModal(report)"
                      class="h-10 rounded-lg border border-slate-300 bg-white px-7 text-sm font-extrabold text-slate-800 transition hover:bg-slate-50"
                    >
                      Detail
                    </button>
                  </div>
                </div>
              </article>

              <div
                v-if="filteredReports.length === 0 && !isLoadingReports"
                class="rounded-xl border border-slate-200 bg-white px-6 py-12 text-center shadow-sm"
              >
                <p class="text-base font-bold text-slate-700">
                  Tidak ada laporan ditemukan.
                </p>
                <p class="mt-1 text-sm text-slate-500">
                  Coba gunakan kata kunci atau filter yang berbeda.
                </p>
              </div>
            </div>

            <div class="mt-10 border-t border-slate-200 pt-6">
              <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                <p class="text-sm text-slate-500">
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
                    class="grid size-9 place-items-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-50"
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
                    class="grid size-9 place-items-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50"
                    :class="pagination.currentPage === pageCount ? 'cursor-not-allowed opacity-50' : ''"
                  >
                    <ChevronRight :size="18" />
                  </button>
                </div>
              </div>
            </div>
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
                {{ selectedReport.code }}
              </p>
              <h2 class="mt-1 text-2xl font-extrabold leading-tight text-slate-950">
                Detail Laporan
              </h2>
            </div>

            <button
              type="button"
              class="grid size-9 shrink-0 place-items-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
              aria-label="Tutup detail laporan"
              @click="closeDetailModal"
            >
              <X :size="20" />
            </button>
          </div>

          <div class="px-6 py-6">
            <div
              class="relative mb-5 h-[190px] overflow-hidden rounded-xl bg-slate-100"
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
                :class="getImageClass(selectedReport.imageType)"
              >
                <div
                  class="absolute inset-0 bg-[linear-gradient(135deg,rgba(255,255,255,0.25),transparent_45%,rgba(0,0,0,0.25))]"
                ></div>

                <span
                  class="absolute bottom-4 right-4 rounded bg-white/85 px-3 py-1 text-xs font-extrabold text-slate-600"
                >
                  {{ getImageLabel(selectedReport.imageType) }}
                </span>
              </div>
            </div>

            <div class="mb-4">
              <span
                class="inline-flex rounded-full px-3 py-1 text-xs font-extrabold"
                :class="selectedReport.statusClass"
              >
                {{ selectedReport.status }}
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
                  Kategori
                </p>
                <p class="mt-1 text-sm font-bold text-slate-700">
                  {{ selectedReport.category }}
                </p>
              </div>

              <div>
                <p class="text-xs font-extrabold uppercase tracking-wide text-slate-400">
                  Lokasi
                </p>
                <p class="mt-1 text-sm font-bold text-slate-700">
                  {{ selectedReport.location }}
                </p>
              </div>

              <div>
                <p class="text-xs font-extrabold uppercase tracking-wide text-slate-400">
                  Waktu
                </p>
                <p class="mt-1 text-sm font-bold text-slate-700">
                  {{ selectedReport.date }}
                </p>
              </div>

              <div>
                <p class="text-xs font-extrabold uppercase tracking-wide text-slate-400">
                  Tanggapan
                </p>
                <p class="mt-1 text-sm font-bold leading-relaxed text-slate-700">
                  {{ selectedReport.adminResponse || 'Belum ada tanggapan dari admin.' }}
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
              Tutup
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>