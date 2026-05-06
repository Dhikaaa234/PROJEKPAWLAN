<script setup>
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

import AdminSidebar from '../components/AdminSidebar.vue'
import DashboardTopbar from '../components/DashboardTopbar.vue'

const stats = [
  {
    title: 'TOTAL',
    value: '1,284',
    subtitle: 'Laporan Masuk',
    icon: FileText,
    iconClass: 'bg-blue-100 text-blue-700',
  },
  {
    title: 'DIKIRIM',
    value: '42',
    subtitle: 'Menunggu Review',
    icon: Send,
    iconClass: 'bg-yellow-100 text-yellow-700',
  },
  {
    title: 'DIPROSES',
    value: '18',
    subtitle: 'Dalam Penanganan',
    icon: UserCog,
    iconClass: 'bg-blue-100 text-blue-700',
  },
  {
    title: 'SELESAI',
    value: '1,224',
    subtitle: 'Berhasil Diperbaiki',
    icon: CheckCircle2,
    iconClass: 'bg-green-100 text-green-700',
  },
]

const incomingReports = [
  {
    id: 1,
    title: 'AC Bocor & Tidak Dingin',
    location: 'GEDUNG G - RUANG 2.4',
    description:
      'Laporan kerusakan unit pendingin ruangan yang mengganggu kegiatan belajar mengajar sejak tadi...',
    priority: 'URGENT',
    priorityClass: 'bg-red-600 text-white',
    imageType: 'auditorium',
    author: 'Budi Santoso',
    avatar: 'BS',
    time: '10 Menit yang lalu',
  },
  {
    id: 2,
    title: 'Kran Air Patah',
    location: 'GEDUNG A - TOILET LT.1',
    description:
      'Keran air di wastafel toilet pria patah sehingga air terus mengalir dan mubazir.',
    priority: 'MEDIUM',
    priorityClass: 'bg-blue-700 text-white',
    imageType: 'wall',
    author: 'Siti Aminah',
    avatar: 'SA',
    time: '24 Menit yang lalu',
  },
]

