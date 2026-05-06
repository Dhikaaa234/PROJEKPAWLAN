<script setup>
import { computed, ref } from 'vue'
import {
  CalendarClock,
  Check,
  CheckCircle2,
  ChevronDown,
  ChevronLeft,
  ChevronRight,
  ClipboardList,
  FileText,
  Search,
  Settings2,
  SlidersHorizontal,
  X,
} from 'lucide-vue-next'

import AdminSidebar from '../components/AdminSidebar.vue'
import DashboardTopbar from '../components/DashboardTopbar.vue'

const searchKeyword = ref('')
const selectedStatus = ref('Semua Status')
const selectedCategory = ref('Semua Kategori')

const isStatusModalOpen = ref(false)
const selectedReport = ref(null)
const selectedUpdateStatus = ref('')
const adminNote = ref('')

const stats = [
  {
    title: 'TOTAL LAPORAN',
    value: '1.284',
    description: '↗ +12% dari bulan lalu',
    descriptionClass: 'text-green-600',
    icon: FileText,
    iconClass: 'bg-blue-50 text-blue-700',
  },
  {
    title: 'PENDING',
    value: '42',
    description: 'Laporan perlu ditinjau',
    descriptionClass: 'text-slate-400',
    icon: CalendarClock,
    iconClass: 'bg-yellow-50 text-yellow-600',
  },
  {
    title: 'DIPROSES',
    value: '18',
    description: 'Sedang dalam perbaikan',
    descriptionClass: 'text-slate-400',
    icon: Settings2,
    iconClass: 'bg-blue-50 text-blue-700',
  },
  {
    title: 'SELESAI',
    value: '1.224',
    description: '95.3% Resolution rate',
    descriptionClass: 'text-green-600',
    icon: CheckCircle2,
    iconClass: 'bg-green-50 text-green-700',
  },
]

const reports = ref([
  {
    id: 1,
    title: 'AC Ruang H.21 Bocor',
    reporter: 'Budi Santoso',
    reporterInitial: 'BS',
    category: 'Kelistrikan',
    location: 'Gedung H, Lantai 2',
    status: 'Diproses',
    statusClass: 'bg-blue-100 text-blue-700',
    date: '24 Okt 2023',
  },
  {
    id: 2,
    title: 'LCD Proyektor Redup',
    reporter: 'Dr. Siti Aminah',
    reporterInitial: 'SA',
    category: 'Infrastruktur IT',
    location: 'Gedung B, Ruang 3.1',
    status: 'Dikirim',
    statusClass: 'bg-yellow-100 text-yellow-700',
    date: '25 Okt 2023',
  },
  {
    id: 3,
    title: 'Keramik Tangga Lepas',
    reporter: 'Rahmat Hidayat',
    reporterInitial: 'RH',
    category: 'Fisik Bangunan',
    location: 'Gedung F, Tangga Timur',
    status: 'Selesai',
    statusClass: 'bg-green-100 text-green-700',
    date: '23 Okt 2023',
  },
])

const filteredReports = computed(() => {
  const keyword = searchKeyword.value.toLowerCase().trim()

  return reports.value.filter((report) => {
    const matchesKeyword =
      !keyword ||
      report.title.toLowerCase().includes(keyword) ||
      report.reporter.toLowerCase().includes(keyword) ||
      report.category.toLowerCase().includes(keyword) ||
      report.location.toLowerCase().includes(keyword) ||
      report.status.toLowerCase().includes(keyword)

    const matchesStatus =
      selectedStatus.value === 'Semua Status' || report.status === selectedStatus.value

    const matchesCategory =
      selectedCategory.value === 'Semua Kategori' || report.category === selectedCategory.value

    return matchesKeyword && matchesStatus && matchesCategory
  })
})

function applyFilter() {
  console.log('Filter diterapkan:', {
    keyword: searchKeyword.value,
    status: selectedStatus.value,
    category: selectedCategory.value,
  })
}

