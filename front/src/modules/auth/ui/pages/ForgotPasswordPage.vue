<script setup lang="ts">
import { inject, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { usePendingAction } from '@/shared/async/usePendingAction'
import { useI18n } from '@/shared/i18n/useI18n'
import AppIcon from '@/shared/ui/AppIcon.vue'
import StatusNotice from '@/shared/ui/StatusNotice.vue'
import { authRepositoryKey } from '../../application/authRepositoryKey'
import { authErrorMessage } from '../lib/authErrorMessage'
import AuthPanel from '../components/AuthPanel.vue'

const repository = inject(authRepositoryKey)
if (!repository) {
  throw new Error('Auth dependencies are not provided.')
}

const { t } = useI18n()
const authRepository = repository
const email = ref('')
const sent = ref(false)
const { pending, errorMessage, run } = usePendingAction((error) => authErrorMessage(error, t))

function onSubmit() {
  return run(async () => {
    await authRepository.requestPasswordReset(email.value.trim())
    sent.value = true
  })
}
</script>

<template>
  <AuthPanel :title="t('auth.forgotTitle')" :subtitle="t('auth.forgotSubtitle')">
    <StatusNotice v-if="sent" tone="positive">
      {{ t('auth.forgotSent') }}
    </StatusNotice>
    <form v-else class="space-y-5" @submit.prevent="onSubmit">
      <StatusNotice v-if="errorMessage" tone="danger">{{ errorMessage }}</StatusNotice>
      <div>
        <label class="field-label" for="reset-email">{{ t('common.email') }}</label>
        <input id="reset-email" v-model="email" type="email" autocomplete="email" required class="field" />
      </div>
      <button type="submit" class="btn-primary btn-lg w-full" :disabled="pending">
        <span v-if="pending" class="animate-orbit"><AppIcon name="loader" :size="17" /></span>
        {{ t('auth.sendLink') }}
      </button>
    </form>
    <template #footer>
      <RouterLink to="/login" class="font-semibold text-strong underline underline-offset-2">{{ t('auth.backToLogin') }}</RouterLink>
    </template>
  </AuthPanel>
</template>
