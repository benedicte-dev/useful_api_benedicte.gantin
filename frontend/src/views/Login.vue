<template>
    <div class="auth-container">
        <div class="auth-card">
            <h2>Connexion</h2>

            <form @submit.prevent="handleLogin" class="auth-form">
                <div class="form-group">
                    <label>Email</label>
                    <input v-model="form.email" type="email" required placeholder="votre@email.com"
                        :disabled="authStore.loading">
                </div>

                <div class="form-group">
                    <label>Mot de passe</label>
                    <input v-model="form.password" type="password" required placeholder="Votre mot de passe"
                        :disabled="authStore.loading">
                </div>

                <button type="submit" :disabled="authStore.loading" class="btn-primary">
                    {{ authStore.loading ? 'Connexion...' : 'Se connecter' }}
                </button>

                <div v-if="authStore.error" class="error-message">
                    {{ authStore.error }}
                </div>
            </form>

            <p class="auth-link">
                Pas de compte ? <router-link to="/register">S'inscrire</router-link>
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()
const router = useRouter()

const form = ref({
    email: '',
    password: ''
})

const handleLogin = async () => {
    try {
        await authStore.login(form.value)
        router.push('/dashboard')
    } catch (error) {
        console.error('Login error:', error)
    }
}
</script>

<style scoped>
.auth-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f5f5f5;
}

.auth-card {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
}

.auth-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.form-group input {
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.btn-primary {
    background: #007bff;
    color: white;
    border: none;
    padding: 0.75rem;
    border-radius: 4px;
    font-size: 1rem;
    cursor: pointer;
}

.btn-primary:disabled {
    background: #6c757d;
    cursor: not-allowed;
}

.error-message {
    color: #dc3545;
    text-align: center;
    margin-top: 1rem;
}

.auth-link {
    text-align: center;
    margin-top: 1rem;
}
</style>
