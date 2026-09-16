<script setup lang="ts">
import { computed } from 'vue'
import AppIcon from '@/shared/ui/AppIcon.vue'
import { useFavorites } from '../application/useFavorites'

const props = withDefaults(
  defineProps<{
    productId: string
    productName: string
    size?: 'sm' | 'lg'
  }>(),
  { size: 'sm' },
)

const { isFavorite, toggle } = useFavorites()
const active = computed(() => isFavorite(props.productId))
const label = computed(() =>
  active.value
    ? `Retirer ${props.productName} de ma liste d'envies`
    : `Ajouter ${props.productName} à ma liste d'envies`,
)
</script>

<template>
  <button
    type="button"
    class="inline-flex items-center justify-center rounded-full border transition-all duration-200 hover:scale-105"
    :class="[
      size === 'lg' ? 'size-12' : 'size-9',
      active
        ? 'border-danger/30 bg-danger-soft text-danger'
        : 'border-line bg-surface/90 text-faint backdrop-blur-sm hover:text-danger',
    ]"
    :aria-label="label"
    :aria-pressed="active"
    :title="label"
    @click.prevent.stop="toggle(productId)"
  >
    <AppIcon name="heart" :size="size === 'lg' ? 20 : 16" :filled="active" />
  </button>
</template>
