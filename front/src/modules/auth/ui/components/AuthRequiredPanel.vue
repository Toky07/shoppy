<script setup lang="ts">
import { RouterLink } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'

withDefaults(
  defineProps<{
    message: string
    redirect: string
    title?: string
    actionLabel?: string
  }>(),
  { title: 'Connexion requise', actionLabel: 'Se connecter' },
)
</script>

<template>
  <div class="panel mx-auto mt-12 max-w-md p-8 text-center">
    <div
      class="mx-auto mb-6 flex size-14 items-center justify-center rounded-2xl border border-line bg-surface-inset text-accent-strong"
    >
      <AppIcon name="lock" :size="24" />
    </div>
    <h2 class="text-xl font-bold text-strong">{{ title }}</h2>
    <p class="mt-3 text-sm text-muted">{{ message }}</p>
    <RouterLink
      :to="{ path: '/login', query: { redirect } }"
      class="btn-primary btn-lg mt-8 w-full"
    >
      {{ actionLabel }}
    </RouterLink>
  </div>
</template>
