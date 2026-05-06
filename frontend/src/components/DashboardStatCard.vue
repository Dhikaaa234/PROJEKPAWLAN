<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  value: {
    type: String,
    required: true,
  },
  note: {
    type: String,
    required: true,
  },
  icon: {
    type: Object,
    required: true,
  },
  iconClass: {
    type: String,
    required: true,
  },
  valueClass: {
    type: String,
    required: true,
  },
  noteClass: {
    type: String,
    required: true,
  },
})
</script>

<template>
  <article
    class="rounded-xl border border-slate-200 bg-white p-6 shadow-[0_2px_8px_rgba(15,23,42,0.04)]"
  >
    <div class="mb-5 flex items-start justify-between">
      <div class="grid size-11 place-items-center rounded-lg" :class="iconClass">
        <component :is="icon" :size="23" />
      </div>

      <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="noteClass">
        {{ note }}
      </span>
    </div>

    <p class="text-sm font-medium text-slate-500">
      {{ title }}
    </p>

    <h3 class="mt-1 text-4xl font-extrabold tracking-tight" :class="valueClass">
      {{ value }}
    </h3>
  </article>
</template>