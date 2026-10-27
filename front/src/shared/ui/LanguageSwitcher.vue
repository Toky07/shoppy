<script setup lang="ts">
import { LOCALES } from '@/shared/i18n/locale'
import { useI18n } from '@/shared/i18n/useI18n'

const { locale, t, setLocale } = useI18n()
</script>

<template>
  <div
    class="inline-flex items-center gap-0.5 rounded-full border border-line bg-surface-inset p-1"
    role="group"
    :aria-label="t('locale.label')"
  >
    <button
      v-for="code in LOCALES"
      :key="code"
      type="button"
      class="min-w-8 rounded-full px-2 py-1 text-[0.65rem] font-bold tracking-[0.08em] uppercase transition-colors"
      :class="
        locale === code ? 'bg-primary text-primary-fg' : 'text-faint hover:text-strong'
      "
      :aria-label="t(`locale.${code}`)"
      :aria-pressed="locale === code"
      @click="setLocale(code)"
    >
      {{ code }}
    </button>
  </div>
</template>
