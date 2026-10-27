<script setup lang="ts">
import { useI18n } from '@/shared/i18n/useI18n'
import AppIcon from './AppIcon.vue'

const { t } = useI18n()
</script>

<template>
  <div class="hidden bg-primary text-primary-fg sm:block dark:bg-canvas-deep dark:text-muted">
    <div
      class="mx-auto flex max-w-7xl items-center justify-center gap-8 px-6 py-2 text-[0.7rem] font-medium tracking-[0.08em] uppercase"
    >
      <span class="inline-flex items-center gap-2">
        <AppIcon name="truck" :size="14" />
        {{ t('trust.shipping') }}
      </span>
      <span class="hidden items-center gap-2 md:inline-flex">
        <AppIcon name="refresh" :size="14" />
        {{ t('trust.returns') }}
      </span>
      <span class="hidden items-center gap-2 lg:inline-flex">
        <AppIcon name="shield" :size="14" />
        {{ t('trust.payment') }}
      </span>
    </div>
  </div>
</template>
