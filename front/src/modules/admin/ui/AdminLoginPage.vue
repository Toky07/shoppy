<script setup lang="ts">
import { computed, inject, onBeforeUnmount, onMounted, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { usePendingAction } from '@/shared/async/usePendingAction'
import { ApiError } from '@/shared/http/ApiError'
import AppIcon from '@/shared/ui/AppIcon.vue'
import BrandMark from '@/shared/ui/BrandMark.vue'
import StatusNotice from '@/shared/ui/StatusNotice.vue'
import { authRepositoryKey } from '@/modules/auth/application/authRepositoryKey'
import { authSessionKey } from '@/modules/auth/application/authSessionKey'
import { login } from '@/modules/auth/application/login'
import { logout } from '@/modules/auth/application/logout'
import { authErrorMessage } from '@/modules/auth/ui/authErrorMessage'
import { adminRedirectPath } from './adminRedirectPath'

const repository = inject(authRepositoryKey)
const session = inject(authSessionKey)

if (!repository || !session) {
  throw new Error('Admin login dependencies are not provided.')
}

const authRepository = repository
const authSession = session
const router = useRouter()
const route = useRoute()
const email = ref('')
const password = ref('')
const revealed = ref(false)
const forbiddenMessage = 'Cet espace est réservé aux administrateurs.'
const { pending, errorMessage, run } = usePendingAction((error) => authErrorMessage(error))
const alreadyCustomer = computed(
  () => authSession.isAuthenticated.value && !authSession.isAdmin.value,
)

onMounted(() => {
  document.title = 'Connexion console — Shoppy'

  if (authSession.isAdmin.value) {
    void router.replace(adminRedirectPath(route.query.redirect))
  }
})

onBeforeUnmount(() => {
  document.title = 'Shoppy — Boutique'
})

function onSubmit() {
  return run(async () => {
    await login(authRepository, authSession, {
      email: email.value.trim(),
      password: password.value,
    })

    if (!authSession.isAdmin.value) {
      throw new ApiError(403, 'forbidden', forbiddenMessage)
    }

    await router.push(adminRedirectPath(route.query.redirect))
  })
}

async function onLogout() {
  await logout(authRepository, authSession)
}
</script>

<template>
  <div class="admin-login relative flex min-h-screen items-center justify-center px-5 py-12">
    <div class="relative w-full max-w-[26rem]">
      <div class="mb-8 flex flex-col items-center text-center">
        <span class="text-white">
          <BrandMark :size="44" />
        </span>
        <p class="mt-5 text-[0.7rem] font-semibold tracking-[0.22em] text-white/50 uppercase">
          Shoppy Console
        </p>
        <h1 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-white">
          Connexion administrateur
        </h1>
        <p class="mt-2 max-w-xs text-sm text-white/55">
          Gérez le catalogue, les commandes et les accès depuis un espace dédié.
        </p>
      </div>

      <div class="admin-login-card rounded-3xl p-7 sm:p-8">
        <form class="space-y-5" @submit.prevent="onSubmit">
          <StatusNotice v-if="alreadyCustomer || errorMessage" tone="danger">
            {{ alreadyCustomer ? forbiddenMessage : errorMessage }}
          </StatusNotice>

          <div>
            <label class="field-label" for="admin-email">Adresse email</label>
            <div class="relative">
              <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-faint">
                <AppIcon name="mail" :size="16" />
              </span>
              <input
                id="admin-email"
                v-model="email"
                type="email"
                name="email"
                autocomplete="username"
                required
                placeholder="admin@shoppy.test"
                class="field pl-11"
              />
            </div>
          </div>

          <div>
            <label class="field-label" for="admin-password">Mot de passe</label>
            <div class="relative">
              <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-faint">
                <AppIcon name="lock" :size="16" />
              </span>
              <input
                id="admin-password"
                v-model="password"
                :type="revealed ? 'text' : 'password'"
                name="password"
                autocomplete="current-password"
                required
                placeholder="Votre mot de passe"
                class="field px-11"
              />
              <button
                type="button"
                class="absolute top-1/2 right-3 flex size-8 -translate-y-1/2 items-center justify-center rounded-full text-faint transition-colors hover:bg-surface-muted hover:text-strong"
                :aria-label="revealed ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                @click="revealed = !revealed"
              >
                <AppIcon :name="revealed ? 'eye-off' : 'eye'" :size="16" />
              </button>
            </div>
          </div>

          <button
            v-if="alreadyCustomer"
            type="button"
            class="btn-outline w-full rounded-xl"
            @click="onLogout"
          >
            Se déconnecter
          </button>
          <button
            v-else
            type="submit"
            class="btn-primary btn-lg w-full rounded-xl"
            :disabled="pending"
          >
            <span v-if="pending" class="animate-orbit">
              <AppIcon name="loader" :size="17" />
            </span>
            <AppIcon v-else name="lock" :size="16" />
            Entrer dans la console
          </button>
        </form>
      </div>

      <p class="mt-8 text-center text-sm text-white/50">
        <RouterLink to="/" class="font-semibold text-white/80 underline-offset-2 hover:underline">
          Retour à la boutique
        </RouterLink>
      </p>
    </div>
  </div>
</template>
