<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import AppIcon from './AppIcon.vue'
import AppImage from './AppImage.vue'

const props = withDefaults(
  defineProps<{
    images: string[]
    alt: string
    showArrows?: boolean
    eager?: boolean
    interactive?: boolean
  }>(),
  {
    showArrows: true,
    eager: false,
    interactive: false,
  },
)

const index = defineModel<number>({ default: 0 })
const direction = ref<'next' | 'prev'>('next')

const sources = computed(() => props.images.filter((url) => url !== ''))
const current = computed(() => sources.value[index.value] ?? null)
const canNavigate = computed(() => sources.value.length > 1)
const currentLoading = computed(() => (props.eager || index.value > 0 ? 'eager' : 'lazy'))
const currentAlt = computed(() =>
  canNavigate.value ? `${props.alt} (${index.value + 1}/${sources.value.length})` : props.alt,
)
const slideName = computed(() => (direction.value === 'next' ? 'media-slide-next' : 'media-slide-prev'))

watch(sources, (next) => {
  if (index.value >= next.length) {
    index.value = 0
  }
})

watch(index, (next, previous) => {
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

let pointerX: number | null = null

function onPointerDown(event: PointerEvent) {
  if (!props.interactive || !canNavigate.value) {
    return
  }

  pointerX = event.clientX
}

function onPointerUp(event: PointerEvent) {
  if (pointerX === null) {
    return
  }

  const dx = event.clientX - pointerX
  pointerX = null

  if (dx > 40) {
    go(-1)
  } else if (dx < -40) {
    go(1)
  }
}
</script>

<template>
  <div
    class="relative h-full w-full"
    role="group"
    :aria-roledescription="canNavigate ? 'carrousel' : undefined"
    :aria-label="alt"
  >
    <div class="relative h-full w-full overflow-hidden" @pointerdown="onPointerDown" @pointerup="onPointerUp">
      <Transition :name="slideName">
        <div :key="index" class="absolute inset-0">
          <AppImage :src="current" :alt="currentAlt" :loading="currentLoading" />
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
        aria-label="Image précédente"
        @click.stop="go(-1)"
      >
        <AppIcon name="chevron-left" :size="16" />
      </button>
      <button
        type="button"
        class="pointer-events-auto flex size-8 items-center justify-center rounded-full bg-canvas/90 text-strong shadow-lifted transition duration-200 hover:bg-canvas active:scale-95"
        aria-label="Image suivante"
        @click.stop="go(1)"
      >
        <AppIcon name="chevron-right" :size="16" />
      </button>
    </div>

    <div
      v-if="canNavigate"
      class="absolute inset-x-0 bottom-3 z-20 flex justify-center"
      role="tablist"
      aria-label="Images du produit"
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
          :aria-label="`Aller à l'image ${slide + 1}`"
          @click.stop="select(slide)"
        />
      </div>
    </div>

    <p v-if="canNavigate" class="sr-only" aria-live="polite">
      Image {{ index + 1 }} sur {{ sources.length }}
    </p>
  </div>
</template>

<style>
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

