<script setup lang="ts">
withDefaults(
  defineProps<{
    size?: number
    label?: string
  }>(),
  {
    size: 28,
    label: 'Chargement',
  },
)
</script>

<template>
  <span
    class="inline-block shrink-0 rounded-full border-2 border-faint/25 border-t-accent shadow-[0_0_12px_-2px] shadow-accent/25 animate-orbit motion-reduce:animate-none"
    :style="{ width: `${size}px`, height: `${size}px` }"
    role="status"
    :aria-label="label"
  />
</template>
