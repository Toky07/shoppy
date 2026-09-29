<script setup lang="ts">
import BrandMark from '@/shared/ui/BrandMark.vue'
import AuthBrandAside from './AuthBrandAside.vue'

defineProps<{
  title: string
  subtitle: string
}>()
</script>

<template>
  <div class="animate-fade-in grid items-stretch gap-8 lg:grid-cols-[1fr_0.85fr]">
    <div class="panel flex flex-col justify-center p-7 sm:p-10">
      <div class="mx-auto w-full max-w-sm">
        <span class="text-strong lg:hidden"><BrandMark :size="34" /></span>
        <h1 class="display-tight mt-6 text-3xl text-strong sm:text-4xl lg:mt-0">{{ title }}</h1>
        <p class="mt-3 text-sm text-muted">{{ subtitle }}</p>
        <div class="mt-8">
          <slot />
        </div>
        <p v-if="$slots.footer" class="mt-8 border-t border-line pt-6 text-center text-sm text-muted">
          <slot name="footer" />
        </p>
      </div>
    </div>
    <AuthBrandAside />
  </div>
</template>
