<script setup lang="ts">
import type { PostalAddress } from '../domain/PostalAddress'

defineProps<{
  shipping: PostalAddress | null
  billing: PostalAddress | null
}>()

const countries: Record<string, string> = {
  FR: 'France',
  BE: 'Belgique',
  CH: 'Suisse',
  LU: 'Luxembourg',
  DE: 'Allemagne',
  ES: 'Espagne',
  IT: 'Italie',
  PT: 'Portugal',
  NL: 'Pays-Bas',
  GB: 'Royaume-Uni',
}

function countryName(code: string): string {
  return countries[code] ?? code
}

function lines(address: PostalAddress): string[] {
  return [
    address.recipient,
    address.line1,
    address.line2 ?? '',
    `${address.postalCode} ${address.city}`,
    countryName(address.country),
  ].filter((line) => line !== '')
}
</script>

<template>
  <div v-if="shipping || billing" class="grid gap-6 border-b border-line p-6 sm:grid-cols-2 sm:p-8">
    <div v-if="shipping">
      <h2 class="field-label">Livraison</h2>
      <address class="mt-3 text-sm leading-relaxed text-body not-italic">
        <span v-for="(line, index) in lines(shipping)" :key="index" class="block">{{ line }}</span>
      </address>
    </div>
    <div v-if="billing">
      <h2 class="field-label">Facturation</h2>
      <address class="mt-3 text-sm leading-relaxed text-body not-italic">
        <span v-for="(line, index) in lines(billing)" :key="index" class="block">{{ line }}</span>
      </address>
    </div>
  </div>
</template>
