<script setup>
import {
  CheckCircle2,
  ClipboardList,
  RefreshCcw,
  Send,
} from 'lucide-vue-next'

import DashboardSidebar from '../components/DashboardSidebar.vue'
import DashboardTopbar from '../components/DashboardTopbar.vue'
import DashboardStatCard from '../components/DashboardStatCard.vue'
import DashboardRecentReports from '../components/DashboardRecentReports.vue'

const stats = [
  {
    title: 'Total Laporan',
    value: '128',
    note: 'Minggu Ini',
    icon: ClipboardList,
    iconClass: 'bg-slate-100 text-slate-600',
    valueClass: 'text-slate-950',
    noteClass: 'text-slate-500',
  },
  {
    title: 'Dikirim',
    value: '24',
    note: 'Baru',
    icon: Send,
    iconClass: 'bg-amber-50 text-amber-600',
    valueClass: 'text-amber-900',
    noteClass: 'bg-amber-50 text-amber-600',
  },
  {
    title: 'Diproses',
    value: '12',
    note: 'Aktif',
    icon: RefreshCcw,
    iconClass: 'bg-blue-50 text-blue-600',
    valueClass: 'text-blue-700',
    noteClass: 'bg-blue-50 text-blue-600',
  },
  {
    title: 'Selesai',
    value: '92',
    note: 'Selesai',
    icon: CheckCircle2,
    iconClass: 'bg-green-50 text-green-600',
    valueClass: 'text-green-700',
    noteClass: 'bg-green-50 text-green-600',
  },
]
</script>

<template>
  <div class="min-h-screen bg-[#faf8ff] font-sans text-slate-900">
    <div class="flex min-h-screen">
      <DashboardSidebar />

      <div class="min-w-0 flex-1">
        <DashboardTopbar />

        <main class="px-5 py-8 md:px-8 lg:px-9 lg:py-5">
          <section class="mx-auto max-w-[1280px]">
            <div class="mb-8">
              <h1 class="text-4xl font-extrabold tracking-tight text-slate-950 md:text-5xl">
                Selamat Datang!
              </h1>
              <p class="mt-3 text-base text-slate-600 md:text-lg">
                Berikut adalah ringkasan laporan fasilitas hari ini.
              </p>
            </div>

            <div class="mb-9 grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
              <DashboardStatCard
                v-for="stat in stats"
                :key="stat.title"
                :title="stat.title"
                :value="stat.value"
                :note="stat.note"
                :icon="stat.icon"
                :icon-class="stat.iconClass"
                :value-class="stat.valueClass"
                :note-class="stat.noteClass"
              />
            </div>

            <DashboardRecentReports />
          </section>
        </main>
      </div>
    </div>
  </div>
</template>