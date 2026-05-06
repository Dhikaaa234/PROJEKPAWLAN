<script setup>
import { computed, ref } from 'vue'
import {
  ChevronLeft,
  ChevronRight,
  MapPin,
  Search,
  Wrench,
} from 'lucide-vue-next'

import DashboardSidebar from '../components/DashboardSidebar.vue'
import DashboardTopbar from '../components/DashboardTopbar.vue'

const searchKeyword = ref('')
const selectedStatus = ref('Semua Status')
const selectedCategory = ref('Semua Kategori')

const reports = ref([
  {
    id: 1,
    code: '#REP-2024-089',
    status: 'Diproses',
    statusClass: 'bg-blue-100 text-blue-700',
    title: 'AC Ruang Kelas G.2.1 Tidak Dingin',
    description:
      'Sudah sejak pagi tadi AC di kelas G.2.1 mengeluarkan suara bising dan tidak mengeluarkan udara dingin sama sekali. Mahasiswa merasa tidak nyaman saat kuliah.',
    category: 'Utilitas gedung',
    location: 'Gedung G, Lantai 2',
    reporter: 'Ahmad Fauzi',
    reporterInitial: 'AF',
    date: '12 Okt 2023, 10:45',
    imageType: 'ac',
  },
  {
    id: 2,
    code: '#REP-2024-090',
    status: 'Dikirim',
    statusClass: 'bg-amber-100 text-amber-700',
    title: 'Kebocoran Pipa di Toilet Lantai 1 Gedung F',
    description:
      'Ada genangan air yang cukup parah di depan wastafel toilet pria lantai 1. Sepertinya ada pipa yang pecah atau keran yang bocor.',
    category: 'Fasilitas umum',
    location: 'Gedung F, Lantai 1',
    reporter: 'Siti Aminah',
    reporterInitial: 'SA',
    date: 'Baru Saja',
    imageType: 'toilet',
  },
  {
    id: 3,
    code: '#REP-2024-085',
    status: 'Selesai',
    statusClass: 'bg-green-100 text-green-700',
    title: 'Instalasi Kabel LAN Lab Komputer 3',
    description:
      'Beberapa unit PC di baris belakang tidak terhubung ke jaringan lokal. Perlu pengecekan kabel dan switch hub.',
    category: 'Sarana belajar',
    location: 'Lab Komputer, Gedung E',
    reporter: 'Budi Santoso',
    reporterInitial: 'BS',
    date: '10 Okt 2023, 14:20',
    imageType: 'computer',
  },
  {
    id: 4,
    code: '#REP-2024-080',
    status: 'Selesai',
    statusClass: 'bg-green-100 text-green-700',
    title: 'Lampu Koridor Gedung H Redup',
    description:
      'Lampu di koridor lantai 3 gedung H sudah mulai redup dan berkedip, membuat suasana remang-remang saat sore hari.',
    category: 'Inventaris & bangunan',
    location: 'Gedung H, Lantai 3',
    reporter: 'Laila Sari',
    reporterInitial: 'LS',
    date: '08 Okt 2023, 09:15',
    imageType: 'corridor',
  },
])

const filteredReports = computed(() => {
  const keyword = searchKeyword.value.toLowerCase().trim()

  return reports.value.filter((report) => {
    const matchesKeyword =
      !keyword ||
      report.title.toLowerCase().includes(keyword) ||
      report.description.toLowerCase().includes(keyword) ||
      report.code.toLowerCase().includes(keyword) ||
      report.category.toLowerCase().includes(keyword) ||
      report.location.toLowerCase().includes(keyword) ||
      report.reporter.toLowerCase().includes(keyword)

    const matchesStatus =
      selectedStatus.value === 'Semua Status' || report.status === selectedStatus.value

    const matchesCategory =
      selectedCategory.value === 'Semua Kategori' || report.category === selectedCategory.value

    return matchesKeyword && matchesStatus && matchesCategory
  })
})

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
</script>

<template>
  <div class="min-h-screen bg-[#faf8ff] text-slate-900">
    <div class="flex min-h-screen">
      <DashboardSidebar />

      <div class="min-w-0 flex-1">
        <DashboardTopbar />

        <main class="px-5 py-8 md:px-8 lg:px-10">
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
                  <option>Semua Status</option>
                  <option>Diproses</option>
                  <option>Dikirim</option>
                  <option>Selesai</option>
                </select>

                <select
                  v-model="selectedCategory"
                  class="h-11 rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                >
                  <option>Semua Kategori</option>
                  <option>Sarana belajar</option>
                  <option>Utilitas gedung</option>
                  <option>Fasilitas umum</option>
                  <option>Inventaris & bangunan</option>
                </select>
              </div>
            </section>

            <div class="space-y-6">
              <article
                v-for="report in filteredReports"
                :key="report.id"
                class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm md:p-6"
              >
                <div class="flex flex-col gap-5 lg:flex-row">
                  <div
                    class="relative h-[180px] w-full shrink-0 overflow-hidden rounded-lg bg-gradient-to-br lg:h-[156px] lg:w-[156px]"
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

                  <div class="min-w-0 flex-1">
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

                    <h2 class="text-xl font-extrabold leading-tight text-slate-950 md:text-2xl">
                      {{ report.title }}
                    </h2>

                    <p class="mt-3 max-w-[900px] text-sm leading-relaxed text-slate-600 md:text-base">
                      {{ report.description }}
                    </p>

                    <div class="mt-5 flex flex-wrap items-center gap-x-6 gap-y-3 text-sm font-semibold text-slate-500">
                      <span class="inline-flex items-center gap-2">
                        <Wrench :size="16" />
                        {{ report.category }}
                      </span>

                      <span class="inline-flex items-center gap-2">
                        <MapPin :size="16" />
                        {{ report.location }}
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
                </div>
              </article>

              <div
                v-if="filteredReports.length === 0"
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
                  Menampilkan {{ filteredReports.length }} dari 128 laporan
                </p>

                <div class="flex items-center gap-2">
                  <button
                    type="button"
                    class="grid size-9 place-items-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-50"
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

                  <span class="px-2 text-slate-400">...</span>

                  <button
                    type="button"
                    class="grid size-9 place-items-center rounded-lg border border-slate-200 bg-white text-sm font-extrabold text-slate-700 transition hover:bg-slate-50"
                  >
                    32
                  </button>

                  <button
                    type="button"
                    class="grid size-9 place-items-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50"
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
  </div>
</template>