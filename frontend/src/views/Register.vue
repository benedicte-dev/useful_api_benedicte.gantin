<template>
    <div class="auth-page">
        <div class="auth-container">
            <div class="auth-card">
                <h2>Créer un compte</h2>

                <form @submit.prevent="handleRegister" class="auth-form">
                    <div class="form-group">
                        <label for="name">Nom complet *</label>
                        <input id="name" v-model="form.name" type="text" required placeholder="Votre nom complet"
                            :disabled="authStore.loading">
                        <div v-if="errors.name" class="field-error">
                            {{ errors.name }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Adresse email *</label>
                        <input id="email" v-model="form.email" type="email" required placeholder="votre@email.com"
                            :disabled="authStore.loading">
                        <div v-if="errors.email" class="field-error">
                            {{ errors.email }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe *</label>
                        <input id="password" v-model="form.password" type="password" required
                            placeholder="Votre mot de passe" :minlength="8" :disabled="authStore.loading">
                        <div class="password-hint">
                            Minimum 8 caractères
                        </div>
                        <div v-if="errors.password" class="field-error">
                            {{ errors.password }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirmer le mot de passe *</label>
                        <input id="password_confirmation" v-model="form.password_confirmation" type="password" required
                            placeholder="Confirmez votre mot de passe" :disabled="authStore.loading">
                        <div v-if="errors.password_confirmation" class="field-error">
                            {{ errors.password_confirmation }}
                        </div>
                    </div>

                    <button type="submit" :disabled="authStore.loading || !isFormValid" class="btn-primary">
                        {{ authStore.loading ? 'Création du compte...' : 'Créer mon compte' }}
                    </button>

                    <!-- Message de succès -->
                    <div v-if="successMessage" class="success-message">
                        {{ successMessage }}
                        <p>Redirection vers la page de connexion...</p>
                    </div>

                    <!-- Erreur générale -->
                    <div v-if="authStore.error" class="error-message">
                        {{ authStore.error }}
                    </div>
                </form>

                <p class="auth-link">
                    Déjà un compte ? <router-link to="/login">Se connecter</router-link>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()
const router = useRouter()

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
})

const errors = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
})

const successMessage = ref('')

// Validation en temps réel
watch(form.value, (newForm) => {
    validateForm()
}, { deep: true })

const isFormValid = computed(() => {
    return form.value.name &&
        form.value.email &&
        form.value.password &&
        form.value.password_confirmation &&
        form.value.password.length >= 8 &&
        form.value.password === form.value.password_confirmation &&
        Object.values(errors.value).every(error => !error)
})

const validateForm = () => {
    // Réinitialiser les erreurs
    Object.keys(errors.value).forEach(key => {
        errors.value[key] = ''
    })

    // Validation du nom
    if (form.value.name && form.value.name.length < 2) {
        errors.value.name = 'Le nom doit contenir au moins 2 caractères'
    }

    // Validation de l'email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    if (form.value.email && !emailRegex.test(form.value.email)) {
        errors.value.email = 'Veuillez entrer une adresse email valide'
    }

    // Validation du mot de passe
    if (form.value.password && form.value.password.length < 8) {
        errors.value.password = 'Le mot de passe doit contenir au moins 8 caractères'
    }

    // Validation de la confirmation
    if (form.value.password_confirmation && form.value.password !== form.value.password_confirmation) {
        errors.value.password_confirmation = 'Les mots de passe ne correspondent pas'
    }
}

const handleRegister = async () => {
    // Validation finale avant soumission
    validateForm()

    if (!isFormValid.value) {
        return
    }

    try {
        const response = await authStore.register(form.value)

        // Succès
        successMessage.value = 'Compte créé avec succès ! Vous allez être redirigé vers la page de connexion.'

        // Redirection après 2 secondes
        setTimeout(() => {
            router.push('/login')
        }, 2000)

    } catch (error) {
        // Gestion des erreurs de validation du backend
        if (error.response?.data?.errors) {
            const backendErrors = error.response.data.errors

            Object.keys(backendErrors).forEach(field => {
                if (errors.value[field] !== undefined) {
                    errors.value[field] = backendErrors[field][0]
                }
            })
        }
        console.error('Registration error:', error)
    }
}

// Nettoyer les erreurs quand l'utilisateur commence à taper
watch(() => form.value.name, () => {
    if (errors.value.name) errors.value.name = ''
})

watch(() => form.value.email, () => {
    if (errors.value.email) errors.value.email = ''
})

watch(() => form.value.password, () => {
    if (errors.value.password) errors.value.password = ''
})

watch(() => form.value.password_confirmation, () => {
    if (errors.value.password_confirmation) errors.value.password_confirmation = ''
})
</script>

<style scoped>
.auth-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 1rem;
}

.auth-container {
    width: 100%;
    max-width: 450px;
}

.auth-card {
    background: white;
    padding: 2.5rem;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.auth-card h2 {
    text-align: center;
    margin-bottom: 2rem;
    color: #333;
    font-size: 1.8rem;
}

.auth-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #333;
    font-size: 0.95rem;
}

.form-group input {
    padding: 0.75rem;
    border: 2px solid #e1e5e9;
    border-radius: 6px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-group input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.form-group input:invalid:not(:focus):not(:placeholder-shown) {
    border-color: #dc3545;
}

.password-hint {
    font-size: 0.8rem;
    color: #666;
    margin-top: 0.25rem;
}

.field-error {
    color: #dc3545;
    font-size: 0.85rem;
    margin-top: 0.25rem;
    padding: 0.25rem 0.5rem;
    background: #f8d7da;
    border-radius: 4px;
    border: 1px solid #f5c6cb;
}

.btn-primary {
    background: #28a745;
    color: white;
    border: none;
    padding: 0.75rem;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 0.5rem;
}

.btn-primary:hover:not(:disabled) {
    background: #218838;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
}

.btn-primary:disabled {
    background: #6c757d;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.success-message {
    background: #d4edda;
    color: #155724;
    padding: 1rem;
    border-radius: 6px;
    border: 1px solid #c3e6cb;
    text-align: center;
    margin-top: 1rem;
}

.success-message p {
    margin-top: 0.5rem;
    font-size: 0.9rem;
    opacity: 0.8;
}

.error-message {
    background: #f8d7da;
    color: #721c24;
    padding: 1rem;
    border-radius: 6px;
    border: 1px solid #f5c6cb;
    text-align: center;
    margin-top: 1rem;
}

.auth-link {
    text-align: center;
    margin-top: 2rem;
    color: #666;
    font-size: 0.95rem;
}

.auth-link a {
    color: #007bff;
    text-decoration: none;
    font-weight: 600;
}

.auth-link a:hover {
    text-decoration: underline;
}

/* Responsive */
@media (max-width: 480px) {
    .auth-card {
        padding: 2rem 1.5rem;
    }

    .auth-card h2 {
        font-size: 1.5rem;
    }
}
</style>
