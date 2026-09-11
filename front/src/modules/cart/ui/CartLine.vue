<script setup lang="ts">
import type { CartItem } from '../domain/CartItem'
import ProductPrice from '@/modules/catalog/ui/ProductPrice.vue'

defineProps<{
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
</script>

<template>
  <li class="flex flex-col sm:flex-row gap-4 p-4 bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
    <div class="h-24 w-24 bg-gray-50 rounded-xl flex-shrink-0 overflow-hidden">
      <!-- Placeholder for image if we had one in CartItem, otherwise just a nice icon -->
      <div class="w-full h-full flex items-center justify-center text-gray-300">
        <i class="fa-solid fa-image text-2xl"></i>
      </div>
    </div>
    
    <div class="flex-grow flex flex-col justify-between">
      <div class="flex justify-between items-start gap-4">
        <div>
          <h2 class="font-bold text-gray-900 line-clamp-2">{{ item.name }}</h2>
          <p class="mt-1 text-sm font-medium text-gray-500">
            <ProductPrice :price="item.unitPrice" /> l'unité
          </p>
        </div>
        <div class="text-right">
          <p class="font-black text-gray-900 text-lg">
            <ProductPrice :price="item.lineTotal" />
          </p>
        </div>
      </div>
      
      <div class="mt-4 flex items-center justify-between">
        <div class="flex items-center border border-gray-200 rounded-lg bg-gray-50">
          <button 
            type="button" 
            class="px-3 py-1.5 text-gray-500 hover:text-indigo-600 transition-colors"
            @click="item.quantity > 1 && emit('updateQuantity', item.quantity - 1)"
            :disabled="item.quantity <= 1"
          >
            <i class="fa-solid fa-minus text-[10px]"></i>
          </button>
          <input
            :aria-label="`Quantité ${item.name}`"
            class="w-10 text-center text-sm font-bold text-gray-900 bg-transparent border-none focus:ring-0 p-0"
            type="number"
            min="1"
            :max="item.availableStock"
            :value="item.quantity"
            @change="onQuantityChange"
          />
          <button 
            type="button" 
            class="px-3 py-1.5 text-gray-500 hover:text-indigo-600 transition-colors"
            @click="item.quantity < item.availableStock && emit('updateQuantity', item.quantity + 1)"
            :disabled="item.quantity >= item.availableStock"
          >
            <i class="fa-solid fa-plus text-[10px]"></i>
          </button>
        </div>
        
        <button 
          type="button" 
          class="text-sm font-medium text-gray-400 hover:text-red-500 transition-colors flex items-center gap-1.5" 
          @click="emit('remove')"
          title="Retirer l'article"
        >
          <i class="fa-regular fa-trash-can"></i>
          <span class="hidden sm:inline">Retirer</span>
        </button>
      </div>
    </div>
  </li>
</template>
