<script setup lang="ts">
defineProps<{
  src: string | null
  alt: string
}>()
</script>

<template>
  <div class="overflow-hidden bg-stone-100 h-full w-full">
    <img
      v-if="src"
      :src="src"
      :alt="alt"
      loading="lazy"
      class="h-full w-full object-cover"
    />
    <div v-else class="flex h-full w-full items-center justify-center text-sm text-stone-400">
      <i class="fa-regular fa-image text-2xl mb-2"></i>
    </div>
  </div>
</template>
