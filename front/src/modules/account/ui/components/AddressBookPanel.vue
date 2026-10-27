<script setup lang="ts">
import { computed, inject } from 'vue'
import { useI18n } from '@/shared/i18n/useI18n'
import AppIcon from '@/shared/ui/AppIcon.vue'
import { addressBookStateKey } from '../../application/addressBookStateKey'
import { formatSavedAddress } from '../../domain/SavedAddress'

const { t } = useI18n()
const book = inject(addressBookStateKey)
const addresses = computed(() => book?.addresses.value ?? [])
</script>

<template>
  <div class="panel p-6 sm:p-7">
    <h2 class="font-display text-lg font-bold text-strong">{{ t('account.addresses') }}</h2>
    <p class="mt-1 text-sm text-muted">
      {{ t('account.addressesHint') }}
    </p>

    <p v-if="addresses.length === 0" class="mt-5 text-sm text-muted">
      {{ t('account.noAddresses') }}
    </p>

    <ul v-else class="mt-5 grid gap-3">
      <li
        v-for="address in addresses"
        :key="address.id"
        class="flex flex-wrap items-start justify-between gap-3 rounded-2xl border border-line px-4 py-3"
      >
        <div class="min-w-0">
          <p class="text-sm font-semibold text-strong">{{ formatSavedAddress(address) }}</p>
          <p v-if="address.isDefault" class="mt-1 text-xs font-semibold text-accent-strong">
            {{ t('checkout.default') }}
          </p>
        </div>
        <div class="flex flex-wrap gap-2">
          <button
            v-if="!address.isDefault"
            type="button"
            class="btn-outline btn-sm"
            @click="book?.markDefault(address.id)"
          >
            {{ t('account.setDefault') }}
          </button>
          <button type="button" class="btn-outline btn-sm" @click="book?.remove(address.id)">
            <AppIcon name="trash" :size="14" />
            {{ t('account.delete') }}
          </button>
        </div>
      </li>
    </ul>
  </div>
</template>
