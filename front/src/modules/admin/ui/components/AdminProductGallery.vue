<script setup lang="ts">
import { toRef } from 'vue'
import { useI18n } from '@/shared/i18n/useI18n'
import { imageSrc } from '@/shared/media/imageDerivatives'
import AppIcon from '@/shared/ui/AppIcon.vue'
import AppImage from '@/shared/ui/AppImage.vue'
import { useGalleryDrag } from '../lib/useGalleryDrag'

const { t } = useI18n()

export type GalleryImage = {
  key: string
  url: string
  id: string | null
  file?: File
}

const props = defineProps<{
  images: GalleryImage[]
  pending: boolean
  queued: boolean
}>()

const emit = defineEmits<{
  add: [files: File[]]
  remove: [index: number]
  move: [index: number, direction: -1 | 1]
  reorder: [from: number, to: number]
  'reorder-end': [changed: boolean]
}>()

const { list, ghost, activeKey, onItemPointerDown } = useGalleryDrag({
  images: toRef(props, 'images'),
  disabled: () => props.pending,
  reorder: (from, to) => emit('reorder', from, to),
  commit: (changed) => emit('reorder-end', changed),
})

function onFiles(event: Event) {
  const input = event.target
  if (!(input instanceof HTMLInputElement) || input.files === null) {
    return
  }
  emit('add', [...input.files])
  input.value = ''
}
</script>

<template>
  <section class="border-t border-line pt-8">
    <div class="flex flex-wrap items-end justify-between gap-3">
      <div>
        <p class="text-[0.7rem] font-semibold tracking-[0.14em] text-muted uppercase">{{ t('admin.visuals') }}</p>
        <p class="mt-2 text-sm text-muted">
          {{ queued ? t('admin.galleryQueued') : t('admin.galleryHint') }}
        </p>
      </div>
      <label class="btn-outline cursor-pointer">
        <AppIcon name="image" :size="15" />
        {{ t('admin.addImages') }}
        <input
          type="file"
          accept="image/jpeg,image/png,image/webp,image/gif"
          multiple
          class="sr-only"
          :disabled="pending"
          @change="onFiles"
        />
      </label>
    </div>

    <p v-if="images.length === 0" class="mt-4 text-sm text-faint">{{ t('admin.noImages') }}</p>

    <div v-else ref="list" data-gallery-list>
      <TransitionGroup
        tag="ul"
        class="mt-4 grid gap-3 sm:grid-cols-3"
        move-class="gallery-tile-move"
        enter-active-class="gallery-tile-enter"
        enter-from-class="gallery-tile-enter-from"
      >
        <li
          v-for="(image, index) in images"
          :key="image.key"
          :data-gallery-item="image.key"
          class="gallery-tile touch-none rounded-2xl border border-line bg-surface-muted"
          :class="
            activeKey === image.key
              ? 'border-dashed border-accent bg-accent-soft/60 shadow-none'
              : 'cursor-grab shadow-soft hover:border-line-strong'
          "
          :aria-label="t('admin.dragImage', { index: index + 1 })"
          @pointerdown="onItemPointerDown($event, image.key)"
        >
          <div class="relative aspect-square overflow-hidden rounded-t-2xl">
            <AppImage
              v-show="activeKey !== image.key"
              :src="image.url"
              :alt="t('admin.imageAlt', { index: index + 1 })"
              variant="card"
              :viewport="false"
            />
            <div
              v-show="activeKey !== image.key"
              class="pointer-events-none absolute top-2 left-2 flex size-8 items-center justify-center rounded-full bg-canvas/80 text-strong shadow-soft"
              aria-hidden="true"
            >
              <AppIcon name="grip" :size="14" />
            </div>
          </div>
          <div data-gallery-no-drag class="flex items-center justify-between gap-1 p-2">
            <button
              type="button"
              class="btn-outline size-9 shrink-0 p-0"
              :disabled="pending || index === 0"
              :aria-label="t('admin.moveImageBack', { index: index + 1 })"
              @click="emit('move', index, -1)"
            >
              <AppIcon name="chevron-left" :size="14" />
            </button>
            <button
              type="button"
              class="btn-outline size-9 shrink-0 p-0"
              :disabled="pending || index === images.length - 1"
              :aria-label="t('admin.moveImageForward', { index: index + 1 })"
              @click="emit('move', index, 1)"
            >
              <AppIcon name="chevron-right" :size="14" />
            </button>
            <button
              type="button"
              class="btn-danger size-9 shrink-0 p-0"
              :disabled="pending"
              :aria-label="t('admin.removeImage', { index: index + 1 })"
              @click="emit('remove', index)"
            >
              <AppIcon name="trash" :size="14" />
            </button>
          </div>
        </li>
      </TransitionGroup>
    </div>

    <Teleport to="body">
      <div
        v-if="ghost"
        class="pointer-events-none fixed top-0 left-0 z-[70]"
        :style="{
          width: `${ghost.width}px`,
          height: `${ghost.height}px`,
          transform: `translate3d(${ghost.x}px, ${ghost.y}px, 0)`,
          transition: ghost.settling ? 'transform 0.28s cubic-bezier(0.22, 1, 0.36, 1)' : 'none',
        }"
      >
        <div
          class="flex h-full origin-center flex-col overflow-hidden rounded-2xl border border-accent bg-surface shadow-float transition-transform duration-200 ease-[cubic-bezier(0.22,1,0.36,1)]"
          :class="ghost.lifted && !ghost.settling ? 'scale-105 rotate-2' : 'scale-100 rotate-0'"
        >
          <img
            :src="imageSrc(ghost.url, 'card')"
            alt=""
            draggable="false"
            class="aspect-square w-full object-cover"
          />
          <div class="flex-1 bg-surface-muted" />
        </div>
      </div>
    </Teleport>
  </section>
</template>

<style scoped>
.gallery-tile-move {
  transition: transform 0.38s cubic-bezier(0.22, 1, 0.36, 1);
}

.gallery-tile-enter {
  transition:
    opacity 0.28s ease,
    transform 0.38s cubic-bezier(0.22, 1, 0.36, 1);
}

.gallery-tile-enter-from {
  opacity: 0;
  transform: scale(0.94);
}

@media (prefers-reduced-motion: reduce) {
  .gallery-tile-move,
  .gallery-tile-enter {
    transition: none;
  }
}
</style>

<style>
html.gallery-dragging,
html.gallery-dragging * {
  cursor: grabbing !important;
  user-select: none !important;
}
</style>
