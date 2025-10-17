import { defineStore } from 'pinia'
import api from '@/services/api'

export const useModuleStore = defineStore('modules', {
    state: () => ({
        modules: [],
        userModules: [],
        loading: false,
        error: null
    }),

    actions: {
        async fetchModules() {
            this.loading = true
            this.error = null

            try {
                const response = await api.get('/modules')
                this.modules = response.data
                return response
            } catch (error) {
                this.error = error.response?.data?.message || 'Erreur lors du chargement des modules'
                throw error
            } finally {
                this.loading = false
            }
        },

        async activateModule(moduleId) {
            this.loading = true
            this.error = null

            try {
                const response = await api.post(`/modules/${moduleId}/activate`)
                await this.fetchModules()
                return response
            } catch (error) {
                this.error = error.response?.data?.message || 'Erreur lors de l activation'
                throw error
            } finally {
                this.loading = false
            }
        },

        async deactivateModule(moduleId) {
            this.loading = true
            this.error = null

            try {
                const response = await api.post(`/modules/${moduleId}/deactivate`)
                await this.fetchModules()
                return response
            } catch (error) {
                this.error = error.response?.data?.message || 'Erreur lors de la desactivation'
                throw error
            } finally {
                this.loading = false
            }
        },

        isModuleActive(moduleId) {
            return this.userModules.some(module => module.id === moduleId && module.active)
        }
    },

    getters: {
        activeModules: (state) => {
            return state.modules.filter(module => module.active)
        },

        desactiveModules: (state) => {
            return state.modules.filter(module => !module.active)
        }
    }
})
