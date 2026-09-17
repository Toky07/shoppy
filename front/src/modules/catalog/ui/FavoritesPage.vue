<script setup lang="ts">
import { inject, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import EmptyState from '@/shared/ui/EmptyState.vue'
import PageHeader from '@/shared/ui/PageHeader.vue'
import PageStatus from '@/shared/ui/PageStatus.vue'
import { toApiError } from '@/shared/http/toApiError'
import { catalogRepositoryKey } from '../application/catalogRepositoryKey'
import { useFavorites } from '../application/useFavorites'
import type { Product } from '../domain/Product'
import ProductCard from './ProductCard.vue'

const repository = inject(catalogRepositoryKey)

if (!repository) {
  throw new Error('CatalogRepository is not provided.')
}

const catalog = repository
const { favorites, count, clear } = useFavorites()
const products = ref<Product[]>([])
const status = ref<'loading' | 'ready' | 'empty' | 'error'>('loading')
const errorMessage = ref<string>()

async function load() {
  errorMessage.value = undefined

  if (favorites.value.length === 0) {
    products.value = []
    status.value = 'empty'
    return
  }

  status.value = 'loading'

  try {
    const loaded = await Promise.all(
      favorites.value.map((id) => catalog.getById(id).catch(() => null)),
    )
    products.value = loaded.filter((product): product is Product => product !== null)
    status.value = products.value.length === 0 ? 'empty' : 'ready'
  } catch (caught) {
    errorMessage.value = toApiError(caught).message
    status.value = 'error'
  }
}

watch(favorites, load, { immediate: true })
</script>

<template>
  <section class="animate-fade-in">
    <PageHeader
      eyebrow="Envies"
      title="Ma liste d'envies"
      icon="heart"
      tone="accent"
      description="Gardée sur cet appareil, sans compte ni e-mail. Ajoutez un produit en touchant le cœur."
    >
      <template #actions>
        <button v-if="count > 0" type="button" class="btn-outline" @click="clear">
          <AppIcon name="trash" :size="16" />
          Vider la liste
        </button>
      </template>
    </PageHeader>

    <div class="mt-10">
      <PageStatus :status="status" :error-message="errorMessage" skeleton="cards">
        <template #empty>
          <EmptyState
            icon="heart"
            title="Votre liste est vide"
            description="Parcourez le catalogue et gardez sous la main les produits qui vous plaisent."
            :heading-level="2"
          >
            <RouterLink to="/" class="btn-primary">
              Découvrir le catalogue
              <AppIcon name="arrow-right" :size="16" />
            </RouterLink>
          </EmptyState>
        </template>

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          <ProductCard v-for="product in products" :key="product.id" :product="product" />
        </div>
      </PageStatus>
    </div>
  </section>
</template>
