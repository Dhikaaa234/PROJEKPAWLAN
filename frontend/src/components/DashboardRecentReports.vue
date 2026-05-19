<script setup>
import { ChevronRight } from 'lucide-vue-next'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'

const props = defineProps({
  reports: {
    type: Array,
    default: () => [],
  },
  isLoading: {
    type: Boolean,
    default: false,
  },
})

const router = useRouter()
const { t } = useI18n()
const displayedReports = computed(() => props.reports.slice(0, 5))

function getStatusClass(status) {
  const classes = {
    Dikirim: 'bg-amber-50 text-amber-600',
    Diproses: 'bg-blue-50 text-blue-600',
    Selesai: 'bg-green-50 text-green-600',
    Dibatalkan: 'bg-red-50 text-red-600',
  }

  return classes[status] || 'bg-slate-100 text-slate-600'
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

function getImageClass(report) {
  return report.imageClass || 'bg-slate-800'
}

function getImageLabel(report) {
  return report.imageLabel || report.category || ''
}

function goToAllReports() {
  router.push('/semua-laporan')
}
</script>

<template>
  <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-6">
      <h2 class="text-xl font-extrabold text-slate-950">
        {{ $t('dashboard.recent_reports') }}
      </h2>

      <button
        type="button"
        @click="goToAllReports"
        class="text-sm font-bold leading-tight text-blue-700 transition hover:text-blue-900"
      >
        {{ $t('common.view_all') }}
      </button>
    </div>

    <div
      v-if="isLoading"
      class="px-6 py-10 text-center"
    >
      <p class="text-sm font-bold text-slate-600">
        {{ $t('dashboard.loading_recent_reports') }}
      </p>
    </div>

    <div
      v-else-if="displayedReports.length === 0"
      class="px-6 py-10 text-center"
    >
      <p class="text-sm font-bold text-slate-700">
        {{ $t('dashboard.no_recent_reports') }}
      </p>
    </div>

    <div
      v-else
      class="divide-y divide-slate-100"
    >
      <article
        v-for="report in displayedReports"
        :key="report.id"
        class="group flex items-center gap-4 px-4 py-4 transition hover:bg-slate-50 sm:px-6"
      >
        <div
          class="grid size-16 shrink-0 place-items-center overflow-hidden rounded-lg px-2 text-center text-xs font-extrabold uppercase text-white"
          :class="report.imageUrl ? 'bg-slate-100' : getImageClass(report)"
        >
          <img
            v-if="report.imageUrl"
            :src="report.imageUrl"
            :alt="report.title"
            class="size-full object-cover"
          />

          <span
            v-else
            class="truncate"
          >
            {{ getImageLabel(report) }}
          </span>
        </div>

        <div class="min-w-0 flex-1">
          <div class="mb-1 flex flex-wrap items-center gap-2">
            <span
              class="rounded-full px-2.5 py-1 text-[11px] font-semibold"
              :class="getStatusClass(report.status)"
            >
              {{ getStatusLabel(report.status) }}
            </span>

            <span class="text-sm text-slate-400">
              {{ report.time }}
            </span>
          </div>

          <h3 class="truncate text-sm font-bold text-slate-950 sm:text-base">
            {{ report.title }}
          </h3>

          <p class="truncate text-sm text-slate-600">
            {{ report.description }}
          </p>
        </div>

        <button
          type="button"
          :aria-label="$t('common.open_report', { title: report.title })"
          @click="goToAllReports"
          class="grid size-9 shrink-0 place-items-center rounded-full text-slate-400 transition group-hover:bg-white group-hover:text-blue-700"
        >
          <ChevronRight :size="22" />
        </button>
      </article>
    </div>
  </section>
</template>
