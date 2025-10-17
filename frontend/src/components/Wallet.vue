<template>
    <div class="widget-container">
        <div class="widget-header">
            <h2>Portefeuille</h2>
            <p>Gérez votre argent et vos transactions</p>
        </div>

        <div class="widget-content">
            <!-- Solde -->
            <div class="balance-section">
                <div class="balance-card">
                    <h3>Solde actuel</h3>
                    <p class="balance-amount">{{ walletStore.formattedBalance }}</p>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="actions-grid">
                <!-- Rechargement -->
                <div class="action-card">
                    <h4>Recharger le portefeuille</h4>
                    <form @submit.prevent="handleTopUp" class="action-form">
                        <input v-model="topUpAmount" type="number" step="0.01" min="0.01" max="10000"
                            placeholder="Montant (max 10 000€)" required :disabled="walletStore.loading">
                        <button type="submit" :disabled="walletStore.loading" class="btn-action">
                            {{ walletStore.loading ? '...' : 'Recharger' }}
                        </button>
                    </form>
                </div>

                <!-- Transfert -->
                <div class="action-card">
                    <h4>Effectuer un transfert</h4>
                    <form @submit.prevent="handleTransfer" class="action-form">
                        <input v-model="transferData.receiver_id" type="number" placeholder="ID du destinataire"
                            required :disabled="walletStore.loading">
                        <input v-model="transferData.amount" type="number" step="0.01" min="0.01" placeholder="Montant"
                            required :disabled="walletStore.loading">
                        <button type="submit" :disabled="walletStore.loading" class="btn-action">
                            {{ walletStore.loading ? '...' : 'Transférer' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Transactions récentes -->
            <div class="transactions-section">
                <div class="section-header">
                    <h3>Transactions récentes</h3>
                    <button @click="refreshTransactions" :disabled="walletStore.loading" class="btn-refresh">
                        Actualiser
                    </button>
                </div>

                <div v-if="walletStore.loading && walletStore.transactions.length === 0" class="loading">
                    Chargement des transactions...
                </div>

                <div v-else-if="walletStore.transactions.length === 0" class="empty-state">
                    <p>Aucune transaction pour le moment</p>
                </div>

                <div v-else class="transactions-list">
                    <div v-for="transaction in walletStore.recentTransactions" :key="transaction.id"
                        class="transaction-item" :class="getTransactionClass(transaction)">
                        <div class="transaction-icon">
                            {{ getTransactionIcon(transaction) }}
                        </div>
                        <div class="transaction-details">
                            <div class="transaction-type">{{ getTransactionType(transaction) }}</div>
                            <div class="transaction-meta">
                                <span v-if="transaction.sender_id === authStore.user?.id">
                                    Vers utilisateur #{{ transaction.receiver_id }}
                                </span>
                                <span v-else-if="transaction.receiver_id === authStore.user?.id">
                                    De utilisateur #{{ transaction.sender_id || 'Système' }}
                                </span>
                            </div>
                            <div class="transaction-date">{{ formatDate(transaction.created_at) }}</div>
                        </div>
                        <div class="transaction-amount" :class="getAmountClass(transaction)">
                            {{ getAmountPrefix(transaction) }}{{ transaction.amount }} €
                        </div>
                    </div>
                </div>
            </div>

            <!-- Erreurs -->
            <div v-if="walletStore.error" class="error-message">
                {{ walletStore.error }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useWalletStore } from '@/stores/walletStore'
import { useAuthStore } from '@/stores/authStore'

const walletStore = useWalletStore()
const authStore = useAuthStore()

const topUpAmount = ref('')
const transferData = ref({
    receiver_id: '',
    amount: ''
})

onMounted(async () => {
    await loadWalletData()
})

const loadWalletData = async () => {
    await walletStore.fetchBalance()
    await walletStore.fetchTransactions()
}

const refreshTransactions = async () => {
    await walletStore.fetchTransactions()
}

const handleTopUp = async () => {
    try {
        await walletStore.topUp(parseFloat(topUpAmount.value))
        topUpAmount.value = ''
    } catch (error) {
        console.error('Top-up error:', error)
    }
}

const handleTransfer = async () => {
    try {
        await walletStore.transfer({
            receiver_id: parseInt(transferData.value.receiver_id),
            amount: parseFloat(transferData.value.amount)
        })
        transferData.value = { receiver_id: '', amount: '' }
    } catch (error) {
        console.error('Transfer error:', error)
    }
}

const getTransactionClass = (transaction) => {
    if (transaction.type === 'topup') return 'topup'
    if (transaction.sender_id === authStore.user?.id) return 'sent'
    return 'received'
}



const getTransactionType = (transaction) => {
    if (transaction.type === 'topup') return 'Rechargement'
    if (transaction.sender_id === authStore.user?.id) return 'Transfert envoyé'
    return 'Transfert reçu'
}

const getAmountClass = (transaction) => {
    if (transaction.type === 'topup' || transaction.receiver_id === authStore.user?.id) {
        return 'positive'
    }
    return 'negative'
}

const getAmountPrefix = (transaction) => {
    if (transaction.type === 'topup' || transaction.receiver_id === authStore.user?.id) {
        return '+'
    }
    return '-'
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>

<style scoped>
.widget-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.widget-header {
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #e9ecef;
    background: #f8f9fa;
}

.widget-header h2 {
    margin: 0 0 0.5rem 0;
    color: #333;
}

.widget-header p {
    margin: 0;
    color: #666;
}

.widget-content {
    padding: 2rem;
}

.balance-section {
    margin-bottom: 2rem;
}

.balance-card {
    text-align: center;
    padding: 2rem;
    background: linear-gradient(135deg, #28a745, #20c997);
    border-radius: 12px;
    color: white;
}

.balance-card h3 {
    margin: 0 0 1rem 0;
    font-size: 1.1rem;
    opacity: 0.9;
}

.balance-amount {
    font-size: 3rem;
    font-weight: bold;
    margin: 0;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.action-card {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.action-card h4 {
    margin: 0 0 1rem 0;
    color: #333;
}

.action-form {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.action-form input {
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
}

.btn-action {
    background: #007bff;
    color: white;
    border: none;
    padding: 0.75rem;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
}

.btn-action:hover:not(:disabled) {
    background: #0056b3;
}

.btn-action:disabled {
    background: #6c757d;
    cursor: not-allowed;
}

.transactions-section {
    border-top: 1px solid #e9ecef;
    padding-top: 2rem;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.section-header h3 {
    margin: 0;
    color: #333;
}

.btn-refresh {
    background: #6c757d;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
}

.transactions-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.transaction-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.transaction-item:hover {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.transaction-item.topup {
    border-left: 4px solid #28a745;
}

.transaction-item.sent {
    border-left: 4px solid #dc3545;
}

.transaction-item.received {
    border-left: 4px solid #007bff;
}

.transaction-icon {
    font-size: 1.5rem;
    margin-right: 1rem;
    width: 40px;
    text-align: center;
}

.transaction-details {
    flex: 1;
}

.transaction-type {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.25rem;
}

.transaction-meta {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 0.25rem;
}

.transaction-date {
    font-size: 0.8rem;
    color: #999;
}

.transaction-amount {
    font-weight: bold;
    font-size: 1.1rem;
}

.transaction-amount.positive {
    color: #28a745;
}

.transaction-amount.negative {
    color: #dc3545;
}

.loading,
.empty-state {
    text-align: center;
    padding: 2rem;
    color: #666;
}

.error-message {
    background: #f8d7da;
    color: #721c24;
    padding: 1rem;
    border-radius: 6px;
    border: 1px solid #f5c6cb;
    margin-top: 1rem;
}
</style>
