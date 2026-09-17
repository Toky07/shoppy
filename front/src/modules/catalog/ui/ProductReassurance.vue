<script setup lang="ts">
import AppIcon from '@/shared/ui/AppIcon.vue'

const items = [
  { icon: 'truck', title: 'Expédié sous 48 h', text: 'Suivi inclus, emballage sobre' },
  { icon: 'refresh', title: 'Retour sous 30 jours', text: 'Remboursé sans discuter' },
  { icon: 'shield', title: 'Garantie 2 ans', text: "Pièces et main d'œuvre" },
  { icon: 'lock', title: 'Paiement sécurisé', text: 'Carte bancaire via Stripe' },
] as const
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
