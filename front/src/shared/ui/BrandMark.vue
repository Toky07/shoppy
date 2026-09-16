<script setup lang="ts">
withDefaults(defineProps<{ size?: number }>(), { size: 36 })
</script>

<template>
  <svg
    :width="size"
    :height="size"
    viewBox="0 0 32 32"
    fill="none"
    aria-hidden="true"
    focusable="false"
    class="shrink-0"
  >
    <rect width="32" height="32" rx="10" fill="currentColor" />
    <path
      d="M21 10.6c-1.1-1.3-2.8-2-4.8-2-2.9 0-4.9 1.5-4.9 3.7 0 2 1.6 3 4.6 3.6 3.4.6 5.3 1.9 5.3 4.4 0 2.8-2.4 4.6-5.9 4.6-2.4 0-4.5-.9-5.7-2.4"
      stroke="var(--ui-accent)"
      stroke-width="2.4"
      stroke-linecap="round"
    />
    <circle cx="24.4" cy="8.2" r="2" fill="var(--ui-accent)" />
  </svg>
</template>
