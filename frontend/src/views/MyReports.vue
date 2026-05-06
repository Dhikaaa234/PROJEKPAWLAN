<script setup>
import { computed, ref } from 'vue'
import {
  CalendarDays,
  CheckCircle2,
  ChevronLeft,
  ChevronRight,
  Filter,
  MapPin,
  MessageSquare,
  Plus,
  Search,
  Send,
  SlidersHorizontal,
  RefreshCcw,
} from 'lucide-vue-next'
import { useRouter } from 'vue-router'

import DashboardSidebar from '../components/DashboardSidebar.vue'
import DashboardTopbar from '../components/DashboardTopbar.vue'

const router = useRouter()

const searchKeyword = ref('')
const selectedStatus = ref('Semua Status')

const summaries = [
  {
    label: 'Dikirim',
    value: '03',
    icon: Send,
    iconClass: 'bg-yellow-100 text-yellow-700',
  },
  {
    label: 'Diproses',
    value: '01',
    icon: RefreshCcw,
    iconClass: 'bg-blue-100 text-blue-700',
  },
  {
    label: 'Selesai',
    value: '12',
    icon: CheckCircle2,
    iconClass: 'bg-green-100 text-green-700',
  },
]

const reports = ref([
  {
    id: 1,
    reportId: '#REP-2023-089',
    title: 'AC Rusak & Kebocoran di Gedung H 2.1',
    description:
      'Air Conditioner di ruang kelas H 2.1 tidak dingin dan mengeluarkan bunyi bising saat dinyalakan. Ada rembesan air di dinding bawah unit AC tersebut.',
    status: 'Diproses',
    statusClass: 'bg-blue-100 text-blue-700',
    date: '24 Okt 2023, 10:15',
    location: 'Gedung H, Lantai 2',
    responses: '2 Tanggapan',
    action: 'active',
    imageType: 'ac',
    titleClass: 'text-slate-950',
  },
  {
    id: 2,
    reportId: '#REP-2023-094',
    title: 'Kursi Patah di Auditorium',
    description:
      'Ditemukan 3 kursi di barisan tengah (F12-F14) yang engselnya lepas dan tidak bisa diduduki dengan aman.',
    status: 'Dikirim',
    statusClass: 'bg-yellow-100 text-yellow-700',
    date: 'Sekarang',
    location: 'Gedung Auditorium Filkom',
    responses: 'Belum ada tanggapan',
    action: 'active',
    imageType: 'chair',
    titleClass: 'text-slate-950',
  },
  {
    id: 3,
    reportId: '#REP-2023-075',
    title: 'Lampu Koridor Lantai 3 Mati',
    description:
      'Lampu di koridor depan Lab Basis Data mati total, membuat area tersebut sangat gelap di sore hari.',
    status: 'Selesai',
    statusClass: 'bg-green-100 text-green-700',
    date: '15 Okt 2023',
    location: 'Gedung G, Lantai 3',
    responses: 'Tuntas Teratasi',
    action: 'history',
    imageType: 'lamp',
    titleClass: 'text-slate-600 line-through decoration-2',
  },
])

const filteredReports = computed(() => {
  const keyword = searchKeyword.value.toLowerCase().trim()

  return reports.value.filter((report) => {
    const matchesKeyword =
      !keyword ||
      report.title.toLowerCase().includes(keyword) ||
      report.description.toLowerCase().includes(keyword) ||
      report.reportId.toLowerCase().includes(keyword) ||
      report.location.toLowerCase().includes(keyword) ||
      report.status.toLowerCase().includes(keyword)

    const matchesStatus =
      selectedStatus.value === 'Semua Status' || report.status === selectedStatus.value

    return matchesKeyword && matchesStatus
  })
})

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
</script>

