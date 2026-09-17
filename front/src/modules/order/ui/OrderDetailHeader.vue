<script setup lang="ts">
import AppIcon from '@/shared/ui/AppIcon.vue'
import { formatDate } from '@/shared/datetime/formatDate'
import { shortId } from '@/shared/id/shortId'
import { paymentStatusLabel } from '@/modules/payment/ui/paymentStatusLabel'
import type { Payment } from '@/modules/payment/domain/Payment'
import type { Order } from '../domain/Order'
import { orderStatusLabel } from './orderStatusLabel'
import { orderStatusStyle } from './orderStatusStyle'

defineProps<{
  order: Order
  payment: Payment | null
}>()
</script>

<template>
  <div
    class="mesh grain flex flex-col gap-5 border-b border-line p-6 sm:flex-row sm:items-center sm:justify-between sm:p-8"
  >
    <div class="relative z-1">
      <h1 class="display-tight text-3xl text-strong">
        Commande <span class="numeric text-accent-strong">#{{ shortId(order.id) }}</span>
      </h1>
      <p class="mt-3 flex items-center gap-2 text-sm text-muted">
        <AppIcon name="calendar" :size="15" />
        Passée le {{ formatDate(order.createdAt) }}
      </p>
    </div>
    <div class="relative z-1 flex flex-col items-start gap-2 sm:items-end">
      <span :class="orderStatusStyle(order.status).badge">
        <AppIcon :name="orderStatusStyle(order.status).icon" :size="12" />
        {{ orderStatusLabel(order.status) }}
      </span>
      <span v-if="payment" class="flex items-center gap-1.5 text-xs font-medium text-muted">
        <AppIcon name="credit-card" :size="14" />Paiement : {{ paymentStatusLabel(payment.status) }}
      </span>
    </div>
  </div>
</template>