const recentReports = [
  {
    id: 1,
    title: 'Lampu Kelas Mati',
    location: 'Gedung F - R.302',
    time: 'Hari ini, 08:30',
    status: 'Dikirim',
    icon: Send,
    iconClass: 'bg-slate-100 text-slate-400',
    statusClass: 'bg-yellow-100 text-yellow-700',
  },
  {
    id: 2,
    title: 'Wi-Fi Tidak Terdeteksi',
    location: 'Gedung D - Lobby',
    time: 'Kemarin, 16:45',
    status: 'Diproses',
    icon: Wifi,
    iconClass: 'bg-blue-50 text-blue-700',
    statusClass: 'bg-blue-100 text-blue-700',
  },
  {
    id: 3,
    title: 'Kursi Lipat Patah',
    location: 'Gedung B - R.101',
    time: 'Kemarin, 14:20',
    status: 'Selesai',
    icon: Armchair,
    iconClass: 'bg-green-50 text-green-700',
    statusClass: 'bg-green-100 text-green-700',
  },
  {
    id: 4,
    title: 'Handle Pintu Lepas',
    location: 'Gedung E - R.205',
    time: '02 Nov, 09:15',
    status: 'Dikirim',
    icon: DoorOpen,
    iconClass: 'bg-slate-100 text-slate-400',
    statusClass: 'bg-yellow-100 text-yellow-700',
  },
]
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
                  Overview System
                </h1>
                <p class="mt-3 text-base text-slate-600 md:text-lg">
                  Monitor and manage all facility reports across Filkom University.
                </p>
              </div>

              <div class="flex flex-col gap-3 sm:flex-row">
                <button
                  type="button"
                  class="inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-5 text-sm font-bold text-slate-800 transition hover:bg-slate-50"
                >
                  <Download :size="17" />
                  Export Data
                </button>

                <button
                  type="button"
                  class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-blue-700 px-5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-800"
                >
                  <Plus :size="18" />
                  Generate Report
                </button>
              </div>
            </div>

            <div class="mb-9 grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
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
                    {{ stat.title }}
                  </p>
                </div>

                <h3 class="text-4xl font-extrabold leading-none tracking-tight text-slate-950">
                  {{ stat.value }}
                </h3>
                <p class="mt-2 text-sm text-slate-600">
                  {{ stat.subtitle }}
                </p>
              </article>
            </div>

            <div class="grid gap-7 xl:grid-cols-[1fr_430px]">
              <section>
                <div class="mb-5 flex items-center justify-between">
                  <h2 class="flex items-center gap-2 text-2xl font-extrabold text-slate-950">
                    <Zap :size="24" class="text-blue-700" />
                    Laporan Baru Masuk
                  </h2>

                  <button
                    type="button"
                    class="text-sm font-extrabold text-blue-700 transition hover:text-blue-900"
                  >
                    Lihat Semua
                  </button>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                  <article
                    v-for="report in incomingReports"
                    :key="report.id"
                    class="overflow-hidden rounded-xl border border-slate-300 bg-white shadow-sm"
                  >
                    <div
                      class="relative h-[176px] overflow-hidden"
                      :class="
                        report.imageType === 'auditorium'
                          ? 'bg-gradient-to-br from-amber-700 via-yellow-600 to-amber-300'
                          : 'bg-gradient-to-br from-slate-200 via-slate-100 to-slate-300'
                      "
                    >
                      <div
                        v-if="report.imageType === 'auditorium'"
                        class="absolute inset-x-0 bottom-0 h-20 bg-black/10"
                      ></div>

                      <div
                        v-if="report.imageType === 'auditorium'"
                        class="absolute inset-x-8 bottom-0 h-14 rounded-t-lg border-x border-t border-white/25 bg-white/20"
                      ></div>

                      <div
                        v-if="report.imageType === 'auditorium'"
                        class="absolute left-1/2 bottom-14 h-24 w-8 -translate-x-1/2 bg-white/20"
                      ></div>

                      <template v-if="report.imageType === 'wall'">
                        <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(202,138,4,0.45)_1px,transparent_1px),linear-gradient(rgba(202,138,4,0.45)_1px,transparent_1px)] bg-[size:84px_84px]"></div>
                        <div class="absolute left-[48%] top-[48%] h-12 w-20 rotate-12 border-l-4 border-t-4 border-slate-800"></div>
                      </template>

                      <span
                        class="absolute left-4 top-4 rounded px-3 py-1 text-xs font-extrabold tracking-[0.16em]"
                        :class="report.priorityClass"
                      >
                        {{ report.priority }}
                      </span>
                    </div>

                    <div class="p-5">
                      <p class="mb-2 text-xs font-extrabold text-slate-500">
                        {{ report.location }}
                      </p>

                      <h3 class="text-2xl font-extrabold leading-tight text-slate-950">
                        {{ report.title }}
                      </h3>

                      <p class="mt-3 line-clamp-2 text-base leading-relaxed text-slate-600">
                        {{ report.description }}
                      </p>

                      <div class="mt-5 border-t border-slate-100 pt-4">
                        <div class="flex items-center justify-between gap-3">
                          <div class="flex items-center gap-3">
                            <div
                              class="grid size-8 place-items-center rounded-full bg-slate-800 text-[10px] font-extrabold text-white"
                            >
                              {{ report.avatar }}
                            </div>
                            <p class="text-sm font-medium text-slate-900">
                              {{ report.author }}
                            </p>
                          </div>

                          <p class="text-xs text-slate-400">
                            {{ report.time }}
                          </p>
                        </div>
                      </div>
                    </div>
                  </article>
                </div>
              </section>

              <section>
                <h2 class="mb-5 text-2xl font-extrabold text-slate-950">
                  Laporan Terbaru
                </h2>

                <div class="overflow-hidden rounded-xl border border-slate-300 bg-white shadow-sm">
                  <article
                    v-for="report in recentReports"
                    :key="report.id"
                    class="group flex items-center gap-4 border-b border-slate-100 px-5 py-4 last:border-b-0 hover:bg-slate-50"
                  >
                    <div
                      class="grid size-13 shrink-0 place-items-center rounded-lg"
                      :class="report.iconClass"
                    >
                      <component :is="report.icon" :size="22" />
                    </div>

                    <div class="min-w-0 flex-1">
                      <div class="flex items-start justify-between gap-3">
                        <h3 class="truncate text-base font-extrabold text-slate-950">
                          {{ report.title }}
                        </h3>

                        <span
                          class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-extrabold"
                          :class="report.statusClass"
                        >
                          {{ report.status }}
                        </span>
                      </div>

                      <p class="mt-1 text-sm text-slate-500">
                        {{ report.location }}
                      </p>
                      <p class="mt-2 text-xs text-slate-400">
                        {{ report.time }}
                      </p>
                    </div>

                    <button
                      type="button"
                      class="grid size-8 place-items-center rounded-full text-slate-300 transition group-hover:bg-blue-50 group-hover:text-blue-700"
                      :aria-label="`Buka ${report.title}`"
                    >
                      <ChevronRight :size="19" />
                    </button>
                  </article>

                  <button
                    type="button"
                    class="w-full border-t border-slate-100 px-5 py-4 text-sm font-extrabold text-blue-700 transition hover:bg-blue-50"
                  >
                    Tampilkan Selengkapnya
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