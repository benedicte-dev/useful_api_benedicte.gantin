<template>
    <div id="app">
        <!-- Header global (optionnel) -->
        <header v-if="isAuthenticated" class="app-header">
            <nav class="app-nav">
                <router-link to="/dashboard" class="nav-link">Tableau de Bord</router-link>
                
            </nav>
        </header>

        <!-- Contenu principal avec router -->
        <main class="app-main">
            <router-view />
        </main>

        <!-- États de chargement global -->
        <div v-if="globalLoading" class="global-loading">
            Chargement...
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()
const router = useRouter()

const isAuthenticated = computed(() => authStore.isAuthenticated)
const globalLoading = computed(() => authStore.loading)

const handleLogout = () => {
    authStore.logout()
    router.push('/login')
}
</script>

<style>
/* Styles globaux */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f5f5f5;
    color: #333;
}

#app {
    min-height: 100vh;
}

.app-header {
    background: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 1rem 2rem;
}

.app-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
}

.nav-link {
    text-decoration: none;
    color: #007bff;
    font-weight: 600;
}

.logout-btn {
    background: #dc3545;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
}

.app-main {
    min-height: calc(100vh - 80px);
}

.global-loading {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}
</style>
