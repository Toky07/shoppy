<script setup lang="ts">
import { computed, inject } from 'vue'
import { RouterLink } from 'vue-router'
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
    <p class="mt-6 text-stone-600">Connectez-vous pour accéder à l'administration.</p>
    <p class="mt-4">
      <RouterLink
        :to="{ path: '/login', query: { redirect: props.redirect } }"
        class="text-stone-900 underline"
      >
        Connexion
      </RouterLink>
    </p>
  </template>
  <p v-else-if="!isAdmin" role="alert">Cette page est réservée aux administrateurs.</p>
  <slot v-else />
</template>
