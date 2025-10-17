<template>
    <div class="dashboard">

        <!-- Gestion des Modules -->
        <section class="modules-section">
            <h2>Gestion des Modules</h2>
            <div class="modules-grid">
                <div v-for="module in moduleStore.modules" :key="module.id" class="module-card">
                    <h3>{{ module.name }}</h3>
                    <p>{{ module.description }}</p>
                    <button @click="toggleModule(module.id)" :disabled="moduleStore.loading"
                        :class="['btn-toggle', module.active ? 'active' : 'inactive']">
                        {{ module.active ? 'Desactiver' : 'Activer' }}
                    </button>
                </div>
            </div>
        </section>

        <!-- Modules Actifs -->
        <section v-if="activeModules.length > 0" class="active-modules">
            <h2>Modules Actifs</h2>

            <!-- Wallet -->
            <div v-if="isModuleActive(2)" class="module-widget">
                <WalletWidget />
            </div>

            <!-- URL Shortener -->
            <div v-if="isModuleActive(1)" class="module-widget">
                <UrlShortenerWidget />
            </div>

            <!-- Time Tracker -->
            <div v-if="isModuleActive(4)" class="module-widget">
                <TimeTrackerWidget />
            </div>
        </section>
    </div>
</template>

<script setup>
import { onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useModuleStore } from '@/stores/modulesStore'
// import WalletWidget from '@/components/Wallet.vue'
// import UrlShortenerWidget from '@/components/UrlShortener.vue'
// import TimeTrackerWidget from '@/components/TimeTracker.vue'

const authStore = useAuthStore()
const moduleStore = useModuleStore()
const router = useRouter()

const activeModules = computed(() => {
    return moduleStore.modules.filter(module => module.active)
})

const isModuleActive = (moduleId) => {
    return activeModules.value.some(module => module.id === moduleId)
}

onMounted(async () => {
    await moduleStore.fetchModules()
})

const toggleModule = async (moduleId) => {
    const module = moduleStore.modules.find(m => m.id === moduleId)

    if (module.active) {
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
.dashboard {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.btn-logout {
    background: #dc3545;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
}

.modules-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.module-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 1.5rem;
    text-align: center;
}

.btn-toggle {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 600;
}

.btn-toggle.active {
    background: #dc3545;
    color: white;
}

.btn-toggle.inactive {
    background: #28a745;
    color: white;
}

.module-widget {
    margin-bottom: 2rem;
}
</style>
