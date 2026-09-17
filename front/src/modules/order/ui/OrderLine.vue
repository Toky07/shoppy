<script setup lang="ts">
import AppIcon from '@/shared/ui/AppIcon.vue'
import ProductPrice from '@/modules/catalog/ui/ProductPrice.vue'
import type { OrderItem } from '../domain/OrderItem'

defineProps<{
  item: OrderItem
}>()
</script>

<template>
  <li class="group flex items-center justify-between gap-4 py-4">
    <div class="flex min-w-0 items-center gap-4">
      <div
        class="flex size-12 shrink-0 items-center justify-center rounded-2xl border border-line bg-surface-inset text-faint transition-colors group-hover:text-accent-strong"
      >
        <AppIcon name="box" :size="20" />
      </div>
      <div class="min-w-0">
        <h3 class="truncate font-semibold text-strong">{{ item.name }}</h3>
        <p class="numeric mt-0.5 text-xs text-muted">
          <ProductPrice :price="item.unitPrice" /> × {{ item.quantity }}
        </p>
      </div>
    </div>
    <p class="numeric shrink-0 font-display font-bold text-strong">
      <ProductPrice :price="item.lineTotal" />
    </p>
  </li>
</template>
