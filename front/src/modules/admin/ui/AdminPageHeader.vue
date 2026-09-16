<script setup lang="ts">
import { RouterLink } from 'vue-router'
import AppIcon from '@/shared/ui/AppIcon.vue'
import type { IconName } from '@/shared/ui/icons'

defineProps<{
  title: string
  eyebrow?: string
  description?: string
  icon?: IconName
  backTo?: string
  backLabel?: string
}>()
</script>

<template>
  <header class="flex flex-wrap items-end justify-between gap-6">
    <div>
      <RouterLink
        v-if="backTo && backLabel"
        :to="backTo"
        class="inline-flex items-center gap-2 text-sm font-medium link-quiet"
      >
        <AppIcon name="arrow-left" :size="15" />
        {{ backLabel }}
      </RouterLink>

      <div class="mt-5 flex items-center gap-3">
        <span
          v-if="icon"
          class="flex size-11 items-center justify-center rounded-2xl border border-line bg-surface-inset text-accent-strong"
        >
          <AppIcon :name="icon" :size="20" />
        </span>
        <div>
          <p v-if="eyebrow" class="text-[0.7rem] font-semibold tracking-[0.16em] text-faint uppercase">
            {{ eyebrow }}
          </p>
          <h1 class="display-tight text-3xl text-strong sm:text-4xl">{{ title }}</h1>
        </div>
      </div>

      <p v-if="description" class="mt-3 max-w-xl text-sm text-muted">{{ description }}</p>
    </div>

    <div v-if="$slots.actions" class="flex flex-wrap items-center gap-3">
      <slot name="actions" />
    </div>
  </header>
</template>
