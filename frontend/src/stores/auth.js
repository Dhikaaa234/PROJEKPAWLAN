import { defineStore } from 'pinia'
import { loadUserLocale } from '../i18n'
import { authAPI } from '../services/api'

const AUTH_STORAGE_KEY = 'filkomcare_user'

function safelyParseJSON(value) {
  try {
    return value ? JSON.parse(value) : null
  } catch {
    return null
  }
}

function getStoredUser() {
  const user = safelyParseJSON(localStorage.getItem('user'))
  const fallbackUser = safelyParseJSON(localStorage.getItem(AUTH_STORAGE_KEY))

  return user || fallbackUser || null
}

function getStoredToken() {
  return localStorage.getItem('auth_token') || null
}

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

function normalizeUser(user, email = '') {
  if (!user) return null

  // Backend bisa mengirim nama sebagai "name" atau "nama".
  // Store menormalkan keduanya agar komponen frontend bisa memakai salah satu.
  const normalizedUser = {
    ...user,
  }

  if (!normalizedUser.role) {
    normalizedUser.role = getRoleFromEmail(normalizedUser.email || email)
  }

  if (!normalizedUser.name && normalizedUser.nama) {
    normalizedUser.name = normalizedUser.nama
  }

  if (!normalizedUser.nama && normalizedUser.name) {
    normalizedUser.nama = normalizedUser.name
  }

  if (!normalizedUser.roleLabel) {
    normalizedUser.roleLabel =
      normalizedUser.role === 'admin' ? 'Super Admin' : 'Mahasiswa'
  }

  return normalizedUser
}

function saveAuthToStorage(token, user, remember = false) {
  localStorage.setItem('auth_token', token)
  localStorage.setItem('user', JSON.stringify(user))
  localStorage.setItem(AUTH_STORAGE_KEY, JSON.stringify(user))

  if (remember) {
    localStorage.setItem('remember_me', 'true')
  }
}

function clearAuthStorage() {
  localStorage.removeItem('auth_token')
  localStorage.removeItem('user')
  localStorage.removeItem('remember_me')
  localStorage.removeItem(AUTH_STORAGE_KEY)
}

const storedToken = getStoredToken()
const storedUser = normalizeUser(getStoredUser())

if (storedToken && storedUser) {
  loadUserLocale(storedUser)
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: storedUser,
    token: storedToken,
    loading: false,
    lastMessage: '',
    error: null,
    isAuthenticated: !!storedToken && !!storedUser,
  }),

  getters: {
    isLoggedIn: (state) => state.isAuthenticated,
    role: (state) => state.user?.role || null,
    isAdmin: (state) => state.user?.role === 'admin',
    isUser: (state) => state.user?.role === 'user',
  },

  actions: {
    setAuth({ token, user, remember = false }) {
      const normalizedUser = normalizeUser(user)

      // Titik utama penyimpanan session setelah login/register/fetch user.
      this.token = token
      this.user = normalizedUser
      this.isAuthenticated = !!token && !!normalizedUser
      this.error = null

      if (token && normalizedUser) {
        saveAuthToStorage(token, normalizedUser, remember)
        loadUserLocale(normalizedUser)
      }
    },

    clearAuth() {
      this.token = null
      this.user = null
      this.isAuthenticated = false
      this.error = null

      clearAuthStorage()
      loadUserLocale(null)
    },

    // LOGIN
    async login({ email, password, remember }) {
      this.loading = true
      this.lastMessage = ''
      this.error = null

      try {
        const response = await authAPI.login({ email, password })

        if (response.status === 200 || response.status === 201) {
          const { token, user } = response.data
          const normalizedUser = normalizeUser(user, email)

          this.setAuth({
            token,
            user: normalizedUser,
            remember,
          })

          this.lastMessage =
            normalizedUser.role === 'admin'
              ? 'Login admin berhasil'
              : `Selamat datang, ${normalizedUser.nama || normalizedUser.name}!`

          return {
            success: true,
            user: normalizedUser,
            token,
          }
        }

        this.error = 'Login gagal. Coba lagi'
        return {
          success: false,
          error: this.error,
        }
      } catch (err) {
        this.clearAuth()

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

        return {
          success: false,
          error: this.error,
        }
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

        if (response.status === 201 || response.status === 200) {
          const { token, user } = response.data
          const normalizedUser = normalizeUser(user, email)

          this.setAuth({
            token,
            user: normalizedUser,
            remember: false,
          })

          this.lastMessage = `Akun berhasil dibuat! Selamat datang, ${
            normalizedUser.nama || normalizedUser.name
          }`

          return {
            success: true,
            user: normalizedUser,
            token,
          }
        }

        this.error = 'Registrasi gagal. Coba lagi'
        return {
          success: false,
          error: this.error,
        }
      } catch (err) {
        this.clearAuth()

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

        return {
          success: false,
          error: this.error,
        }
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

        return {
          success: true,
        }
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

        return {
          success: false,
          error: this.error,
        }
      } finally {
        this.loading = false
      }
    },

    // LOGOUT
    async logout() {
      this.loading = true

      try {
        if (this.token) {
          await authAPI.logout()
        }
      } catch (err) {
        console.error('Logout error:', err)
      } finally {
        this.clearAuth()
        this.lastMessage = 'Anda telah berhasil logout'
        this.loading = false
      }
    },

    // GET CURRENT USER
    async fetchCurrentUser() {
      this.loading = true
      this.error = null

      try {
        const response = await authAPI.me()
        const userData = response.data?.user || response.data
        const normalizedUser = normalizeUser(userData)

        this.user = normalizedUser
        this.isAuthenticated = !!this.token && !!normalizedUser

        if (this.token && normalizedUser) {
          saveAuthToStorage(this.token, normalizedUser)
          loadUserLocale(normalizedUser)
        }

        return normalizedUser
      } catch (err) {
        this.clearAuth()
        throw err
      } finally {
        this.loading = false
      }
    },

    restoreSession() {
      const token = getStoredToken()
      const user = normalizeUser(getStoredUser())

      // Dipakai router guard agar pindah halaman cepat tanpa request /api/me.
      this.token = token
      this.user = user
      this.isAuthenticated = !!token && !!user

      loadUserLocale(this.isAuthenticated ? user : null)

      return this.isAuthenticated
    },

    clearError() {
      this.error = null
    },
  },
})
