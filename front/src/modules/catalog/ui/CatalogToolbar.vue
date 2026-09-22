<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import { parseSearchQuery } from '@/shared/routing/parseSearchQuery'
import {
  DEFAULT_PRODUCT_SORT,
  PRODUCT_SORT_OPTIONS,
  parseProductSort,
  type ProductSort,
} from '../application/productSort'
import { useCatalogView } from '../application/useCatalogView'
import type { Category } from '../domain/Category'

const props = withDefaults(
  defineProps<{
    total?: number
    showView?: boolean
    categories?: Category[]
  }>(),
  { showView: true, categories: () => [] },
)

const route = useRoute()
const router = useRouter()
const { view, setView } = useCatalogView()
const searchInput = ref(parseSearchQuery(route.query.q))
let debounceId: ReturnType<typeof setTimeout> | undefined

watch(
  () => route.query.q,
  (value) => {
    const next = parseSearchQuery(value)
    if (next !== searchInput.value.trim()) {
      searchInput.value = next
    }
  },
)

watch(searchInput, (value) => {
  window.clearTimeout(debounceId)
  debounceId = setTimeout(() => {
    void commitSearch(value)
  }, 300)
})

onBeforeUnmount(() => {
  window.clearTimeout(debounceId)
  window.clearTimeout(priceDebounceId)
})

const sort = computed(() => parseProductSort(route.query.sort))
const minPrice = ref(typeof route.query.min === 'string' ? route.query.min : '')
const maxPrice = ref(typeof route.query.max === 'string' ? route.query.max : '')
const inStockOnly = computed(() => route.query.stock === '1')
const activeCategory = computed(() =>
  typeof route.query.category === 'string' && route.query.category !== '' ? route.query.category : undefined,
)
let priceDebounceId: ReturnType<typeof setTimeout> | undefined
const countLabel = computed(() =>
  props.total === undefined ? '' : `${props.total} produit${props.total > 1 ? 's' : ''}`,
)

async function pushCatalogQuery(patch: {
  q?: string
  sort?: ProductSort
  min?: string
  max?: string
  stock?: boolean
}) {
  const search = patch.q !== undefined ? patch.q.trim() : parseSearchQuery(route.query.q)
  const nextSort = patch.sort ?? sort.value
  const query: Record<string, string> = {}

  for (const [key, value] of Object.entries(route.query)) {
    if (key === 'page' || key === 'q' || key === 'sort' || typeof value !== 'string') {
      continue
    }

    query[key] = value
  }

  if (search !== '') {
    query.q = search
  }

  if (nextSort !== DEFAULT_PRODUCT_SORT) {
    query.sort = nextSort
  }

  if (patch.min !== undefined) {
    if (patch.min === '') {
      delete query.min
    } else {
      query.min = patch.min
    }
  }

  if (patch.max !== undefined) {
    if (patch.max === '') {
      delete query.max
    } else {
      query.max = patch.max
    }
  }

  if (patch.stock !== undefined) {
    if (patch.stock) {
      query.stock = '1'
    } else {
      delete query.stock
    }
  }

  await router.push({ path: route.path, query })
}

async function commitSearch(value: string) {
  const next = value.trim()
  if (next === parseSearchQuery(route.query.q)) {
    return
  }

  await pushCatalogQuery({ q: next })
}

function onSearchSubmit() {
  window.clearTimeout(debounceId)
  void commitSearch(searchInput.value)
}

function onClearSearch() {
  searchInput.value = ''
  window.clearTimeout(debounceId)
  void pushCatalogQuery({ q: '' })
}

function onSortChange(event: Event) {
  const value = (event.target as HTMLSelectElement).value
  void pushCatalogQuery({ sort: parseProductSort(value) })
}

watch(
  () => route.query.min,
  (value) => {
    minPrice.value = typeof value === 'string' ? value : ''
  },
)

watch(
  () => route.query.max,
  (value) => {
    maxPrice.value = typeof value === 'string' ? value : ''
  },
)

watch([minPrice, maxPrice], () => {
  window.clearTimeout(priceDebounceId)
  priceDebounceId = setTimeout(() => {
    const min = String(minPrice.value).trim()
    const max = String(maxPrice.value).trim()
    const currentMin = typeof route.query.min === 'string' ? route.query.min : ''
    const currentMax = typeof route.query.max === 'string' ? route.query.max : ''
    if (min === currentMin && max === currentMax) {
      return
    }

    void pushCatalogQuery({ min, max })
  }, 300)
})

function categoryLocation(slug?: string) {
  const query: Record<string, string> = {}

  for (const [key, value] of Object.entries(route.query)) {
    if (key === 'page' || key === 'category' || typeof value !== 'string') {
      continue
    }

    query[key] = value
  }

  if (slug !== undefined) {
    query.category = slug
  }

  return { path: route.path, query }
}

function onStockChange(event: Event) {
  void pushCatalogQuery({ stock: (event.target as HTMLInputElement).checked })
}
</script>

