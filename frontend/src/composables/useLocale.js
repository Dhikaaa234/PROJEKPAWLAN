import { computed } from 'vue'
import i18n, {
  loadUserLocale,
  setLocaleForCurrentUser,
} from '../i18n'
import { messages } from '../i18n/messages'

function flattenMessages(source, prefix = '', target = {}) {
  Object.entries(source).forEach(([key, value]) => {
    const nextKey = prefix ? `${prefix}.${key}` : key

    if (value && typeof value === 'object' && !Array.isArray(value)) {
      flattenMessages(value, nextKey, target)
      return
    }

    target[nextKey] = value
    target[key] = value
  })

  return target
}

export function useLocale() {
  const locale = computed({
    get: () => i18n.global.locale.value,
    set: (value) => setLocaleForCurrentUser(value),
  })

  const t = computed(() => flattenMessages(messages[locale.value] || messages.id))

  return {
    t,
    locale,
    loadUserLocale,
    setLocale: setLocaleForCurrentUser,
    setLocaleForCurrentUser,
  }
}
