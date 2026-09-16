<script setup lang="ts">
import { useTheme, type ThemePreference } from '@/shared/theme/useTheme'
import AppIcon from './AppIcon.vue'
import type { IconName } from './icons'

const { preference, setPreference } = useTheme()

const options: { value: ThemePreference; icon: IconName; label: string }[] = [
  { value: 'light', icon: 'sun', label: 'Thème clair' },
  { value: 'system', icon: 'monitor', label: 'Thème système' },
  { value: 'dark', icon: 'moon', label: 'Thème sombre' },
]
</script>

<template>
  <div
    class="inline-flex items-center gap-0.5 rounded-full border border-line bg-surface-inset p-1"
    role="group"
    aria-label="Apparence"
  >
    <button
      v-for="option in options"
      :key="option.value"
      type="button"
      class="inline-flex size-7 items-center justify-center rounded-full transition-colors"
      :class="
        preference === option.value
          ? 'bg-primary text-primary-fg'
          : 'text-faint hover:text-strong'
      "
      :aria-label="option.label"
      :title="option.label"
      :aria-pressed="preference === option.value"
      @click="setPreference(option.value)"
    >
      <AppIcon :name="option.icon" :size="15" />
    </button>
  </div>
</template>
