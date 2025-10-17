<template>
    <div class="widget-container">
        <div class="widget-header">
            <h2>Raccourcisseur d'URL</h2>
        </div>

        <div class="widget-content">
            <!-- Formulaire de création -->
            <div class="creation-section">
                <h3>Créer un nouveau lien court</h3>
                <form @submit.prevent="handleCreateLink" class="creation-form">
                    <div class="form-group">
                        <label>URL originale *</label>
                        <input v-model="form.original_url" type="url" required placeholder="https://example.com"
                            :disabled="urlStore.loading">
                    </div>

                    <div class="form-group">
                        <label>Code personnalisé (optionnel)</label>
                        <input v-model="form.custom_code" type="text" placeholder="mon-lien" maxlength="10"
                            :disabled="urlStore.loading">
                        <small>Max 10 caractères (lettres, chiffres, -, _)</small>
                    </div>

                    <button type="submit" :disabled="urlStore.loading || !form.original_url" class="btn-primary">
                        {{ urlStore.loading ? 'Création...' : 'Créer le lien court' }}
                    </button>
                </form>

                <!-- Lien créé avec succès -->
                <div v-if="createdLink" class="success-card">
                    <h4>Lien créé avec succès !</h4>
                    <div class="link-result">
                        <div class="link-original">
                            <strong>URL originale :</strong> {{ createdLink.original_url }}
                        </div>
                        <div class="link-short">
                            <strong>Lien court :</strong>
                            <a :href="getShortUrl(createdLink.code)" target="_blank" class="short-url">
                                {{ getShortUrl(createdLink.code) }}
                            </a>
                            <button @click="copyToClipboard(getShortUrl(createdLink.code))" class="btn-copy">
                                Copier
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistiques -->
            <div v-if="urlStore.links.length > 0" class="stats-section">
                <h3>Statistiques</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number">{{ urlStore.links.length }}</div>
                        <div class="stat-label">Liens créés</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">{{ urlStore.totalClicks }}</div>
                        <div class="stat-label">Total des clics</div>
                    </div>
                    <div v-if="urlStore.mostPopularLink" class="stat-card">
                        <div class="stat-number">{{ urlStore.mostPopularLink.clicks }}</div>
                        <div class="stat-label">Clics sur le lien populaire</div>
                    </div>
                </div>
            </div>

            <!-- Liste des liens -->
            <div class="links-section">
                <div class="section-header">
                    <h3>Mes liens courts</h3>
                    <button @click="refreshLinks" :disabled="urlStore.loading" class="btn-refresh">
                        Actualiser
                    </button>
                </div>

                <div v-if="urlStore.loading && urlStore.links.length === 0" class="loading">
                    Chargement des liens...
                </div>

                <div v-else-if="urlStore.links.length === 0" class="empty-state">
                    <p>Aucun lien créé pour le moment</p>
                    <p>Créez votre premier lien court ci-dessus !</p>
                </div>

                <div v-else class="links-list">
                    <div v-for="link in urlStore.links" :key="link.id" class="link-item">
                        <div class="link-info">
                            <div class="link-original">
                                <strong>{{ truncateUrl(link.original_url) }}</strong>
                            </div>
                            <div class="link-short">
                                <a :href="getShortUrl(link.code)" target="_blank" class="short-url">
                                    {{ getShortUrl(link.code) }}
                                </a>
                                <button @click="copyToClipboard(getShortUrl(link.code))" class="btn-copy-small"
                                    title="Copier le lien">
                                    Copier
                                </button>
                            </div>
                            <div class="link-meta">
                                <span class="clicks">{{ link.clicks }} clics</span>
                                <span class="date">Créé le {{ formatDate(link.created_at) }}</span>
                            </div>
                        </div>

                        <div class="link-actions">
                            <button @click="handleDeleteLink(link.id)" :disabled="urlStore.loading" class="btn-delete"
                                title="Supprimer le lien">
                                Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Erreurs -->
            <div v-if="urlStore.error" class="error-message">
                {{ urlStore.error }}
            </div>
        </div>
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
