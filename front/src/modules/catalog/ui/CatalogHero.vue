<script setup lang="ts">
import { RouterLink } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'

const highlights = [
  { icon: 'truck', value: '48 h', label: 'Expédition' },
  { icon: 'refresh', value: '30 j', label: "Pour changer d'avis" },
  { icon: 'shield', value: '2 ans', label: 'De garantie' },
] as const
</script>

<template>
  <div class="mesh grain relative overflow-hidden rounded-panel border border-line bg-surface">
    <div class="relative z-1 grid gap-10 p-7 sm:p-10 lg:grid-cols-[1.25fr_1fr] lg:items-end lg:p-14">
      <div>
        <span class="badge-accent">
          <AppIcon name="sparkles" :size="13" />
          Sélection de saison
        </span>
        <h1 class="display-tight mt-6 text-5xl text-strong sm:text-6xl lg:text-7xl">
          Notre <span class="text-accent-strong">Collection</span>
        </h1>
        <p class="mt-5 max-w-lg text-base leading-relaxed text-muted">
          Des objets choisis un par un, gardés seulement s'ils tiennent la route. Pas de catalogue
          interminable : juste ce qui mérite d'être acheté.
        </p>
        <div class="mt-8 flex flex-wrap items-center gap-3">
          <RouterLink to="/?sort=newest" class="btn-primary btn-lg">
            Voir les nouveautés
            <AppIcon name="arrow-right" :size="17" />
          </RouterLink>
          <RouterLink to="/favorites" class="btn-outline btn-lg">
            <AppIcon name="heart" :size="16" />
            Ma liste d'envies
          </RouterLink>
        </div>
      </div>

      <dl class="grid grid-cols-3 gap-3">
        <div
          v-for="item in highlights"
          :key="item.label"
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
