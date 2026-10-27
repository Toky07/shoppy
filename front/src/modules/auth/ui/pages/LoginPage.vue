<script setup lang="ts">
import { inject } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { usePendingAction } from '@/shared/async/usePendingAction'
import { safeRedirectPath } from '@/shared/routing/safeRedirectPath'
import { authRepositoryKey } from '../../application/authRepositoryKey'
import { authSessionKey } from '../../application/authSessionKey'
import { login } from '../../application/login'
import AuthCredentialsForm from '../components/AuthCredentialsForm.vue'
import { useI18n } from '@/shared/i18n/useI18n'
import { authErrorMessage } from '../lib/authErrorMessage'

const repository = inject(authRepositoryKey)
const session = inject(authSessionKey)

if (!repository || !session) {
  throw new Error('Auth dependencies are not provided.')
}

const authRepository = repository
const authSession = session
const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const { pending, errorMessage, run } = usePendingAction((error) => authErrorMessage(error, t))

function onSubmit(credentials: { email: string; password: string }) {
  return run(async () => {
    await login(authRepository, authSession, credentials)
    await router.push(safeRedirectPath(route.query.redirect))
  })
}
</script>

<template>
  <AuthCredentialsForm
    :title="t('auth.loginTitle')"
    :subtitle="t('auth.loginSubtitle')"
    :submit-label="t('auth.loginSubmit')"
    :pending="pending"
    :error-message="errorMessage"
    @submit="onSubmit"
  >
    <template #password-aside>
      <RouterLink to="/forgot-password" class="text-xs font-semibold text-strong underline underline-offset-2">
        {{ t('auth.forgot') }}
      </RouterLink>
    </template>
    {{ t('auth.noAccount') }}
    <RouterLink to="/register" class="ml-1 font-semibold text-strong underline underline-offset-2">
      {{ t('auth.createAccount') }}
    </RouterLink>
  </AuthCredentialsForm>
</template>
