<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import AppIcon from './AppIcon.vue'

const props = defineProps<{
  page: number
  limit: number
  total: number
}>()

const route = useRoute()
const pageCount = computed(() => Math.max(1, Math.ceil(props.total / props.limit)))
const visible = computed(() => props.total > props.limit)

/** 1 … 4 5 6 … 12 */
const pageNumbers = computed<(number | 'gap')[]>(() => {
  const current = props.page
  const last = pageCount.value
  const items: (number | 'gap')[] = [1]

  for (let index = Math.max(2, current - 1); index <= Math.min(last - 1, current + 1); index += 1) {
    items.push(index)
  }

  if (current - 1 > 2) {
    items.splice(1, 0, 'gap')
  }
  if (current + 1 < last - 1) {
    items.push('gap')
  }
  if (last !== 1) {
    items.push(last)
  }

  return items
})

function toPage(target: number) {
  return { path: route.path, query: { ...route.query, page: String(target) } }
}
</script>

<template>
  <nav v-if="visible" aria-label="Pagination" class="flex flex-col items-center gap-4">
    <div class="flex items-center gap-2">
      <RouterLink
        v-if="page > 1"
        :to="toPage(page - 1)"
        class="btn-outline size-10 p-0"
        aria-label="Page précédente"
      >
        <AppIcon name="chevron-left" :size="16" />
      </RouterLink>
      <span v-else class="btn size-10 border border-line p-0 text-faint" aria-hidden="true">
        <AppIcon name="chevron-left" :size="16" />
      </span>

      <div class="flex items-center gap-1 rounded-full border border-line bg-surface p-1">
        <template v-for="(item, index) in pageNumbers" :key="index">
          <span v-if="item === 'gap'" class="px-1.5 text-faint" aria-hidden="true">…</span>
          <RouterLink
            v-else
            :to="toPage(item)"
            class="numeric inline-flex size-8 items-center justify-center rounded-full text-sm font-semibold transition-colors"
            :class="
              item === page
                ? 'bg-primary text-primary-fg'
                : 'text-muted hover:bg-surface-muted hover:text-strong'
            "
            :aria-current="item === page ? 'page' : undefined"
            >{{ item }}</RouterLink
          >
        </template>
      </div>

      <RouterLink
        v-if="page < pageCount"
        :to="toPage(page + 1)"
        class="btn-outline size-10 p-0"
        aria-label="Page suivante"
      >
        <AppIcon name="chevron-right" :size="16" />
      </RouterLink>
      <span v-else class="btn size-10 border border-line p-0 text-faint" aria-hidden="true">
        <AppIcon name="chevron-right" :size="16" />
      </span>
    </div>

    <p class="numeric text-xs text-muted">Page {{ page }} sur {{ pageCount }} · {{ total }} résultats</p>
  </nav>
</template>
