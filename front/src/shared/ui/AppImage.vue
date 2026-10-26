<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { imageSizes, imageSrc, imageSrcSet, type ImageVariant } from '@/shared/media/imageDerivatives'
import AppIcon from './AppIcon.vue'
import AppSpinner from './AppSpinner.vue'
import { useInViewport } from './useInViewport'

const props = withDefaults(
  defineProps<{
    src: string | null
    alt: string
    loading?: 'lazy' | 'eager'
    variant?: ImageVariant
    /** When false, the image URL is not requested (placeholder only). */
    fetch?: boolean
    /** When true, fetching starts only after the element enters the viewport. */
    viewport?: boolean
  }>(),
  {
    loading: 'lazy',
    variant: 'card',
    fetch: true,
    viewport: true,
  },
)

const root = ref<HTMLElement | null>(null)
const inViewport = useInViewport(root, { rootMargin: '120px' })

const loaded = ref(false)
const failed = ref(false)
const useOriginal = ref(false)

const resolvedSrc = computed(() => {
  if (props.src === null || props.src === '') {
    return null
  }

  return useOriginal.value ? props.src : imageSrc(props.src, props.variant)
})

const srcset = computed(() =>
  props.src === null || useOriginal.value ? undefined : imageSrcSet(props.src),
)

const sizes = computed(() => (srcset.value === undefined ? undefined : imageSizes(props.variant)))

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
    useOriginal.value = false
  },
)

function onLoad() {
  loaded.value = true
}

function onError() {
  if (!useOriginal.value && props.src !== null && resolvedSrc.value !== props.src) {
    useOriginal.value = true
    loaded.value = false
    failed.value = false
    return
  }

  failed.value = true
  loaded.value = false
}
</script>

<template>
  <div ref="root" class="relative h-full w-full overflow-hidden bg-surface-inset">
    <img
      v-if="shouldFetch && !failed"
      :src="resolvedSrc ?? undefined"
      :srcset="srcset"
      :sizes="sizes"
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
