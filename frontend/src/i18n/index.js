import { createI18n } from 'vue-i18n'
import { messages } from './messages'

export const DEFAULT_LOCALE = 'id'
export const GUEST_LOCALE_KEY = 'locale_guest'

let currentLocaleUser = null

function normalizeLocale(locale) {
  return messages[locale] ? locale : DEFAULT_LOCALE
}

export function getLocaleStorageKey(user = currentLocaleUser) {
  return user?.id ? `locale_user_${user.id}` : GUEST_LOCALE_KEY
}

export function getStoredLocaleForUser(user = currentLocaleUser) {
  return normalizeLocale(localStorage.getItem(getLocaleStorageKey(user)))
}

function applyLocale(locale) {
  i18n.global.locale.value = normalizeLocale(locale)
}

export const defaultLocale = getStoredLocaleForUser(null)

const i18n = createI18n({
  legacy: false,
  globalInjection: true,
  locale: defaultLocale,
  fallbackLocale: 'id',
  messages,
})

export function loadUserLocale(user = null) {
  currentLocaleUser = user || null
  const locale = getStoredLocaleForUser(currentLocaleUser)

  applyLocale(locale)

  return locale
}

export function setLocaleForCurrentUser(locale, user = currentLocaleUser) {
  const normalizedLocale = normalizeLocale(locale)

  currentLocaleUser = user || currentLocaleUser || null
  localStorage.setItem(getLocaleStorageKey(currentLocaleUser), normalizedLocale)
  applyLocale(normalizedLocale)

  return normalizedLocale
}

export default i18n
