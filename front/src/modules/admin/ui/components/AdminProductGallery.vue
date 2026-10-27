<script setup lang="ts">
import { useI18n } from '@/shared/i18n/useI18n'
import AppIcon from '@/shared/ui/AppIcon.vue'
import AppImage from '@/shared/ui/AppImage.vue'

const { t } = useI18n()

export type GalleryImage = {
  key: string
  url: string
  id: string | null
  file?: File
}

defineProps<{
  images: GalleryImage[]
  pending: boolean
  queued: boolean
}>()

const emit = defineEmits<{
  add: [files: File[]]
  remove: [index: number]
  move: [index: number, direction: -1 | 1]
}>()

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

    <ul v-else class="mt-4 grid gap-3 sm:grid-cols-3">
      <li v-for="(image, index) in images" :key="image.key" class="overflow-hidden rounded-2xl border border-line bg-surface-muted">
        <AppImage :src="image.url" :alt="t('admin.imageAlt', { index: index + 1 })" variant="card" class="aspect-square w-full" />
        <div class="flex items-center justify-between gap-1 p-2">
          <button
            type="button"
            class="btn-outline btn-sm"
            :disabled="pending || index === 0"
            :aria-label="t('admin.moveImageBack', { index: index + 1 })"
            @click="emit('move', index, -1)"
          >
            <AppIcon name="chevron-left" :size="14" />
          </button>
          <button
            type="button"
            class="btn-outline btn-sm"
            :disabled="pending || index === images.length - 1"
            :aria-label="t('admin.moveImageForward', { index: index + 1 })"
            @click="emit('move', index, 1)"
          >
            <AppIcon name="chevron-right" :size="14" />
          </button>
          <button
            type="button"
            class="btn-danger btn-sm"
            :disabled="pending"
            :aria-label="t('admin.removeImage', { index: index + 1 })"
            @click="emit('remove', index)"
          >
            <AppIcon name="trash" :size="14" />
          </button>
        </div>
      </li>
    </ul>
  </section>
</template>