<template>
  <div class="panel-flat flex flex-col gap-3 p-3">
    <nav v-if="props.categories.length > 0" class="flex flex-wrap gap-2" aria-label="Catégories">
      <RouterLink v-slot="{ href, navigate }" custom :to="categoryLocation()">
        <a
          :href="href"
          class="rounded-full px-3 py-1.5 text-sm font-medium transition-colors"
          :class="activeCategory === undefined ? 'bg-primary text-primary-fg' : 'bg-surface-inset text-muted hover:text-strong'"
          :aria-current="activeCategory === undefined ? 'page' : undefined"
          @click="navigate"
        >
          Tous
        </a>
      </RouterLink>
      <RouterLink
        v-for="category in props.categories"
        :key="category.id"
        v-slot="{ href, navigate }"
        custom
        :to="categoryLocation(category.slug)"
      >
        <a
          :href="href"
          class="rounded-full px-3 py-1.5 text-sm font-medium transition-colors"
          :class="
            activeCategory === category.slug ? 'bg-primary text-primary-fg' : 'bg-surface-inset text-muted hover:text-strong'
          "
          :aria-current="activeCategory === category.slug ? 'page' : undefined"
          @click="navigate"
        >
          {{ category.name }}
        </a>
      </RouterLink>
    </nav>

    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:gap-4">
    <form class="relative min-w-0 flex-1" role="search" @submit.prevent="onSearchSubmit">
      <label class="sr-only" for="catalog-search">Rechercher un produit</label>
      <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-faint">
        <AppIcon name="search" :size="17" />
      </span>
      <input
        id="catalog-search"
        v-model="searchInput"
        type="search"
        name="q"
        placeholder="Rechercher un produit..."
        class="field rounded-full border-transparent bg-surface-inset py-3 pl-11.5 pr-11"
      />
      <button
        v-if="searchInput"
        type="button"
        class="absolute top-1/2 right-2.5 flex size-7 -translate-y-1/2 items-center justify-center rounded-full text-faint transition-colors hover:bg-surface-muted hover:text-strong"
        aria-label="Effacer la recherche"
        @click="onClearSearch"
      >
        <AppIcon name="close" :size="14" />
      </button>
    </form>

    <div class="flex flex-wrap items-end gap-3">
      <div>
        <label class="field-label" for="catalog-min-price">Prix min (€)</label>
        <input
          id="catalog-min-price"
          v-model="minPrice"
          type="text"
          inputmode="numeric"
          autocomplete="off"
          class="field w-28"
        />
      </div>
      <div>
        <label class="field-label" for="catalog-max-price">Prix max (€)</label>
        <input
          id="catalog-max-price"
          v-model="maxPrice"
          type="text"
          inputmode="numeric"
          autocomplete="off"
          class="field w-28"
        />
      </div>
      <label class="mb-3 flex items-center gap-2 text-sm font-medium text-strong">
        <input type="checkbox" class="size-4" :checked="inStockOnly" @change="onStockChange" />
        En stock seulement
      </label>
    </div>

    <div class="flex flex-wrap items-center gap-3">
      <p v-if="countLabel" class="numeric hidden px-1 text-xs font-medium text-muted sm:block">
        {{ countLabel }}
      </p>

      <div class="relative inline-flex min-w-48 items-center">
        <label class="sr-only" for="catalog-sort">Trier les produits</label>
        <span class="pointer-events-none absolute left-4 text-faint">
          <AppIcon name="sliders" :size="16" />
        </span>
        <select
          id="catalog-sort"
          class="field appearance-none rounded-full border-transparent bg-surface-inset py-3 pr-10 pl-11 font-medium"
          :value="sort"
          @change="onSortChange"
        >
          <option v-for="option in PRODUCT_SORT_OPTIONS" :key="option.value" :value="option.value">
            {{ option.label }}
          </option>
        </select>
        <span class="pointer-events-none absolute right-4 text-faint">
          <AppIcon name="chevron-down" :size="14" />
        </span>
      </div>

      <div
        v-if="props.showView"
        class="hidden items-center gap-0.5 rounded-full border border-line bg-surface-inset p-1 sm:flex"
        role="group"
        aria-label="Affichage"
      >
        <button
          type="button"
          class="inline-flex size-8 items-center justify-center rounded-full transition-colors"
          :class="view === 'grid' ? 'bg-primary text-primary-fg' : 'text-faint hover:text-strong'"
          aria-label="Affichage en grille"
          :aria-pressed="view === 'grid'"
          @click="setView('grid')"
        >
          <AppIcon name="grid" :size="15" />
        </button>
        <button
          type="button"
          class="inline-flex size-8 items-center justify-center rounded-full transition-colors"
          :class="view === 'list' ? 'bg-primary text-primary-fg' : 'text-faint hover:text-strong'"
          aria-label="Affichage en liste"
          :aria-pressed="view === 'list'"
          @click="setView('list')"
        >
          <AppIcon name="list" :size="15" />
        </button>
      </div>
    </div>
    </div>
  </div>
</template>
