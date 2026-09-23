<script setup lang="ts">
import { computed, inject, ref } from 'vue'
import { useRouter } from 'vue-router'
import { usePendingAction } from '@/shared/async/usePendingAction'
import AppIcon from '@/shared/ui/AppIcon.vue'
import StatusNotice from '@/shared/ui/StatusNotice.vue'
import { changePassword, deleteAccount, logoutAll } from '../application/accountActions'
import { authRepositoryKey } from '../application/authRepositoryKey'
import { authSessionKey } from '../application/authSessionKey'
import { authErrorMessage } from './authErrorMessage'

const repository = inject(authRepositoryKey)
const session = inject(authSessionKey)
if (!repository || !session) {
  throw new Error('Auth dependencies are not provided.')
}

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

const passwordAction = usePendingAction((error) => authErrorMessage(error))
const emailAction = usePendingAction((error) => authErrorMessage(error))
const verificationAction = usePendingAction((error) => authErrorMessage(error))
const sessionAction = usePendingAction((error) => authErrorMessage(error))
const deleteAction = usePendingAction((error) => authErrorMessage(error))

function onChangePassword() {
  return passwordAction.run(async () => {
    await changePassword(authRepository, authSession, currentPassword.value, newPassword.value)
    await router.push('/login')
  })
}

function onChangeEmail() {
  return emailAction.run(async () => {
    await authRepository.requestEmailChange(nextEmail.value.trim(), emailPassword.value)
    emailNotice.value = `Un lien de confirmation a été envoyé à ${nextEmail.value.trim()}.`
    nextEmail.value = ''
    emailPassword.value = ''
  })
}

function onResendVerification() {
  return verificationAction.run(async () => {
    await authRepository.requestEmailVerification()
    verificationNotice.value = 'Email de confirmation envoyé.'
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
    <h2 class="font-display text-lg font-bold text-strong">Sécurité</h2>
    <p class="mt-1 text-sm text-muted">Mot de passe, email, sessions et suppression du compte.</p>

    <div v-if="!user.emailVerified" class="mt-5">
      <StatusNotice tone="accent">Votre adresse email n'est pas encore confirmée.</StatusNotice>
      <button
        type="button"
        class="btn-outline btn-sm mt-3"
        :disabled="verificationAction.pending.value"
        @click="onResendVerification"
      >
        Renvoyer l'email
      </button>
      <p v-if="verificationNotice" class="mt-3 text-sm text-muted">{{ verificationNotice }}</p>
      <StatusNotice v-if="verificationAction.errorMessage.value" class="mt-3" tone="danger">
        {{ verificationAction.errorMessage.value }}
      </StatusNotice>
    </div>

    <form class="mt-6 grid gap-4 border-t border-line pt-6" @submit.prevent="onChangePassword">
      <h3 class="text-sm font-semibold text-strong">Changer le mot de passe</h3>
      <StatusNotice v-if="passwordAction.errorMessage.value" tone="danger">
        {{ passwordAction.errorMessage.value }}
      </StatusNotice>
      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <label class="field-label" for="current-password">Mot de passe actuel</label>
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
          <label class="field-label" for="account-new-password">Nouveau mot de passe</label>
          <input
            id="account-new-password"
            v-model="newPassword"
            type="password"
            autocomplete="new-password"
            required
            minlength="12"
            class="field"
          />
        </div>
      </div>
      <button type="submit" class="btn-primary w-fit" :disabled="passwordAction.pending.value">
        Mettre à jour le mot de passe
      </button>
    </form>

    <form class="mt-6 grid gap-4 border-t border-line pt-6" @submit.prevent="onChangeEmail">
      <h3 class="text-sm font-semibold text-strong">Changer l'email</h3>
      <p class="text-xs text-muted">L'adresse change seulement après confirmation du lien reçu.</p>
      <StatusNotice v-if="emailNotice" tone="positive">{{ emailNotice }}</StatusNotice>
      <StatusNotice v-if="emailAction.errorMessage.value" tone="danger">{{ emailAction.errorMessage.value }}</StatusNotice>
      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <label class="field-label" for="next-email">Nouvelle adresse</label>
          <input id="next-email" v-model="nextEmail" type="email" autocomplete="email" required class="field" />
        </div>
        <div>
          <label class="field-label" for="email-password">Confirmer avec le mot de passe</label>
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
      <button type="submit" class="btn-outline w-fit" :disabled="emailAction.pending.value">Envoyer le lien</button>
    </form>

    <div class="mt-6 border-t border-line pt-6">
      <h3 class="text-sm font-semibold text-strong">Sessions</h3>
      <p class="mt-1 text-xs text-muted">Ferme la session de cet appareil et de tous les autres.</p>
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
        Déconnecter tous les appareils
      </button>
    </div>

    <form class="mt-6 grid gap-4 border-t border-line pt-6" @submit.prevent="onDelete">
      <h3 class="text-sm font-semibold text-strong">Supprimer le compte</h3>
      <p class="text-xs text-muted">
        Le compte est anonymisé. Les commandes restent, la connexion devient impossible.
      </p>
      <StatusNotice v-if="deleteAction.errorMessage.value" tone="danger">
        {{ deleteAction.errorMessage.value }}
      </StatusNotice>
      <div class="max-w-sm">
        <label class="field-label" for="delete-password">Mot de passe du compte</label>
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
        Supprimer mon compte
      </button>
    </form>
  </section>
</template>
