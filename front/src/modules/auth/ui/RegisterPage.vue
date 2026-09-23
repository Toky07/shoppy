<script setup lang="ts">
import { inject, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { usePendingAction } from '@/shared/async/usePendingAction'
import { authRepositoryKey } from '../application/authRepositoryKey'
import { register } from '../application/register'
import AuthCredentialsForm from './AuthCredentialsForm.vue'
import { authErrorMessage } from './authErrorMessage'

const repository = inject(authRepositoryKey)

if (!repository) {
  throw new Error('Auth dependencies are not provided.')
}

const authRepository = repository
const accepted = ref(false)
const { pending, errorMessage, run } = usePendingAction((error) => authErrorMessage(error))

function onSubmit(credentials: { email: string; password: string }) {
  return run(async () => {
    await register(authRepository, credentials)
    accepted.value = true
  })
}
</script>

<template>
  <p v-if="accepted" class="mx-auto max-w-md text-sm leading-6 text-muted">
    Si cette adresse peut recevoir un message, un email vient d'être envoyé. Vous pouvez ensuite
    <RouterLink to="/login" class="font-semibold text-strong underline underline-offset-2">vous connecter</RouterLink>.
  </p>
  <AuthCredentialsForm
    v-else
    title="Inscription"
    subtitle="Deux champs, trente secondes, et c'est fait."
    submit-label="Créer un compte"
    :pending="pending"
    :error-message="errorMessage"
    password-autocomplete="new-password"
    :password-min-length="12"
    @submit="onSubmit"
  >
    Déjà un compte ?
    <RouterLink to="/login" class="ml-1 font-semibold text-strong underline underline-offset-2">
      Se connecter
    </RouterLink>
  </AuthCredentialsForm>
</template>
