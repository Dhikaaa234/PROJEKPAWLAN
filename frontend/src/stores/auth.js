import { defineStore } from 'pinia'
import { authAPI } from '../services/api'

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
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('auth_token') || null,
    loading: false,
    lastMessage: '',
    error: null,
    isAuthenticated: !!localStorage.getItem('auth_token'),
  }),

  getters: {
    isLoggedIn: (state) => state.isAuthenticated,
    role: (state) => state.user?.role || null,
    isAdmin: (state) => state.user?.role === 'admin',
    isUser: (state) => state.user?.role === 'user',
  },

  actions: {
    // LOGIN
    async login({ email, password, remember }) {
      this.loading = true
      this.lastMessage = ''
      this.error = null

      try {
        const response = await authAPI.login({ email, password })

        if (response.status === 200 || response.status === 201) {
          const { token, user } = response.data

          // fallback role jika backend belum mengirim role
          if (!user.role) {
            user.role = getRoleFromEmail(email)
          }

          this.token = token
          this.user = user
          this.isAuthenticated = true

          this.lastMessage =
            user.role === 'admin'
              ? 'Login admin berhasil'
              : `Selamat datang, ${user.nama || user.name}!`

          localStorage.setItem('auth_token', token)
          localStorage.setItem('user', JSON.stringify(user))
          localStorage.setItem(AUTH_STORAGE_KEY, JSON.stringify(user))

          if (remember) {
            localStorage.setItem('remember_me', 'true')
          }

          return { success: true, user, token }
        }
      } catch (err) {
        this.isAuthenticated = false
        this.token = null
        this.user = null

        if (err.response?.status === 401) {
          this.error =
            err.response.data?.message || 'Email atau password salah'
        } else if (err.response?.status === 422) {
          this.error =
            'Data tidak valid. Periksa kembali email dan password Anda'
        } else if (err.code === 'ECONNREFUSED') {
          this.error =
            'Tidak dapat terhubung ke server. Pastikan backend berjalan'
        } else if (err.message === 'Network Error') {
          this.error =
            'Kesalahan jaringan. Periksa koneksi internet Anda'
        } else {
          this.error =
            err.response?.data?.message || 'Login gagal. Coba lagi'
        }

        this.lastMessage = ''
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    // REGISTER
    async register({ name, nim, email, password }) {
      this.loading = true
      this.lastMessage = ''
      this.error = null

      try {
        const response = await authAPI.register({
          nama: name,
          nim,
          email,
          password,
        })

        if (response.status === 201) {
          const { token, user } = response.data

          if (!user.role) {
            user.role = getRoleFromEmail(email)
          }

          this.token = token
          this.user = user
          this.isAuthenticated = true

          this.lastMessage = `Akun berhasil dibuat! Selamat datang, ${
            user.nama || user.name
          }`

          localStorage.setItem('auth_token', token)
          localStorage.setItem('user', JSON.stringify(user))
          localStorage.setItem(AUTH_STORAGE_KEY, JSON.stringify(user))

          return { success: true, user, token }
        }
      } catch (err) {
        this.isAuthenticated = false
        this.token = null
        this.user = null

        if (err.response?.status === 422) {
          const errors = err.response.data?.errors || {}

          if (errors.email) {
            this.error = 'Email sudah terdaftar'
          } else if (errors.nama) {
            this.error = 'Nama tidak valid'
          } else if (errors.password) {
            this.error = 'Password minimal 6 karakter'
          } else {
            this.error =
              'Data tidak valid. Periksa kembali formulir Anda'
          }
        } else if (err.code === 'ECONNREFUSED') {
          this.error =
            'Tidak dapat terhubung ke server. Pastikan backend berjalan'
        } else if (err.message === 'Network Error') {
          this.error =
            'Kesalahan jaringan. Periksa koneksi internet Anda'
        } else {
          this.error =
            err.response?.data?.message || 'Registrasi gagal. Coba lagi'
        }

        this.lastMessage = ''
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    // RESET PASSWORD
    async sendResetLink(email) {
      this.loading = true
      this.lastMessage = ''
      this.error = null

      try {
        await authAPI.sendResetLink(email)

        this.lastMessage = `Link reset password telah dikirim ke ${email}`

        return { success: true }
      } catch (err) {
        if (err.code === 'ECONNREFUSED') {
          this.error =
            'Tidak dapat terhubung ke server. Pastikan backend berjalan'
        } else if (err.message === 'Network Error') {
          this.error =
            'Kesalahan jaringan. Periksa koneksi internet Anda'
        } else if (err.response?.status === 404) {
          this.error = 'Email tidak ditemukan'
        } else {
          this.error =
            err.response?.data?.message || 'Gagal mengirim link reset'
        }

        this.lastMessage = ''
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    // LOGOUT
    async logout() {
      this.loading = true

      try {
        await authAPI.logout()
      } catch (err) {
        console.error('Logout error:', err)
      }

      this.token = null
      this.user = null
      this.isAuthenticated = false
      this.lastMessage = 'Anda telah berhasil logout'
      this.error = null

      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
      localStorage.removeItem('remember_me')
      localStorage.removeItem(AUTH_STORAGE_KEY)

      this.loading = false
    },

    // GET CURRENT USER
    async fetchCurrentUser() {
      this.loading = true

      try {
        const response = await authAPI.me()

        this.user = response.data
        this.isAuthenticated = true

        return response.data
      } catch (err) {
        this.token = null
        this.user = null
        this.isAuthenticated = false

        localStorage.removeItem('auth_token')
        localStorage.removeItem('user')

        throw err
      } finally {
        this.loading = false
      }
    },

    clearError() {
      this.error = null
    },
  },
})