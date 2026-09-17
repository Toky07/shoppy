<script setup lang="ts">
import { ref } from 'vue'
import AppIcon from '@/shared/ui/AppIcon.vue'
import BrandMark from '@/shared/ui/BrandMark.vue'
import StatusNotice from '@/shared/ui/StatusNotice.vue'
import AuthBrandAside from './AuthBrandAside.vue'

const props = withDefaults(
  defineProps<{
    title: string
    submitLabel: string
    pending: boolean
    subtitle?: string
    errorMessage?: string
    passwordAutocomplete?: string
  }>(),
  { passwordAutocomplete: 'current-password' },
)

const emit = defineEmits<{
  submit: [credentials: { email: string; password: string }]
}>()

const email = ref('')
const password = ref('')
const revealed = ref(false)

function onSubmit() {
  emit('submit', { email: email.value.trim(), password: password.value })
}
</script>

<template>
  <div class="animate-fade-in grid items-stretch gap-8 lg:grid-cols-[1fr_0.85fr]">
    <div class="panel flex flex-col justify-center p-7 sm:p-10">
      <div class="mx-auto w-full max-w-sm">
        <span class="text-strong lg:hidden"><BrandMark :size="34" /></span>

        <h1 class="display-tight mt-6 text-3xl text-strong sm:text-4xl lg:mt-0">{{ title }}</h1>
        <p class="mt-3 text-sm text-muted">
          {{ props.subtitle ?? 'Entrez vos identifiants pour continuer.' }}
        </p>

        <form class="mt-8 space-y-5" @submit.prevent="onSubmit">
          <StatusNotice v-if="props.errorMessage" tone="danger">{{ props.errorMessage }}</StatusNotice>

          <div>
            <label class="field-label" for="auth-email">Adresse email</label>
            <div class="relative">
              <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-faint">
                <AppIcon name="mail" :size="16" />
              </span>
              <input
                id="auth-email"
                v-model="email"
                type="email"
                name="email"
                autocomplete="email"
                required
                placeholder="vous@exemple.com"
                class="field pl-11"
              />
            </div>
          </div>

          <div>
            <label class="field-label" for="auth-password">Mot de passe</label>
            <div class="relative">
              <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-faint">
                <AppIcon name="lock" :size="16" />
              </span>
              <input
                id="auth-password"
                v-model="password"
                :type="revealed ? 'text' : 'password'"
                name="password"
                :autocomplete="props.passwordAutocomplete"
                required
                minlength="8"
                placeholder="8 caractères minimum"
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

          <button type="submit" class="btn-primary btn-lg w-full" :disabled="props.pending">
            <span v-if="props.pending" class="animate-orbit">
              <AppIcon name="loader" :size="17" />
            </span>
            {{ props.submitLabel }}
          </button>
        </form>

        <p class="mt-8 border-t border-line pt-6 text-center text-sm text-muted">
          <slot />
        </p>
      </div>
    </div>

    <AuthBrandAside />
  </div>
</template>
