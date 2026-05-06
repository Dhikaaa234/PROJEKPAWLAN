import { defineStore } from 'pinia'

const AUTH_STORAGE_KEY = 'filkomcare_user'

function getRoleFromEmail(email) {
  const normalizedEmail = String(email || '').toLowerCase().trim()

  if (
    normalizedEmail === 'admin@filkom.edu' ||
    normalizedEmail.includes('admin')
  ) {
    return 'admin'
  }

  return 'user'
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem(AUTH_STORAGE_KEY)) || null,
    loading: false,
    lastMessage: '',
  }),

  getters: {
    isAuthenticated: (state) => Boolean(state.user),
    role: (state) => state.user?.role || null,
    isAdmin: (state) => state.user?.role === 'admin',
    isUser: (state) => state.user?.role === 'user',
  },

  actions: {
    async login({ email, password, remember }) {
      this.loading = true
      this.lastMessage = ''

      try {
        await new Promise((resolve) => setTimeout(resolve, 600))

        if (!email || !password) {
          throw new Error('Email dan password wajib diisi.')
        }

        if (password.length < 6) {
          throw new Error('Password minimal 6 karakter.')
        }

        const role = getRoleFromEmail(email)

        const user = {
          id: role === 'admin' ? 1 : 2,
          name: role === 'admin' ? 'Admin Filkom' : 'User Filkom',
          role,
          roleLabel: role === 'admin' ? 'Super Admin' : 'Mahasiswa',
          email,
          remember,
        }

        this.user = user
        localStorage.setItem(AUTH_STORAGE_KEY, JSON.stringify(user))

        this.lastMessage =
          role === 'admin'
            ? 'Login admin berhasil. Mengalihkan ke dashboard admin...'
            : 'Login berhasil. Mengalihkan ke dashboard...'

        return user
      } finally {
        this.loading = false
      }
    },

    logout() {
      this.user = null
      this.lastMessage = ''
      localStorage.removeItem(AUTH_STORAGE_KEY)
    },
  },
})