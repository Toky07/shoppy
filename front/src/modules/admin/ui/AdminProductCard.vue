<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import AppImage from '@/shared/ui/AppImage.vue'
import ProductPrice from '@/modules/catalog/ui/ProductPrice.vue'
import { stockLabel } from '@/modules/catalog/ui/stockLabel'
import type { Product } from '@/modules/catalog/domain/Product'

const props = defineProps<{
  product: Product
  pending: boolean
}>()

const emit = defineEmits<{
  delete: []
}>()

const stockTone = computed(() => {
  if (props.product.stock <= 0) {
    return 'badge-danger'
  }
  if (props.product.stock <= 5) {
    return 'badge-warning'
  }
  return 'badge-positive'
})

const imageCount = computed(() => props.product.imageUrls.length)
</script>

<template>
  <article class="admin-product-row">
    <RouterLink
      :to="{ name: 'admin-product-edit', params: { id: product.id } }"
      class="flex min-w-0 items-center gap-4"
      :aria-label="`Modifier ${product.name}`"
    >
      <div class="relative size-16 shrink-0 overflow-hidden rounded-2xl bg-surface-inset sm:size-20">
        <AppImage :src="product.imageUrl" :alt="product.name" />
        <span
          v-if="imageCount > 1"
          class="absolute right-1.5 bottom-1.5 inline-flex items-center gap-0.5 rounded-full bg-black/55 px-1.5 py-0.5 text-[0.62rem] font-semibold text-white"
        >
          <AppIcon name="image" :size="10" />
          {{ imageCount }}
        </span>
      </div>
      <div class="min-w-0">
        <p class="truncate font-display text-base font-bold text-strong">{{ product.name }}</p>
        <p v-if="product.slug" class="mt-0.5 truncate text-[0.7rem] font-semibold tracking-[0.12em] text-faint uppercase">
          /{{ product.slug }}
        </p>
        <p v-if="product.description" class="mt-1 hidden truncate text-sm text-muted lg:block">
          {{ product.description }}
        </p>
      </div>
    </RouterLink>

    <p class="numeric text-right font-display text-lg font-extrabold text-strong">
      <ProductPrice :price="product.price" />
    </p>

    <div>
      <span :class="stockTone">{{ stockLabel(product.stock) }}</span>
    </div>

    <div class="flex flex-wrap items-center justify-end gap-2">
      <RouterLink
        :to="{ name: 'admin-product-edit', params: { id: product.id } }"
        class="btn-outline btn-sm"
      >
        <AppIcon name="settings" :size="14" />
        Modifier
      </RouterLink>
      <button
        type="button"
        class="btn-danger btn-sm"
        :disabled="pending"
        :aria-label="`Supprimer ${product.name}`"
        @click="emit('delete')"
      >
        <AppIcon name="trash" :size="14" />
        <span class="hidden sm:inline">Supprimer</span>
      </button>
    </div>
  </article>
</template>
