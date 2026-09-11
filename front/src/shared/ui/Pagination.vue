<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'

const props = defineProps<{
  page: number
  limit: number
  total: number
}>()

const route = useRoute()
const pageCount = computed(() => Math.max(1, Math.ceil(props.total / props.limit)))
const visible = computed(() => props.total > props.limit)

// Générer les numéros de page à afficher (ex: 1, 2, ..., 5, 6, 7, ..., 10)
const pageNumbers = computed(() => {
  const current = props.page
  const last = pageCount.value
  const delta = 1 // Nombre de pages à afficher autour de la page courante
  
  const range = []
  for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
    range.push(i)
  }

  if (current - delta > 2) {
    range.unshift('...')
  }
  if (current + delta < last - 1) {
    range.push('...')
  }

  range.unshift(1)
  if (last !== 1) {
    range.push(last)
  }

  return range
})

function toPage(target: number) {
  return { path: route.path, query: { ...route.query, page: String(target) } }
}
</script>

<template>
  <nav v-if="visible" aria-label="Pagination" class="flex items-center justify-center gap-2 mt-8">
    <!-- Bouton Précédent -->
    <RouterLink
      v-if="page > 1"
      :to="toPage(page - 1)"
      class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-gray-500 border border-gray-200 shadow-sm hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 transition-all group"
      aria-label="Page précédente"
    >
      <i class="fa-solid fa-chevron-left text-xs group-hover:-translate-x-0.5 transition-transform"></i>
    </RouterLink>
    <div v-else class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-50 text-gray-300 border border-gray-100 cursor-not-allowed">
      <i class="fa-solid fa-chevron-left text-xs"></i>
    </div>

    <!-- Numéros de page -->
    <div class="flex items-center gap-1.5 bg-white border border-gray-200 rounded-xl p-1 shadow-sm">
      <template v-for="(item, index) in pageNumbers" :key="index">
        <span v-if="item === '...'" class="flex h-8 w-8 items-center justify-center text-gray-400 text-sm">
          <i class="fa-solid fa-ellipsis"></i>
        </span>
        <RouterLink
          v-else
          :to="toPage(item as number)"
          class="flex h-8 w-8 items-center justify-center rounded-lg text-sm font-bold transition-all"
          :class="[
            item === page 
              ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200' 
              : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
          ]"
          :aria-current="item === page ? 'page' : undefined"
        >
          {{ item }}
        </RouterLink>
      </template>
    </div>

    <!-- Bouton Suivant -->
    <RouterLink
      v-if="page < pageCount"
      :to="toPage(page + 1)"
      class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-gray-500 border border-gray-200 shadow-sm hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 transition-all group"
      aria-label="Page suivante"
    >
      <i class="fa-solid fa-chevron-right text-xs group-hover:translate-x-0.5 transition-transform"></i>
    </RouterLink>
    <div v-else class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-50 text-gray-300 border border-gray-100 cursor-not-allowed">
      <i class="fa-solid fa-chevron-right text-xs"></i>
    </div>
  </nav>
</template>
