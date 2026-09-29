<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import AppIcon from './AppIcon.vue'
import AppSpinner from './AppSpinner.vue'
import { useInViewport } from './useInViewport'

const props = withDefaults(
  defineProps<{
    src: string | null
    alt: string
    loading?: 'lazy' | 'eager'
    /** When false, the image URL is not requested (placeholder only). */
    fetch?: boolean
    /** When true, fetching starts only after the element enters the viewport. */
    viewport?: boolean
  }>(),
  {
    loading: 'lazy',
    fetch: true,
    viewport: true,
  },
)

const root = ref<HTMLElement | null>(null)
const inViewport = useInViewport(root, { rootMargin: '120px' })

const loaded = ref(false)
const failed = ref(false)

const shouldFetch = computed(() => {
  if (!props.fetch || props.src === null || props.src === '') {
    return false
  }

  if (props.viewport) {
    return inViewport.value
  }

  return true
})

const isLoading = computed(() => shouldFetch.value && !loaded.value && !failed.value)
const showPlaceholderIcon = computed(
  () => !props.src || failed.value || (!shouldFetch.value && props.src !== null && props.src !== ''),
)

watch(
  () => props.src,
  () => {
    loaded.value = false
    failed.value = false
  },
)

function onLoad() {
  loaded.value = true
}

function onError() {
  failed.value = true
  loaded.value = false
}
</script>

<template>
  <div ref="root" class="relative h-full w-full overflow-hidden bg-surface-inset">
    <img
      v-if="shouldFetch && !failed"
      :src="src ?? undefined"
      :alt="alt"
      :loading="loading"
      decoding="async"
      class="h-full w-full object-cover transition-opacity duration-500 ease-out"
      :class="loaded ? 'opacity-100' : 'opacity-0'"
      @load="onLoad"
      @error="onError"
    />

    <Transition
      enter-active-class="transition-opacity duration-300 ease-out"
      leave-active-class="transition-opacity duration-300 ease-out"
      enter-from-class="opacity-0"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isLoading"
        class="mesh absolute inset-0 flex items-center justify-center"
        aria-hidden="true"
      >
        <AppSpinner :label="`Chargement de ${alt}`" />
      </div>
    </Transition>

    <div
      v-if="showPlaceholderIcon"
      class="mesh absolute inset-0 flex flex-col items-center justify-center gap-2 text-faint"
      role="img"
      :aria-label="alt"
    >
      <AppIcon name="image" :size="26" />
    </div>
  </div>
</template>
