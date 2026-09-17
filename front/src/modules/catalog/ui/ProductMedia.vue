<script setup lang="ts">
import { computed, ref } from 'vue'
import AppIcon from '@/shared/ui/AppIcon.vue'
import AppImage from '@/shared/ui/AppImage.vue'
import ImageCarousel from '@/shared/ui/ImageCarousel.vue'
import { useCopyToClipboard } from '@/shared/clipboard/useCopyToClipboard'
import { shortId } from '@/shared/id/shortId'
import type { Product } from '../domain/Product'
import FavoriteButton from './FavoriteButton.vue'

const props = defineProps<{
  product: Product
}>()

const slide = ref(0)
const images = computed(() => props.product.imageUrls)
const { copied, copy } = useCopyToClipboard()
const outOfStock = computed(() => props.product.stock <= 0)

function onCopyLink() {
  return copy(window.location.href)
}
</script>

<template>
  <div class="lg:sticky lg:top-28">
    <div
      class="group relative aspect-4/5 overflow-hidden rounded-panel border border-line bg-surface shadow-lifted"
    >
      <ImageCarousel
        v-model="slide"
        :images="images"
        :alt="product.name"
        eager
        interactive
      />
      <div class="absolute top-5 left-5 z-20 flex flex-col gap-2">
        <span v-if="outOfStock" class="badge-danger">Rupture</span>
        <span v-else class="badge-accent">Disponible</span>
      </div>
      <div class="absolute top-5 right-5 z-20">
        <FavoriteButton :product-id="product.id" :product-name="product.name" size="lg" />
      </div>
    </div>

    <div v-if="images.length > 1" class="mt-3 grid grid-cols-5 gap-2">
      <button
        v-for="(url, index) in images"
        :key="`${url}-${index}`"
        type="button"
        class="aspect-square overflow-hidden rounded-2xl border transition"
        :class="
          slide === index
            ? 'border-line-strong ring-2 ring-accent/40'
            : 'border-line hover:border-line-strong'
        "
        :aria-label="`Voir l'image ${index + 1}`"
        :aria-current="slide === index ? 'true' : undefined"
        @click="slide = index"
      >
        <AppImage :src="url" :alt="`${product.name} ${index + 1}`" />
      </button>
    </div>

    <div class="mt-4 flex items-center justify-between px-1">
      <p class="text-xs text-faint">Réf. {{ shortId(product.id) }}</p>
      <button
        type="button"
        class="inline-flex items-center gap-2 text-xs font-semibold link-quiet"
        @click="onCopyLink"
      >
        <AppIcon :name="copied ? 'check' : 'copy'" :size="14" />
        {{ copied ? 'Lien copié' : 'Partager' }}
      </button>
    </div>
  </div>
</template>
