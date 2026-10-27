<script setup lang="ts">
import { inject, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { usePendingAction } from '@/shared/async/usePendingAction'
import { authRepositoryKey } from '../../application/authRepositoryKey'
import { register } from '../../application/register'
import AuthCredentialsForm from '../components/AuthCredentialsForm.vue'
import { useI18n } from '@/shared/i18n/useI18n'
import { authErrorMessage } from '../lib/authErrorMessage'

const repository = inject(authRepositoryKey)

if (!repository) {
  throw new Error('Auth dependencies are not provided.')
}

const authRepository = repository
const { t } = useI18n()
const accepted = ref(false)
const { pending, errorMessage, run } = usePendingAction((error) => authErrorMessage(error, t))

function onSubmit(credentials: { email: string; password: string }) {
  return run(async () => {
    await register(authRepository, credentials)
    accepted.value = true
  })
}
</script>

<template>
  <p v-if="accepted" class="mx-auto max-w-md text-sm leading-6 text-muted">
    {{ t('auth.registerSent') }}
    <RouterLink to="/login" class="font-semibold text-strong underline underline-offset-2">{{
      t('auth.registerSentLogin')
    }}</RouterLink>.
  </p>
  <AuthCredentialsForm
    v-else
    :title="t('auth.registerTitle')"
    :subtitle="t('auth.registerSubtitle')"
    :submit-label="t('auth.createAccount')"
    :pending="pending"
    :error-message="errorMessage"
    password-autocomplete="new-password"
    :password-min-length="12"
    :password-max-length="4096"
    @submit="onSubmit"
  >
    {{ t('auth.alreadyAccount') }}
    <RouterLink to="/login" class="ml-1 font-semibold text-strong underline underline-offset-2">
      {{ t('auth.loginSubmit') }}
    </RouterLink>
  </AuthCredentialsForm>
</template>
