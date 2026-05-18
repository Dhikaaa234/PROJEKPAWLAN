<script setup>
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  BarChart3,
  Bell,
  Building2,
  Camera,
  CheckCircle2,
  ChevronLeft,
  CirclePlus,
  FileImage,
  Image,
  LayoutDashboard,
  ListChecks,
  LogOut,
  MapPin,
  Search,
  Send,
  Settings,
  SlidersHorizontal,
  UserSearch,
  Wrench,
} from 'lucide-vue-next'
import { useAuthStore } from '../stores/auth'
import DashboardSidebar from '../components/DashboardSidebar.vue'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const isMobileMenuOpen = ref(false)
const isSubmitting = ref(false)

const form = ref({
  title: '',
  category: '',
  location: '',
  description: '',
})

const uploadedPhotos = ref([
  {
    id: 1,
    type: 'preview',
  },
  {
    id: 2,
    type: 'empty',
  },
])

const categories = [
  'Sarana Prasarana',
  'Kebersihan & Sanitasi',
  'IT & Jaringan',
  'Kelistrikan',
  'Keamanan',
]

const menuItems = [
  {
    label: 'Dashboard',
    icon: LayoutDashboard,
    path: '/dashboard',
  },
  {
    label: 'Semua Laporan',
    icon: BarChart3,
    path: '/semua-laporan',
  },
  {
    label: 'Laporan Saya',
    icon: UserSearch,
    path: '/laporan-saya',
  },
  {
    label: 'Buat Laporan',
    icon: CirclePlus,
    path: '/buat-laporan',
  },
  {
    label: 'Notifikasi',
    icon: Bell,
    path: '/notifikasi',
  },
]

const similarReports = [
  {
    id: 1,
    title: 'Lampu Meja Laboratorium Redup',
    time: '2 jam yang lalu',
    status: 'Diproses',
    statusClass: 'text-blue-700',
    imageClass: 'from-slate-950 via-slate-700 to-slate-400',
  },
  {
    id: 2,
    title: 'Kabel LAN di G2.4 Terputus',
    time: 'Kemarin',
    status: 'Dikirim',
    statusClass: 'text-amber-700',
    imageClass: 'from-slate-950 via-cyan-950 to-slate-600',
  },
]

const userInitials = computed(() => {
  const name = auth.user?.name || 'Admin Filkom'

  return name
    .split(' ')
    .map((word) => word[0])
    .join('')
    .slice(0, 2)
    .toUpperCase()
})

function isActive(path) {
  return route.path === path
}

function goTo(path) {
  if (route.path !== path) {
    router.push(path)
  }

  closeMobileMenu()
}

function toggleMobileMenu() {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}

function closeMobileMenu() {
  isMobileMenuOpen.value = false
}

function logout() {
  auth.logout()
  closeMobileMenu()
  router.push('/login')
}

function cancelForm() {
  router.push('/dashboard')
}

