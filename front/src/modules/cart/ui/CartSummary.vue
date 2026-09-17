<script setup lang="ts">
import AppIcon from '@/shared/ui/AppIcon.vue'
import ProductPrice from '@/modules/catalog/ui/ProductPrice.vue'
import type { Money } from '@/shared/money/Money'

defineProps<{
  itemCount: number
  total: Money
  pending: boolean
}>()

const emit = defineEmits<{
  checkout: []
  clear: []
}>()
</script>

<template>
  <div class="panel sticky top-28 p-6">
    <h2 class="font-display text-lg font-bold text-strong">Résumé</h2>

    <dl class="mt-6 space-y-3 text-sm">
      <div class="flex items-baseline justify-between gap-4">
        <dt class="text-muted">
          Sous-total ({{ itemCount }} article{{ itemCount > 1 ? 's' : '' }})
        </dt>
        <dd class="numeric font-semibold text-strong">
          <ProductPrice :price="total" />
        </dd>
      </div>
      <div class="flex items-baseline justify-between gap-4">
        <dt class="text-muted">Livraison</dt>
        <dd class="font-semibold text-positive">Offerte</dd>
      </div>
    </dl>

    <div class="mt-6 flex items-baseline justify-between gap-4 border-t border-line pt-6">
      <span class="font-semibold text-strong">Total TTC</span>
      <span class="numeric font-display text-2xl font-extrabold text-strong">
        <ProductPrice :price="total" />
      </span>
    </div>

    <div class="mt-7 flex flex-col gap-2.5">
      <button
        type="button"
        class="btn-primary btn-lg w-full"
        :disabled="pending"
        @click="emit('checkout')"
      >
        <span v-if="pending" class="animate-orbit">
          <AppIcon name="loader" :size="17" />
        </span>
        <AppIcon v-else name="lock" :size="16" />
        Payer ma commande
      </button>
      <button type="button" class="btn-ghost w-full" :disabled="pending" @click="emit('clear')">
        <AppIcon name="trash" :size="15" />
        Vider le panier
      </button>
    </div>

    <p class="mt-6 flex items-center justify-center gap-2 text-center text-[0.7rem] text-faint">
      <AppIcon name="shield" :size="14" />
      Paiement chiffré, aucune donnée bancaire stockée
    </p>
  </div>
</template>
