<script setup lang="ts">
import { computed, inject, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import PageStatus from '@/shared/ui/PageStatus.vue'
import AddToCartForm from '@/modules/cart/ui/AddToCartForm.vue'
import { catalogRepositoryKey } from '../application/catalogRepositoryKey'
import { useProduct } from '../application/useProduct'
import ProductMedia from './ProductMedia.vue'
import ProductPrice from './ProductPrice.vue'
import ProductReassurance from './ProductReassurance.vue'
import ProductReviews from './ProductReviews.vue'
import ProductTabs from './ProductTabs.vue'
import ProductCard from './ProductCard.vue'
import VariantPicker from './VariantPicker.vue'
import { stockLabel } from './stockLabel'
import type { Product } from '../domain/Product'

const repository = inject(catalogRepositoryKey)

if (!repository) {
  throw new Error('CatalogRepository is not provided.')
}

const route = useRoute()
const router = useRouter()
const productRef = computed(() => String(route.params.slug ?? ''))
const { status, product, error } = useProduct(repository, productRef)
const selectedVariantId = ref('')
const selectedVariant = computed(
  () => product.value?.variants.find((variant) => variant.id === selectedVariantId.value) ?? null,
)
const selectedStock = computed(() => selectedVariant.value?.stock ?? product.value?.stock ?? 0)
const selectedSku = computed(() => selectedVariant.value?.sku ?? product.value?.sku ?? '')
const selectedName = computed(() => {
  const current = product.value
  const variant = selectedVariant.value
  if (!current) {
    return ''
  }
  if (!variant) {
    return current.name
  }
  const options = [variant.size, variant.color].filter((value) => value)
  return options.length > 0 ? `${current.name} — ${options.join(' / ')}` : current.name
})
const related = ref<Product[]>([])
const notFound = computed(() => error.value?.code === 'product_not_found')
const errorMessage = computed(() =>
  notFound.value ? 'Ce produit est introuvable.' : error.value?.message,
)

watch(product, async (current) => {
  if (current !== null && current.variants.length > 0) {
    const available = current.variants.find((variant) => variant.stock > 0) ?? current.variants[0]
    selectedVariantId.value = available?.id ?? ''
  } else {
    selectedVariantId.value = ''
  }

  related.value = []
  if (current !== null) {
    try {
      related.value = await repository.listRelated(current.id)
    } catch {
      related.value = []
    }
  }

  if (current === null || route.params.slug === current.slug) {
    return
  }

  void router.replace({ name: 'product', params: { slug: current.slug } })
})
</script>

<template>
  <section class="animate-fade-in">
    <nav class="mb-8 flex items-center gap-3 text-sm" aria-label="Fil d'Ariane">
      <RouterLink to="/" class="inline-flex items-center gap-2 font-medium link-quiet">
        <AppIcon name="arrow-left" :size="15" />
        Retour au catalogue
      </RouterLink>
      <span v-if="product" class="text-faint" aria-hidden="true">/</span>
      <span v-if="product" class="truncate font-medium text-strong">{{ product.name }}</span>
    </nav>

    <PageStatus
      :status="status === 'ready' ? 'ready' : status"
      :error-message="errorMessage"
      skeleton="detail"
    >
      <article v-if="product" class="grid items-start gap-10 lg:grid-cols-2 lg:gap-16">
        <ProductMedia :product="product" :reference="selectedSku" :stock="selectedStock" />

        <div class="flex flex-col">
          <p class="text-xs font-semibold tracking-[0.12em] text-muted uppercase">
            <RouterLink
              v-if="product.category"
              :to="{ path: '/', query: { category: product.category.slug } }"
              class="link-quiet"
            >
              {{ product.category.name }}
            </RouterLink>
            <span v-if="product.category" class="mx-2 text-faint" aria-hidden="true">·</span>
            <span>{{ stockLabel(selectedStock) }}</span>
          </p>

          <h1 class="display-tight mt-4 text-4xl text-strong sm:text-5xl">{{ product.name }}</h1>

          <div class="mt-6 flex flex-wrap items-baseline gap-4">
            <p class="numeric font-display text-4xl font-extrabold text-strong">
              <ProductPrice :price="product.price" />
            </p>
            <p class="text-xs text-muted">TTC · livraison offerte dès 49 €</p>
          </div>

          <VariantPicker
            v-if="product.variants.length > 0 && selectedVariantId"
            v-model="selectedVariantId"
            :variants="product.variants"
          />

          <div class="panel mt-8 p-5 sm:p-6">
            <AddToCartForm
              :product-id="product.id"
              :variant-id="selectedVariant?.id"
              :stock="selectedStock"
              :name="selectedName"
              :unit-price-cents="product.price.cents"
            />
          </div>

          <ProductTabs :description="product.description" />
          <ProductReassurance />
        </div>
      </article>

      <section v-if="product && related.length > 0" class="mt-16" aria-labelledby="related-products-title">
        <h2 id="related-products-title" class="font-display text-3xl font-extrabold text-strong">
          Produits associés
        </h2>
        <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
          <ProductCard v-for="item in related" :key="item.id" :product="item" />
        </div>
      </section>

      <ProductReviews v-if="product" :product-id="product.id" :product-slug="product.slug" />
    </PageStatus>
  </section>
</template>
