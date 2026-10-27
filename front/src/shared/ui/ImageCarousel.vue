<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import type { ImageVariant } from '@/shared/media/imageDerivatives'
import { useI18n } from '@/shared/i18n/useI18n'
import AppIcon from './AppIcon.vue'
import AppImage from './AppImage.vue'
import { useInViewport } from './useInViewport'

const { t } = useI18n()

const props = withDefaults(
  defineProps<{
    images: string[]
    alt: string
    variant?: ImageVariant
    showArrows?: boolean
    interactive?: boolean
  }>(),
  {
    variant: 'card',
    showArrows: true,
    interactive: false,
  },
)

const index = defineModel<number>({ default: 0 })
const direction = ref<'next' | 'prev'>('next')
const root = ref<HTMLElement | null>(null)
const inViewport = useInViewport(root, { rootMargin: '80px' })

/** Slide indices whose image URL may be fetched. */
const fetchedSlides = ref<Set<number>>(new Set())

const sources = computed(() => props.images.filter((url) => url !== ''))
const current = computed(() => sources.value[index.value] ?? null)
const canNavigate = computed(() => sources.value.length > 1)
const currentAlt = computed(() =>
  canNavigate.value ? `${props.alt} (${index.value + 1}/${sources.value.length})` : props.alt,
)
const slideName = computed(() => (direction.value === 'next' ? 'media-slide-next' : 'media-slide-prev'))
const fetchCurrent = computed(() => inViewport.value && fetchedSlides.value.has(index.value))

function markSlideForFetch(slide: number) {
  if (slide < 0 || slide >= sources.value.length) {
    return
  }

  if (fetchedSlides.value.has(slide)) {
    return
  }

  fetchedSlides.value = new Set(fetchedSlides.value).add(slide)
}

watch(sources, (next) => {
  fetchedSlides.value = new Set()
  if (index.value >= next.length) {
    index.value = 0
  }
  if (inViewport.value && next.length > 0) {
    markSlideForFetch(index.value)
  }
})

watch(inViewport, (visible) => {
  if (visible && sources.value.length > 0) {
    markSlideForFetch(index.value)
  }
})

watch(index, (next, previous) => {
  if (inViewport.value) {
    markSlideForFetch(next)
  }

  if (previous === undefined || next === previous) {
    return
  }

  const length = sources.value.length
  if (length === 0) {
    return
  }

  if (next === (previous + 1) % length) {
    direction.value = 'next'
  } else if (next === (previous - 1 + length) % length) {
    direction.value = 'prev'
  } else {
    direction.value = next > previous ? 'next' : 'prev'
  }
})

function go(delta: number) {
  if (!canNavigate.value) {
    return
  }

  index.value = (index.value + delta + sources.value.length) % sources.value.length
}

function select(next: number) {
  index.value = next
}

const SWIPE_THRESHOLD = 40
const SWIPE_LOCK = 8

let pointerId: number | null = null
let startX = 0
let startY = 0
let axis: 'x' | 'y' | null = null

function capturePointer(target: EventTarget | null, id: number) {
  if (!(target instanceof HTMLElement) || typeof target.setPointerCapture !== 'function') {
    return
  }

  try {
    target.setPointerCapture(id)
  } catch {
    /* jsdom and some browsers reject capture before a pointer is down. */
  }
}

function releasePointer(target: EventTarget | null, id: number) {
  if (!(target instanceof HTMLElement) || typeof target.releasePointerCapture !== 'function') {
    return
  }

  try {
    if (target.hasPointerCapture(id)) {
      target.releasePointerCapture(id)
    }
  } catch {
    /* capture may already have been released */
  }
}

function resetSwipe() {
  pointerId = null
  axis = null
}

function onPointerDown(event: PointerEvent) {
  if (!props.interactive || !canNavigate.value) {
    return
  }

  if (event.pointerType === 'mouse' && event.button !== 0) {
    return
  }

  pointerId = event.pointerId
  startX = event.clientX
  startY = event.clientY
  axis = null
  capturePointer(event.currentTarget, event.pointerId)
}

function onPointerMove(event: PointerEvent) {
  if (pointerId !== event.pointerId) {
    return
  }

  const dx = event.clientX - startX
  const dy = event.clientY - startY

  if (axis === null && (Math.abs(dx) > SWIPE_LOCK || Math.abs(dy) > SWIPE_LOCK)) {
    axis = Math.abs(dx) > Math.abs(dy) ? 'x' : 'y'
  }

  if (axis === 'x' && event.cancelable) {
    event.preventDefault()
  }
}

