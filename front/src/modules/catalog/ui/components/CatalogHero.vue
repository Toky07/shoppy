<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useI18n } from '@/shared/i18n/useI18n'
import AppIcon from '@/shared/ui/AppIcon.vue'

const { t } = useI18n()
const highlights = computed(() => [
  { icon: 'truck' as const, value: t('catalog.shipValue'), label: t('catalog.shipLabel') },
  { icon: 'refresh' as const, value: t('catalog.returnValue'), label: t('catalog.returnLabel') },
  { icon: 'shield' as const, value: t('catalog.warrantyValue'), label: t('catalog.warrantyLabel') },
])
</script>

<template>
  <div class="mesh grain relative overflow-hidden rounded-panel border border-line bg-surface">
    <div class="relative z-1 grid gap-10 p-7 sm:p-10 lg:grid-cols-[1.25fr_1fr] lg:items-end lg:p-14">
      <div>
        <span class="badge-accent">
          <AppIcon name="sparkles" :size="13" />
          {{ t('catalog.badge') }}
        </span>
        <h1 class="display-tight mt-6 text-5xl text-strong sm:text-6xl lg:text-7xl">
          {{ t('catalog.titleBefore') }} <span class="text-accent-strong">{{ t('catalog.titleAccent') }}</span>
        </h1>
        <p class="mt-5 max-w-lg text-base leading-relaxed text-muted">
          {{ t('catalog.intro') }}
        </p>
        <div class="mt-8 flex flex-wrap items-center gap-3">
          <RouterLink to="/?sort=newest" class="btn-primary btn-lg">
            {{ t('catalog.newest') }}
            <AppIcon name="arrow-right" :size="17" />
          </RouterLink>
          <RouterLink to="/favorites" class="btn-outline btn-lg">
            <AppIcon name="heart" :size="16" />
            {{ t('catalog.wishlist') }}
          </RouterLink>
        </div>
      </div>

      <dl class="grid grid-cols-3 gap-3">
        <div
          v-for="item in highlights"
          :key="item.icon"
          class="rounded-2xl border border-line bg-surface/70 p-4 backdrop-blur-sm"
        >
          <span class="text-accent-strong"><AppIcon :name="item.icon" :size="20" /></span>
          <dt class="numeric mt-3 font-display text-xl font-extrabold text-strong">
            {{ item.value }}
          </dt>
          <dd class="mt-0.5 text-[0.7rem] leading-tight font-medium text-muted">{{ item.label }}</dd>
        </div>
      </dl>
    </div>
  </div>
</template>
