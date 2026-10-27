<script setup lang="ts">
import { SHIPPING_METHODS, type ShippingMethodCode } from '@/modules/order/domain/ShippingMethod'
import { useI18n } from '@/shared/i18n/useI18n'
import { formatMoney } from '@/shared/money/formatMoney'

const { t, intlLocale } = useI18n()
const shippingMethod = defineModel<ShippingMethodCode>({ required: true })

function tariff(code: ShippingMethodCode): string {
  const method = SHIPPING_METHODS.find((candidate) => candidate.code === code)
  if (!method) {
    return ''
  }

  const price = formatMoney({ cents: method.feeCents, currency: 'EUR' }, intlLocale.value)
  if (method.freeFromCents === null) {
    return price
  }

  const threshold = formatMoney({ cents: method.freeFromCents, currency: 'EUR' }, intlLocale.value)
  return t('checkout.tariffFreeFrom', { price, threshold })
}
</script>

<template>
  <section class="panel p-6" aria-labelledby="checkout-shipping-title">
    <h2 id="checkout-shipping-title" class="font-display text-lg font-bold text-strong">{{ t('checkout.methodTitle') }}</h2>
    <p class="mt-1 text-sm text-muted">{{ t('checkout.methodHint') }}</p>

    <fieldset class="mt-5 space-y-3">
      <legend class="sr-only">{{ t('checkout.methodTitle') }}</legend>
      <label
        v-for="method in SHIPPING_METHODS"
        :key="method.code"
        class="flex items-start gap-3 rounded-lg border border-line px-4 py-3"
      >
        <input
          v-model="shippingMethod"
          type="radio"
          name="shipping-method"
          class="mt-1 size-4 accent-accent"
          :value="method.code"
        />
        <span>
          <span class="block text-sm font-semibold text-strong">{{ t(`shipping.${method.code}`) }}</span>
          <span class="mt-0.5 block text-sm text-muted">{{ tariff(method.code) }}</span>
        </span>
      </label>
    </fieldset>
  </section>
</template>
