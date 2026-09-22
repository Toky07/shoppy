<script setup lang="ts">
import { inject, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { usePendingAction } from '@/shared/async/usePendingAction'
import AppIcon from '@/shared/ui/AppIcon.vue'
import StatusNotice from '@/shared/ui/StatusNotice.vue'
import { authRepositoryKey } from '../application/authRepositoryKey'
import { authErrorMessage } from './authErrorMessage'
import AuthPanel from './AuthPanel.vue'

const repository = inject(authRepositoryKey)
if (!repository) {
  throw new Error('Auth dependencies are not provided.')
}

const authRepository = repository
const email = ref('')
const sent = ref(false)
const { pending, errorMessage, run } = usePendingAction((error) => authErrorMessage(error))

function onSubmit() {
  return run(async () => {
    await authRepository.requestPasswordReset(email.value.trim())
    sent.value = true
  })
}
</script>

<template>
  <AuthPanel
    title="Mot de passe oublié"
    subtitle="Indiquez votre email. S'il correspond à un compte, vous recevrez un lien valable une heure."
  >
    <StatusNotice v-if="sent" tone="positive">
      Si un compte existe pour cette adresse, un email vient d'être envoyé.
    </StatusNotice>
    <form v-else class="space-y-5" @submit.prevent="onSubmit">
      <StatusNotice v-if="errorMessage" tone="danger">{{ errorMessage }}</StatusNotice>
      <div>
        <label class="field-label" for="reset-email">Adresse email</label>
        <input id="reset-email" v-model="email" type="email" autocomplete="email" required class="field" />
      </div>
      <button type="submit" class="btn-primary btn-lg w-full" :disabled="pending">
        <span v-if="pending" class="animate-orbit"><AppIcon name="loader" :size="17" /></span>
        Envoyer le lien
      </button>
    </form>
    <template #footer>
      <RouterLink to="/login" class="font-semibold text-strong underline underline-offset-2">Retour à la connexion</RouterLink>
    </template>
  </AuthPanel>
</template>
