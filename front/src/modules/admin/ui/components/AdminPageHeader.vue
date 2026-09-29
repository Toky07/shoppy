<script setup lang="ts">
import { RouterLink } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import type { IconName } from '@/shared/ui/icons'

defineProps<{
  title: string
  description?: string
  icon?: IconName
  backTo?: string
  backLabel?: string
}>()
</script>

<template>
  <header class="flex flex-wrap items-end justify-between gap-4">
    <div class="min-w-0">
      <RouterLink
        v-if="backTo && backLabel"
        :to="backTo"
        class="mb-3 inline-flex items-center gap-2 text-sm font-medium link-quiet"
      >
        <AppIcon name="arrow-left" :size="15" />
        {{ backLabel }}
      </RouterLink>

      <div class="flex items-center gap-3">
        <span
          v-if="icon"
          class="flex size-10 items-center justify-center rounded-xl border border-line bg-surface text-accent-strong"
        >
          <AppIcon :name="icon" :size="18" />
        </span>
        <div>
          <h1 class="font-display text-2xl font-bold tracking-tight text-strong">{{ title }}</h1>
          <p v-if="description" class="mt-1 max-w-xl text-sm text-muted">{{ description }}</p>
        </div>
      </div>
    </div>

    <div v-if="$slots.actions" class="flex flex-wrap items-center gap-3">
      <slot name="actions" />
    </div>
  </header>
</template>
