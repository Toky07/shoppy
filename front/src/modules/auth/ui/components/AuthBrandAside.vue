<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from '@/shared/i18n/useI18n'
import AppIcon from '@/shared/ui/AppIcon.vue'
import BrandMark from '@/shared/ui/BrandMark.vue'

const { t } = useI18n()
const perks = computed(
  () =>
    [
      { icon: 'lock' as const, title: t('auth.perkPayTitle'), text: t('auth.perkPayText') },
      { icon: 'package' as const, title: t('auth.perkTrackTitle'), text: t('auth.perkTrackText') },
      { icon: 'truck' as const, title: t('auth.perkShipTitle'), text: t('auth.perkShipText') },
    ] as const,
)
</script>

<template>
  <aside
    class="mesh grain relative hidden overflow-hidden rounded-panel border border-line bg-surface p-10 lg:flex lg:flex-col lg:justify-between"
  >
    <div class="relative z-1">
      <span class="text-strong"><BrandMark :size="38" /></span>
      <p class="display-tight mt-8 text-3xl text-strong">
        {{ t('auth.brandTitle') }} <span class="text-accent-strong">{{ t('auth.brandAccent') }}</span>.
      </p>
      <p class="mt-4 max-w-xs text-sm leading-relaxed text-muted">
        {{ t('auth.brandBody') }}
      </p>
    </div>

    <ul class="relative z-1 mt-10 space-y-5">
      <li v-for="perk in perks" :key="perk.title" class="flex items-start gap-3">
        <span
          class="flex size-9 shrink-0 items-center justify-center rounded-xl border border-line bg-surface/70 text-accent-strong backdrop-blur-sm"
        >
          <AppIcon :name="perk.icon" :size="17" />
        </span>
        <span>
          <strong class="block text-sm font-semibold text-strong">{{ perk.title }}</strong>
          <span class="text-xs text-muted">{{ perk.text }}</span>
        </span>
      </li>
    </ul>
  </aside>
</template>
