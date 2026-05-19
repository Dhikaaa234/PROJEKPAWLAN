import { ref, computed } from 'vue'
import id from '../locales/id.js'
import en from '../locales/en.js'

const translations = { id, en }
const locale = ref(localStorage.getItem('locale') || 'id')

export function useLocale() {
  const t = computed(() => translations[locale.value] || translations.id)

  const setLocale = (newLocale) => {
    if (translations[newLocale]) {
      locale.value = newLocale
      localStorage.setItem('locale', newLocale)
      window.location.reload() // refresh halaman setelah ganti bahasa
    }
  }

  return { t, setLocale, locale }
}

