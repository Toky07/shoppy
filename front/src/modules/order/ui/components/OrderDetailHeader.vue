<script setup lang="ts">
import AppIcon from '@/shared/ui/AppIcon.vue'
import { formatDate } from '@/shared/datetime/formatDate'
import { shortId } from '@/shared/id/shortId'
import { paymentStatusLabel } from '@/modules/payment/ui/lib/paymentStatusLabel'
import type { Payment } from '@/modules/payment/domain/Payment'
import type { Order } from '../../domain/Order'
import { useI18n } from '@/shared/i18n/useI18n'
import { orderStatusLabel } from '../lib/orderStatusLabel'
import { orderStatusStyle } from '../lib/orderStatusStyle'

const { t, intlLocale } = useI18n()

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
        {{ t('orders.titleNamed', { id: shortId(order.id) }) }}
      </h1>
      <p class="mt-3 flex items-center gap-2 text-sm text-muted">
        <AppIcon name="calendar" :size="15" />
        {{ t('orders.placedOn', { date: formatDate(order.createdAt, intlLocale) }) }}
      </p>
    </div>
    <div class="relative z-1 flex flex-col items-start gap-2 sm:items-end">
      <span :class="orderStatusStyle(order.status).badge">
        <AppIcon :name="orderStatusStyle(order.status).icon" :size="12" />
        {{ orderStatusLabel(order.status, t) }}
      </span>
      <span v-if="payment" class="flex items-center gap-1.5 text-xs font-medium text-muted">
        <AppIcon name="credit-card" :size="14" />{{ t('orders.paymentLabel', { status: paymentStatusLabel(payment.status, t) }) }}
      </span>
    </div>
  </div>
</template>
