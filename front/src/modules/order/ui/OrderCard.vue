<script setup lang="ts">
import { RouterLink } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import ProductPrice from '@/modules/catalog/ui/ProductPrice.vue'
import { formatDate } from '@/shared/datetime/formatDate'
import { shortId } from '@/shared/id/shortId'
import type { Order } from '../domain/Order'
import { orderStatusLabel } from './orderStatusLabel'
import { orderStatusStyle } from './orderStatusStyle'

defineProps<{
  order: Order
}>()

function itemCount(count: number) {
  return `${count} article${count > 1 ? 's' : ''}`
}
</script>

<template>
  <article
    class="group panel relative flex flex-col p-6 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lifted"
  >
    <div class="flex items-start justify-between gap-3">
      <div>
        <p class="text-xs font-semibold tracking-[0.12em] text-faint uppercase">
          N° {{ shortId(order.id) }}
        </p>
        <p class="mt-2 flex items-center gap-2 text-sm font-medium text-body">
          <AppIcon name="calendar" :size="14" />
          {{ formatDate(order.createdAt) }}
        </p>
      </div>
      <span :class="orderStatusStyle(order.status).badge">
        <AppIcon :name="orderStatusStyle(order.status).icon" :size="12" />
        {{ orderStatusLabel(order.status) }}
      </span>
    </div>

    <div class="mt-6 flex items-end justify-between gap-4">
      <div>
        <p class="text-xs text-muted">{{ itemCount(order.items.length) }}</p>
        <p class="numeric mt-1 font-display text-2xl font-extrabold text-strong">
          <ProductPrice :price="order.total" />
        </p>
      </div>
    </div>

    <div class="mt-6 border-t border-line pt-4">
      <RouterLink
        :to="{ name: 'order', params: { id: order.id } }"
        class="inline-flex items-center gap-2 text-sm font-semibold text-strong"
      >
        Voir les détails
        <AppIcon
          name="arrow-right"
          :size="15"
          class="transition-transform duration-300 group-hover:translate-x-1"
        />
      </RouterLink>
    </div>
  </article>
</template>
