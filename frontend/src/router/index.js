import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/authStore'



const routes = [
    {
        path: '/login',
        name: 'Login',
        component: () => import('../views/Login.vue'),
        meta: { requiresGuest: true }
    },

    {
        path: '/register',
        name: 'Register',
        component: () => import('../views/Register.vue'),
        meta: { requiresGuest: true }
    },
    {
        path: '/dashboard',
        name: 'Dashboard',
        component: () => import('../views/Dashboard.vue'),
        meta: { requiresAuth: true }
    },

    {
        path: '/urlshortener',
        name: 'urlshortener',
        component: () => import('../components/UrlShortener.vue'),
        meta: { requiresAuth: true }
    },

    {
        path: '/wallet',
        name: 'Wallet',
        component: () => import('../components/Wallet.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/',
        redirect: '/dashboard'
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore()

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next('/login')
    } else if (to.meta.requiresGuest && authStore.isAuthenticated) {
        next('/dashboard')
    } else {
        next()
    }
})

export default router
