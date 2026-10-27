<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from '@/shared/i18n/useI18n'
import AppIcon from '@/shared/ui/AppIcon.vue'

const { t } = useI18n()
const items = computed(() => [
  { icon: 'truck' as const, title: t('catalog.reassurance.shipTitle'), text: t('catalog.reassurance.shipText') },
  { icon: 'refresh' as const, title: t('catalog.reassurance.returnTitle'), text: t('catalog.reassurance.returnText') },
  { icon: 'shield' as const, title: t('catalog.reassurance.warrantyTitle'), text: t('catalog.reassurance.warrantyText') },
  { icon: 'lock' as const, title: t('catalog.reassurance.payTitle'), text: t('catalog.reassurance.payText') },
])
</script>

<template>
  <ul class="mt-10 grid gap-4 sm:grid-cols-2">
    <li v-for="item in items" :key="item.title" class="flex items-start gap-3">
      <span
        class="flex size-10 shrink-0 items-center justify-center rounded-2xl border border-line bg-surface-inset text-accent-strong"
      >
        <AppIcon :name="item.icon" :size="18" />
      </span>
      <span>
        <strong class="block text-sm font-semibold text-strong">{{ item.title }}</strong>
        <span class="text-xs text-muted">{{ item.text }}</span>
      </span>
    </li>
  </ul>
</template>
