<script setup lang="ts">
import { computed, inject } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import EmptyState from '@/shared/ui/EmptyState.vue'
import PageStatus from '@/shared/ui/PageStatus.vue'
import Pagination from '@/shared/ui/Pagination.vue'
import { parsePageQuery } from '@/shared/routing/parsePageQuery'
import { parseSearchQuery } from '@/shared/routing/parseSearchQuery'
import { catalogRepositoryKey } from '../application/catalogRepositoryKey'
import { DEFAULT_PRODUCT_LIMIT } from '../application/defaultProductLimit'
import { parseProductSort } from '../application/productSort'
import { useCatalogView } from '../application/useCatalogView'
import { useProductList } from '../application/useProductList'
import CatalogToolbar from './CatalogToolbar.vue'
import ProductCard from './ProductCard.vue'

const repository = inject(catalogRepositoryKey)

if (!repository) {
  throw new Error('CatalogRepository is not provided.')
}

const route = useRoute()
const { view } = useCatalogView()
const search = computed(() => parseSearchQuery(route.query.q))
const query = computed(() => ({
  page: parsePageQuery(route.query.page),
  limit: DEFAULT_PRODUCT_LIMIT,
  search: search.value || undefined,
  sort: parseProductSort(route.query.sort),
}))
const { status, page, error } = useProductList(repository, query)

const highlights = [
  { icon: 'truck', value: '48 h', label: 'Expédition' },
  { icon: 'refresh', value: '30 j', label: 'Pour changer d\'avis' },
  { icon: 'shield', value: '2 ans', label: 'De garantie' },
] as const
</script>

<template>
  <section class="animate-fade-in">
    <!-- Hero -->
    <div class="mesh grain relative overflow-hidden rounded-panel border border-line bg-surface">
      <div class="relative z-1 grid gap-10 p-7 sm:p-10 lg:grid-cols-[1.25fr_1fr] lg:items-end lg:p-14">
        <div>
          <span class="badge-accent">
            <AppIcon name="sparkles" :size="13" />
            Sélection de saison
          </span>
          <h1 class="display-tight mt-6 text-5xl text-strong sm:text-6xl lg:text-7xl">Notre <span class="text-accent-strong">Collection</span></h1>
          <p class="mt-5 max-w-lg text-base leading-relaxed text-muted">
            Des objets choisis un par un, gardés seulement s'ils tiennent la route. Pas de
            catalogue interminable : juste ce qui mérite d'être acheté.
          </p>
          <div class="mt-8 flex flex-wrap items-center gap-3">
            <RouterLink to="/?sort=newest" class="btn-primary btn-lg">
              Voir les nouveautés
              <AppIcon name="arrow-right" :size="17" />
            </RouterLink>
            <RouterLink to="/favorites" class="btn-outline btn-lg">
              <AppIcon name="heart" :size="16" />
              Ma liste d'envies
            </RouterLink>
          </div>
        </div>

        <dl class="grid grid-cols-3 gap-3">
          <div
            v-for="item in highlights"
            :key="item.label"
            class="rounded-2xl border border-line bg-surface/70 p-4 backdrop-blur-sm"
          >
            <span class="text-accent-strong"><AppIcon :name="item.icon" :size="20" /></span>
            <dt class="numeric mt-3 font-display text-xl font-extrabold text-strong">
              {{ item.value }}
            </dt>
            <dd class="mt-0.5 text-[0.7rem] leading-tight font-medium text-muted">{{ item.label }}</dd>
          </div>
        </dl>
      </div>
    </div>

    <!-- Barre d'outils -->
    <div
      class="sticky top-16 z-30 -mx-5 mt-8 bg-canvas/80 px-5 py-3 backdrop-blur-xl lg:-mx-6 lg:px-6"
    >
      <CatalogToolbar :total="page?.total" />
    </div>

    <PageStatus :status="status" :error-message="error?.message" skeleton="cards">
      <template #empty>
        <EmptyState
          class="mt-6"
          icon="search"
          :title="search ? 'Aucun résultat' : 'Aucun produit'"
          :description="
            search
              ? `Aucun produit ne correspond à « ${search} ».`
              : 'Revenez bientôt : de nouvelles pièces arrivent chaque mois.'
          "
        >
          <RouterLink v-if="search" to="/" class="btn-primary">Voir tout le catalogue</RouterLink>
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
