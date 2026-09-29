<script setup lang="ts">
import AppIcon from '@/shared/ui/AppIcon.vue'
import BrandMark from '@/shared/ui/BrandMark.vue'

const perks = [
  { icon: 'lock', title: 'Paiement sécurisé', text: 'Carte bancaire traitée par Stripe' },
  { icon: 'package', title: 'Suivi de commande', text: 'Historique et statuts en temps réel' },
  { icon: 'truck', title: 'Expédition 48 h', text: "Offerte dès 49 € d'achat" },
] as const
</script>

<template>
  <aside
    class="mesh grain relative hidden overflow-hidden rounded-panel border border-line bg-surface p-10 lg:flex lg:flex-col lg:justify-between"
  >
    <div class="relative z-1">
      <span class="text-strong"><BrandMark :size="38" /></span>
      <p class="display-tight mt-8 text-3xl text-strong">
        Une boutique courte, <span class="text-accent-strong">sans bruit</span>.
      </p>
      <p class="mt-4 max-w-xs text-sm leading-relaxed text-muted">
        On ne vend pas tout, on vend ce qui dure. Votre compte garde vos commandes et vos envies au
        même endroit.
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
