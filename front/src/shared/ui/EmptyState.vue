<script setup lang="ts">
import AppIcon from './AppIcon.vue'
import type { IconName } from './icons'

withDefaults(
  defineProps<{
    icon?: IconName
    title: string
    description?: string
    headingLevel?: 2 | 3
  }>(),
  { icon: 'package', headingLevel: 3 },
)
</script>

<template>
  <div class="panel-flat grain flex flex-col items-center px-6 py-16 text-center">
    <div
      class="mb-6 flex size-16 items-center justify-center rounded-2xl border border-line bg-surface-inset text-faint"
    >
      <AppIcon :name="icon" :size="26" />
    </div>
    <component :is="`h${headingLevel}`" class="text-xl font-bold text-strong">{{ title }}</component>
    <p v-if="description" class="mt-2 max-w-sm text-sm text-muted">{{ description }}</p>
    <div v-if="$slots.default" class="mt-7">
      <slot />
    </div>
  </div>
</template>
