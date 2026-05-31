import axios from 'axios'
import router from '../router'

// Base URL untuk backend API
const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

// Buat instance axios dengan default config
const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Interceptor untuk menambahkan token ke setiap request
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }

    // FormData dipakai saat upload foto laporan. Content-Type dibiarkan Axios/browser
    // agar boundary multipart otomatis terbentuk.
    if (config.data instanceof FormData) {
      if (typeof config.headers.delete === 'function') {
        config.headers.delete('Content-Type')
      } else {
        delete config.headers['Content-Type']
      }
    }

    return config
  },
  (error) => Promise.reject(error)
)

// Interceptor untuk handle response errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Hanya redirect jika bukan endpoint public (login/register/forgot-password)
      const url = error.config?.url
      const isPublicEndpoint = url && ['/login', '/register', '/forgot-password', '/reset-password'].some(ep => url.includes(ep))
      
      if (!isPublicEndpoint) {
        // Token expired atau invalid - untuk protected routes
        // Hapus semua kunci storage terkait auth agar tidak meninggalkan state yang tidak konsisten
        localStorage.removeItem('auth_token')
        localStorage.removeItem('user')
        localStorage.removeItem('filkomcare_user')
        localStorage.removeItem('remember_me')

        // Navigasi ke login — router.push tidak melakukan reload, tetapi route guard akan
        // memulihkan session dari storage yang sudah dibersihkan.
        router.push('/login').catch(() => {})
      }
    }
    return Promise.reject(error)
  }
)

// Auth API endpoints
export const authAPI = {
  register: (data) => api.post('/register', data),
  login: (data) => api.post('/login', data),
  logout: () => api.post('/logout'),
  me: () => api.get('/me'),
  sendResetLink: (email) => api.post('/forgot-password', { email })
}

// Endpoint laporan dipisah agar view tidak perlu tahu detail konfigurasi Axios.
export const reportAPI = {
  createReport: (payload) => api.post('/reports', payload),
}

export default api
