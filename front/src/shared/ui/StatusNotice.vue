<script setup lang="ts">
import AppIcon from './AppIcon.vue'
import type { IconName } from './icons'

type Tone = 'danger' | 'positive' | 'accent'

defineProps<{
  tone: Tone
  icon?: IconName
}>()

const icons: Record<Tone, IconName> = {
  danger: 'alert-circle',
  positive: 'check-circle',
  accent: 'sparkles',
}

const roles: Record<Tone, 'alert' | 'status'> = {
  danger: 'alert',
  positive: 'status',
  accent: 'status',
}
</script>

<template>
  <div :role="roles[tone]" :class="`notice-${tone}`">
    <AppIcon :name="icon ?? icons[tone]" :size="18" class="mt-0.5" />
    <div>
      <slot />
    </div>
  </div>
</template>
