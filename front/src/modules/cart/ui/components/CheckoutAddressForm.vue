<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import type { SavedAddress } from '@/modules/account/domain/SavedAddress'
import { formatSavedAddress } from '@/modules/account/domain/SavedAddress'
import {
  emptyAddressDraft,
  fromPostalAddress,
  type AddressDraft,
} from '@/modules/order/domain/PostalAddress'
import { useI18n } from '@/shared/i18n/useI18n'

const { t } = useI18n()
const countryCodes = ['FR', 'BE', 'CH', 'LU', 'DE', 'ES', 'IT', 'PT', 'NL', 'GB'] as const
const countries = computed(() =>
  countryCodes.map((code) => ({ code, name: t(`checkout.countries.${code}`) })),
)

const props = defineProps<{
  savedAddresses?: SavedAddress[]
}>()

const shipping = defineModel<AddressDraft>('shipping', { required: true })
const billing = defineModel<AddressDraft>('billing', { required: true })
const billingSameAsShipping = defineModel<boolean>('billingSameAsShipping', { required: true })
const selectedId = ref('new')

watch(
  () => props.savedAddresses,
  (addresses) => {
    if (!addresses?.length || shipping.value.recipient.trim() !== '') {
      return
    }

    const selected = addresses.find((address) => address.isDefault) ?? addresses[0]
    if (selected === undefined) {
      return
    }

    apply(selected)
  },
  { immediate: true },
)

function apply(address: SavedAddress) {
  selectedId.value = address.id
  Object.assign(shipping.value, fromPostalAddress(address))
}

function startNew() {
  selectedId.value = 'new'
  Object.assign(shipping.value, emptyAddressDraft())
}

</script>

<template>
  <section class="panel p-6" aria-labelledby="checkout-address-title">
    <h2 id="checkout-address-title" class="font-display text-lg font-bold text-strong">{{ t('checkout.shippingTitle') }}</h2>
    <p class="mt-1 text-sm text-muted">
      {{ t('checkout.shippingHint') }}
    </p>

    <fieldset v-if="savedAddresses?.length" class="mt-5 grid gap-2">
      <legend class="field-label">{{ t('checkout.chooseAddress') }}</legend>
      <label
        v-for="address in savedAddresses"
        :key="address.id"
        class="flex cursor-pointer items-start gap-3 rounded-2xl border border-line px-3 py-3"
      >
        <input
          class="mt-1 size-4 accent-accent"
          type="radio"
          name="saved-address"
          :checked="selectedId === address.id"
          @change="apply(address)"
        />
        <span>
          <span class="block text-sm font-semibold text-strong">{{ formatSavedAddress(address) }}</span>
          <span v-if="address.isDefault" class="mt-0.5 block text-xs text-muted">{{ t('checkout.default') }}</span>
        </span>
      </label>
      <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-line px-3 py-3">
        <input
          class="mt-1 size-4 accent-accent"
          type="radio"
          name="saved-address"
          :checked="selectedId === 'new'"
          @change="startNew"
        />
        <span class="text-sm font-semibold text-strong">{{ t('checkout.newAddress') }}</span>
      </label>
    </fieldset>

    <div class="mt-5 grid gap-4 sm:grid-cols-2">
      <label class="block sm:col-span-2">
        <span class="field-label">{{ t('checkout.recipient') }}</span>
        <input v-model="shipping.recipient" class="field" autocomplete="name" maxlength="80" required />
      </label>
      <label class="block sm:col-span-2">
        <span class="field-label">{{ t('checkout.address') }}</span>
        <input v-model="shipping.line1" class="field" autocomplete="address-line1" maxlength="120" required />
      </label>
      <label class="block sm:col-span-2">
        <span class="field-label">{{ t('checkout.line2') }}</span>
        <input v-model="shipping.line2" class="field" autocomplete="address-line2" maxlength="120" />
      </label>
      <label class="block">
        <span class="field-label">{{ t('checkout.postalCode') }}</span>
        <input v-model="shipping.postalCode" class="field" autocomplete="postal-code" maxlength="12" required />
      </label>
      <label class="block">
        <span class="field-label">{{ t('checkout.city') }}</span>
        <input v-model="shipping.city" class="field" autocomplete="address-level2" maxlength="80" required />
      </label>
      <label class="block sm:col-span-2">
        <span class="field-label">{{ t('checkout.country') }}</span>
        <select v-model="shipping.country" class="field" autocomplete="country">
          <option v-for="country in countries" :key="country.code" :value="country.code">{{ country.name }}</option>
        </select>
      </label>
    </div>

    <label class="mt-5 flex items-start gap-3">
      <input v-model="billingSameAsShipping" type="checkbox" class="mt-1 size-4 accent-accent" />
      <span class="text-sm font-semibold text-strong">{{ t('checkout.billingSame') }}</span>
    </label>

    <div v-if="!billingSameAsShipping" class="mt-5 grid gap-4 border-t border-line pt-5 sm:grid-cols-2">
      <h3 class="font-display text-base font-bold text-strong sm:col-span-2">{{ t('checkout.billingTitle') }}</h3>
      <label class="block sm:col-span-2">
        <span class="field-label">{{ t('checkout.billingRecipient') }}</span>
        <input v-model="billing.recipient" class="field" autocomplete="billing name" maxlength="80" required />
      </label>
      <label class="block sm:col-span-2">
        <span class="field-label">{{ t('checkout.billingAddress') }}</span>
        <input v-model="billing.line1" class="field" autocomplete="billing address-line1" maxlength="120" required />
      </label>
      <label class="block sm:col-span-2">
        <span class="field-label">{{ t('checkout.billingLine2') }}</span>
        <input v-model="billing.line2" class="field" autocomplete="billing address-line2" maxlength="120" />
      </label>
      <label class="block">
        <span class="field-label">{{ t('checkout.billingPostalCode') }}</span>
        <input v-model="billing.postalCode" class="field" autocomplete="billing postal-code" maxlength="12" required />
      </label>
      <label class="block">
        <span class="field-label">{{ t('checkout.billingCity') }}</span>
        <input v-model="billing.city" class="field" autocomplete="billing address-level2" maxlength="80" required />
      </label>
      <label class="block sm:col-span-2">
        <span class="field-label">{{ t('checkout.billingCountry') }}</span>
        <select v-model="billing.country" class="field" autocomplete="billing country">
          <option v-for="country in countries" :key="country.code" :value="country.code">{{ country.name }}</option>
        </select>
      </label>
    </div>
  </section>
</template>