function commitSwipe(event: PointerEvent) {
  if (pointerId !== event.pointerId) {
    return
  }

  const dx = event.clientX - startX
  const dy = event.clientY - startY
  const horizontal = axis === 'x' || (axis === null && Math.abs(dx) > Math.abs(dy))

  releasePointer(event.currentTarget, event.pointerId)
  resetSwipe()

  if (!horizontal) {
    return
  }

  if (dx > SWIPE_THRESHOLD) {
    go(-1)
  } else if (dx < -SWIPE_THRESHOLD) {
    go(1)
  }
}

function onPointerCancel(event: PointerEvent) {
  if (pointerId !== event.pointerId) {
    return
  }

  releasePointer(event.currentTarget, event.pointerId)
  resetSwipe()
}
</script>

<template>
  <div
    ref="root"
    class="relative h-full w-full"
    role="group"
    :aria-roledescription="canNavigate ? 'carrousel' : undefined"
    :aria-label="alt"
  >
    <div
      class="carousel-track relative h-full w-full overflow-hidden select-none"
      @pointerdown="onPointerDown"
      @pointermove="onPointerMove"
      @pointerup="commitSwipe"
      @pointercancel="onPointerCancel"
    >
      <Transition :name="slideName">
        <div :key="index" class="absolute inset-0">
          <AppImage
            :src="current"
            :alt="currentAlt"
            :variant="variant"
            :fetch="fetchCurrent"
            :viewport="false"
            loading="lazy"
          />
        </div>
      </Transition>
    </div>

    <div
      v-if="canNavigate && showArrows"
      class="pointer-events-none absolute inset-0 z-20 flex items-center justify-between px-2"
    >
      <button
        type="button"
        class="pointer-events-auto flex size-8 items-center justify-center rounded-full bg-canvas/90 text-strong shadow-lifted transition duration-200 hover:bg-canvas active:scale-95"
        :aria-label="t('common.previousImage')"
        @click.stop="go(-1)"
      >
        <AppIcon name="chevron-left" :size="16" />
      </button>
      <button
        type="button"
        class="pointer-events-auto flex size-8 items-center justify-center rounded-full bg-canvas/90 text-strong shadow-lifted transition duration-200 hover:bg-canvas active:scale-95"
        :aria-label="t('common.nextImage')"
        @click.stop="go(1)"
      >
        <AppIcon name="chevron-right" :size="16" />
      </button>
    </div>

    <div
      v-if="canNavigate"
      class="absolute inset-x-0 bottom-3 z-20 flex justify-center"
      role="tablist"
      :aria-label="t('common.productImages')"
    >
      <div class="flex gap-1.5 rounded-full bg-black/40 px-2 py-1.5 backdrop-blur-sm">
        <button
          v-for="(_, slide) in sources"
          :key="slide"
          type="button"
          class="h-1.5 rounded-full transition-all duration-300"
          :class="slide === index ? 'w-4 bg-white' : 'w-1.5 bg-white/45 hover:bg-white/80'"
          role="tab"
          :aria-selected="slide === index"
          :aria-label="t('common.goToImage', { index: slide + 1 })"
          @click.stop="select(slide)"
        />
      </div>
    </div>

    <p v-if="canNavigate" class="sr-only" aria-live="polite">
      {{ t('common.imageOf', { page: index + 1, count: sources.length }) }}
    </p>
  </div>
</template>

<style>
.carousel-track {
  touch-action: pan-y;
}

.carousel-track img {
  pointer-events: none;
  -webkit-user-drag: none;
  user-select: none;
}

.media-slide-next-enter-active,
.media-slide-next-leave-active,
.media-slide-prev-enter-active,
.media-slide-prev-leave-active {
  transition:
    transform 0.5s cubic-bezier(0.22, 1, 0.36, 1),
    opacity 0.4s ease;
}

.media-slide-next-leave-active,
.media-slide-prev-leave-active {
  position: absolute;
  inset: 0;
}

.media-slide-next-enter-from {
  transform: translateX(16%);
  opacity: 0;
}

.media-slide-next-leave-to {
  transform: translateX(-16%);
  opacity: 0;
}

.media-slide-prev-enter-from {
  transform: translateX(-16%);
  opacity: 0;
}

.media-slide-prev-leave-to {
  transform: translateX(16%);
  opacity: 0;
}
</style>
