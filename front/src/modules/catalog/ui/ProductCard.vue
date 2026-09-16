<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import type { Product } from '../domain/Product'
import FavoriteButton from './FavoriteButton.vue'
import ProductImage from './ProductImage.vue'
import ProductPrice from './ProductPrice.vue'
import { stockLabel } from './stockLabel'

const props = withDefaults(
  defineProps<{
    product: Product
    variant?: 'grid' | 'list'
  }>(),
  { variant: 'grid' },
)

const to = computed(() => ({ name: 'product', params: { id: props.product.id } }))
const outOfStock = computed(() => props.product.stock <= 0)
const lowStock = computed(() => props.product.stock > 0 && props.product.stock <= 3)
</script>

<template>
  <article
    v-if="variant === 'list'"
    class="group panel-flat relative flex gap-4 p-4 transition-all duration-300 hover:border-line-strong hover:shadow-lifted sm:gap-6 sm:p-5"
  >
    <div class="relative size-24 shrink-0 overflow-hidden rounded-2xl sm:size-32">
      <ProductImage
        :src="product.imageUrl"
        :alt="product.name"
        class="transition-transform duration-700 group-hover:scale-105"
      />
    </div>

    <div class="flex min-w-0 flex-1 flex-col">
      <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
          <h2 class="truncate font-display text-lg font-bold text-strong">{{ product.name }}</h2>
          <p class="mt-1.5 text-xs font-medium" :class="outOfStock ? 'text-danger' : 'text-muted'">
            <span>{{ stockLabel(product.stock) }}</span>
          </p>
        </div>
        <p class="numeric shrink-0 font-display text-xl font-extrabold text-strong">
          <ProductPrice :price="product.price" />
        </p>
      </div>

      <p v-if="product.description" class="mt-3 line-clamp-2 text-sm text-muted">
        {{ product.description }}
      </p>

      <div class="mt-auto flex items-center justify-between gap-4 pt-4">
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-strong">
          Voir le produit
          <AppIcon
            name="arrow-right"
            :size="15"
            class="transition-transform duration-300 group-hover:translate-x-1"
          />
        </span>
        <span class="relative z-20">
          <FavoriteButton :product-id="product.id" :product-name="product.name" />
        </span>
      </div>
    </div>

    <RouterLink :to="to" class="stretched-link rounded-card">
      <span class="sr-only">Voir {{ product.name }}</span>
    </RouterLink>
  </article>

  <article
    v-else
    class="group panel-flat relative flex flex-col overflow-hidden transition-all duration-500 hover:-translate-y-1 hover:border-line-strong hover:shadow-float"
  >
    <div class="relative aspect-4/5 overflow-hidden">
      <ProductImage
        :src="product.imageUrl"
        :alt="product.name"
        class="transition-transform duration-[900ms] group-hover:scale-108"
      />
      <div
        class="pointer-events-none absolute inset-x-0 bottom-0 h-28 bg-linear-to-t from-black/30 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100"
      ></div>

      <div class="absolute top-3 left-3 flex flex-col items-start gap-2">
        <span v-if="outOfStock" class="badge-danger">Rupture</span>
        <span v-else-if="lowStock" class="badge-warning">Derniers exemplaires</span>
      </div>

      <div class="absolute top-3 right-3 z-20">
        <FavoriteButton :product-id="product.id" :product-name="product.name" />
      </div>
    </div>

    <div class="flex flex-1 flex-col p-5">
      <div class="flex items-start justify-between gap-3">
        <h2 class="line-clamp-2 font-display text-base leading-snug font-bold text-strong">{{ product.name }}</h2>
        <p class="numeric shrink-0 font-display text-lg font-extrabold text-strong">
          <ProductPrice :price="product.price" />
        </p>
      </div>

      <div class="mt-4 flex items-center justify-between gap-3 border-t border-line pt-4">
        <span class="text-xs font-medium" :class="outOfStock ? 'text-danger' : 'text-muted'">{{
          stockLabel(product.stock)
        }}</span>
        <span
          class="inline-flex items-center gap-1 text-xs font-semibold text-strong opacity-0 transition-all duration-300 group-hover:opacity-100"
        >
          Découvrir
          <AppIcon name="arrow-right" :size="14" />
        </span>
      </div>
    </div>

    <RouterLink :to="to" class="stretched-link rounded-card">
      <span class="sr-only">Voir {{ product.name }}</span>
    </RouterLink>
  </article>
</template>
