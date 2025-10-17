import { defineStore } from 'pinia'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('auth_token') || null,
        isAuthenticated: !!localStorage.getItem('auth_token'),
        loading: false,
        error: null
    }),

    actions: {
        async login(credentials) {
            this.loading = true
            this.error = null

            try {
                const response = await api.post('/login', credentials)

                this.token = response.data.token
                this.isAuthenticated = true
                this.user = { id: response.data.user_id }

                localStorage.setItem('auth_token', this.token)
                localStorage.setItem('user', JSON.stringify(this.user))

                return response
            } catch (error) {
                this.error = error.response?.data?.message || 'Erreur de connexion'
                throw error
            } finally {
                this.loading = false
            }
        },

        async register(userData) {
            this.loading = true
            this.error = null

            try {
                const response = await api.post('/register', userData)
                return response
            } catch (error) {
                this.error = error.response?.data?.message || 'Erreur d inscription'
                throw error
            } finally {
                this.loading = false
            }
        },

        async logout() {
            try {
                await api.post('/logout')
            } catch (error) {
                console.error('Logout error:', error)
            } finally {
                this.user = null
                this.token = null
                this.isAuthenticated = false
                this.error = null
                localStorage.removeItem('auth_token')
                localStorage.removeItem('user')
            }
        }
    }
})
