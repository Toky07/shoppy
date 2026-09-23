<script setup lang="ts">
import { inject, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
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
const route = useRoute()
const password = ref('')
const saved = ref(false)
const { pending, errorMessage, run } = usePendingAction((error) => authErrorMessage(error))
const token = typeof route.query.token === 'string' ? route.query.token : ''

function onSubmit() {
  return run(async () => {
    await authRepository.resetPassword(token, password.value)
    saved.value = true
  })
}
</script>

<template>
  <AuthPanel title="Nouveau mot de passe" subtitle="Choisissez un mot de passe d'au moins 12 caractères.">
    <StatusNotice v-if="!token" tone="danger">Ce lien est incomplet.</StatusNotice>
    <StatusNotice v-else-if="saved" tone="positive">
      Mot de passe mis à jour. Vous pouvez vous reconnecter.
    </StatusNotice>
    <form v-else class="space-y-5" @submit.prevent="onSubmit">
      <StatusNotice v-if="errorMessage" tone="danger">{{ errorMessage }}</StatusNotice>
      <div>
        <label class="field-label" for="new-password">Nouveau mot de passe</label>
        <input
          id="new-password"
          v-model="password"
          type="password"
          autocomplete="new-password"
          required
          minlength="12"
          class="field"
        />
      </div>
      <button type="submit" class="btn-primary btn-lg w-full" :disabled="pending">
        <span v-if="pending" class="animate-orbit"><AppIcon name="loader" :size="17" /></span>
        Enregistrer
      </button>
    </form>
    <template #footer>
      <RouterLink to="/login" class="font-semibold text-strong underline underline-offset-2">Se connecter</RouterLink>
    </template>
  </AuthPanel>
</template>
