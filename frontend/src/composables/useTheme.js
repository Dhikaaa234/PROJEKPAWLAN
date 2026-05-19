// src/composables/useTheme.js
import { ref, watch, onMounted } from 'vue'

const isDark = ref(false)

export function useTheme() {
  const loadTheme = () => {
    const saved = localStorage.getItem('theme')
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
    isDark.value = saved === 'dark' || (saved === null && prefersDark)
    applyTheme()
  }

  const applyTheme = () => {
    if (isDark.value) {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
  }

  const toggleTheme = () => {
    isDark.value = !isDark.value
    applyTheme()
  }

  onMounted(() => loadTheme())

  return { isDark, toggleTheme }
}
