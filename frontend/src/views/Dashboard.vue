<template>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Modules</h2>
            </div>

            <nav class="sidebar-nav">
                <ul class="modules-list">
                    <li v-for="module in modulesList" :key="module.id" class="module-item"
                        :class="{ 'active': module.active }">
                        <div class="module-content">
                            <span class="module-name">{{ module.name }}</span>
                            <button @click="toggleModule(module.id)" :disabled="moduleStore.loading"
                                :class="['btn-toggle', module.active ? 'active' : 'inactive']">
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
                    <button @click="handleLogout" class="btn-logout">Déconnexion</button>
                </div>
            </header>

            <div class="content-area">
                <!-- Aucun module activé -->
                <div v-if="!hasActiveModules" class="welcome-section">
                    <div class="welcome-card">
                        <h2>Bienvenue sur votre Dashboard</h2>

                    </div>
                </div>

                <!-- Modules activés -->
                <div v-else class="modules-grid">
                    <!-- URL Shortener -->
                    <div v-if="isModuleActive(1)" class="module-widget">
                        <router-link to="/wallet"></router-link>
                        <UrlShortener/>
                    </div>

                    <!-- Wallet -->
                    <div v-if="isModuleActive(2)" class="module-widget">

                        <Wallet/>
                    </div>

                    <!-- Time Tracker -->
                    <div v-if="isModuleActive(4)" class="module-widget">
                        <TimeTracker/>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useModuleStore } from '@/stores/modulesStore'
import Wallet from '@/components/Wallet.vue'
import UrlShortener from '@/components/UrlShortener.vue'
// import TimeTrackerWidget from '@/components/TimeTrackerWidget.vue'

const authStore = useAuthStore()
const moduleStore = useModuleStore()
const router = useRouter()

// Liste des modules à afficher
const modulesList = computed(() => {
    const allowedModules = [
        { id: 1, name: 'URL Shortener' },
        { id: 2, name: 'Wallet' },
        { id: 4, name: 'Time Tracker' }
    ]

    return allowedModules.map(module => {
        const moduleData = moduleStore.modules.find(m => m.id === module.id)
        return {
            ...module,
            active: moduleData ? moduleData.active : false
        }
    })
})

const hasActiveModules = computed(() => {
    return modulesList.value.some(module => module.active)
})

const isModuleActive = (moduleId) => {
    const module = modulesList.value.find(m => m.id === moduleId)
    return module ? module.active : false
}

onMounted(async () => {
    await moduleStore.fetchModules()
})

const toggleModule = async (moduleId) => {
    const module = moduleStore.modules.find(m => m.id === moduleId)

    if (module?.active) {
        await moduleStore.deactivateModule(moduleId)
    } else {
        await moduleStore.activateModule(moduleId)
    }
}

const handleLogout = () => {
    authStore.logout()
    router.push('/login')
}
</script>

<style scoped>
.dashboard-layout {
    display: flex;
    min-height: 100vh;
    background: #f8f9fa;
}

/* Sidebar */
.sidebar {
    width: 280px;
    background: white;
    border-right: 1px solid #e9ecef;
    display: flex;
    flex-direction: column;
}

.sidebar-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e9ecef;
    background: #f8f9fa;
}

.sidebar-header h2 {
    margin: 0;
    color: #333;
    font-size: 1.25rem;
}

.sidebar-nav {
    flex: 1;
    padding: 1rem 0;
}

.modules-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.module-item {
    margin-bottom: 0.5rem;
    padding: 0 1rem;
}

.module-item.active {
    background: #f8f9fa;
}

.module-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #e9ecef;
}

.module-name {
    font-weight: 500;
    color: #333;
}

.btn-toggle {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.btn-toggle.active {
    background: #dc3545;
    color: white;
}

.btn-toggle.inactive {
    background: #28a745;
    color: white;
}

.btn-toggle:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.btn-toggle:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* Contenu principal */
.main-content {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.content-header {
    background: white;
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.content-header h1 {
    margin: 0;
    color: #333;
    font-size: 1.75rem;
}

.user-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-info {
    color: #666;
    font-size: 0.9rem;
}

.btn-logout {
    background: #dc3545;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
}

.btn-logout:hover {
    background: #c82333;
}

.content-area {
    flex: 1;
    padding: 2rem;
    overflow-y: auto;
}

/* Section d'accueil */
.welcome-section {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
}

.welcome-card {
    background: white;
    padding: 3rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    text-align: center;
    max-width: 500px;
}

.welcome-card h2 {
    color: #333;
    margin-bottom: 1rem;
}

.welcome-card p {
    color: #666;
    margin: 0;
    font-size: 1.1rem;
}

/* Grille des modules */
.modules-grid {
    display: grid;
    gap: 2rem;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
}

.module-widget {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

/* Responsive */
@media (max-width: 1024px) {
    .modules-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .dashboard-layout {
        flex-direction: column;
    }

    .sidebar {
        width: 100%;
        height: auto;
    }

    .content-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }

    .module-content {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }

    .btn-toggle {
        width: 100%;
    }
}
</style>
