<script setup lang="ts">
import { inject } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { usePendingAction } from '@/shared/async/usePendingAction'
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
const { pending, errorMessage, run } = usePendingAction((error) => authErrorMessage(error))

function onSubmit(credentials: { email: string; password: string }) {
  return run(async () => {
    await login(authRepository, authSession, credentials)
    await router.push(safeRedirectPath(route.query.redirect))
  })
}
</script>

<template>
  <AuthCredentialsForm
    title="Connexion"
    subtitle="Content de vous revoir. Reprenez là où vous en étiez."
    submit-label="Se connecter"
    :pending="pending"
    :error-message="errorMessage"
    @submit="onSubmit"
  >
    Pas encore de compte ?
    <RouterLink to="/register" class="ml-1 font-semibold text-strong underline underline-offset-2">
      Créer un compte
    </RouterLink>
  </AuthCredentialsForm>
</template>
