<script setup lang="ts">
import { computed, inject, ref } from 'vue'
import { useRouter } from 'vue-router'
import { usePendingAction } from '@/shared/async/usePendingAction'
import AppIcon from '@/shared/ui/AppIcon.vue'
import StatusNotice from '@/shared/ui/StatusNotice.vue'
import { changePassword, deleteAccount, logoutAll } from '../../application/accountActions'
import { authRepositoryKey } from '../../application/authRepositoryKey'
import { authSessionKey } from '../../application/authSessionKey'
import { useI18n } from '@/shared/i18n/useI18n'
import { authErrorMessage } from '../lib/authErrorMessage'

const repository = inject(authRepositoryKey)
const session = inject(authSessionKey)
if (!repository || !session) {
  throw new Error('Auth dependencies are not provided.')
}

const { t } = useI18n()
const authRepository = repository
const authSession = session
const router = useRouter()
const user = computed(() => authSession.session.value?.user ?? null)

const currentPassword = ref('')
const newPassword = ref('')
const nextEmail = ref('')
const emailPassword = ref('')
const deletePassword = ref('')
const emailNotice = ref('')
const verificationNotice = ref('')

const passwordAction = usePendingAction((error) => authErrorMessage(error, t))
const emailAction = usePendingAction((error) => authErrorMessage(error, t))
const verificationAction = usePendingAction((error) => authErrorMessage(error, t))
const sessionAction = usePendingAction((error) => authErrorMessage(error, t))
const deleteAction = usePendingAction((error) => authErrorMessage(error, t))

function onChangePassword() {
  return passwordAction.run(async () => {
    await changePassword(authRepository, authSession, currentPassword.value, newPassword.value)
    await router.push('/login')
  })
}

function onChangeEmail() {
  return emailAction.run(async () => {
    await authRepository.requestEmailChange(nextEmail.value.trim(), emailPassword.value)
    emailNotice.value = t('account.emailChangeSent', { email: nextEmail.value.trim() })
    nextEmail.value = ''
    emailPassword.value = ''
  })
}

function onResendVerification() {
  return verificationAction.run(async () => {
    await authRepository.requestEmailVerification()
    verificationNotice.value = t('account.verificationSent')
  })
}

function onLogoutAll() {
  return sessionAction.run(async () => {
    await logoutAll(authRepository, authSession)
    await router.push('/')
  })
}

function onDelete() {
  return deleteAction.run(async () => {
    await deleteAccount(authRepository, authSession, deletePassword.value)
    await router.push('/')
  })
}
</script>

<template>
  <section v-if="user" class="panel p-6 sm:p-7">
    <h2 class="font-display text-lg font-bold text-strong">{{ t('account.security') }}</h2>
    <p class="mt-1 text-sm text-muted">{{ t('account.securityHint') }}</p>

    <div v-if="!user.emailVerified" class="mt-5">
      <StatusNotice tone="accent">{{ t('account.emailNotConfirmed') }}</StatusNotice>
      <button
        type="button"
        class="btn-outline btn-sm mt-3"
        :disabled="verificationAction.pending.value"
        @click="onResendVerification"
      >
        {{ t('account.resendEmail') }}
      </button>
      <p v-if="verificationNotice" class="mt-3 text-sm text-muted">{{ verificationNotice }}</p>
      <StatusNotice v-if="verificationAction.errorMessage.value" class="mt-3" tone="danger">
        {{ verificationAction.errorMessage.value }}
      </StatusNotice>
    </div>

    <form class="mt-6 grid gap-4 border-t border-line pt-6" @submit.prevent="onChangePassword">
      <h3 class="text-sm font-semibold text-strong">{{ t('account.changePassword') }}</h3>
      <StatusNotice v-if="passwordAction.errorMessage.value" tone="danger">
        {{ passwordAction.errorMessage.value }}
      </StatusNotice>
      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <label class="field-label" for="current-password">{{ t('account.currentPassword') }}</label>
          <input
            id="current-password"
            v-model="currentPassword"
            type="password"
            autocomplete="current-password"
            required
            class="field"
          />
        </div>
        <div>
          <label class="field-label" for="account-new-password">{{ t('account.newPassword') }}</label>
          <input
            id="account-new-password"
            v-model="newPassword"
            type="password"
            autocomplete="new-password"
            required
            minlength="12"
            maxlength="4096"
            class="field"
          />
        </div>
      </div>
      <button type="submit" class="btn-primary w-fit" :disabled="passwordAction.pending.value">
        {{ t('account.updatePassword') }}
      </button>
    </form>

    <form class="mt-6 grid gap-4 border-t border-line pt-6" @submit.prevent="onChangeEmail">
      <h3 class="text-sm font-semibold text-strong">{{ t('account.changeEmail') }}</h3>
      <p class="text-xs text-muted">{{ t('account.emailChangeHint') }}</p>
      <StatusNotice v-if="emailNotice" tone="positive">{{ emailNotice }}</StatusNotice>
      <StatusNotice v-if="emailAction.errorMessage.value" tone="danger">{{ emailAction.errorMessage.value }}</StatusNotice>
      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <label class="field-label" for="next-email">{{ t('account.newEmail') }}</label>
          <input id="next-email" v-model="nextEmail" type="email" autocomplete="email" required class="field" />
        </div>
        <div>
          <label class="field-label" for="email-password">{{ t('account.confirmWithPassword') }}</label>
          <input
            id="email-password"
            v-model="emailPassword"
            type="password"
            autocomplete="current-password"
            required
            class="field"
          />
        </div>
      </div>
      <button type="submit" class="btn-outline w-fit" :disabled="emailAction.pending.value">{{ t('account.sendLink') }}</button>
    </form>

    <div class="mt-6 border-t border-line pt-6">
      <h3 class="text-sm font-semibold text-strong">{{ t('account.sessions') }}</h3>
      <p class="mt-1 text-xs text-muted">{{ t('account.sessionsHint') }}</p>
      <StatusNotice v-if="sessionAction.errorMessage.value" class="mt-3" tone="danger">
        {{ sessionAction.errorMessage.value }}
      </StatusNotice>
      <button
        type="button"
        class="btn-outline mt-4"
        :disabled="sessionAction.pending.value"
        @click="onLogoutAll"
      >
        <AppIcon name="logout" :size="15" />
        {{ t('account.logoutAll') }}
      </button>
    </div>

    <form class="mt-6 grid gap-4 border-t border-line pt-6" @submit.prevent="onDelete">
      <h3 class="text-sm font-semibold text-strong">{{ t('account.deleteAccount') }}</h3>
      <p class="text-xs text-muted">
        {{ t('account.deleteHint') }}
      </p>
      <StatusNotice v-if="deleteAction.errorMessage.value" tone="danger">
        {{ deleteAction.errorMessage.value }}
      </StatusNotice>
      <div class="max-w-sm">
        <label class="field-label" for="delete-password">{{ t('account.accountPassword') }}</label>
        <input
          id="delete-password"
          v-model="deletePassword"
          type="password"
          autocomplete="current-password"
          required
          class="field"
        />
      </div>
      <button type="submit" class="btn-danger w-fit" :disabled="deleteAction.pending.value">
        {{ t('account.deleteMyAccount') }}
      </button>
    </form>
  </section>
</template>
