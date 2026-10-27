<script setup lang="ts">
import { ref } from 'vue'
import { useI18n } from '@/shared/i18n/useI18n'

defineProps<{
  description: string | null
}>()

const { t } = useI18n()
const tabs = ['description', 'delivery', 'warranty'] as const
const activeTab = ref<(typeof tabs)[number]>('description')
</script>

<template>
  <div class="mt-10">
    <div class="flex gap-1 border-b border-line" role="tablist" :aria-label="t('catalog.tabs.details')">
      <button
        v-for="tab in tabs"
        :key="tab"
        type="button"
        role="tab"
        class="relative -mb-px border-b-2 px-4 py-3 text-sm font-semibold transition-colors"
        :class="
          activeTab === tab
            ? 'border-accent text-strong'
            : 'border-transparent text-muted hover:text-strong'
        "
        :aria-selected="activeTab === tab"
        @click="activeTab = tab"
      >
        {{ t(`catalog.tabs.${tab}`) }}
      </button>
    </div>

    <div class="pt-6 text-sm leading-relaxed text-body">
      <template v-if="activeTab === 'description'">
        <p v-if="description">{{ description }}</p>
        <p v-else class="text-faint italic">{{ t('catalog.tabs.noDescription') }}</p>
      </template>
      <p v-else-if="activeTab === 'delivery'">
        {{ t('catalog.tabs.deliveryText') }}
      </p>
      <p v-else>
        {{ t('catalog.tabs.warrantyText') }}
      </p>
    </div>
  </div>
</template>
