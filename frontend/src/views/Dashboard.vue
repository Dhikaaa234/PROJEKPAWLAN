<script setup>
import { onMounted, ref } from 'vue'
import {
  CheckCircle2,
  ClipboardList,
  RefreshCcw,
  Send,
} from 'lucide-vue-next'

import api from '../services/api'
import DashboardSidebar from '../components/DashboardSidebar.vue'
import DashboardTopbar from '../components/DashboardTopbar.vue'
import DashboardStatCard from '../components/DashboardStatCard.vue'
import DashboardRecentReports from '../components/DashboardRecentReports.vue'

const RECENT_REPORT_LIMIT = 5

const stats = ref([])
const recentReports = ref([])
const isLoadingDashboard = ref(false)

const statIconMap = {
  total: ClipboardList,
  dikirim: Send,
  diproses: RefreshCcw,
  selesai: CheckCircle2,
}

const statStyleMap = {
  total: {
    iconClass: 'bg-slate-100 text-slate-600',
    valueClass: 'text-slate-950',
    noteClass: 'text-slate-500',
  },
  dikirim: {
    iconClass: 'bg-amber-50 text-amber-600',
    valueClass: 'text-amber-900',
    noteClass: 'bg-amber-50 text-amber-600',
  },
  diproses: {
    iconClass: 'bg-blue-50 text-blue-600',
    valueClass: 'text-blue-700',
    noteClass: 'bg-blue-50 text-blue-600',
  },
  selesai: {
    iconClass: 'bg-green-50 text-green-600',
    valueClass: 'text-green-700',
    noteClass: 'bg-green-50 text-green-600',
  },
}

function unwrapResponse(response) {
  return response?.data?.data ?? response?.data ?? {}
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
    note: stat.note ?? stat.subtitle ?? '',
    icon: statIconMap[key] || ClipboardList,
    iconClass: stat.iconClass ?? style.iconClass,
    valueClass: stat.valueClass ?? style.valueClass,
    noteClass: stat.noteClass ?? style.noteClass,
  }
}

function normalizeReport(report) {
  return {
    id: report.id ?? report.code ?? report.reportId,
    title: report.title ?? '',
    description: report.description ?? '',
    status: report.status ?? '',
    time: report.time ?? report.createdAt ?? report.created_at ?? '',
    category: report.category ?? '',
    imageUrl: report.imageUrl ?? report.image_url ?? null,
    imagePath: report.imagePath ?? report.image_path ?? null,
    imageLabel: report.imageLabel ?? report.category ?? report.status ?? '',
    imageClass: report.imageClass ?? 'bg-slate-800',
  }
}

async function fetchDashboard() {
  isLoadingDashboard.value = true

  try {
    const response = await api.get('/user/dashboard')
    const payload = unwrapResponse(response)
    const dashboardReports = Array.isArray(payload.recentReports)
      ? payload.recentReports
      : []

    stats.value = Array.isArray(payload.stats)
      ? payload.stats.map(normalizeStat)
      : []

    recentReports.value = dashboardReports
      .map(normalizeReport)
      .slice(0, RECENT_REPORT_LIMIT)
  } catch (error) {
    stats.value = []
    recentReports.value = []
  } finally {
    isLoadingDashboard.value = false
  }
}

onMounted(fetchDashboard)
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
                Berikut adalah ringkasan laporan fasilitas yang tersedia.
              </p>
            </div>

            <div
              v-if="stats.length > 0 || isLoadingDashboard"
              class="mb-9 grid gap-6 sm:grid-cols-2 xl:grid-cols-4"
            >
              <DashboardStatCard
                v-for="stat in stats"
                :key="stat.title"
                :title="stat.title"
                :value="String(stat.value)"
                :note="stat.note"
                :icon="stat.icon"
                :icon-class="stat.iconClass"
                :value-class="stat.valueClass"
                :note-class="stat.noteClass"
              />
            </div>

            <DashboardRecentReports
              :reports="recentReports"
              :is-loading="isLoadingDashboard"
            />
          </section>
        </main>
      </div>
    </div>
  </div>
</template>
