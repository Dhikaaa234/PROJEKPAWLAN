<script setup>
import { computed, onMounted, onUnmounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { BarChart3, Bell, Building2, Camera, CheckCircle2, ChevronLeft, CirclePlus, FileImage, Image, LayoutDashboard, ListChecks, LogOut, MapPin, Send, UserSearch, X } from "lucide-vue-next";
import { useAuthStore } from "../stores/auth";
import api, { reportAPI } from "../services/api";
import DashboardSidebar from "../components/DashboardSidebar.vue";
import DashboardTopbar from "../components/DashboardTopbar.vue";

const SIMILAR_REPORT_LIMIT = 5;

const router = useRouter();
const route = useRoute();
const auth = useAuthStore();
const { t } = useI18n();

const isMobileMenuOpen = ref(false);
const isSubmitting = ref(false);
const isLoadingOptions = ref(false);
const fileInput = ref(null);
const selectedImageFile = ref(null);
const selectedImagePreview = ref("");
const imageError = ref("");
const maxImageSize = 5 * 1024 * 1024;
const allowedImageTypes = ["image/jpeg", "image/png", "image/webp"];

const form = ref({
  title: "",
  category: "",
  location: "",
  description: "",
});

const categories = ref([]);
const similarReports = ref([]);
const reportSummary = ref(null);

const menuItems = computed(() => [
  {
    label: t("sidebar.dashboard"),
    icon: LayoutDashboard,
    path: "/dashboard",
  },
  {
    label: t("sidebar.all_reports"),
    icon: BarChart3,
    path: "/semua-laporan",
  },
  {
    label: t("sidebar.my_reports"),
    icon: UserSearch,
    path: "/laporan-saya",
  },
  {
    label: t("sidebar.create_report"),
    icon: CirclePlus,
    path: "/buat-laporan",
  },
  {
    label: t("sidebar.notifications"),
    icon: Bell,
    path: "/notifikasi",
  },
]);

const emptyPhotoSlots = computed(() => 2);

const monthlyReportTotal = computed(() => reportSummary.value?.monthlyTotal ?? reportSummary.value?.monthly_total ?? "");

function unwrapResponse(response) {
  return response?.data?.data ?? response?.data ?? {};
}

function getStatusTextClass(status) {
  if (status === "Dikirim") return "text-amber-700";
  if (status === "Diproses") return "text-blue-700";
  if (status === "Selesai") return "text-green-700";
  if (status === "Dibatalkan") return "text-red-700";
  return "text-slate-600";
}

function getStatusLabel(status) {
  const labels = {
    Dikirim: t("reports.status_sent"),
    Diproses: t("reports.status_processed"),
    Selesai: t("reports.status_completed"),
    Dibatalkan: t("reports.status_cancelled"),
  };

  return labels[status] || status;
}

function normalizeSimilarReport(report) {
  return {
    id: report.id ?? report.code ?? report.reportId,
    title: report.title ?? "",
    date: report.date ?? report.time ?? report.createdAt ?? report.created_at ?? "",
    time: report.time ?? report.date ?? report.createdAt ?? report.created_at ?? "",
    status: report.status ?? "",
    statusClass: report.statusClass ?? getStatusTextClass(report.status),
    imagePath: report.imagePath ?? report.image_path ?? null,
    imageUrl: report.imageUrl ?? report.image_url ?? null,
    imageClass: report.imageClass ?? "from-slate-950 via-slate-700 to-slate-400",
  };
}

function extractReports(payload) {
  if (Array.isArray(payload)) return payload;
  if (Array.isArray(payload.reports)) return payload.reports;
  if (Array.isArray(payload.similarReports)) return payload.similarReports;
  if (Array.isArray(payload.items)) return payload.items;
  return [];
}

async function fetchReportOptions() {
  isLoadingOptions.value = true;

  try {
    // Options mengambil kategori/summary, similar mengambil maksimal 5 laporan terbaru.
    const [optionsResult, similarResult] = await Promise.allSettled([
      api.get("/reports/options"),
      api.get("/reports/similar", {
        params: {
          limit: SIMILAR_REPORT_LIMIT,
        },
      }),
    ]);

    const optionsPayload =
      optionsResult.status === "fulfilled"
        ? unwrapResponse(optionsResult.value)
        : {};

    const similarPayload =
      similarResult.status === "fulfilled"
        ? unwrapResponse(similarResult.value)
        : {};

    categories.value = Array.isArray(optionsPayload.categories) ? optionsPayload.categories : [];
    similarReports.value = extractReports(similarPayload)
      .map(normalizeSimilarReport)
      .slice(0, SIMILAR_REPORT_LIMIT);
    reportSummary.value = optionsPayload.reportSummary ?? optionsPayload.summary ?? null;
  } catch (error) {
    categories.value = [];
    similarReports.value = [];
    reportSummary.value = null;
  } finally {
    isLoadingOptions.value = false;
  }
}

function isActive(path) {
  return route.path === path;
}

function goTo(path) {
  if (route.path !== path) {
    router.push(path);
  }

  closeMobileMenu();
}

function toggleMobileMenu() {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
}

function closeMobileMenu() {
  isMobileMenuOpen.value = false;
}

function logout() {
  auth.logout();
  closeMobileMenu();
  router.push("/login");
}

function cancelForm() {
  router.push("/dashboard");
}

function goToAllReports() {
  router.push("/semua-laporan");
}

function openPhotoPicker() {
  fileInput.value?.click();
}

function handleImageChange(event) {
  const file = event.target.files?.[0] ?? null;
  imageError.value = "";

  if (!file) return;

  // Validasi frontend agar file yang dikirim sesuai aturan backend.
  if (!allowedImageTypes.includes(file.type)) {
    imageError.value = t("create_report.image_format_error");
    event.target.value = "";
    return;
  }

  if (file.size > maxImageSize) {
    imageError.value = t("create_report.image_size_error");
    event.target.value = "";
    return;
  }

  if (selectedImagePreview.value) {
    URL.revokeObjectURL(selectedImagePreview.value);
  }

  selectedImageFile.value = file;
  selectedImagePreview.value = URL.createObjectURL(file);
  event.target.value = "";
}

function removeImage() {
  if (selectedImagePreview.value) {
    URL.revokeObjectURL(selectedImagePreview.value);
  }

  selectedImageFile.value = null;
  selectedImagePreview.value = "";
  imageError.value = "";
}

async function submitReport() {
  isSubmitting.value = true;

  try {
    // FormData wajib dipakai agar file gambar terkirim sebagai multipart/form-data.
    const payload = new FormData();
    payload.append("title", form.value.title);
    payload.append("category", form.value.category);
    payload.append("location", form.value.location);
    payload.append("description", form.value.description);
    if (selectedImageFile.value) {
      payload.append("image", selectedImageFile.value);
    }

    await reportAPI.createReport(payload);

    router.push("/laporan-saya");
  } finally {
    isSubmitting.value = false;
  }
}

onMounted(fetchReportOptions);

onUnmounted(() => {
  if (selectedImagePreview.value) {
    URL.revokeObjectURL(selectedImagePreview.value);
  }
});
</script>

<template>
  <div class="min-h-screen bg-[#faf8ff] text-slate-900">
    <div class="flex min-h-screen">
      <DashboardSidebar />

      <div class="min-w-0 flex-1">
        <DashboardTopbar />

        <button type="button" class="fixed bottom-5 right-5 z-50 grid size-12 place-items-center rounded-full bg-blue-700 text-white shadow-lg lg:hidden" :aria-label="$t('common.toggle_menu')" @click="toggleMobileMenu">
          <CirclePlus v-if="!isMobileMenuOpen" :size="24" />
          <ChevronLeft v-else :size="24" />
        </button>

        <div v-if="isMobileMenuOpen" class="fixed inset-0 z-40 bg-slate-950/40 lg:hidden" @click="closeMobileMenu"></div>

        <aside class="fixed bottom-0 left-0 top-0 z-50 flex w-[280px] transform flex-col bg-white p-5 shadow-2xl transition lg:hidden" :class="isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'">
          <div class="mb-8 flex items-center gap-3">
            <div class="grid size-10 place-items-center rounded-lg bg-blue-600 text-white">
              <Building2 :size="22" />
            </div>

            <div>
              <h1 class="text-xl font-extrabold text-slate-950">FilkomCare</h1>
              <p class="text-xs font-extrabold uppercase tracking-wide text-blue-700">{{ $t('common.facility_management') }}</p>
            </div>
          </div>

          <nav class="space-y-2">
            <button
              v-for="item in menuItems"
              :key="item.label"
              type="button"
              @click="goTo(item.path)"
              class="flex w-full items-center gap-3 rounded-lg px-4 py-3 text-left text-sm font-medium transition"
              :class="isActive(item.path) ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-700'"
            >
              <component :is="item.icon" :size="21" />
              <span>{{ item.label }}</span>
            </button>
          </nav>

          <button type="button" @click="logout" class="mt-auto flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium text-red-600 transition hover:bg-red-50 hover:text-red-700">
            <LogOut :size="21" />
            <span>{{ $t('common.logout') }}</span>
          </button>
        </aside>

        <main class="px-5 py-8 md:px-8 lg:px-10">
          <section class="mx-auto grid max-w-[1280px] gap-6 xl:grid-cols-[1fr_390px]">
            <form class="overflow-hidden rounded-xl border border-slate-300 bg-white shadow-sm" @submit.prevent="submitReport">
              <div class="border-b border-slate-300 px-6 py-6">
                <h1 class="text-xl font-medium text-slate-950">{{ $t('create_report.info_title') }}</h1>
                <p class="mt-2 text-base text-slate-600">Lengkapi detail kerusakan atau masalah fasilitas di bawah ini.</p>
              </div>

              <div class="space-y-7 px-6 py-7">
                <div>
                  <label for="title" class="mb-3 block text-base font-medium text-slate-700">{{ $t('create_report.title') }}</label>

                  <input
                    id="title"
                    v-model="form.title"
                    type="text"
                    required
                    :placeholder="$t('create_report.title_placeholder')"
                    class="h-13 w-full rounded-lg border border-slate-300 bg-white px-4 text-base text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                  />
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                  <div>
                    <label for="category" class="mb-3 block text-base font-medium text-slate-700">{{ $t('create_report.category') }}</label>

                    <div class="relative">
                      <select
                        id="category"
                        v-model="form.category"
                        required
                        class="h-13 w-full appearance-none rounded-lg border border-slate-300 bg-white px-4 pr-10 text-base text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                      >
                        <option value="" disabled>{{ $t('create_report.select_category') }}</option>
                        <option v-for="category in categories" :key="category" :value="category">
                          {{ category }}
                        </option>
                      </select>

                      <ChevronLeft :size="18" class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 -rotate-90 text-slate-500" />
                    </div>
                  </div>

                  <div>
                    <label for="location" class="mb-3 block text-base font-medium text-slate-700">{{ $t('create_report.location') }}</label>

                    <div class="relative">
                      <MapPin :size="20" class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-500" />

                      <input
                        id="location"
                        v-model="form.location"
                        type="text"
                        required
                        :placeholder="$t('create_report.location_placeholder')"
                        class="h-13 w-full rounded-lg border border-slate-300 bg-white pl-12 pr-4 text-base text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                      />
                    </div>
                  </div>
                </div>

                <div>
                  <label for="description" class="mb-3 block text-base font-medium text-slate-700">{{ $t('create_report.description') }}</label>

                  <textarea
                    id="description"
                    v-model="form.description"
                    required
                    rows="6"
                    :placeholder="$t('create_report.description_placeholder')"
                    class="min-h-[150px] w-full resize-none rounded-lg border border-slate-300 bg-white px-4 py-4 text-base leading-relaxed text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                  ></textarea>
                </div>

                <div>
                  <label class="mb-3 block text-base font-medium text-slate-700">{{ $t('create_report.photo') }}</label>

                  <div class="grid gap-4 sm:grid-cols-3">
                    <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="handleImageChange" />

                    <button
                      v-if="!selectedImageFile"
                      type="button"
                      @click="openPhotoPicker"
                      class="grid h-[170px] place-items-center rounded-lg border-2 border-dashed border-slate-300 bg-white text-slate-600 transition hover:border-blue-400 hover:bg-blue-50"
                    >
                      <div class="text-center">
                        <Camera :size="36" class="mx-auto mb-3" />
                        <p class="text-base font-medium">{{ $t('create_report.upload') }}</p>
                      </div>
                    </button>

                    <div v-else class="relative grid h-[170px] place-items-center overflow-hidden rounded-lg border border-slate-300 bg-slate-100">
                      <img :src="selectedImagePreview" :alt="selectedImageFile.name" class="absolute inset-0 size-full object-cover" />

                      <button
                        type="button"
                        class="absolute right-2 top-2 grid size-8 place-items-center rounded-full bg-white/90 text-slate-700 shadow-sm transition hover:bg-white"
                        :aria-label="`Hapus ${selectedImageFile.name}`"
                        @click="removeImage"
                      >
                        <X :size="16" />
                      </button>
                    </div>

                    <div v-for="slot in emptyPhotoSlots" :key="`empty-${slot}`" class="relative grid h-[170px] place-items-center overflow-hidden rounded-lg border border-slate-300 bg-slate-100">
                      <FileImage :size="24" class="text-slate-500" />
                    </div>
                  </div>

                  <p class="mt-3 text-sm italic text-slate-600">{{ $t('create_report.format_note') }}</p>

                  <p v-if="imageError" class="mt-2 text-sm font-medium text-red-600">
                    {{ imageError }}
                  </p>
                </div>
              </div>

              <div class="flex flex-col gap-3 border-t border-slate-300 px-6 py-6 sm:flex-row sm:justify-end">
                <button type="button" @click="cancelForm" class="h-12 rounded-lg border border-slate-400 bg-white px-8 text-base font-medium text-slate-800 transition hover:bg-slate-50">{{ $t('common.cancel') }}</button>

                <button
                  type="submit"
                  :disabled="isSubmitting"
                  class="inline-flex h-12 items-center justify-center gap-3 rounded-lg bg-blue-700 px-8 text-base font-medium text-white transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-70"
                >
                  <Send :size="20" />
                  <span v-if="!isSubmitting">{{ $t('create_report.submit') }}</span>
                  <span v-else>{{ $t('common.submitting') }}</span>
                </button>
              </div>
            </form>

            <aside class="space-y-6">
              <section class="relative overflow-hidden rounded-xl bg-[#dfe4ff] p-6 text-slate-900 shadow-sm">
                <div class="absolute -right-8 -top-8 size-28 rounded-full border-[12px] border-slate-500/10"></div>
                <div class="absolute -right-5 top-16 h-5 w-20 rounded-full bg-slate-500/10"></div>

                <div class="mb-5 flex items-center gap-3">
                  <ListChecks :size="25" class="text-slate-900" />
                  <h2 class="text-xl font-medium">{{ $t('create_report.tips') }}</h2>
                </div>

                <div class="space-y-5 text-base leading-relaxed text-slate-900">
                  <div class="flex gap-3">
                    <CheckCircle2 :size="18" class="mt-1 shrink-0" />
                    <p>{{ $t('create_report.tip1') }}</p>
                  </div>

                  <div class="flex gap-3">
                    <CheckCircle2 :size="18" class="mt-1 shrink-0" />
                    <p>{{ $t('create_report.tip2') }}</p>
                  </div>

                  <div class="flex gap-3">
                    <CheckCircle2 :size="18" class="mt-1 shrink-0" />
                    <p>{{ $t('create_report.tip3') }}</p>
                  </div>
                </div>
              </section>

              <section class="rounded-xl border border-slate-300 bg-white p-6 shadow-sm">
                <div class="mb-7 flex items-center justify-between">
                  <h2 class="text-xl font-medium text-slate-950">{{ $t('create_report.similar') }}</h2>

                  <span class="rounded bg-yellow-300 px-2 py-1 text-[10px] font-extrabold text-yellow-900">
                    {{ $t('common.live_data') }}
                  </span>
                </div>

                <div class="space-y-7">
                  <article v-for="report in similarReports.slice(0, SIMILAR_REPORT_LIMIT)" :key="report.id" class="flex items-center gap-4">
                    <div class="grid size-12 shrink-0 place-items-center overflow-hidden rounded bg-slate-100" :class="report.imageUrl ? '' : ['bg-gradient-to-br', report.imageClass]">
                      <img
                        v-if="report.imageUrl"
                        :src="report.imageUrl"
                        :alt="report.title"
                        class="h-full w-full object-cover"
                      />

                      <Image v-else :size="18" class="text-white/70" />
                    </div>

                    <div>
                      <h3 class="text-base font-medium text-slate-950">
                        {{ report.title }}
                      </h3>

                      <p class="mt-1 text-xs font-medium text-slate-500">
                        {{ report.date }}
                        <span class="mx-1">/</span>
                        <span :class="report.statusClass">
                          {{ getStatusLabel(report.status) }}
                        </span>
                      </p>
                    </div>
                  </article>

                  <p v-if="similarReports.length === 0 && !isLoadingOptions" class="text-sm font-medium text-slate-500">{{ $t('create_report.no_similar') }}</p>
                </div>

                <button type="button" @click="goToAllReports" class="mt-8 w-full text-center text-base font-medium text-blue-700 transition hover:text-blue-900">{{ $t('create_report.see_all_area') }}</button>
              </section>

              <section v-if="monthlyReportTotal !== ''" class="rounded-xl border border-slate-300 bg-white p-6 shadow-sm">
                <div class="flex items-center gap-4">
                  <div class="grid size-12 place-items-center rounded-full bg-blue-100 text-blue-700">
                    <Send :size="24" />
                  </div>

                  <p class="text-base leading-relaxed text-slate-600">
                    Anda telah membuat<br />
                    <span class="font-bold text-slate-950"> {{ monthlyReportTotal }} {{ $t('common.report') }} </span>
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
