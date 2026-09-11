<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  DEFAULT_PRODUCT_SORT,
  PRODUCT_SORT_OPTIONS,
  parseProductSort,
  type ProductSort,
} from '../application/productSort'
import { parseSearchQuery } from '@/shared/routing/parseSearchQuery'

const props = defineProps<{
  total?: number
}>()

const route = useRoute()
const router = useRouter()
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
})

const sort = computed(() => parseProductSort(route.query.sort))

async function pushCatalogQuery(patch: { q?: string; sort?: ProductSort }) {
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
</script>

<template>
  <div class="flex w-full flex-col gap-3 lg:flex-row lg:items-center">
    <form class="relative flex-1" role="search" @submit.prevent="onSearchSubmit">
      <label class="sr-only" for="catalog-search">Rechercher un produit</label>
      <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
      <input
        id="catalog-search"
        v-model="searchInput"
        type="search"
        name="q"
        placeholder="Rechercher un produit..."
        class="w-full rounded-2xl border border-gray-200 bg-white py-3 pl-11 pr-12 text-sm text-gray-900 shadow-sm outline-none transition-all placeholder:text-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10"
      />
      <button
        v-if="searchInput"
        type="button"
        class="absolute right-3 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-700"
        aria-label="Effacer la recherche"
        @click="onClearSearch"
      >
        <i class="fa-solid fa-xmark text-sm"></i>
      </button>
    </form>

    <div class="flex items-center gap-3">
      <div class="relative inline-flex min-w-[13rem] items-center">
        <label class="sr-only" for="catalog-sort">Trier les produits</label>
        <i class="fa-solid fa-arrow-down-wide-short pointer-events-none absolute left-4 text-gray-400"></i>
        <select
          id="catalog-sort"
          class="w-full appearance-none rounded-2xl border border-gray-200 bg-white py-3 pl-11 pr-10 text-sm font-medium text-gray-700 shadow-sm outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10"
          :value="sort"
          @change="onSortChange"
        >
          <option v-for="option in PRODUCT_SORT_OPTIONS" :key="option.value" :value="option.value">
            {{ option.label }}
          </option>
        </select>
        <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 text-xs text-gray-400"></i>
      </div>

      <p v-if="props.total !== undefined" class="hidden whitespace-nowrap text-sm font-medium text-gray-500 sm:block">
        {{ props.total }} produit{{ props.total > 1 ? 's' : '' }}
      </p>
    </div>
  </div>
</template>
