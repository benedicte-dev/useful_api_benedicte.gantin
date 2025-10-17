import { defineStore } from 'pinia'
import api from '@/services/api'

export const useWalletStore = defineStore('wallet', {
    state: () => ({
        balance: 0,
        transactions: [],
        loading: false,
        error: null
    }),

    actions: {
        async fetchBalance() {
            this.loading = true
            this.error = null

            try {
                const response = await api.get('/wallet')
                this.balance = response.data.balance
                return response
            } catch (error) {
                this.error = error.response?.data?.message || 'Erreur lors du chargement du solde'
                throw error
            } finally {
                this.loading = false
            }
        },

        async fetchTransactions() {
            this.loading = true
            this.error = null

            try {
                const response = await api.get('/wallet/transactions')
                this.transactions = response.data
                return response
            } catch (error) {
                this.error = error.response?.data?.message || 'Erreur lors du chargement des transactions'
                throw error
            } finally {
                this.loading = false
            }
        },

        async topUp(amount) {
            this.loading = true
            this.error = null

            try {
                const response = await api.post('/wallet/topup', { amount })
                this.balance = response.data.balance
                await this.fetchTransactions()
                return response
            } catch (error) {
                this.error = error.response?.data?.message || 'Erreur lors du rechargement'
                throw error
            } finally {
                this.loading = false
            }
        },

        async transfer(transferData) {
            this.loading = true
            this.error = null

            try {
                const response = await api.post('/wallet/transfer', transferData)
                await this.fetchBalance()
                await this.fetchTransactions()
                return response
            } catch (error) {
                this.error = error.response?.data?.message || 'Erreur lors du transfert'
                throw error
            } finally {
                this.loading = false
            }
        }
    },

    getters: {
        formattedBalance: (state) => {
            return new Intl.NumberFormat('fr-FR', {
                style: 'currency',
                currency: 'EUR'
            }).format(state.balance)
        },

        recentTransactions: (state) => {
            return state.transactions.slice(0, 5)
        }
    }
})
