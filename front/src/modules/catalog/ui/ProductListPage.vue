<script setup lang="ts">
import { computed, inject, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import EmptyState from '@/shared/ui/EmptyState.vue'
import PageStatus from '@/shared/ui/PageStatus.vue'
import Pagination from '@/shared/ui/Pagination.vue'
import { parsePageQuery } from '@/shared/routing/parsePageQuery'
import { parseSearchQuery } from '@/shared/routing/parseSearchQuery'
import { catalogCentsFromEuros, isInStockQuery } from '../application/catalogFilters'
import { catalogRepositoryKey } from '../application/catalogRepositoryKey'
import { DEFAULT_PRODUCT_LIMIT } from '../application/defaultProductLimit'
import { parseProductSort } from '../application/productSort'
import type { Category } from '../domain/Category'
import { useCatalogView } from '../application/useCatalogView'
import { useProductList } from '../application/useProductList'
import CatalogHero from './CatalogHero.vue'
import CatalogToolbar from './CatalogToolbar.vue'
import ProductCard from './ProductCard.vue'

const repository = inject(catalogRepositoryKey)

if (!repository) {
  throw new Error('CatalogRepository is not provided.')
}

const route = useRoute();
const { view } = useCatalogView();
const categories = ref<Category[]>([]);
void repository.listCategories().then(
  (items) => {
    categories.value = items
  },
  () => {
    categories.value = []
  },
);
const search = computed(() => parseSearchQuery(route.query.q));
const categorySlug = computed(() =>
  typeof route.query.category === 'string' && route.query.category !== '' ? route.query.category : undefined,
);
const minPriceCents = computed(() => catalogCentsFromEuros(route.query.min));
const maxPriceCents = computed(() => catalogCentsFromEuros(route.query.max));
const inStockOnly = computed(() => isInStockQuery(route.query.stock));
const filtered = computed(
  () =>
    search.value !== '' ||
    categorySlug.value !== undefined ||
    minPriceCents.value !== undefined ||
    maxPriceCents.value !== undefined ||
    inStockOnly.value,
);
const query = computed(() => ({
  page: parsePageQuery(route.query.page),
  limit: DEFAULT_PRODUCT_LIMIT,
  search: search.value || undefined,
  sort: parseProductSort(route.query.sort),
  minPriceCents: minPriceCents.value,
  maxPriceCents: maxPriceCents.value,
  inStockOnly: inStockOnly.value || undefined,
  categorySlug: categorySlug.value,
}));
const { status, page, error } = useProductList(repository, query);
</script>

<template>
  <section class="animate-fade-in">
    <CatalogHero />

    <div
      class="sticky top-16 z-30 -mx-5 mt-8 bg-canvas/80 px-5 py-3 backdrop-blur-xl lg:-mx-6 lg:px-6"
    >
      <CatalogToolbar :total="page?.total" :categories="categories" />
    </div>

    <PageStatus :status="status" :error-message="error?.message" skeleton="cards">
      <template #empty>
        <EmptyState
          class="mt-6"
          icon="search"
          :title="filtered ? 'Aucun résultat' : 'Aucun produit'"
          :description="
            search
              ? `Aucun produit ne correspond à « ${search} ».`
              : filtered
                ? 'Aucun produit ne correspond à ces filtres.'
                : 'Revenez bientôt : de nouvelles pièces arrivent chaque mois.'
          "
        >
          <RouterLink v-if="filtered" to="/" class="btn-primary">Voir tout le catalogue</RouterLink>
        </EmptyState>
      </template>

      <div
        v-if="page"
        class="mt-6"
        :class="
          view === 'list'
            ? 'flex flex-col gap-3'
            : 'grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4'
        "
      >
        <ProductCard
          v-for="product in page.items"
          :key="product.id"
          :product="product"
          :variant="view"
        />
      </div>

      <div v-if="page" class="mt-12 flex justify-center">
        <Pagination :page="page.page" :limit="page.limit" :total="page.total" />
      </div>
    </PageStatus>
  </section>
</template>
