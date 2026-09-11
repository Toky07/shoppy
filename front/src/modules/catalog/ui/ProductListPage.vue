<script setup lang="ts">
import { computed, inject } from 'vue'
import { useRoute } from 'vue-router'
import PageStatus from '@/shared/ui/PageStatus.vue'
import Pagination from '@/shared/ui/Pagination.vue'
import { parsePageQuery } from '@/shared/routing/parsePageQuery'
import { parseSearchQuery } from '@/shared/routing/parseSearchQuery'
import { catalogRepositoryKey } from '../application/catalogRepositoryKey'
import { DEFAULT_PRODUCT_LIMIT } from '../application/defaultProductLimit'
import { parseProductSort } from '../application/productSort'
import { useProductList } from '../application/useProductList'
import CatalogToolbar from './CatalogToolbar.vue'
import ProductCard from './ProductCard.vue'

const repository = inject(catalogRepositoryKey)

if (!repository) {
  throw new Error('CatalogRepository is not provided.')
}

const route = useRoute()
const search = computed(() => parseSearchQuery(route.query.q))
const query = computed(() => ({
  page: parsePageQuery(route.query.page),
  limit: DEFAULT_PRODUCT_LIMIT,
  search: search.value || undefined,
  sort: parseProductSort(route.query.sort),
}))
const { status, page, error } = useProductList(repository, query)
</script>

<template>
  <section class="animate-fade-in">
    <div class="mb-8 flex flex-col gap-6">
      <div>
        <h1 class="mb-2 text-4xl font-extrabold tracking-tight text-gray-900">Notre Collection</h1>
        <p class="text-gray-500">Découvrez nos produits soigneusement sélectionnés pour vous.</p>
      </div>
      <CatalogToolbar :total="page?.total" />
    </div>

    <PageStatus :status="status" :error-message="error?.message">
      <template #empty>
        <div class="flex flex-col items-center justify-center py-20 text-center bg-white rounded-2xl border border-gray-100 shadow-sm">
          <div class="h-20 w-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
            <i class="fa-solid fa-box-open text-3xl text-gray-400"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-900">
            {{ search ? 'Aucun résultat' : 'Aucun produit' }}
          </h3>
          <p class="text-gray-500 mt-1">
            {{
              search
                ? `Aucun produit ne correspond à « ${search} ».`
                : 'Revenez plus tard pour découvrir nos nouveautés.'
            }}
          </p>
        </div>
      </template>
      <div v-if="page" class="mt-6 grid gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <ProductCard v-for="product in page.items" :key="product.id" :product="product" />
      </div>
      <div class="mt-12 flex justify-center">
        <Pagination
          v-if="page"
          :page="page.page"
          :limit="page.limit"
          :total="page.total"
        />
      </div>
    </PageStatus>
  </section>
</template>
