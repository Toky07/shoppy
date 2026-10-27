<script setup lang="ts">
import { inject, onMounted, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { usePendingAction } from '@/shared/async/usePendingAction'
import { useI18n } from '@/shared/i18n/useI18n'
import StatusNotice from '@/shared/ui/StatusNotice.vue'
import { markEmailVerified } from '../../application/accountActions'
import { authRepositoryKey } from '../../application/authRepositoryKey'
import { authSessionKey } from '../../application/authSessionKey'
import { accountLinkToken, concealAccountLinkToken } from '../lib/accountLinkToken'
import { authErrorMessage } from '../lib/authErrorMessage'
import AuthPanel from '../components/AuthPanel.vue'

const repository = inject(authRepositoryKey)
const session = inject(authSessionKey)
if (!repository || !session) {
  throw new Error('Auth dependencies are not provided.')
}

const { t } = useI18n()
const authRepository = repository
const authSession = session
const route = useRoute()
const router = useRouter()
const confirmed = ref(false)
const { pending, errorMessage, run } = usePendingAction((error) => authErrorMessage(error, t))
const token = accountLinkToken(route)

onMounted(async () => {
  await concealAccountLinkToken(router, route)

  if (!token) {
    return
  }

  await run(async () => {
    await authRepository.verifyEmail(token)
    markEmailVerified(authSession)
    confirmed.value = true
  })
})
</script>

<template>
  <AuthPanel :title="t('auth.verifyTitle')" :subtitle="t('auth.verifySubtitle')">
    <StatusNotice v-if="!token" tone="danger">{{ t('auth.incompleteLink') }}</StatusNotice>
    <StatusNotice v-else-if="pending" tone="accent">{{ t('auth.verifying') }}</StatusNotice>
    <StatusNotice v-else-if="confirmed" tone="positive">{{ t('auth.verified') }}</StatusNotice>
    <StatusNotice v-else-if="errorMessage" tone="danger">{{ errorMessage }}</StatusNotice>
    <template #footer>
      <RouterLink to="/account" class="font-semibold text-strong underline underline-offset-2">{{ t('auth.goToAccount') }}</RouterLink>
    </template>
  </AuthPanel>
</template>
