<template>
  <div data-testid="password-strength" v-if="value">
    <div class="flex gap-1.5 h-1.5">
      <span
        v-for="i in 4"
        :key="i"
        class="flex-1 rounded-full transition-colors duration-300"
        :class="i <= score ? barColor : 'bg-slate-200'"
      />
    </div>
    <p class="mt-1.5 text-xs font-medium" :class="textColor">
      {{ label }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({ value: { type: String, default: '' } })

const score = computed(() => {
  const v = props.value || ''
  let s = 0
  if (v.length >= 6) s++
  if (v.length >= 10) s++
  if (/[A-Z]/.test(v) && /[a-z]/.test(v)) s++
  if (/\d/.test(v) && /[^A-Za-z0-9]/.test(v)) s++
  return s
})

const label = computed(() => ['Too short', 'Weak', 'Fair', 'Strong', 'Excellent'][score.value])
const barColor = computed(() => ['bg-rose-400', 'bg-rose-400', 'bg-amber-400', 'bg-emerald-400', 'bg-emerald-500'][score.value])
const textColor = computed(() => ['text-rose-500', 'text-rose-500', 'text-amber-600', 'text-emerald-600', 'text-emerald-700'][score.value])
</script>
