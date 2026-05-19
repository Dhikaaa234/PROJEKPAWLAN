<template>
  <div>
    <router-view />
  </div>
</template>

<script setup>
import { useTheme } from './composables/useTheme'
useTheme() // panggil agar dark mode aktif
</script>