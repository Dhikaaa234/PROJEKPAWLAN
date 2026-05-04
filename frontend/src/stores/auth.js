import { defineStore } from 'pinia'

// Frontend-only mock auth store. Ganti bagian ini kalau sudah mau connect ke backend.
export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    loading: false,
    lastMessage: ''
  }),
  actions: {
    async login({ email, password, remember }) {
      this.loading = true
      this.lastMessage = ''
      await new Promise((r) => setTimeout(r, 700))
      this.user = { email, remember: !!remember }
      this.loading = false
      this.lastMessage = `Signed in as ${email}`
      return this.user
    },
    async register({ name, nim, email, password }) {
      this.loading = true
      this.lastMessage = ''
      await new Promise((r) => setTimeout(r, 800))
      this.user = { email, name, nim }
      this.loading = false
      this.lastMessage = `Account created for ${email}`
      return this.user
    },
    async sendResetLink(email) {
      this.loading = true
      this.lastMessage = ''
      await new Promise((r) => setTimeout(r, 700))
      this.loading = false
      this.lastMessage = `Reset link sent to ${email}`
      return true
    },
    logout() {
      this.user = null
      this.lastMessage = 'Signed out'
    }
  }
})
