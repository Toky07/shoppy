<script setup lang="ts">
import { inject } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { usePendingAction } from '@/shared/async/usePendingAction'
import { authRepositoryKey } from '../application/authRepositoryKey'
import { authSessionKey } from '../application/authSessionKey'
import { register } from '../application/register'
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
const { pending, errorMessage, run } = usePendingAction((error) => authErrorMessage(error))

function onSubmit(credentials: { email: string; password: string }) {
  return run(async () => {
    await register(authRepository, authSession, credentials)
    await router.push('/')
  })
}
</script>

<template>
  <AuthCredentialsForm
    title="Inscription"
    subtitle="Deux champs, trente secondes, et c'est fait."
    submit-label="Créer un compte"
    :pending="pending"
    :error-message="errorMessage"
    password-autocomplete="new-password"
    @submit="onSubmit"
  >
    Déjà un compte ?
    <RouterLink to="/login" class="ml-1 font-semibold text-strong underline underline-offset-2">
      Se connecter
    </RouterLink>
  </AuthCredentialsForm>
</template>
