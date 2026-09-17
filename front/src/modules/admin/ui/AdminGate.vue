<script setup lang="ts">
import AuthRequiredPanel from '@/modules/auth/ui/AuthRequiredPanel.vue'
import AppIcon from '@/shared/ui/AppIcon.vue'
import { authSessionKey } from '@/modules/auth/application/authSessionKey'
import { computed, inject } from 'vue'

const props = defineProps<{
  redirect: string
}>()

const session = inject(authSessionKey)
const isAuthenticated = computed(() => session?.isAuthenticated.value ?? false)
const isAdmin = computed(() => session?.isAdmin.value ?? false)
</script>

<template>
  <AuthRequiredPanel
    v-if="!isAuthenticated"
    message="Connectez-vous pour accéder à l'administration."
    :redirect="props.redirect"
    action-label="Connexion"
  />

  <div v-else-if="!isAdmin" role="alert" class="notice-danger mx-auto mt-10 max-w-md">
    <AppIcon name="shield" :size="18" class="mt-0.5" />
    <span>Cette page est réservée aux administrateurs.</span>
  </div>

  <slot v-else />
</template>
