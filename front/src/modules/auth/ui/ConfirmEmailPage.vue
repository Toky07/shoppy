<script setup lang="ts">
import { inject, onMounted, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { usePendingAction } from '@/shared/async/usePendingAction'
import StatusNotice from '@/shared/ui/StatusNotice.vue'
import { confirmEmailChange } from '../application/accountActions'
import { authRepositoryKey } from '../application/authRepositoryKey'
import { authSessionKey } from '../application/authSessionKey'
import { authErrorMessage } from './authErrorMessage'
import AuthPanel from './AuthPanel.vue'

const repository = inject(authRepositoryKey)
const session = inject(authSessionKey)
if (!repository || !session) {
  throw new Error('Auth dependencies are not provided.')
}

const authRepository = repository
const authSession = session
const route = useRoute()
const confirmed = ref(false)
const { pending, errorMessage, run } = usePendingAction((error) => authErrorMessage(error))
const token = typeof route.query.token === 'string' ? route.query.token : ''

onMounted(() => {
  if (!token) {
    return
  }

  return run(async () => {
    await confirmEmailChange(authRepository, authSession, token)
    confirmed.value = true
  })
})
</script>

<template>
  <AuthPanel
    title="Nouvelle adresse"
    subtitle="La confirmation remplace votre email et ferme les sessions ouvertes."
  >
    <StatusNotice v-if="!token" tone="danger">Ce lien est incomplet.</StatusNotice>
    <StatusNotice v-else-if="pending" tone="accent">Confirmation en cours…</StatusNotice>
    <StatusNotice v-else-if="confirmed" tone="positive">
      Adresse confirmée. Reconnectez-vous avec cette nouvelle adresse.
    </StatusNotice>
    <StatusNotice v-else-if="errorMessage" tone="danger">{{ errorMessage }}</StatusNotice>
    <template #footer>
      <RouterLink to="/login" class="font-semibold text-strong underline underline-offset-2">Se connecter</RouterLink>
    </template>
  </AuthPanel>
</template>
