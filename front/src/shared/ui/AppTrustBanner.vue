<script setup lang="ts">
import AppIcon from './AppIcon.vue'
</script>

<template>
  <div class="hidden bg-primary text-primary-fg sm:block dark:bg-canvas-deep dark:text-muted">
    <div
      class="mx-auto flex max-w-7xl items-center justify-center gap-8 px-6 py-2 text-[0.7rem] font-medium tracking-[0.08em] uppercase"
    >
      <span class="inline-flex items-center gap-2">
        <AppIcon name="truck" :size="14" />
        Livraison offerte dès 49 €
      </span>
      <span class="hidden items-center gap-2 md:inline-flex">
        <AppIcon name="refresh" :size="14" />
        Retours sous 30 jours
      </span>
      <span class="hidden items-center gap-2 lg:inline-flex">
        <AppIcon name="shield" :size="14" />
        Paiement sécurisé par Stripe
      </span>
    </div>
  </div>
</template>