<template>
  <div class="min-h-screen bg-[#faf8ff] text-slate-900">
    <div class="flex min-h-screen">
      <DashboardSidebar />

      <div class="min-w-0 flex-1">
        <DashboardTopbar />

        <main class="px-5 py-8 md:px-8 lg:px-10">
          <section class="mx-auto max-w-[1240px]">
            <div class="mb-8 flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
              <div>
                <h1 class="text-4xl font-extrabold tracking-tight text-slate-950 md:text-5xl">
                  Laporan Saya
                </h1>
                <p class="mt-3 text-base text-slate-600 md:text-lg">
                  Kelola dan pantau status laporan fasilitas yang telah Anda kirimkan.
                </p>
              </div>

              <button
                type="button"
                @click="goToCreateReport"
                class="inline-flex h-12 items-center justify-center gap-3 rounded-lg bg-blue-700 px-6 text-sm font-bold text-white shadow-[0_8px_18px_rgba(29,78,216,0.28)] transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-200"
              >
                <Plus :size="21" />
                Buat Laporan Baru
              </button>
            </div>

            <div class="mb-8 grid gap-6 xl:grid-cols-[1fr_300px]">
              <div class="grid gap-6 md:grid-cols-3">
                <article
                  v-for="summary in summaries"
                  :key="summary.label"
                  class="flex items-center gap-4 rounded-xl border border-slate-300 bg-white p-6 shadow-sm"
                >
                  <div class="grid size-14 shrink-0 place-items-center rounded-full" :class="summary.iconClass">
                    <component :is="summary.icon" :size="25" />
                  </div>

                  <div>
                    <p class="text-sm font-medium text-slate-600">
                      {{ summary.label }}
                    </p>
                    <h2 class="text-3xl font-extrabold leading-none tracking-tight text-slate-950">
                      {{ summary.value }}
                    </h2>
                  </div>
                </article>
              </div>

              <aside class="rounded-xl border border-dashed border-slate-400 bg-white/35 p-5">
                <div class="mb-3 flex items-center justify-between">
                  <h3 class="text-sm font-extrabold text-slate-700">
                    Filter Cepat
                  </h3>

                  <SlidersHorizontal :size="17" class="text-slate-600" />
                </div>

                <div class="flex flex-wrap gap-2">
                  <button
                    type="button"
                    class="h-8 rounded-full border border-slate-300 bg-white px-4 text-xs font-extrabold text-slate-800 shadow-sm transition hover:bg-slate-50"
                  >
                    Terbaru
                  </button>

                  <button
                    type="button"
                    class="h-8 rounded-full border border-slate-300 bg-white px-4 text-xs font-extrabold text-slate-800 shadow-sm transition hover:bg-slate-50"
                  >
                    Minggu Ini
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
                    placeholder="Cari laporan saya berdasarkan judul, lokasi, ID..."
                    class="h-11 w-full rounded-lg border border-slate-200 bg-slate-50 pl-11 pr-4 text-sm font-medium text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                  />
                </label>

                <select
                  v-model="selectedStatus"
                  class="h-11 rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                >
                  <option>Semua Status</option>
                  <option>Dikirim</option>
                  <option>Diproses</option>
                  <option>Selesai</option>
                </select>
              </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-slate-300 bg-white shadow-sm">
              <div class="flex flex-col gap-4 border-b border-slate-300 px-5 py-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                  <h2 class="text-2xl font-extrabold text-slate-950">
                    Daftar Laporan
                  </h2>

                  <span class="rounded-md bg-blue-100 px-3 py-1 text-xs font-extrabold text-blue-700">
                    16 TOTAL
                  </span>
                </div>

                <button
                  type="button"
                  class="grid size-10 place-items-center rounded-lg border border-slate-300 bg-white text-slate-700 transition hover:bg-slate-50"
                  aria-label="Filter"
                >
                  <Filter :size="19" />
                </button>
              </div>

              <div class="divide-y divide-slate-200">
                <article
                  v-for="report in filteredReports"
                  :key="report.id"
                  class="p-5 md:p-6"
                >
                  <div class="grid gap-5 xl:grid-cols-[190px_1fr_210px]">
                    <div
                      class="relative h-[150px] overflow-hidden rounded-lg bg-gradient-to-br md:h-[128px]"
                      :class="getReportImageClass(report.imageType)"
                    ></div>

                    <div class="min-w-0">
                      <div class="mb-3 flex flex-wrap items-center gap-3">
                        <span
                          class="rounded-full px-3 py-1 text-xs font-extrabold"
                          :class="report.statusClass"
                        >
                          {{ report.status }}
                        </span>
                      </div>

                      <h3 class="text-2xl font-extrabold leading-tight" :class="report.titleClass">
                        {{ report.title }}
                      </h3>

                      <p class="mt-3 text-base leading-relaxed text-slate-600">
                        {{ report.description }}
                      </p>

                      <div class="mt-5 flex flex-wrap items-center gap-x-6 gap-y-3 text-sm font-semibold text-slate-500">
                        <span class="inline-flex items-center gap-2">
                          <CalendarDays :size="16" />
                          {{ report.date }}
                        </span>

                        <span class="inline-flex items-center gap-2">
                          <MapPin :size="16" />
                          {{ report.location }}
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

                    <div class="flex flex-col items-start justify-between gap-5 xl:items-end">
                      <p class="text-base font-medium text-slate-700">
                        ID: {{ report.reportId }}
                      </p>

                      <div class="flex gap-3 xl:flex-col xl:items-end">
                        <button
                          v-if="report.action === 'active'"
                          type="button"
                          class="h-10 rounded-lg border border-slate-300 bg-white px-7 text-sm font-extrabold text-slate-800 transition hover:bg-slate-50"
                        >
                          Detail
                        </button>

                        <button
                          v-if="report.action === 'active'"
                          type="button"
                          class="h-10 px-3 text-sm font-extrabold text-red-600 transition hover:text-red-700"
                        >
                          Batalkan
                        </button>

                        <button
                          v-if="report.action === 'history'"
                          type="button"
                          class="h-10 rounded-lg border border-slate-300 bg-white px-6 text-sm font-extrabold text-slate-800 transition hover:bg-slate-50"
                        >
                          Lihat Riwayat
                        </button>
                      </div>
                    </div>
                  </div>
                </article>

                <div
                  v-if="filteredReports.length === 0"
                  class="px-6 py-12 text-center"
                >
                  <p class="text-base font-bold text-slate-700">
                    Tidak ada laporan ditemukan.
                  </p>
                  <p class="mt-1 text-sm text-slate-500">
                    Coba gunakan kata kunci atau filter yang berbeda.
                  </p>
                </div>
              </div>

              <div class="flex flex-col gap-5 border-t border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
                <p class="text-sm font-medium text-slate-600">
                  Menampilkan {{ filteredReports.length }} dari 16 laporan
                </p>

                <div class="flex items-center gap-2">
                  <button
                    type="button"
                    class="grid size-10 place-items-center rounded-lg border border-slate-300 bg-white text-slate-400 transition hover:bg-slate-50"
                  >
                    <ChevronLeft :size="18" />
                  </button>

                  <button
                    type="button"
                    class="grid size-10 place-items-center rounded-lg bg-blue-700 text-sm font-extrabold text-white"
                  >
                    1
                  </button>

                  <button
                    type="button"
                    class="grid size-10 place-items-center rounded-lg border border-slate-300 bg-white text-sm font-extrabold text-slate-700 transition hover:bg-slate-50"
                  >
                    2
                  </button>

                  <button
                    type="button"
                    class="grid size-10 place-items-center rounded-lg border border-slate-300 bg-white text-slate-700 transition hover:bg-slate-50"
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
  </div>
</template>