<script setup lang="ts">
import { computed, inject, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { usePendingAction } from '@/shared/async/usePendingAction'
import { ApiError } from '@/shared/http/ApiError'
import { useI18n } from '@/shared/i18n/useI18n'
import AppIcon from '@/shared/ui/AppIcon.vue'
import BrandMark from '@/shared/ui/BrandMark.vue'
import LanguageSwitcher from '@/shared/ui/LanguageSwitcher.vue'
import StatusNotice from '@/shared/ui/StatusNotice.vue'
import { authRepositoryKey } from '@/modules/auth/application/authRepositoryKey'
import { authSessionKey } from '@/modules/auth/application/authSessionKey'
import { login } from '@/modules/auth/application/login'
import { logout } from '@/modules/auth/application/logout'
import { authErrorMessage } from '@/modules/auth/ui/lib/authErrorMessage'
import { adminRedirectPath } from '../lib/adminRedirectPath'

const repository = inject(authRepositoryKey)
const session = inject(authSessionKey)

if (!repository || !session) {
  throw new Error('Admin login dependencies are not provided.')
}

const authRepository = repository
const authSession = session
const { t, locale } = useI18n()
const router = useRouter()
const route = useRoute()
const email = ref('')
const password = ref('')
const revealed = ref(false)
const forbiddenMessage = computed(() => t('admin.forbidden'))
const { pending, errorMessage, run } = usePendingAction((error) => authErrorMessage(error, t))
const alreadyCustomer = computed(
  () => authSession.isAuthenticated.value && !authSession.isAdmin.value,
)

watch(
  locale,
  () => {
    document.title = t('meta.adminLoginTitle')
  },
  { immediate: true },
)

onMounted(() => {
  if (authSession.isAdmin.value) {
    void router.replace(adminRedirectPath(route.query.redirect))
  }
})

onBeforeUnmount(() => {
  document.title = t('meta.homeTitle')
})

function onSubmit() {
  return run(async () => {
    await login(authRepository, authSession, {
      email: email.value.trim(),
      password: password.value,
    })

    if (!authSession.isAdmin.value) {
      throw new ApiError(403, 'forbidden', forbiddenMessage.value)
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
          {{ t('admin.loginTitle') }}
        </h1>
        <p class="mt-2 max-w-xs text-sm text-white/55">
          {{ t('admin.loginIntro') }}
        </p>
      </div>

      <div class="admin-login-card rounded-3xl p-7 sm:p-8">
        <form class="space-y-5" @submit.prevent="onSubmit">
          <StatusNotice v-if="alreadyCustomer || errorMessage" tone="danger">
            {{ alreadyCustomer ? forbiddenMessage : errorMessage }}
          </StatusNotice>

          <div>
            <label class="field-label" for="admin-email">{{ t('common.email') }}</label>
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
            <label class="field-label" for="admin-password">{{ t('common.password') }}</label>
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
                :placeholder="t('common.passwordPlaceholder')"
                class="field px-11"
              />
              <button
                type="button"
                class="absolute top-1/2 right-3 flex size-8 -translate-y-1/2 items-center justify-center rounded-full text-faint transition-colors hover:bg-surface-muted hover:text-strong"
                :aria-label="revealed ? t('common.hidePassword') : t('common.showPassword')"
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
            {{ t('account.signOut') }}
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
            {{ t('admin.enterConsole') }}
          </button>
        </form>
      </div>

      <p class="mt-8 text-center text-sm text-white/50">
        <RouterLink to="/" class="font-semibold text-white/80 underline-offset-2 hover:underline">
          {{ t('common.backToShop') }}
        </RouterLink>
      </p>
      <div class="mt-6 flex justify-center">
        <LanguageSwitcher />
      </div>
    </div>
  </div>
</template>
