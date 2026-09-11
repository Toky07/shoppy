<script setup lang="ts">
import { RouterLink } from 'vue-router'
import type { Product } from '../domain/Product'
import ProductImage from './ProductImage.vue'
import ProductPrice from './ProductPrice.vue'
import { stockLabel } from './stockLabel'

defineProps<{
  product: Product
}>()
</script>

<template>
  <article class="group relative flex flex-col bg-white rounded-[2rem] overflow-hidden transition-all duration-500 hover:shadow-2xl hover:shadow-indigo-100/50">
    <RouterLink :to="{ name: 'product', params: { id: product.id } }" class="flex-grow flex flex-col">
      <!-- Image Container -->
      <div class="relative aspect-[4/5] overflow-hidden bg-gray-100">
        <ProductImage :src="product.imageUrl" :alt="product.name" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110" />
        
        <!-- Overlays -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
        
        <!-- Badges -->
        <div class="absolute top-4 left-4 flex flex-col gap-2">
          <span v-if="product.stock > 0" class="px-3 py-1 bg-white/90 backdrop-blur-sm text-gray-900 text-xs font-bold uppercase tracking-wider rounded-full shadow-sm">
            En stock
          </span>
          <span v-else class="px-3 py-1 bg-red-500/90 backdrop-blur-sm text-white text-xs font-bold uppercase tracking-wider rounded-full shadow-sm">
            Rupture
          </span>
        </div>

        <!-- Wishlist Button -->
        <button class="absolute top-4 right-4 h-10 w-10 rounded-full bg-white/90 backdrop-blur-sm text-gray-400 hover:text-red-500 shadow-sm flex items-center justify-center transition-all duration-300 hover:scale-110 opacity-0 group-hover:opacity-100 translate-y-2 group-hover:translate-y-0" @click.prevent>
          <i class="fa-regular fa-heart"></i>
        </button>
      </div>
      
      <!-- Content -->
      <div class="p-6 flex flex-col flex-grow bg-white">
        <div class="flex justify-between items-start gap-4 mb-2">
          <h2 class="text-lg font-bold text-gray-900 line-clamp-2 group-hover:text-indigo-600 transition-colors leading-tight">{{ product.name }}</h2>
          <p class="text-lg font-black text-gray-900 whitespace-nowrap">
            <ProductPrice :price="product.price" />
          </p>
        </div>
        
        <div class="mt-auto pt-4 flex items-center justify-between">
          <p class="text-sm font-medium text-gray-500 flex items-center gap-1.5">
            <i class="fa-solid fa-box-open text-gray-400"></i> {{ stockLabel(product.stock) }}
          </p>
          
          <!-- Action Button (appears on hover) -->
          <div class="flex items-center gap-2 text-sm font-bold text-indigo-600 opacity-0 group-hover:opacity-100 -translate-x-4 group-hover:translate-x-0 transition-all duration-300">
            Découvrir <i class="fa-solid fa-arrow-right"></i>
          </div>
        </div>
      </div>
    </RouterLink>
  </article>
</template>
