<script setup lang="ts">
import type { OrderItem } from '../domain/OrderItem'
import ProductPrice from '@/modules/catalog/ui/ProductPrice.vue'

defineProps<{
  item: OrderItem
}>()
</script>

<template>
  <li class="flex items-center justify-between gap-4 py-4 group">
    <div class="flex items-center gap-4">
      <div class="h-16 w-16 bg-gray-50 rounded-xl flex items-center justify-center text-gray-300 flex-shrink-0 group-hover:bg-indigo-50 group-hover:text-indigo-300 transition-colors">
        <i class="fa-solid fa-box text-xl"></i>
      </div>
      <div>
        <h3 class="font-bold text-gray-900">{{ item.name }}</h3>
        <p class="text-sm font-medium text-gray-500 mt-0.5">
          Quantité : <span class="text-gray-700 bg-gray-100 px-2 py-0.5 rounded-md ml-1">{{ item.quantity }}</span>
        </p>
      </div>
    </div>
    <div class="text-right">
      <p class="font-black text-gray-900">
        <ProductPrice :price="item.lineTotal" />
      </p>
      <p class="text-xs font-medium text-gray-400 mt-1">
        <ProductPrice :price="item.unitPrice" /> / unité
      </p>
    </div>
  </li>
</template>
