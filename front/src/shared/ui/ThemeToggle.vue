<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from '@/shared/i18n/useI18n'
import { useTheme, type ThemePreference } from '@/shared/theme/useTheme'
import AppIcon from './AppIcon.vue'
import type { IconName } from './icons'

const { preference, setPreference } = useTheme()
const { t } = useI18n()

const options = computed<{ value: ThemePreference; icon: IconName; label: string }[]>(() => [
  { value: 'light', icon: 'sun', label: t('theme.light') },
  { value: 'system', icon: 'monitor', label: t('theme.system') },
  { value: 'dark', icon: 'moon', label: t('theme.dark') },
])
</script>

<template>
  <div
    class="inline-flex items-center gap-0.5 rounded-full border border-line bg-surface-inset p-1"
    role="group"
    :aria-label="t('theme.appearance')"
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