async function submitReport() {
  isSubmitting.value = true

  try {
    await new Promise((resolve) => setTimeout(resolve, 700))
    router.push('/laporan-saya')
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-[#faf8ff] text-slate-900">
    <div class="flex min-h-screen">
      <DashboardSidebar />

      <div class="min-w-0 flex-1">
        <header
          class="sticky top-0 z-30 flex h-[66px] items-center justify-between border-b border-slate-200 bg-white/95 px-5 backdrop-blur md:px-8"
        >
          <h2 class="text-xl font-extrabold tracking-tight text-blue-700">
            FilkomCare
          </h2>

          <div class="flex items-center gap-4">

            <button
              type="button"
              aria-label="Notifikasi"
              class="grid size-9 place-items-center rounded-full text-slate-600 transition hover:bg-slate-100 hover:text-blue-700"
            >
              <Bell :size="20" />
            </button>

            <button
              type="button"
              aria-label="Pengaturan"
              class="grid size-9 place-items-center rounded-full text-slate-600 transition hover:bg-slate-100 hover:text-blue-700"
            >
              <Settings :size="20" />
            </button>

            <div class="hidden h-8 w-px bg-slate-200 md:block"></div>

            <div class="flex items-center gap-3">
              <div
                class="grid size-10 place-items-center rounded-full bg-orange-100 text-sm font-extrabold text-orange-700 ring-2 ring-orange-50"
              >
                {{ userInitials }}
              </div>

              <div class="hidden leading-tight sm:block">
                <p class="text-sm font-bold text-slate-950">
                  {{ auth.user?.name || 'Admin Filkom' }}
                </p>
                <p class="text-xs text-slate-500">
                  {{ auth.user?.roleLabel || 'Super Admin' }}
                </p>
              </div>
            </div>
          </div>
        </header>

        <button
          type="button"
          class="fixed bottom-5 right-5 z-50 grid size-12 place-items-center rounded-full bg-blue-700 text-white shadow-lg lg:hidden"
          aria-label="Toggle menu"
          @click="toggleMobileMenu"
        >
          <CirclePlus v-if="!isMobileMenuOpen" :size="24" />
          <ChevronLeft v-else :size="24" />
        </button>

        <div
          v-if="isMobileMenuOpen"
          class="fixed inset-0 z-40 bg-slate-950/40 lg:hidden"
          @click="closeMobileMenu"
        ></div>

        <aside
          class="fixed bottom-0 left-0 top-0 z-50 flex w-[280px] transform flex-col bg-white p-5 shadow-2xl transition lg:hidden"
          :class="isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'"
        >
          <div class="mb-8 flex items-center gap-3">
            <div class="grid size-10 place-items-center rounded-lg bg-blue-600 text-white">
              <Building2 :size="22" />
            </div>

            <div>
              <h1 class="text-xl font-extrabold text-slate-950">
                FilkomCare
              </h1>
              <p class="text-xs font-extrabold uppercase tracking-wide text-blue-700">
                Facility Management
              </p>
            </div>
          </div>

          <nav class="space-y-2">
            <button
              v-for="item in menuItems"
              :key="item.label"
              type="button"
              @click="goTo(item.path)"
              class="flex w-full items-center gap-3 rounded-lg px-4 py-3 text-left text-sm font-medium transition"
              :class="
                isActive(item.path)
                  ? 'bg-blue-50 text-blue-700'
                  : 'text-slate-600 hover:bg-slate-50 hover:text-blue-700'
              "
            >
              <component :is="item.icon" :size="21" />
              <span>{{ item.label }}</span>
            </button>
          </nav>

          <button
            type="button"
            @click="logout"
            class="mt-auto flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium text-red-600 transition hover:bg-red-50 hover:text-red-700"
          >
            <LogOut :size="21" />
            <span>Logout</span>
          </button>
        </aside>

        <main class="px-5 py-8 md:px-8 lg:px-10">
          <section class="mx-auto grid max-w-[1280px] gap-6 xl:grid-cols-[1fr_390px]">
            <form
              class="overflow-hidden rounded-xl border border-slate-300 bg-white shadow-sm"
              @submit.prevent="submitReport"
            >
              <div class="border-b border-slate-300 px-6 py-6">
                <h1 class="text-xl font-medium text-slate-950">
                  Informasi Laporan
                </h1>
                <p class="mt-2 text-base text-slate-600">
                  Lengkapi detail kerusakan atau masalah fasilitas di bawah ini.
                </p>
              </div>

              <div class="space-y-7 px-6 py-7">
                <div>
                  <label for="title" class="mb-3 block text-base font-medium text-slate-700">
                    Judul Laporan
                  </label>

                  <input
                    id="title"
                    v-model="form.title"
                    type="text"
                    required
                    placeholder="Contoh: AC Ruang H4.1 Tidak Dingin"
                    class="h-13 w-full rounded-lg border border-slate-300 bg-white px-4 text-base text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                  />
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                  <div>
                    <label for="category" class="mb-3 block text-base font-medium text-slate-700">
                      Kategori
                    </label>

                    <div class="relative">
                      <select
                        id="category"
                        v-model="form.category"
                        required
                        class="h-13 w-full appearance-none rounded-lg border border-slate-300 bg-white px-4 pr-10 text-base text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                      >
                        <option value="" disabled>Pilih Kategori</option>
                        <option
                          v-for="category in categories"
                          :key="category"
                          :value="category"
                        >
                          {{ category }}
                        </option>
                      </select>

                      <ChevronLeft
                        :size="18"
                        class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 -rotate-90 text-slate-500"
                      />
                    </div>
                  </div>

                  <div>
                    <label for="location" class="mb-3 block text-base font-medium text-slate-700">
                      Lokasi Spesifik
                    </label>

                    <div class="relative">
                      <MapPin
                        :size="20"
                        class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"
                      />

                      <input
                        id="location"
                        v-model="form.location"
                        type="text"
                        required
                        placeholder="Gedung G, Lantai 2, R. G2.4"
                        class="h-13 w-full rounded-lg border border-slate-300 bg-white pl-12 pr-4 text-base text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                      />
                    </div>
                  </div>
                </div>

                <div>
                  <label for="description" class="mb-3 block text-base font-medium text-slate-700">
                    Deskripsi Kerusakan
                  </label>

                  <textarea
                    id="description"
                    v-model="form.description"
                    required
                    rows="6"
                    placeholder="Jelaskan secara detail mengenai kondisi kerusakan yang anda temukan..."
                    class="min-h-[150px] w-full resize-none rounded-lg border border-slate-300 bg-white px-4 py-4 text-base leading-relaxed text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                  ></textarea>
                </div>

                <div>
                  <label class="mb-3 block text-base font-medium text-slate-700">
                    Foto Bukti (Maks. 3 Foto)
                  </label>

                  <div class="grid gap-4 sm:grid-cols-3">
                    <button
                      type="button"
                      class="grid h-[170px] place-items-center rounded-lg border-2 border-dashed border-slate-300 bg-white text-slate-600 transition hover:border-blue-400 hover:bg-blue-50"
                    >
                      <div class="text-center">
                        <Camera :size="36" class="mx-auto mb-3" />
                        <p class="text-base font-medium">
                          Unggah
                        </p>
                      </div>
                    </button>

                    <div
                      v-for="photo in uploadedPhotos"
                      :key="photo.id"
                      class="relative grid h-[170px] place-items-center overflow-hidden rounded-lg border border-slate-300 bg-slate-100"
                    >
                      <div
                        v-if="photo.type === 'preview'"
                        class="absolute inset-0 bg-gradient-to-br from-slate-300 via-slate-500 to-slate-800"
                      ></div>

                      <Wrench
                        v-if="photo.type === 'preview'"
                        :size="92"
                        class="relative z-10 rotate-[-28deg] text-white/30"
                      />

                      <FileImage
                        v-if="photo.type === 'empty'"
                        :size="24"
                        class="text-slate-500"
                      />
                    </div>
                  </div>

                  <p class="mt-3 text-sm italic text-slate-600">
                    Format: JPG, PNG. Maksimal ukuran per file: 5MB.
                  </p>
                </div>
              </div>

              <div class="flex flex-col gap-3 border-t border-slate-300 px-6 py-6 sm:flex-row sm:justify-end">
                <button
                  type="button"
                  @click="cancelForm"
                  class="h-12 rounded-lg border border-slate-400 bg-white px-8 text-base font-medium text-slate-800 transition hover:bg-slate-50"
                >
                  Batal
                </button>

                <button
                  type="submit"
                  :disabled="isSubmitting"
                  class="inline-flex h-12 items-center justify-center gap-3 rounded-lg bg-blue-700 px-8 text-base font-medium text-white transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-70"
                >
                  <Send :size="20" />
                  <span v-if="!isSubmitting">Kirim Laporan</span>
                  <span v-else>Mengirim...</span>
                </button>
              </div>
            </form>

            <aside class="space-y-6">
              <section class="relative overflow-hidden rounded-xl bg-[#dfe4ff] p-6 text-slate-900 shadow-sm">
                <div class="absolute -right-8 -top-8 size-28 rounded-full border-[12px] border-slate-500/10"></div>
                <div class="absolute -right-5 top-16 h-5 w-20 rounded-full bg-slate-500/10"></div>

                <div class="mb-5 flex items-center gap-3">
                  <ListChecks :size="25" class="text-slate-900" />
                  <h2 class="text-xl font-medium">
                    Tips Melapor
                  </h2>
                </div>

                <div class="space-y-5 text-base leading-relaxed text-slate-900">
                  <div class="flex gap-3">
                    <CheckCircle2 :size="18" class="mt-1 shrink-0" />
                    <p>Sebutkan nama ruangan secara akurat untuk mempercepat teknisi menemukan lokasi.</p>
                  </div>

                  <div class="flex gap-3">
                    <CheckCircle2 :size="18" class="mt-1 shrink-0" />
                    <p>Ambil foto kerusakan dari jarak dekat dan jarak jauh (konteks ruangan).</p>
                  </div>

                  <div class="flex gap-3">
                    <CheckCircle2 :size="18" class="mt-1 shrink-0" />
                    <p>Pastikan judul laporan singkat namun jelas menggambarkan masalah utama.</p>
                  </div>
                </div>
              </section>

              <section class="rounded-xl border border-slate-300 bg-white p-6 shadow-sm">
                <div class="mb-7 flex items-center justify-between">
                  <h2 class="text-xl font-medium text-slate-950">
                    Laporan Mirip Terdekat
                  </h2>

                  <span class="rounded bg-yellow-300 px-2 py-1 text-[10px] font-extrabold text-yellow-900">
                    LIVE DATA
                  </span>
                </div>

                <div class="space-y-7">
                  <article
                    v-for="report in similarReports"
                    :key="report.id"
                    class="flex items-center gap-4"
                  >
                    <div
                      class="grid size-12 shrink-0 place-items-center overflow-hidden rounded bg-gradient-to-br"
                      :class="report.imageClass"
                    >
                      <Image :size="18" class="text-white/70" />
                    </div>

                    <div>
                      <h3 class="text-base font-medium text-slate-950">
                        {{ report.title }}
                      </h3>

                      <p class="mt-1 text-xs font-medium text-slate-500">
                        {{ report.time }}
                        <span class="mx-1">•</span>
                        <span :class="report.statusClass">
                          {{ report.status }}
                        </span>
                      </p>
                    </div>
                  </article>
                </div>

                <button
                  type="button"
                  class="mt-8 w-full text-center text-base font-medium text-blue-700 transition hover:text-blue-900"
                >
                  Lihat semua laporan di area ini
                </button>
              </section>

              <section class="rounded-xl border border-slate-300 bg-white p-6 shadow-sm">
                <div class="flex items-center gap-4">
                  <div class="grid size-12 place-items-center rounded-full bg-blue-100 text-blue-700">
                    <Send :size="24" />
                  </div>

                  <p class="text-base leading-relaxed text-slate-600">
                    Anda telah membuat<br />
                    <span class="font-bold text-slate-950">12 Laporan</span>
                    Bulan Ini
                  </p>
                </div>
              </section>
            </aside>
          </section>
        </main>
      </div>
    </div>
  </div>
</template>