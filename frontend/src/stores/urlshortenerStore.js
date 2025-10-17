import { defineStore } from 'pinia'
import api from '@/services/api'

export const useUrlShortenerStore = defineStore('urlShortener', {
    state: () => ({
        links: [],
        loading: false,
        error: null
    }),

    actions: {
        async fetchLinks() {
            this.loading = true
            this.error = null

            try {
                const response = await api.get('/links')
                this.links = response.data
                return response
            } catch (error) {
                this.error = error.response?.data?.message || 'Erreur lors du chargement des liens'
                throw error
            } finally {
                this.loading = false
            }
        },

        async createShortLink(linkData) {
            this.loading = true
            this.error = null

            try {
                const response = await api.post('/shorten', linkData)
                this.links.unshift(response.data)
                return response
            } catch (error) {
                this.error = error.response?.data?.message || 'Erreur lors de la creation du lien'
                throw error
            } finally {
                this.loading = false
            }
        },

        getShortUrl(code) {
            return `${import.meta.env.VITE_APP_URL}/api/s/${code}`
        }
    }
})
