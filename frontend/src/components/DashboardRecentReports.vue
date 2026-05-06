<script setup>
import { ChevronRight } from 'lucide-vue-next'

const reports = [
  {
    id: 1,
    title: 'AC Ruang Kelas G.21 Mati Total',
    description: 'Pendingin ruangan di gedung G lantai 2 tidak bisa dinyalakan sejak pagi...',
    status: 'Dikirim',
    time: '2 menit yang lalu',
    imageClass: 'bg-slate-800',
    emoji: '❄️',
  },
  {
    id: 2,
    title: 'Kebocoran Pipa Wastafel Toilet Lantai 1',
    description: 'Air menggenang di area toilet pria gedung A karena pipa bocor...',
    status: 'Diproses',
    time: '1 jam yang lalu',
    imageClass: 'bg-slate-900',
    emoji: '🚰',
  },
  {
    id: 3,
    title: 'Proyektor Ruang Seminar Burem',
    description: 'Lensa proyektor sudah dibersihkan dan kalibrasi warna selesai...',
    status: 'Selesai',
    time: '3 jam yang lalu',
    imageClass: 'bg-slate-800',
    emoji: '📽️',
  },
]

function getStatusClass(status) {
  const classes = {
    Dikirim: 'bg-amber-50 text-amber-600',
    Diproses: 'bg-blue-50 text-blue-600',
    Selesai: 'bg-green-50 text-green-600',
  }

  return classes[status] || 'bg-slate-100 text-slate-600'
}
</script>

<template>
  <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-6">
      <h2 class="text-xl font-extrabold text-slate-950">
        Laporan Terbaru
      </h2>

      <button
        type="button"
        class="text-sm font-bold leading-tight text-blue-700 transition hover:text-blue-900"
      >
        Lihat<br />
        Semua
      </button>
    </div>

    <div class="divide-y divide-slate-100">
      <article
        v-for="report in reports"
        :key="report.id"
        class="group flex items-center gap-4 px-4 py-4 transition hover:bg-slate-50 sm:px-6"
      >
        <div
          class="grid size-16 shrink-0 place-items-center rounded-lg text-2xl text-white"
          :class="report.imageClass"
        >
          {{ report.emoji }}
        </div>

        <div class="min-w-0 flex-1">
          <div class="mb-1 flex flex-wrap items-center gap-2">
            <span
              class="rounded-full px-2.5 py-1 text-[11px] font-semibold"
              :class="getStatusClass(report.status)"
            >
              {{ report.status }}
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
          :aria-label="`Buka ${report.title}`"
          class="grid size-9 shrink-0 place-items-center rounded-full text-slate-400 transition group-hover:bg-white group-hover:text-blue-700"
        >
          <ChevronRight :size="22" />
        </button>
      </article>
    </div>

    <div class="h-40 sm:h-48 lg:h-52"></div>
  </section>
</template>