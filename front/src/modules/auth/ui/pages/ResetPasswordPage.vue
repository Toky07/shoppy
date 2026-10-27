<script setup lang="ts">
import { inject, onMounted, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { usePendingAction } from '@/shared/async/usePendingAction'
import { useI18n } from '@/shared/i18n/useI18n'
import AppIcon from '@/shared/ui/AppIcon.vue'
import StatusNotice from '@/shared/ui/StatusNotice.vue'
import { authRepositoryKey } from '../../application/authRepositoryKey'
import { accountLinkToken, concealAccountLinkToken } from '../lib/accountLinkToken'
import { authErrorMessage } from '../lib/authErrorMessage'
import AuthPanel from '../components/AuthPanel.vue'

const repository = inject(authRepositoryKey)
if (!repository) {
  throw new Error('Auth dependencies are not provided.')
}

const { t } = useI18n()
const authRepository = repository
const route = useRoute()
const router = useRouter()
const password = ref('')
const saved = ref(false)
const { pending, errorMessage, run } = usePendingAction((error) => authErrorMessage(error, t))
const token = accountLinkToken(route)

onMounted(() => {
  return concealAccountLinkToken(router, route)
})

function onSubmit() {
  return run(async () => {
    await authRepository.resetPassword(token, password.value)
    saved.value = true
  })
}
</script>

<template>
  <AuthPanel :title="t('auth.resetTitle')" :subtitle="t('auth.resetSubtitle')">
    <StatusNotice v-if="!token" tone="danger">{{ t('auth.incompleteLink') }}</StatusNotice>
    <StatusNotice v-else-if="saved" tone="positive">
      {{ t('auth.resetSaved') }}
    </StatusNotice>
    <form v-else class="space-y-5" @submit.prevent="onSubmit">
      <StatusNotice v-if="errorMessage" tone="danger">{{ errorMessage }}</StatusNotice>
      <div>
        <label class="field-label" for="new-password">{{ t('account.newPassword') }}</label>
        <input
          id="new-password"
          v-model="password"
          type="password"
          autocomplete="new-password"
          required
          minlength="12"
          maxlength="4096"
          class="field"
        />
      </div>
      <button type="submit" class="btn-primary btn-lg w-full" :disabled="pending">
        <span v-if="pending" class="animate-orbit"><AppIcon name="loader" :size="17" /></span>
        {{ t('auth.save') }}
      </button>
    </form>
    <template #footer>
      <RouterLink to="/login" class="font-semibold text-strong underline underline-offset-2">{{ t('auth.loginSubmit') }}</RouterLink>
    </template>
  </AuthPanel>
</template>
