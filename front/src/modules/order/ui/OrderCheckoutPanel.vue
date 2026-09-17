<script setup lang="ts">
import AppIcon from '@/shared/ui/AppIcon.vue'
import StatusNotice from '@/shared/ui/StatusNotice.vue'
import ProductPrice from '@/modules/catalog/ui/ProductPrice.vue'
import type { Money } from '@/shared/money/Money'

defineProps<{
  total: Money
  pending: boolean
  canPay: boolean
  canCancel: boolean
  actionError?: string
}>()

const emit = defineEmits<{
  pay: []
  cancel: []
}>()
</script>

<template>
  <div class="border-t border-line bg-surface-inset p-6 sm:p-8">
    <StatusNotice v-if="actionError" tone="danger" class="mb-6">{{ actionError }}</StatusNotice>

    <dl class="ml-auto max-w-xs space-y-3 text-sm">
      <div class="flex items-baseline justify-between gap-6">
        <dt class="text-muted">Sous-total</dt>
        <dd class="numeric font-semibold text-strong">
          <ProductPrice :price="total" />
        </dd>
      </div>
      <div class="flex items-baseline justify-between gap-6">
        <dt class="text-muted">Livraison</dt>
        <dd class="font-semibold text-positive">Offerte</dd>
      </div>
      <div class="flex items-baseline justify-between gap-6 border-t border-line pt-3 text-base">
        <dt class="font-semibold text-strong">Total</dt>
        <dd class="numeric font-display text-2xl font-extrabold text-strong">
          <ProductPrice :price="total" />
        </dd>
      </div>
    </dl>

    <div v-if="canPay || canCancel" class="mt-8 flex flex-wrap justify-end gap-3">
      <button
        v-if="canCancel"
        type="button"
        class="btn-outline"
        :disabled="pending"
        @click="emit('cancel')"
      >
        <AppIcon name="close" :size="15" />
        Annuler la commande
      </button>
      <button
        v-if="canPay"
        type="button"
        class="btn-primary btn-lg"
        :disabled="pending"
        @click="emit('pay')"
      >
        <span v-if="pending" class="animate-orbit">
          <AppIcon name="loader" :size="17" />
        </span>
        <AppIcon v-else name="credit-card" :size="16" />
        Procéder au paiement
      </button>
    </div>
  </div>
</template>
