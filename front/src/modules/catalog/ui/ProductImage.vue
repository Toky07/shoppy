<script setup lang="ts">
import AppIcon from '@/shared/ui/AppIcon.vue'

defineProps<{
  src: string | null
  alt: string
}>()
</script>

<template>
  <div class="relative h-full w-full overflow-hidden bg-surface-inset">
    <img v-if="src" :src="src" :alt="alt" loading="lazy" class="h-full w-full object-cover" />
    <div
      v-else
      class="mesh flex h-full w-full flex-col items-center justify-center gap-2 text-faint"
      role="img"
      :aria-label="alt"
    >
      <AppIcon name="image" :size="26" />
      <span class="text-[0.65rem] font-semibold tracking-[0.15em] uppercase">Shoppy</span>
    </div>
  </div>
</template>
