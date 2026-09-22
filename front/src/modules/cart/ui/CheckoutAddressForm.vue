<script setup lang="ts">
import type { AddressDraft } from '@/modules/order/domain/PostalAddress'

const shipping = defineModel<AddressDraft>('shipping', { required: true })
const billing = defineModel<AddressDraft>('billing', { required: true })
const billingSameAsShipping = defineModel<boolean>('billingSameAsShipping', { required: true })

const countries = [
  { code: 'FR', name: 'France' },
  { code: 'BE', name: 'Belgique' },
  { code: 'CH', name: 'Suisse' },
  { code: 'LU', name: 'Luxembourg' },
  { code: 'DE', name: 'Allemagne' },
  { code: 'ES', name: 'Espagne' },
  { code: 'IT', name: 'Italie' },
  { code: 'PT', name: 'Portugal' },
  { code: 'NL', name: 'Pays-Bas' },
  { code: 'GB', name: 'Royaume-Uni' },
]
</script>

<template>
  <section class="panel p-6" aria-labelledby="checkout-address-title">
    <h2 id="checkout-address-title" class="font-display text-lg font-bold text-strong">Livraison</h2>
    <p class="mt-1 text-sm text-muted">Cette adresse est enregistrée sur la commande.</p>

    <div class="mt-5 grid gap-4 sm:grid-cols-2">
      <label class="block sm:col-span-2">
        <span class="field-label">Destinataire</span>
        <input v-model="shipping.recipient" class="field" autocomplete="name" maxlength="80" required />
      </label>
      <label class="block sm:col-span-2">
        <span class="field-label">Adresse</span>
        <input v-model="shipping.line1" class="field" autocomplete="address-line1" maxlength="120" required />
      </label>
      <label class="block sm:col-span-2">
        <span class="field-label">Complément</span>
        <input v-model="shipping.line2" class="field" autocomplete="address-line2" maxlength="120" />
      </label>
      <label class="block">
        <span class="field-label">Code postal</span>
        <input v-model="shipping.postalCode" class="field" autocomplete="postal-code" maxlength="12" required />
      </label>
      <label class="block">
        <span class="field-label">Ville</span>
        <input v-model="shipping.city" class="field" autocomplete="address-level2" maxlength="80" required />
      </label>
      <label class="block sm:col-span-2">
        <span class="field-label">Pays</span>
        <select v-model="shipping.country" class="field" autocomplete="country">
          <option v-for="country in countries" :key="country.code" :value="country.code">{{ country.name }}</option>
        </select>
      </label>
    </div>

    <label class="mt-5 flex items-start gap-3">
      <input v-model="billingSameAsShipping" type="checkbox" class="mt-1 size-4 accent-accent" />
      <span class="text-sm font-semibold text-strong">L'adresse de facturation est identique</span>
    </label>

    <div v-if="!billingSameAsShipping" class="mt-5 grid gap-4 border-t border-line pt-5 sm:grid-cols-2">
      <h3 class="font-display text-base font-bold text-strong sm:col-span-2">Facturation</h3>
      <label class="block sm:col-span-2">
        <span class="field-label">Destinataire de facturation</span>
        <input v-model="billing.recipient" class="field" autocomplete="billing name" maxlength="80" required />
      </label>
      <label class="block sm:col-span-2">
        <span class="field-label">Adresse de facturation</span>
        <input v-model="billing.line1" class="field" autocomplete="billing address-line1" maxlength="120" required />
      </label>
      <label class="block sm:col-span-2">
        <span class="field-label">Complément de facturation</span>
        <input v-model="billing.line2" class="field" autocomplete="billing address-line2" maxlength="120" />
      </label>
      <label class="block">
        <span class="field-label">Code postal de facturation</span>
        <input v-model="billing.postalCode" class="field" autocomplete="billing postal-code" maxlength="12" required />
      </label>
      <label class="block">
        <span class="field-label">Ville de facturation</span>
        <input v-model="billing.city" class="field" autocomplete="billing address-level2" maxlength="80" required />
      </label>
      <label class="block sm:col-span-2">
        <span class="field-label">Pays de facturation</span>
        <select v-model="billing.country" class="field" autocomplete="billing country">
          <option v-for="country in countries" :key="country.code" :value="country.code">{{ country.name }}</option>
        </select>
      </label>
    </div>
  </section>
</template>
