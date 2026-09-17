<script setup lang="ts">
import { ref, watch } from 'vue'
import AppIcon from './AppIcon.vue'

const props = withDefaults(
  defineProps<{
    src: string | null
    alt: string
    loading?: 'lazy' | 'eager'
  }>(),
  { loading: 'lazy' },
)

const loaded = ref(false)
const failed = ref(false)

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
  <div class="relative h-full w-full overflow-hidden bg-surface-inset">
    <img
      v-if="src && !failed"
      :src="src"
      :alt="alt"
      :loading="loading"
      decoding="async"
      class="h-full w-full object-cover transition-opacity duration-300"
      :class="loaded ? 'opacity-100' : 'opacity-0'"
      @load="onLoad"
      @error="onError"
    />
    <div
      v-if="!src || failed || !loaded"
      class="mesh absolute inset-0 flex flex-col items-center justify-center gap-2 text-faint"
      :role="src && !failed ? 'presentation' : 'img'"
      :aria-hidden="Boolean(src && !failed)"
      :aria-label="src && !failed ? undefined : alt"
    >
      <AppIcon name="image" :size="26" />
    </div>
  </div>
</template>
