<script setup lang="ts">
import { computed, inject } from 'vue'
import { RouterLink } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import { authSessionKey } from '@/modules/auth/application/authSessionKey'

const props = defineProps<{
  redirect: string
}>()

const session = inject(authSessionKey)
const isAuthenticated = computed(() => session?.isAuthenticated.value ?? false)
const isAdmin = computed(() => session?.isAdmin.value ?? false)
</script>

<template>
  <template v-if="!isAuthenticated">
    <div class="panel mx-auto mt-10 max-w-md p-8 text-center">
      <div
        class="mx-auto mb-6 flex size-14 items-center justify-center rounded-2xl border border-line bg-surface-inset text-accent-strong"
      >
        <AppIcon name="lock" :size="24" />
      </div>
      <p class="text-sm text-muted">Connectez-vous pour accéder à l'administration.</p>
      <RouterLink
        :to="{ path: '/login', query: { redirect: props.redirect } }"
        class="btn-primary btn-lg mt-7 w-full"
      >
        Connexion
      </RouterLink>
    </div>
  </template>

  <div v-else-if="!isAdmin" role="alert" class="notice-danger mx-auto mt-10 max-w-md">
    <AppIcon name="shield" :size="18" class="mt-0.5" />
    <span>Cette page est réservée aux administrateurs.</span>
  </div>

  <slot v-else />
</template>
