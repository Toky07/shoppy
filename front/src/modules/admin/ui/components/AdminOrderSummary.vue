<script setup lang="ts">
import AppIcon from '@/shared/ui/AppIcon.vue'
import StatusNotice from '@/shared/ui/StatusNotice.vue'
import ProductPrice from '@/modules/catalog/ui/components/ProductPrice.vue'
import { formatDate } from '@/shared/datetime/formatDate'
import type { Order } from '@/modules/order/domain/Order'
import { useI18n } from '@/shared/i18n/useI18n'
import { orderStatusLabel } from '@/modules/order/ui/lib/orderStatusLabel'
import { orderStatusStyle } from '@/modules/order/ui/lib/orderStatusStyle'

const { t, intlLocale } = useI18n()

defineProps<{
  order: Order
  pending: boolean
  canMarkPaid: boolean
  actionError?: string
}>()

const emit = defineEmits<{
  markPaid: []
}>()
</script>

<template>
  <div class="flex flex-wrap items-center justify-between gap-4 border-b border-line bg-surface-inset p-6">
    <div>
      <h2 class="font-display text-xl font-bold text-strong">
        {{ t('admin.orderOf', { date: formatDate(order.createdAt, intlLocale) }) }}
      </h2>
      <p class="numeric mt-2 text-xs break-all text-faint">{{ t('orders.customer', { id: order.customerId }) }}</p>
    </div>
    <span :class="orderStatusStyle(order.status).badge">
      <AppIcon :name="orderStatusStyle(order.status).icon" :size="12" />
      {{ orderStatusLabel(order.status, t) }}
    </span>
  </div>

  <div class="p-6">
    <h3 class="field-label">{{ t('orders.itemsHeading') }}</h3>
    <ul class="divide-y divide-line">
      <slot />
    </ul>
  </div>

  <div class="border-t border-line bg-surface-inset p-6">
    <StatusNotice v-if="actionError" tone="danger" class="mb-5">{{ actionError }}</StatusNotice>

    <div class="flex flex-wrap items-center justify-between gap-5">
      <div>
        <p class="text-xs text-muted">{{ t('admin.total') }}</p>
        <p class="numeric mt-1 font-display text-2xl font-extrabold text-strong">
          <ProductPrice :price="order.total" />
        </p>
        <p v-if="order.shipping" class="mt-1 text-xs text-muted">
          {{ t('cart.shipping', { label: t(`shipping.${order.shipping.method}`) }) }}
          <template v-if="order.shipping.fee.cents === 0"> {{ t('admin.shippingOffered') }}</template>
          <template v-else>
            · <ProductPrice :price="order.shipping.fee" />
          </template>
        </p>
      </div>

      <button
        v-if="canMarkPaid"
        type="button"
        class="btn-primary"
        :disabled="pending"
        @click="emit('markPaid')"
      >
        <span v-if="pending" class="animate-orbit">
          <AppIcon name="loader" :size="16" />
        </span>
        <AppIcon v-else name="check" :size="16" />
        {{ t('admin.markPaid') }}
      </button>
    </div>
  </div>
</template>
