<template>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Modules</h2>
            </div>

            <nav class="sidebar-nav">
                <ul class="modules-list">
                    <li
                        v-for="module in modulesList"
                        :key="module.id"
                        class="module-item"
                        :class="{ 'active': module.active }"
                    >
                        <div class="module-content">
                            <span class="module-name">{{ module.name }}</span>
                            <button
                                @click="toggleModule(module.id)"
                                :disabled="moduleStore.loading"
                                :class="['btn-toggle', module.active ? 'active' : 'inactive']"
                            >
                                {{ module.active ? 'Désactiver' : 'Activer' }}
                            </button>
                        </div>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Contenu principal -->
        <main class="main-content">
            <header class="content-header">
                <h1>Tableau de Bord</h1>
                <div class="user-actions">
                    <span class="user-info">Utilisateur #{{ authStore.user?.id }}</span>
                    <button @click="handleLogout" class="btn-logout">Déconnexion</button>
                </div>
            </header>

            <div class="content-area">
                <!-- Aucun module activé -->
                <div v-if="!hasActiveModules" class="welcome-section">
                    <div class="welcome-card">
                        <h2>Bienvenue sur votre Dashboard</h2>
                        <p>Activez un module dans la sidebar pour commencer à l'utiliser.</p>
                    </div>
                </div>

                <!-- Modules activés -->
                <div v-else class="modules-grid">
                    <!-- URL Shortener -->
                    <div v-if="isModuleActive(1)" class="module-widget">
                        <UrlShortenerWidget />
                    </div>

                    <!-- Wallet -->
                    <div v-if="isModuleActive(2)" class="module-widget">
                        <WalletWidget />
                    </div>

                    <!-- Time Tracker -->
                    <div v-if="isModuleActive(4)" class="module-widget">
                        <TimeTrackerWidget />
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useUrlShortenerStore } from '@/stores/urlshortenerStore'

const urlStore = useUrlShortenerStore()

const form = ref({
    original_url: '',
    custom_code: ''
})

const createdLink = ref(null)

onMounted(async () => {
    await urlStore.fetchLinks()
})

const refreshLinks = async () => {
    await urlStore.fetchLinks()
}

const handleCreateLink = async () => {
    try {
        const response = await urlStore.createShortLink(form.value)
        createdLink.value = response.data
        form.value = { original_url: '', custom_code: '' }
    } catch (error) {
        console.error('Error creating link:', error)
    }
}

const handleDeleteLink = async (linkId) => {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce lien ?')) {
        return
    }

    try {
        await urlStore.deleteLink(linkId)
    } catch (error) {
        console.error('Error deleting link:', error)
    }
}

const getShortUrl = (code) => {
    return urlStore.getShortUrl(code)
}

const truncateUrl = (url) => {
    if (url.length > 50) {
        return url.substring(0, 47) + '...'
    }
    return url
}

const copyToClipboard = async (text) => {
    try {
        await navigator.clipboard.writeText(text)
        alert('Lien copié dans le presse-papier !')
    } catch (err) {
        const textArea = document.createElement('textarea')
        textArea.value = text
        document.body.appendChild(textArea)
        textArea.select()
        document.execCommand('copy')
        document.body.removeChild(textArea)
        alert('Lien copié dans le presse-papier !')
    }
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('fr-FR')
}
</script>

<style scoped>
.creation-section {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #e9ecef;
}

.creation-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    max-width: 500px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #333;
}

.form-group input {
    padding: 0.75rem;
    border: 2px solid #e1e5e9;
    border-radius: 6px;
    font-size: 1rem;
}

.form-group input:focus {
    outline: none;
    border-color: #007bff;
}

.form-group small {
    color: #666;
    margin-top: 0.25rem;
    font-size: 0.8rem;
}

.btn-primary {
    background: #007bff;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    align-self: flex-start;
}

.btn-primary:hover:not(:disabled) {
    background: #0056b3;
}

.btn-primary:disabled {
    background: #6c757d;
    cursor: not-allowed;
}

.success-card {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    border-radius: 8px;
    padding: 1.5rem;
    margin-top: 1.5rem;
}

.link-result {
    margin-top: 1rem;
}

.link-original,
.link-short {
    margin-bottom: 0.5rem;
}

.short-url {
    color: #007bff;
    text-decoration: none;
    font-weight: 600;
    margin: 0 0.5rem;
}

.short-url:hover {
    text-decoration: underline;
}

.btn-copy {
    background: #28a745;
    color: white;
    border: none;
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
}

.stats-section {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #e9ecef;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
}

.stat-card {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    text-align: center;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #007bff;
    margin-bottom: 0.5rem;
}

.stat-label {
    color: #666;
    font-size: 0.9rem;
}

.links-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.link-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.link-item:hover {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.link-info {
    flex: 1;
}

.link-original {
    margin-bottom: 0.5rem;
    word-break: break-all;
}

.link-short {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
}

.btn-copy-small {
    background: none;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 0.25rem 0.5rem;
    cursor: pointer;
    margin-left: 0.5rem;
    font-size: 0.8rem;
}

.link-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.875rem;
    color: #666;
}

.clicks {
    color: #28a745;
    font-weight: 600;
}

.btn-delete {
    background: #dc3545;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
}

.btn-delete:hover:not(:disabled) {
    background: #c82333;
}

.btn-delete:disabled {
    opacity: 0.6;
    cursor: not-allowed;
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
