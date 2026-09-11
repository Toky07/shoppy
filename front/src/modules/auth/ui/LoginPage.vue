<script setup lang="ts">
import { inject, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { toApiError } from '@/shared/http/toApiError'
import { safeRedirectPath } from '@/shared/routing/safeRedirectPath'
import { authRepositoryKey } from '../application/authRepositoryKey'
import { authSessionKey } from '../application/authSessionKey'
import { login } from '../application/login'
import AuthCredentialsForm from './AuthCredentialsForm.vue'
import { authErrorMessage } from './authErrorMessage'

const repository = inject(authRepositoryKey)
const session = inject(authSessionKey)

if (!repository || !session) {
  throw new Error('Auth dependencies are not provided.')
}

const authRepository = repository
const authSession = session

const router = useRouter()
const route = useRoute()
const pending = ref(false)
const errorMessage = ref<string>()

async function onSubmit(credentials: { email: string; password: string }) {
  pending.value = true
  errorMessage.value = undefined

  try {
    await login(authRepository, authSession, credentials)
    await router.push(safeRedirectPath(route.query.redirect))
  } catch (caught) {
    errorMessage.value = authErrorMessage(toApiError(caught))
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <AuthCredentialsForm
    title="Connexion"
    submit-label="Se connecter"
    :pending="pending"
    :error-message="errorMessage"
    @submit="onSubmit"
  >
    Pas encore de compte ?
    <RouterLink to="/register" class="text-indigo-600 hover:text-indigo-700 font-bold ml-1 transition-colors">Créer un compte</RouterLink>
  </AuthCredentialsForm>
</template>
