import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import ForgotPassword from '../views/ForgotPassword.vue'

import Dashboard from '../views/Dashboard.vue'
import AllReports from '../views/AllReports.vue'
import MyReports from '../views/MyReports.vue'
import CreateReport from '../views/CreateReport.vue'
import Notifications from '../views/Notifications.vue'

import AdminDashboard from '../views/AdminDashboard.vue'
import AdminReportManagement from '../views/AdminReportManagement.vue'
import AdminNotifications from '../views/AdminNotifications.vue'

const routes = [
  {
    path: '/',
    redirect: '/login',
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: {
      guestOnly: true,
    },
  },
  {
    path: '/register',
    name: 'Register',
    component: Register,
    meta: {
      guestOnly: true,
    },
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: ForgotPassword,
    meta: {
      guestOnly: true,
    },
  },

  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: {
      requiresAuth: true,
      role: 'user',
    },
  },
  {
    path: '/semua-laporan',
    name: 'AllReports',
    component: AllReports,
    meta: {
      requiresAuth: true,
      role: 'user',
    },
  },
  {
    path: '/laporan-saya',
    name: 'MyReports',
    component: MyReports,
    meta: {
      requiresAuth: true,
      role: 'user',
    },
  },
  {
    path: '/buat-laporan',
    name: 'CreateReport',
    component: CreateReport,
    meta: {
      requiresAuth: true,
      role: 'user',
    },
  },
  {
    path: '/notifikasi',
    name: 'Notifications',
    component: Notifications,
    meta: {
      requiresAuth: true,
      role: 'user',
    },
  },

  {
    path: '/admin/dashboard',
    name: 'AdminDashboard',
    component: AdminDashboard,
    meta: {
      requiresAuth: true,
      role: 'admin',
    },
  },
  {
    path: '/admin/notifikasi',
    name: 'AdminNotifications',
    component: AdminNotifications,
    meta: {
      requiresAuth: true,
      role: 'admin',
    },
  },
  {
    path: '/admin/management-laporan',
    name: 'AdminReportManagement',
    component: AdminReportManagement,
    meta: {
      requiresAuth: true,
      role: 'admin',
    },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return '/login'
  }

  if (to.meta.guestOnly && auth.isAuthenticated) {
    return auth.isAdmin ? '/admin/dashboard' : '/dashboard'
  }

  if (to.meta.role && auth.role !== to.meta.role) {
    return auth.isAdmin ? '/admin/dashboard' : '/dashboard'
  }

  return true
})

export default router
