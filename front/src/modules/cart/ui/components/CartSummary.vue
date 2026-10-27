<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from '@/shared/i18n/useI18n'
import AppIcon from '@/shared/ui/AppIcon.vue'
import ProductPrice from '@/modules/catalog/ui/components/ProductPrice.vue'
import type { Money } from '@/shared/money/Money'

const props = defineProps<{
  itemCount: number
  total: Money
  shippingLabel: string
  shippingFeeCents: number
  pending: boolean
  actionLabel?: string
  note?: string
}>()

const shippingFee = computed<Money>(() => ({
  cents: props.shippingFeeCents,
  currency: props.total.currency,
}))
const grandTotal = computed<Money>(() => ({
  cents: props.total.cents + props.shippingFeeCents,
  currency: props.total.currency,
}))

const { t } = useI18n()
const subtotalLabel = computed(() =>
  props.itemCount > 1
    ? t('cart.subtotalPlural', { count: props.itemCount })
    : t('cart.subtotal', { count: props.itemCount }),
)

const emit = defineEmits<{
  checkout: []
  clear: []
}>()
</script>

<template>
  <div class="panel sticky top-28 p-6">
    <h2 class="font-display text-lg font-bold text-strong">{{ t('cart.summary') }}</h2>

    <dl class="mt-6 space-y-3 text-sm">
      <div class="flex items-baseline justify-between gap-4">
        <dt class="text-muted">
          {{ subtotalLabel }}
        </dt>
        <dd class="numeric font-semibold text-strong">
          <ProductPrice :price="total" />
        </dd>
      </div>
      <div class="flex items-baseline justify-between gap-4">
        <dt class="text-muted">{{ t('cart.shipping', { label: shippingLabel }) }}</dt>
        <dd v-if="shippingFee.cents === 0" class="font-semibold text-positive">{{ t('cart.shippingFree') }}</dd>
        <dd v-else class="numeric font-semibold text-strong">
          <ProductPrice :price="shippingFee" />
        </dd>
      </div>
    </dl>

    <div class="mt-6 flex items-baseline justify-between gap-4 border-t border-line pt-6">
      <span class="font-semibold text-strong">{{ t('cart.totalTtc') }}</span>
      <span class="numeric font-display text-2xl font-extrabold text-strong">
        <ProductPrice :price="grandTotal" />
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
        {{ actionLabel ?? t('cart.checkoutAction') }}
      </button>
      <button type="button" class="btn-ghost w-full" :disabled="pending" @click="emit('clear')">
        <AppIcon name="trash" :size="15" />
        {{ t('cart.clear') }}
      </button>
    </div>

    <p class="mt-6 flex items-center justify-center gap-2 text-center text-[0.7rem] text-faint">
      <AppIcon name="shield" :size="14" />
      {{ note ?? t('cart.checkoutNoteAuth') }}
    </p>
  </div>
</template>
