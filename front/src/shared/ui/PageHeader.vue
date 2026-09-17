<script setup lang="ts">
import AppIcon from './AppIcon.vue'
import type { IconName } from './icons'

withDefaults(
  defineProps<{
    eyebrow: string
    title: string
    icon?: IconName
    description?: string
    tone?: 'neutral' | 'accent'
  }>(),
  { tone: 'neutral' },
)
</script>

<template>
  <header class="flex flex-wrap items-end justify-between gap-6">
    <div>
      <span :class="tone === 'accent' ? 'badge-accent' : 'badge-neutral'">
        <AppIcon v-if="icon" :name="icon" :size="13" />
        {{ eyebrow }}
      </span>
      <h1 class="display-tight mt-5 text-4xl text-strong sm:text-5xl">{{ title }}</h1>
      <p v-if="description" class="mt-3 max-w-xl text-sm text-muted">{{ description }}</p>
    </div>

    <div v-if="$slots.actions" class="flex flex-wrap items-center gap-3">
      <slot name="actions" />
    </div>
  </header>
</template>
