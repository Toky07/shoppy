<script setup lang="ts">
import { computed } from 'vue'
import AppIcon from '@/shared/ui/AppIcon.vue'
import StatusNotice from '@/shared/ui/StatusNotice.vue'
import ProductPrice from '@/modules/catalog/ui/components/ProductPrice.vue'
import type { OrderShipping } from '@/modules/order/domain/ShippingMethod'
import { useI18n } from '@/shared/i18n/useI18n'
import type { Money } from '@/shared/money/Money'

const { t } = useI18n()

const props = defineProps<{
  total: Money
  shipping: OrderShipping | null
  pending: boolean
  canPay: boolean
  canCancel: boolean
  actionError?: string
}>()

const shippingFee = computed<Money>(() => props.shipping?.fee ?? { cents: 0, currency: props.total.currency })
const merchandise = computed<Money>(() => ({
  cents: props.total.cents - shippingFee.value.cents,
  currency: props.total.currency,
}))

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
        <dt class="text-muted">{{ t('orders.subtotal') }}</dt>
        <dd class="numeric font-semibold text-strong">
          <ProductPrice :price="merchandise" />
        </dd>
      </div>
      <div class="flex items-baseline justify-between gap-6">
        <dt class="text-muted">{{ t('cart.shipping', { label: shipping ? t(`shipping.${shipping.method}`) : '' }) }}</dt>
        <dd v-if="shippingFee.cents === 0" class="font-semibold text-positive">{{ t('cart.shippingFree') }}</dd>
        <dd v-else class="numeric font-semibold text-strong">
          <ProductPrice :price="shippingFee" />
        </dd>
      </div>
      <div class="flex items-baseline justify-between gap-6 border-t border-line pt-3 text-base">
        <dt class="font-semibold text-strong">{{ t('cart.total') }}</dt>
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
        {{ t('orders.cancel') }}
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
        {{ t('orders.payNow') }}
      </button>
    </div>
  </div>
</template>
