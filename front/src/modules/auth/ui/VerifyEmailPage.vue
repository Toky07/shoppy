<script setup lang="ts">
import { inject, onMounted, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { usePendingAction } from '@/shared/async/usePendingAction'
import StatusNotice from '@/shared/ui/StatusNotice.vue'
import { markEmailVerified } from '../application/accountActions'
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
    await authRepository.verifyEmail(token)
    markEmailVerified(authSession)
    confirmed.value = true
  })
})
</script>

<template>
  <AuthPanel title="Confirmation d'email" subtitle="Nous vérifions le lien reçu dans votre boîte mail.">
    <StatusNotice v-if="!token" tone="danger">Ce lien est incomplet.</StatusNotice>
    <StatusNotice v-else-if="pending" tone="accent">Confirmation en cours…</StatusNotice>
    <StatusNotice v-else-if="confirmed" tone="positive">Votre adresse email est confirmée.</StatusNotice>
    <StatusNotice v-else-if="errorMessage" tone="danger">{{ errorMessage }}</StatusNotice>
    <template #footer>
      <RouterLink to="/account" class="font-semibold text-strong underline underline-offset-2">Aller au compte</RouterLink>
    </template>
  </AuthPanel>
</template>
