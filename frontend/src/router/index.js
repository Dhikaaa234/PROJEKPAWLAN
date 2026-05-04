import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import ForgotPassword from '../views/ForgotPassword.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/login' },
    { path: '/login', name: 'login', component: Login, meta: { title: 'Sign in — FilkomCare' } },
    { path: '/register', name: 'register', component: Register, meta: { title: 'Create account — FilkomCare' } },
    { path: '/forgot-password', name: 'forgot-password', component: ForgotPassword, meta: { title: 'Reset password — FilkomCare' } },
    { path: '/:pathMatch(.*)*', redirect: '/login' }
  ],
  scrollBehavior() {
    return { top: 0 }
  }
})

router.afterEach((to) => {
  if (to.meta?.title) {
    document.title = to.meta.title
  }
})

export default router
