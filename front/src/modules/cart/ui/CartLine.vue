<script setup lang="ts">
import AppIcon from '@/shared/ui/AppIcon.vue'
import ProductPrice from '@/modules/catalog/ui/ProductPrice.vue'
import type { CartItem } from '../domain/CartItem'

const props = defineProps<{
  item: CartItem
}>()

const emit = defineEmits<{
  updateQuantity: [quantity: number]
  remove: []
}>()

function onQuantityChange(event: Event) {
  const value = Number((event.target as HTMLInputElement).value)
  if (!Number.isInteger(value) || value < 1) {
    return
  }
  emit('updateQuantity', value)
}

function step(delta: number) {
  const next = props.item.quantity + delta
  if (next >= 1 && next <= props.item.availableStock) {
    emit('updateQuantity', next)
  }
}
</script>

<template>
  <li class="panel-flat flex gap-4 p-4 transition-shadow hover:shadow-soft sm:gap-5 sm:p-5">
    <div
      class="flex size-20 shrink-0 items-center justify-center rounded-2xl border border-line bg-surface-inset text-faint sm:size-24"
    >
      <AppIcon name="package" :size="26" />
    </div>

    <div class="flex min-w-0 flex-1 flex-col">
      <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
          <h2 class="truncate font-display text-base font-bold text-strong">{{ item.name }}</h2>
          <p class="numeric mt-1 text-xs text-muted">
            <ProductPrice :price="item.unitPrice" /> l'unité
          </p>
        </div>
        <p class="numeric shrink-0 font-display text-lg font-extrabold text-strong">
          <ProductPrice :price="item.lineTotal" />
        </p>
      </div>

      <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-1 rounded-full border border-line bg-surface-inset p-1">
          <button
            type="button"
            class="flex size-8 items-center justify-center rounded-full text-body transition-colors hover:bg-surface hover:text-strong disabled:opacity-40"
            :aria-label="`Diminuer la quantité de ${item.name}`"
            :disabled="item.quantity <= 1"
            @click="step(-1)"
          >
            <AppIcon name="minus" :size="14" />
          </button>
          <input
            :aria-label="`Quantité ${item.name}`"
            class="numeric w-10 border-none bg-transparent text-center text-sm font-bold text-strong focus:outline-none"
            type="number"
            min="1"
            :max="item.availableStock"
            :value="item.quantity"
            @change="onQuantityChange"
          />
          <button
            type="button"
            class="flex size-8 items-center justify-center rounded-full text-body transition-colors hover:bg-surface hover:text-strong disabled:opacity-40"
            :aria-label="`Augmenter la quantité de ${item.name}`"
            :disabled="item.quantity >= item.availableStock"
            @click="step(1)"
          >
            <AppIcon name="plus" :size="14" />
          </button>
        </div>

        <button
          type="button"
          class="inline-flex items-center gap-1.5 text-xs font-semibold text-faint transition-colors hover:text-danger"
          @click="emit('remove')"
        >
          <AppIcon name="trash" :size="14" />
          Retirer
        </button>
      </div>
    </div>
  </li>
</template>