function getStatusClass(status) {
  if (status === 'Dikirim') return 'bg-yellow-100 text-yellow-700'
  if (status === 'Diproses') return 'bg-blue-100 text-blue-700'
  if (status === 'Selesai') return 'bg-green-100 text-green-700'
  return 'bg-slate-100 text-slate-700'
}

function openStatusModal(report) {
  selectedReport.value = { ...report }
  selectedUpdateStatus.value = report.status
  adminNote.value = ''
  isStatusModalOpen.value = true
}

function closeStatusModal() {
  isStatusModalOpen.value = false
  selectedReport.value = null
  selectedUpdateStatus.value = ''
  adminNote.value = ''
}

function saveStatusUpdate() {
  if (!selectedReport.value || !selectedUpdateStatus.value) return

  reports.value = reports.value.map((report) => {
    if (report.id === selectedReport.value.id) {
      return {
        ...report,
        status: selectedUpdateStatus.value,
        statusClass: getStatusClass(selectedUpdateStatus.value),
      }
    }

    return report
  })

  console.log('Status laporan diperbarui:', {
    reportId: selectedReport.value.id,
    title: selectedReport.value.title,
    status: selectedUpdateStatus.value,
    note: adminNote.value,
  })

  closeStatusModal()
}
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

            <div class="mb-8 grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
              <article
                v-for="stat in stats"
                :key="stat.title"
                class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
              >
                <div class="mb-8 flex items-start justify-between">
                  <p class="text-xs font-extrabold uppercase tracking-wide text-slate-400">
                    {{ stat.title }}
                  </p>

                  <div class="grid size-10 place-items-center rounded-lg" :class="stat.iconClass">
                    <component :is="stat.icon" :size="22" />
                  </div>
                </div>

                <h2 class="text-4xl font-extrabold leading-none tracking-tight text-slate-950">
                  {{ stat.value }}
                </h2>
                <p class="mt-2 text-sm font-medium" :class="stat.descriptionClass">
                  {{ stat.description }}
                </p>
              </article>
            </div>

            <section class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
              <div class="grid gap-3 lg:grid-cols-[1fr_160px_170px_120px]">
                <label class="relative">
                  <Search
                    :size="20"
                    class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"
                  />

                  <input
                    v-model="searchKeyword"
                    type="search"
                    placeholder="Cari berdasarkan judul atau pelapor..."
                    class="h-12 w-full rounded-lg border border-slate-200 bg-slate-50 pl-12 pr-4 text-sm font-medium text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                  />
                </label>

                <div class="relative">
                  <select
                    v-model="selectedStatus"
                    class="h-12 w-full appearance-none rounded-lg border border-slate-200 bg-slate-50 px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                  >
                    <option>Semua Status</option>
                    <option>Dikirim</option>
                    <option>Diproses</option>
                    <option>Selesai</option>
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
                    <option>Semua Kategori</option>
                    <option>Kelistrikan</option>
                    <option>Infrastruktur IT</option>
                    <option>Fisik Bangunan</option>
                  </select>

                  <ChevronDown
                    :size="18"
                    class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-slate-500"
                  />
                </div>

                <button
                  type="button"
                  @click="applyFilter"
                  class="inline-flex h-12 items-center justify-center gap-2 rounded-lg bg-blue-700 px-5 text-sm font-extrabold text-white transition hover:bg-blue-800"
                >
                  <SlidersHorizontal :size="17" />
                  Terapkan
                </button>
              </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
              <div class="overflow-x-auto">
                <table class="min-w-[1280px] table-fixed border-collapse">
                  <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-left">
                      <th class="w-[260px] px-7 py-5 text-sm font-extrabold text-slate-500">
                        Judul
                      </th>
                      <th class="w-[210px] px-7 py-5 text-sm font-extrabold text-slate-500">
                        Pelapor
                      </th>
                      <th class="w-[190px] px-7 py-5 text-sm font-extrabold text-slate-500">
                        Kategori
                      </th>
                      <th class="w-[260px] px-7 py-5 text-sm font-extrabold text-slate-500">
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
                    <tr
                      v-for="report in filteredReports"
                      :key="report.id"
                      class="border-b border-slate-100 last:border-b-0 hover:bg-slate-50"
                    >
                      <td class="px-7 py-6">
                        <p class="text-base font-extrabold text-slate-900">
                          {{ report.title }}
                        </p>
                      </td>

                      <td class="px-7 py-6">
                        <div class="flex items-center gap-3">
                          <div
                            class="grid size-7 place-items-center rounded-full bg-slate-800 text-[9px] font-extrabold text-white"
                          >
                            {{ report.reporterInitial }}
                          </div>
                          <p class="text-sm font-medium text-slate-700">
                            {{ report.reporter }}
                          </p>
                        </div>
                      </td>

                      <td class="px-7 py-6">
                        <span class="rounded bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-700">
                          {{ report.category }}
                        </span>
                      </td>

                      <td class="px-7 py-6">
                        <p class="text-sm font-medium text-slate-500">
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
                            :class="report.id === 2 ? 'bg-blue-50 text-blue-700 shadow-sm' : ''"
                            :aria-label="`Ubah status ${report.title}`"
                          >
                            <ClipboardList :size="18" />
                          </button>
                        </div>
                      </td>
                    </tr>

                    <tr v-if="filteredReports.length === 0">
                      <td colspan="7" class="px-7 py-12 text-center">
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
                  Menampilkan 1-3 dari 1,284 laporan
                </p>

                <div class="flex items-center gap-2">
                  <button
                    type="button"
                    class="grid size-9 place-items-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50"
                  >
                    <ChevronLeft :size="18" />
                  </button>

                  <button
                    type="button"
                    class="grid size-9 place-items-center rounded-lg bg-blue-700 text-sm font-extrabold text-white"
                  >
                    1
                  </button>

                  <button
                    type="button"
                    class="grid size-9 place-items-center rounded-lg border border-slate-200 bg-white text-sm font-extrabold text-slate-700 transition hover:bg-slate-50"
                  >
                    2
                  </button>

                  <button
                    type="button"
                    class="grid size-9 place-items-center rounded-lg border border-slate-200 bg-white text-sm font-extrabold text-slate-700 transition hover:bg-slate-50"
                  >
                    3
                  </button>

                  <button
                    type="button"
                    class="grid size-9 place-items-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-50"
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

    <!-- Modal Update Status -->
    <div
      v-if="isStatusModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/30 px-4 backdrop-blur-sm"
    >
      <div class="w-full max-w-[560px] rounded-2xl bg-white shadow-[0_20px_60px_rgba(15,23,42,0.22)]">
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
            class="flex items-start gap-3 rounded-xl border border-blue-100 bg-blue-50 px-4 py-4"
          >
            <div class="mt-0.5 grid size-8 shrink-0 place-items-center rounded-full bg-white text-blue-700">
              <FileText :size="16" />
            </div>

            <div class="min-w-0">
              <p class="text-base font-extrabold text-blue-700">
                {{ selectedReport.title }}
              </p>
              <p class="mt-1 text-sm text-blue-600">
                Dilaporkan oleh {{ selectedReport.reporter }} • {{ selectedReport.location }}
              </p>
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
                  <span class="text-base font-semibold text-slate-800">Dikirim</span>
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
                  <span class="text-base font-semibold text-slate-800">Diproses</span>
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
                  <span class="text-base font-semibold text-slate-800">Selesai</span>
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
            <label for="admin-note" class="mb-2 block text-sm font-semibold text-slate-700">
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
            @click="saveStatusUpdate"
            class="inline-flex h-12 items-center justify-center rounded-xl bg-blue-700 px-6 text-sm font-bold text-white shadow-sm transition hover:bg-blue-800"
          >
            Simpan Perubahan
          </button>
        </div>
      </div>
    </div>
  </div>
</template>